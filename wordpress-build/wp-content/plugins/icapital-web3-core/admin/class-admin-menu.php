<?php
/**
 * WP Admin Menu — LLC Applications overview
 * @package icapital-web3-core
 */
defined( 'ABSPATH' ) || exit;

class ICapital_Admin_Menu {

	public function init() : void {
		add_action( 'admin_menu', [ $this, 'register_menu' ] );
	}

	public function register_menu() : void {
		add_menu_page(
			__( 'iCapital LLC Admin', 'icapital-web3-core' ),
			__( 'LLC Applications', 'icapital-web3-core' ),
			'manage_options',
			'icapital-llc-admin',
			[ $this, 'render_page' ],
			'dashicons-building',
			30
		);
		add_submenu_page( 'icapital-llc-admin', __( 'All Applications', 'icapital-web3-core' ), __( 'All Applications', 'icapital-web3-core' ), 'manage_options', 'icapital-llc-admin', [ $this, 'render_page' ] );
		add_submenu_page( 'icapital-llc-admin', __( 'Users', 'icapital-web3-core' ),            __( 'Users', 'icapital-web3-core' ),            'manage_options', 'icapital-users',     [ $this, 'render_users_page' ] );
	}

	public function render_page() : void {
		if ( ! current_user_can( 'manage_options' ) ) { wp_die( esc_html__( 'Unauthorized', 'icapital-web3-core' ) ); }
		global $wpdb;
		$table = $wpdb->prefix . 'icapital_llc_registrations';

		// Handle status update POST
		if ( 'POST' === $_SERVER['REQUEST_METHOD'] && isset( $_POST['_wpnonce'] ) ) {
			check_admin_referer( 'icapital_update_status' );
			$allowed = [ 'PENDING_PAYMENT', 'PROCESSING', 'APPROVED', 'REJECTED' ];
			$new_status = sanitize_text_field( wp_unslash( $_POST['status'] ?? '' ) );
			$llc_id     = absint( $_POST['llc_id'] ?? 0 );
			if ( in_array( $new_status, $allowed, true ) && $llc_id ) {
				$wpdb->update( $table, [ 'status' => $new_status ], [ 'id' => $llc_id ], [ '%s' ], [ '%d' ] ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			}
		}

		$rows = $wpdb->get_results( "SELECT * FROM $table ORDER BY created_at DESC" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.NotPrepared
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'iCapital Wyoming LLC — LLC Applications', 'icapital-web3-core' ); ?></h1>
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'ID', 'icapital-web3-core' ); ?></th>
						<th><?php esc_html_e( 'Company', 'icapital-web3-core' ); ?></th>
						<th><?php esc_html_e( 'User ID', 'icapital-web3-core' ); ?></th>
						<th><?php esc_html_e( 'State', 'icapital-web3-core' ); ?></th>
						<th><?php esc_html_e( 'Crypto', 'icapital-web3-core' ); ?></th>
						<th><?php esc_html_e( 'Status', 'icapital-web3-core' ); ?></th>
						<th><?php esc_html_e( 'Created', 'icapital-web3-core' ); ?></th>
						<th><?php esc_html_e( 'Update Status', 'icapital-web3-core' ); ?></th>
					</tr>
				</thead>
				<tbody>
				<?php if ( $rows ) : foreach ( $rows as $row ) : ?>
					<tr>
						<td><?php echo esc_html( $row->id ); ?></td>
						<td><strong><?php echo esc_html( $row->company_name ); ?></strong></td>
						<td><?php echo esc_html( $row->user_id ); ?></td>
						<td><?php echo esc_html( $row->state ); ?></td>
						<td><?php echo $row->crypto_protection ? '✅' : '—'; ?></td>
						<td><code><?php echo esc_html( $row->status ); ?></code></td>
						<td><?php echo esc_html( gmdate( 'Y-m-d', strtotime( $row->created_at ) ) ); ?></td>
						<td>
							<form method="POST">
								<?php wp_nonce_field( 'icapital_update_status' ); ?>
								<input type="hidden" name="llc_id" value="<?php echo esc_attr( $row->id ); ?>">
								<select name="status">
									<?php foreach ( [ 'PENDING_PAYMENT', 'PROCESSING', 'APPROVED', 'REJECTED' ] as $s ) : ?>
										<option value="<?php echo esc_attr( $s ); ?>" <?php selected( $row->status, $s ); ?>><?php echo esc_html( $s ); ?></option>
									<?php endforeach; ?>
								</select>
								<button class="button button-small"><?php esc_html_e( 'Save', 'icapital-web3-core' ); ?></button>
							</form>
						</td>
					</tr>
				<?php endforeach; else : ?>
					<tr><td colspan="8"><?php esc_html_e( 'No LLC applications yet.', 'icapital-web3-core' ); ?></td></tr>
				<?php endif; ?>
				</tbody>
			</table>
		</div>
		<?php
	}

	public function render_users_page() : void {
		if ( ! current_user_can( 'manage_options' ) ) { wp_die( esc_html__( 'Unauthorized', 'icapital-web3-core' ) ); }
		$users = get_users( [ 'role' => 'subscriber', 'number' => 200, 'orderby' => 'registered', 'order' => 'DESC' ] );
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'iCapital Wyoming LLC — Users', 'icapital-web3-core' ); ?></h1>
			<table class="wp-list-table widefat fixed striped">
				<thead><tr>
					<th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Wallet</th><th>Registered</th>
				</tr></thead>
				<tbody>
				<?php foreach ( $users as $u ) : ?>
					<tr>
						<td><?php echo esc_html( $u->ID ); ?></td>
						<td><?php echo esc_html( $u->first_name . ' ' . $u->last_name ); ?></td>
						<td><?php echo esc_html( $u->user_email ); ?></td>
						<td><?php echo esc_html( get_user_meta( $u->ID, 'phone', true ) ); ?></td>
						<td><code style="font-size:0.7rem;"><?php echo esc_html( get_user_meta( $u->ID, 'wallet_address', true ) ); ?></code></td>
						<td><?php echo esc_html( $u->user_registered ); ?></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<?php
	}
}
