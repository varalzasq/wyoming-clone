<?php
/**
 * SIWE (Sign-In With Ethereum) Handler
 * POST /wp-json/icapital/v1/auth/siwe
 * @package icapital-web3-core
 */
defined( 'ABSPATH' ) || exit;

class ICapital_SIWE {
	private const NAMESPACE = 'icapital/v1';

	public function register_routes() : void {
		register_rest_route( self::NAMESPACE, '/auth/siwe/nonce', [
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => [ $this, 'get_nonce' ],
			'permission_callback' => '__return_true',
		] );
		register_rest_route( self::NAMESPACE, '/auth/siwe', [
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => [ $this, 'authenticate' ],
			'permission_callback' => '__return_true',
			'args'                => [
				'address' => [ 'required' => true, 'sanitize_callback' => 'sanitize_text_field' ],
				'nonce'   => [ 'required' => true, 'sanitize_callback' => 'sanitize_text_field' ],
			],
		] );
		register_rest_route( self::NAMESPACE, '/auth/siwe/link', [
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => [ $this, 'link_wallet' ],
			'permission_callback' => 'is_user_logged_in',
			'args'                => [ 'address' => [ 'required' => true, 'sanitize_callback' => 'sanitize_text_field' ] ],
		] );
	}

	public function get_nonce( WP_REST_Request $request ) : WP_REST_Response {
		$nonce = wp_generate_password( 16, false );
		set_transient( 'siwe_nonce_' . $nonce, true, 300 );
		return rest_ensure_response( [ 'nonce' => $nonce, 'expires' => gmdate( 'c', time() + 300 ) ] );
	}

	public function authenticate( WP_REST_Request $request ) : WP_REST_Response|WP_Error {
		$address   = strtolower( $request->get_param( 'address' ) );
		$nonce     = $request->get_param( 'nonce' );
		$cache_key = 'siwe_nonce_' . $nonce;

		if ( ! get_transient( $cache_key ) ) {
			return new WP_Error( 'invalid_nonce', __( 'SIWE nonce is invalid or expired.', 'icapital-web3-core' ), [ 'status' => 401 ] );
		}
		delete_transient( $cache_key );

		$users = get_users( [ 'meta_key' => 'wallet_address', 'meta_value' => $address, 'number' => 1 ] ); // phpcs:ignore WordPress.DB.SlowDBQuery

		if ( ! empty( $users ) ) {
			$user = $users[0];
		} else {
			$user_id = wp_insert_user( [
				'user_login'   => 'wallet_' . substr( $address, 2, 8 ),
				'user_email'   => substr( $address, 2, 8 ) . '@wallet.icapital.local',
				'user_pass'    => wp_generate_password( 32 ),
				'display_name' => 'Wallet ' . strtoupper( substr( $address, 2, 6 ) ),
				'role'         => 'subscriber',
			] );
			if ( is_wp_error( $user_id ) ) { return $user_id; }
			update_user_meta( $user_id, 'wallet_address', $address );
			$user = get_userdata( $user_id );
		}

		wp_set_auth_cookie( $user->ID, true );
		return rest_ensure_response( [ 'success' => true, 'token' => wp_create_nonce( 'wp_rest' ), 'user' => [ 'id' => $user->ID, 'walletAddress' => $address ] ] );
	}

	public function link_wallet( WP_REST_Request $request ) : WP_REST_Response {
		$address = strtolower( $request->get_param( 'address' ) );
		update_user_meta( get_current_user_id(), 'wallet_address', $address );
		return rest_ensure_response( [ 'success' => true, 'address' => $address ] );
	}
}
