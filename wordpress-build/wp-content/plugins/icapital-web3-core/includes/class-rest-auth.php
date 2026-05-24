<?php
/**
 * REST Auth Endpoints: /wp-json/icapital/v1/login & /register
 * @package icapital-web3-core
 */
defined( 'ABSPATH' ) || exit;

class ICapital_REST_Auth {

	private const NAMESPACE = 'icapital/v1';
	private const RATE_LIMIT_MAX     = 5;
	private const RATE_LIMIT_WINDOW  = 900; // 15 minutes

	public function register_routes() : void {
		register_rest_route( self::NAMESPACE, '/login', [
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => [ $this, 'login' ],
			'permission_callback' => '__return_true',
			'args'                => [
				'email'    => [ 'required' => true, 'sanitize_callback' => 'sanitize_email' ],
				'password' => [ 'required' => true ],
			],
		] );

		register_rest_route( self::NAMESPACE, '/register', [
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => [ $this, 'register' ],
			'permission_callback' => '__return_true',
			'args'                => [
				'email'       => [ 'required' => true, 'sanitize_callback' => 'sanitize_email' ],
				'password'    => [ 'required' => true ],
				'firstName'   => [ 'required' => true, 'sanitize_callback' => 'sanitize_text_field' ],
				'lastName'    => [ 'required' => true, 'sanitize_callback' => 'sanitize_text_field' ],
				'phone'       => [ 'sanitize_callback' => 'sanitize_text_field' ],
				'companyName' => [ 'required' => true, 'sanitize_callback' => 'sanitize_text_field' ],
				'state'       => [ 'sanitize_callback' => 'sanitize_text_field' ],
				'industry'    => [ 'sanitize_callback' => 'sanitize_text_field' ],
			],
		] );

		register_rest_route( self::NAMESPACE, '/logout', [
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => [ $this, 'logout' ],
			'permission_callback' => 'is_user_logged_in',
		] );
	}

	public function login( WP_REST_Request $request ) : WP_REST_Response|WP_Error {
		$email    = $request->get_param( 'email' );
		$password = $request->get_param( 'password' );
		$ip_key   = 'icapital_rate_' . md5( $_SERVER['REMOTE_ADDR'] ?? '' );

		// Rate limiting
		$attempts = (int) get_transient( $ip_key );
		if ( $attempts >= self::RATE_LIMIT_MAX ) {
			return new WP_Error( 'too_many_requests', __( 'Too many login attempts. Try again in 15 minutes.', 'icapital-web3-core' ), [ 'status' => 429 ] );
		}

		$user = wp_authenticate( $email, $password );
		if ( is_wp_error( $user ) ) {
			set_transient( $ip_key, $attempts + 1, self::RATE_LIMIT_WINDOW );
			return new WP_Error( 'invalid_credentials', __( 'Invalid email or password.', 'icapital-web3-core' ), [ 'status' => 401 ] );
		}

		delete_transient( $ip_key );
		wp_set_auth_cookie( $user->ID, true );

		return rest_ensure_response( [
			'success' => true,
			'token'   => wp_create_nonce( 'wp_rest' ),
			'user'    => [
				'id'          => $user->ID,
				'email'       => $user->user_email,
				'firstName'   => get_user_meta( $user->ID, 'first_name', true ),
				'lastName'    => get_user_meta( $user->ID, 'last_name', true ),
				'displayName' => $user->display_name,
				'role'        => implode( ',', $user->roles ),
			],
		] );
	}

	public function register( WP_REST_Request $request ) : WP_REST_Response|WP_Error {
		$email    = $request->get_param( 'email' );
		$password = $request->get_param( 'password' );
		$first    = $request->get_param( 'firstName' );
		$last     = $request->get_param( 'lastName' );
		$company  = $request->get_param( 'companyName' );
		$phone    = $request->get_param( 'phone' );
		$state    = $request->get_param( 'state' )    ?: 'WY';
		$industry = $request->get_param( 'industry' ) ?: '';
		$crypto   = (bool) $request->get_param( 'cryptoProtection' );

		if ( ! is_email( $email ) ) {
			return new WP_Error( 'invalid_email', __( 'Please provide a valid email address.', 'icapital-web3-core' ), [ 'status' => 400 ] );
		}
		if ( email_exists( $email ) ) {
			return new WP_Error( 'email_exists', __( 'An account with this email already exists.', 'icapital-web3-core' ), [ 'status' => 409 ] );
		}

		$user_id = wp_insert_user( [
			'user_login'   => $email,
			'user_email'   => $email,
			'user_pass'    => $password,
			'first_name'   => $first,
			'last_name'    => $last,
			'display_name' => trim( $first . ' ' . $last ),
			'role'         => 'subscriber',
		] );

		if ( is_wp_error( $user_id ) ) {
			return $user_id;
		}

		update_user_meta( $user_id, 'phone', sanitize_text_field( $phone ) );

		// Create LLC DB record
		global $wpdb;
		$wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->prefix . 'icapital_llc_registrations',
			[
				'user_id'          => $user_id,
				'company_name'     => $company,
				'state'            => $state,
				'industry'         => $industry,
				'crypto_protection'=> $crypto ? 1 : 0,
				'status'           => 'PENDING_PAYMENT',
			],
			[ '%d', '%s', '%s', '%s', '%d', '%s' ]
		);

		// Also create CPT post
		$post_id = wp_insert_post( [
			'post_type'   => 'icapital_llc',
			'post_title'  => sanitize_text_field( $company ),
			'post_status' => 'publish',
			'post_author' => $user_id,
		] );
		if ( $post_id && ! is_wp_error( $post_id ) ) {
			update_post_meta( $post_id, 'user_id',           $user_id );
			update_post_meta( $post_id, 'company_name',      $company );
			update_post_meta( $post_id, 'state',             $state );
			update_post_meta( $post_id, 'industry',          $industry );
			update_post_meta( $post_id, 'crypto_protection', $crypto );
			update_post_meta( $post_id, 'status',            'PENDING_PAYMENT' );
		}

		wp_set_auth_cookie( $user_id, true );

		return rest_ensure_response( [
			'success' => true,
			'token'   => wp_create_nonce( 'wp_rest' ),
			'message' => __( 'Account created successfully.', 'icapital-web3-core' ),
			'user'    => [ 'id' => $user_id, 'email' => $email, 'firstName' => $first, 'lastName' => $last ],
		] );
	}

	public function logout( WP_REST_Request $request ) : WP_REST_Response {
		wp_logout();
		return rest_ensure_response( [ 'success' => true ] );
	}
}
