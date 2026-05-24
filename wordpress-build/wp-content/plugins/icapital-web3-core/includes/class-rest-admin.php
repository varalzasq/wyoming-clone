<?php
/**
 * REST Admin Endpoints (gated to manage_options)
 * GET  /wp-json/icapital/v1/admin/users
 * GET  /wp-json/icapital/v1/admin/submissions
 * PATCH /wp-json/icapital/v1/admin/llc/{id}/status
 * @package icapital-web3-core
 */
defined( 'ABSPATH' ) || exit;

class ICapital_REST_Admin {

	private const NAMESPACE = 'icapital/v1';

	public function register_routes() : void {
		$auth = [ $this, 'check_admin' ];

		register_rest_route( self::NAMESPACE, '/admin/users', [
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => [ $this, 'get_users' ],
			'permission_callback' => $auth,
		] );

		register_rest_route( self::NAMESPACE, '/admin/submissions', [
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => [ $this, 'get_submissions' ],
			'permission_callback' => $auth,
		] );

		register_rest_route( self::NAMESPACE, '/admin/llc/(?P<id>\d+)/status', [
			'methods'             => 'PATCH',
			'callback'            => [ $this, 'update_llc_status' ],
			'permission_callback' => $auth,
			'args'                => [
				'id'     => [ 'validate_callback' => static fn($v) => is_numeric($v) ],
				'status' => [ 'required' => true, 'sanitize_callback' => 'sanitize_text_field' ],
			],
		] );

		register_rest_route( self::NAMESPACE, '/admin/stats', [
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => [ $this, 'get_stats' ],
			'permission_callback' => $auth,
		] );
	}

	public function check_admin() : bool {
		return current_user_can( 'manage_options' );
	}

	public function get_users( WP_REST_Request $request ) : WP_REST_Response {
		global $wpdb;
		$llc_t = $wpdb->prefix . 'icapital_llc_registrations';

		$users = get_users( [ 'role' => 'subscriber', 'number' => 200, 'orderby' => 'registered', 'order' => 'DESC' ] );

		$data = array_map( static function ( WP_User $u ) use ( $wpdb, $llc_t ) {
			$llc_count = (int) $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
				$wpdb->prepare( "SELECT COUNT(*) FROM $llc_t WHERE user_id = %d", $u->ID ) // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			);
			return [
				'id'         => $u->ID,
				'email'      => $u->user_email,
				'firstName'  => get_user_meta( $u->ID, 'first_name', true ),
				'lastName'   => get_user_meta( $u->ID, 'last_name',  true ),
				'phone'      => get_user_meta( $u->ID, 'phone', true ),
				'registered' => $u->user_registered,
				'llcCount'   => $llc_count,
			];
		}, $users );

		return rest_ensure_response( [ 'data' => $data ] );
	}

	public function get_submissions( WP_REST_Request $request ) : WP_REST_Response {
		global $wpdb;
		$table = $wpdb->prefix . 'icapital_llc_registrations';
		$rows  = $wpdb->get_results( "SELECT * FROM $table ORDER BY created_at DESC LIMIT 200" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.NotPrepared
		return rest_ensure_response( [ 'data' => $rows ] );
	}

	public function update_llc_status( WP_REST_Request $request ) : WP_REST_Response|WP_Error {
		global $wpdb;
		$allowed = [ 'PENDING_PAYMENT', 'PROCESSING', 'APPROVED', 'REJECTED' ];
		$status  = $request->get_param( 'status' );
		$id      = absint( $request->get_param( 'id' ) );

		if ( ! in_array( $status, $allowed, true ) ) {
			return new WP_Error( 'invalid_status', __( 'Invalid status value.', 'icapital-web3-core' ), [ 'status' => 400 ] );
		}

		$updated = $wpdb->update( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->prefix . 'icapital_llc_registrations',
			[ 'status' => $status ],
			[ 'id'     => $id ],
			[ '%s' ],
			[ '%d' ]
		);

		if ( false === $updated ) {
			return new WP_Error( 'db_error', __( 'Failed to update status.', 'icapital-web3-core' ), [ 'status' => 500 ] );
		}

		// Also update CPT post meta
		$post_id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM {$wpdb->prefix}icapital_llc_registrations WHERE id = %d", $id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		if ( $post_id ) {
			update_post_meta( (int) $post_id, 'status', $status );
		}

		return rest_ensure_response( [ 'success' => true, 'status' => $status ] );
	}

	public function get_stats( WP_REST_Request $request ) : WP_REST_Response {
		global $wpdb;
		$llc_t = $wpdb->prefix . 'icapital_llc_registrations';

		return rest_ensure_response( [
			'data' => [
				'totalUsers'   => (int) count_users()['total_users'],
				'totalLLCs'    => (int) $wpdb->get_var( "SELECT COUNT(*) FROM $llc_t" ), // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.NotPrepared
				'pendingLLCs'  => (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $llc_t WHERE status = %s", 'PENDING_PAYMENT' ) ), // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				'approvedLLCs' => (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $llc_t WHERE status = %s", 'APPROVED' ) ), // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			],
		] );
	}
}
