<?php
/**
 * REST LLC Registration Endpoint
 * POST /wp-json/icapital/v1/llc/register
 * POST /wp-json/icapital/v1/llc/register-internal (called from PHP template)
 * @package icapital-web3-core
 */
defined( 'ABSPATH' ) || exit;

class ICapital_REST_LLC {

	private const NAMESPACE = 'icapital/v1';

	public function register_routes() : void {
		// Public registration via React app
		register_rest_route( self::NAMESPACE, '/llc/register', [
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => [ $this, 'register_llc' ],
			'permission_callback' => 'is_user_logged_in',
			'args'                => [
				'companyName'       => [ 'required' => true, 'sanitize_callback' => 'sanitize_text_field' ],
				'state'             => [ 'sanitize_callback' => 'sanitize_text_field' ],
				'industry'          => [ 'sanitize_callback' => 'sanitize_text_field' ],
				'cryptoProtection'  => [],
			],
		] );

		// Internal call from PHP template form (passes userId directly)
		register_rest_route( self::NAMESPACE, '/llc/register-internal', [
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => [ $this, 'register_internal' ],
			'permission_callback' => static fn() => current_user_can( 'read' ),
		] );
	}

	public function register_llc( WP_REST_Request $request ) : WP_REST_Response|WP_Error {
		global $wpdb;
		$user_id = get_current_user_id();
		$company = $request->get_param( 'companyName' );
		$state   = $request->get_param( 'state' )   ?: 'WY';
		$industry= $request->get_param( 'industry' ) ?: '';
		$crypto  = (bool) $request->get_param( 'cryptoProtection' );

		return $this->create_llc_record( $user_id, $company, $state, $industry, $crypto );
	}

	public function register_internal( WP_REST_Request $request ) : WP_REST_Response|WP_Error {
		$body    = $request->get_json_params();
		$user_id = absint( $body['userId']       ?? 0 );
		$company = sanitize_text_field( $body['companyName'] ?? '' );
		$state   = sanitize_text_field( $body['state']       ?? 'WY' );
		$industry= sanitize_text_field( $body['industry']    ?? '' );
		$crypto  = (bool) ( $body['cryptoProtection'] ?? false );

		if ( ! $user_id || empty( $company ) ) {
			return new WP_Error( 'missing_fields', 'Missing required fields.', [ 'status' => 400 ] );
		}

		return $this->create_llc_record( $user_id, $company, $state, $industry, $crypto );
	}

	private function create_llc_record( int $user_id, string $company, string $state, string $industry, bool $crypto ) : WP_REST_Response|WP_Error {
		global $wpdb;
		$table = $wpdb->prefix . 'icapital_llc_registrations';

		$inserted = $wpdb->insert( $table, [ // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			'user_id'           => $user_id,
			'company_name'      => $company,
			'state'             => $state,
			'industry'          => $industry,
			'crypto_protection' => $crypto ? 1 : 0,
			'status'            => 'PENDING_PAYMENT',
			'state_fee'         => 120.00,
		], [ '%d', '%s', '%s', '%s', '%d', '%s', '%f' ] );

		if ( ! $inserted ) {
			return new WP_Error( 'db_error', __( 'Failed to create LLC record.', 'icapital-web3-core' ), [ 'status' => 500 ] );
		}

		$llc_id = $wpdb->insert_id;

		// Create CPT post
		$post_id = wp_insert_post( [
			'post_type'   => 'icapital_llc',
			'post_title'  => $company,
			'post_status' => 'publish',
			'post_author' => $user_id,
		] );

		if ( $post_id && ! is_wp_error( $post_id ) ) {
			update_post_meta( $post_id, 'llc_db_id',        $llc_id );
			update_post_meta( $post_id, 'user_id',           $user_id );
			update_post_meta( $post_id, 'status',            'PENDING_PAYMENT' );
			update_post_meta( $post_id, 'crypto_protection', $crypto );

			// Update DB record with post_id
			$wpdb->update( $table, [ 'post_id' => $post_id ], [ 'id' => $llc_id ], [ '%d' ], [ '%d' ] ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		}

		// Send confirmation email
		$user  = get_userdata( $user_id );
		$to    = $user ? $user->user_email : '';
		$subj  = sprintf( __( 'Your %s LLC Application Has Been Received', 'icapital-web3-core' ), $company );
		$msg   = sprintf(
			__( "Dear %s,\n\nThank you for submitting your Wyoming LLC application for %s.\n\nYour application is now PENDING_PAYMENT. Please log in to your dashboard to complete your payment and proceed with formation.\n\niCapital Wyoming LLC\nicapitalwyomingllc.com", 'icapital-web3-core' ),
			$user ? $user->first_name : 'Valued Client',
			$company
		);
		if ( $to ) {
			wp_mail( $to, $subj, $msg );
		}

		return rest_ensure_response( [
			'success' => true,
			'llcId'   => $llc_id,
			'postId'  => $post_id,
		] );
	}
}
