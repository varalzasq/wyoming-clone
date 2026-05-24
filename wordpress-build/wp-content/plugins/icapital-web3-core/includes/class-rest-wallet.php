<?php
/**
 * REST Wallet Endpoints
 * GET  /wp-json/icapital/v1/wallet/balances
 * GET  /wp-json/icapital/v1/wallet/transactions
 * POST /wp-json/icapital/v1/wallet/send
 * POST /wp-json/icapital/v1/wallet/deposit
 * @package icapital-web3-core
 */
defined( 'ABSPATH' ) || exit;

class ICapital_REST_Wallet {

	private const NAMESPACE = 'icapital/v1';

	public function register_routes() : void {
		$auth = [ $this, 'check_auth' ];

		register_rest_route( self::NAMESPACE, '/wallet/balances', [
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => [ $this, 'get_balances' ],
			'permission_callback' => $auth,
		] );

		register_rest_route( self::NAMESPACE, '/wallet/transactions', [
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => [ $this, 'get_transactions' ],
			'permission_callback' => $auth,
		] );

		register_rest_route( self::NAMESPACE, '/wallet/send', [
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => [ $this, 'send' ],
			'permission_callback' => $auth,
			'args'                => [
				'asset'  => [ 'required' => true, 'sanitize_callback' => 'sanitize_text_field' ],
				'amount' => [ 'required' => true, 'validate_callback' => static fn($v) => is_numeric($v) && $v > 0 ],
				'to'     => [ 'required' => true, 'sanitize_callback' => 'sanitize_text_field' ],
			],
		] );

		register_rest_route( self::NAMESPACE, '/wallet/deposit', [
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => [ $this, 'deposit' ],
			'permission_callback' => $auth,
			'args'                => [
				'asset'   => [ 'required' => true, 'sanitize_callback' => 'sanitize_text_field' ],
				'amount'  => [ 'required' => true, 'validate_callback' => static fn($v) => is_numeric($v) && $v > 0 ],
				'tx_hash' => [ 'sanitize_callback' => 'sanitize_text_field' ],
			],
		] );
	}

	public function check_auth() : bool {
		return is_user_logged_in();
	}

	public function get_balances( WP_REST_Request $request ) : WP_REST_Response {
		global $wpdb;
		$user_id       = get_current_user_id();
		$bal_table     = $wpdb->prefix . 'icapital_virtual_balances';
		$asset_table   = $wpdb->prefix . 'icapital_wallet_assets';

		$rows = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->prepare( // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"SELECT b.asset_symbol, b.amount, a.name, a.network, a.icon_color, a.price, a.price_change
				 FROM $bal_table b
				 LEFT JOIN $asset_table a ON a.symbol = b.asset_symbol
				 WHERE b.user_id = %d",
				$user_id
			)
		);

		$balances = array_map( static function ( $row ) {
			$value = (float) $row->amount * (float) $row->price;
			return [
				'symbol'      => $row->asset_symbol,
				'name'        => $row->name,
				'network'     => $row->network,
				'iconColor'   => $row->icon_color,
				'balance'     => (float) $row->amount,
				'price'       => (float) $row->price,
				'priceChange' => (float) $row->price_change,
				'value'       => round( $value, 2 ),
			];
		}, $rows );

		$total_value = array_sum( array_column( $balances, 'value' ) );

		return rest_ensure_response( [
			'data' => [
				'balances'   => $balances,
				'totalValue' => round( $total_value, 2 ),
			],
		] );
	}

	public function get_transactions( WP_REST_Request $request ) : WP_REST_Response {
		global $wpdb;
		$user_id = get_current_user_id();
		$table   = $wpdb->prefix . 'icapital_transactions';

		$rows = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->prepare( // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"SELECT * FROM $table WHERE user_id = %d ORDER BY created_at DESC LIMIT 50",
				$user_id
			)
		);

		return rest_ensure_response( [ 'data' => $rows ] );
	}

	public function send( WP_REST_Request $request ) : WP_REST_Response|WP_Error {
		global $wpdb;
		$user_id = get_current_user_id();
		$asset   = $request->get_param( 'asset' );
		$amount  = (float) $request->get_param( 'amount' );
		$bal_t   = $wpdb->prefix . 'icapital_virtual_balances';
		$tx_t    = $wpdb->prefix . 'icapital_transactions';

		// Check balance
		$current = (float) $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->prepare( "SELECT amount FROM $bal_t WHERE user_id = %d AND asset_symbol = %s", $user_id, $asset ) // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		);

		if ( $current < $amount ) {
			return new WP_Error( 'insufficient_balance', __( 'Insufficient balance.', 'icapital-web3-core' ), [ 'status' => 400 ] );
		}

		$wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->prepare( "UPDATE $bal_t SET amount = amount - %f WHERE user_id = %d AND asset_symbol = %s", $amount, $user_id, $asset ) // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		);

		$wpdb->insert( $tx_t, [ // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			'user_id'      => $user_id,
			'type'         => 'SEND',
			'asset_symbol' => $asset,
			'amount'       => $amount,
			'status'       => 'COMPLETED',
		], [ '%d', '%s', '%s', '%f', '%s' ] );

		return rest_ensure_response( [ 'success' => true, 'message' => __( 'Transfer initiated.', 'icapital-web3-core' ) ] );
	}

	public function deposit( WP_REST_Request $request ) : WP_REST_Response {
		global $wpdb;
		$user_id = get_current_user_id();
		$asset   = $request->get_param( 'asset' );
		$amount  = (float) $request->get_param( 'amount' );
		$tx_hash = $request->get_param( 'tx_hash' );
		$bal_t   = $wpdb->prefix . 'icapital_virtual_balances';
		$tx_t    = $wpdb->prefix . 'icapital_transactions';

		// Upsert balance
		$wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->prepare( // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"INSERT INTO $bal_t (user_id, asset_symbol, amount)
				 VALUES (%d, %s, %f)
				 ON DUPLICATE KEY UPDATE amount = amount + %f",
				$user_id, $asset, $amount, $amount
			)
		);

		$wpdb->insert( $tx_t, [ // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			'user_id'      => $user_id,
			'type'         => 'DEPOSIT',
			'asset_symbol' => $asset,
			'amount'       => $amount,
			'status'       => 'COMPLETED',
			'tx_hash'      => $tx_hash,
		], [ '%d', '%s', '%s', '%f', '%s', '%s' ] );

		return rest_ensure_response( [ 'success' => true ] );
	}
}
