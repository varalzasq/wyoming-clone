<?php
/**
 * REST Dashboard Endpoints
 * GET /wp-json/icapital/v1/user
 * GET /wp-json/icapital/v1/llc-stats
 * GET /wp-json/icapital/v1/llc-list
 * @package icapital-web3-core
 */
defined( 'ABSPATH' ) || exit;

class ICapital_REST_Dashboard {

	private const NAMESPACE = 'icapital/v1';

	public function register_routes() : void {
		$auth = [ $this, 'check_auth' ];

		register_rest_route( self::NAMESPACE, '/user', [
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => [ $this, 'get_user' ],
			'permission_callback' => $auth,
		] );

		register_rest_route( self::NAMESPACE, '/llc-stats', [
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => [ $this, 'get_llc_stats' ],
			'permission_callback' => $auth,
		] );

		register_rest_route( self::NAMESPACE, '/llc-list', [
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => [ $this, 'get_llc_list' ],
			'permission_callback' => $auth,
		] );
	}

	public function check_auth() : bool {
		return is_user_logged_in();
	}

	public function get_user( WP_REST_Request $request ) : WP_REST_Response {
		$user = wp_get_current_user();
		return rest_ensure_response( [
			'data' => [
				'id'        => $user->ID,
				'firstName' => get_user_meta( $user->ID, 'first_name', true ),
				'lastName'  => get_user_meta( $user->ID, 'last_name',  true ),
				'email'     => $user->user_email,
				'phone'     => get_user_meta( $user->ID, 'phone', true ),
				'role'      => implode( ',', $user->roles ),
			],
		] );
	}

	public function get_llc_stats( WP_REST_Request $request ) : WP_REST_Response {
		global $wpdb;
		$user_id = get_current_user_id();
		$table   = $wpdb->prefix . 'icapital_llc_registrations';

		$rows = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->prepare( "SELECT status FROM $table WHERE user_id = %d", $user_id ) // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		);

		$stats = [ 'total' => 0, 'approved' => 0, 'pending' => 0, 'rejected' => 0, 'processing' => 0 ];
		foreach ( $rows as $row ) {
			$stats['total']++;
			switch ( $row->status ) {
				case 'APPROVED':        $stats['approved']++;   break;
				case 'PENDING_PAYMENT': $stats['pending']++;    break;
				case 'REJECTED':        $stats['rejected']++;   break;
				case 'PROCESSING':      $stats['processing']++; break;
			}
		}

		return rest_ensure_response( [ 'data' => $stats ] );
	}

	public function get_llc_list( WP_REST_Request $request ) : WP_REST_Response {
		global $wpdb;
		$user_id = get_current_user_id();
		$table   = $wpdb->prefix . 'icapital_llc_registrations';

		$rows = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->prepare( "SELECT * FROM $table WHERE user_id = %d ORDER BY created_at DESC", $user_id ) // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		);

		$data = array_map( static function ( $row ) {
			return [
				'id'                    => (int) $row->id,
				'companyName'           => $row->company_name,
				'entityType'            => 'LLC',
				'state'                 => $row->state,
				'status'                => $row->status,
				'date'                  => gmdate( 'Y-m-d', strtotime( $row->created_at ) ),
				'stateFee'              => (float) $row->state_fee,
				'cryptoProtectionActive'=> (bool) $row->crypto_protection,
			];
		}, $rows );

		return rest_ensure_response( [ 'data' => $data ] );
	}
}
