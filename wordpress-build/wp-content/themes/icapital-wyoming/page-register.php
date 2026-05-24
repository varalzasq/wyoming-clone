<?php
/**
 * Template Name: LLC Registration
 * @package icapital-wyoming
 */
if ( is_user_logged_in() ) {
	wp_safe_redirect( esc_url( home_url( '/dashboard' ) ) );
	exit;
}
get_header();
$error   = '';
$success = false;

if ( 'POST' === $_SERVER['REQUEST_METHOD'] ) {
	if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'icapital_register' ) ) {
		$error = __( 'Security check failed. Please try again.', 'icapital-wyoming' );
	} else {
		// Sanitize all inputs
		$first  = sanitize_text_field( wp_unslash( $_POST['firstName'] ?? '' ) );
		$last   = sanitize_text_field( wp_unslash( $_POST['lastName']  ?? '' ) );
		$email  = sanitize_email( wp_unslash( $_POST['email']     ?? '' ) );
		$phone  = sanitize_text_field( wp_unslash( $_POST['phone']     ?? '' ) );
		$pass   = isset( $_POST['password'] ) ? wp_unslash( $_POST['password'] ) : '';
		$company= sanitize_text_field( wp_unslash( $_POST['companyName'] ?? '' ) );

		if ( empty( $first ) || empty( $last ) || empty( $email ) || empty( $pass ) || empty( $company ) ) {
			$error = __( 'Please fill in all required fields.', 'icapital-wyoming' );
		} elseif ( ! is_email( $email ) ) {
			$error = __( 'Please enter a valid email address.', 'icapital-wyoming' );
		} elseif ( email_exists( $email ) ) {
			$error = __( 'An account with this email already exists. Please log in.', 'icapital-wyoming' );
		} else {
			// Create WordPress user
			$user_id = wp_insert_user( [
				'user_login'   => $email,
				'user_email'   => $email,
				'user_pass'    => $pass,
				'first_name'   => $first,
				'last_name'    => $last,
				'display_name' => $first . ' ' . $last,
				'role'         => 'subscriber',
			] );

			if ( is_wp_error( $user_id ) ) {
				$error = $user_id->get_error_message();
			} else {
				// Save additional meta
				update_user_meta( $user_id, 'phone',      $phone );
				update_user_meta( $user_id, 'company',    $company );

				// Fire REST endpoint to create LLC in plugin
				$rest_url  = rest_url( 'icapital/v1/llc/register-internal' );
				$llc_state = sanitize_text_field( wp_unslash( $_POST['llcState'] ?? 'WY' ) );
				$industry  = sanitize_text_field( wp_unslash( $_POST['industry'] ?? '' ) );
				$crypto    = isset( $_POST['cryptoProtection'] ) ? 1 : 0;

				wp_remote_post( $rest_url, [
					'body'    => wp_json_encode( [
						'userId'              => $user_id,
						'companyName'         => $company,
						'state'               => $llc_state,
						'industry'            => $industry,
						'cryptoProtection'    => $crypto,
					] ),
					'headers' => [ 'Content-Type' => 'application/json', 'X-WP-Nonce' => wp_create_nonce( 'wp_rest' ) ],
					'sslverify' => false,
				] );

				// Log user in
				wp_set_auth_cookie( $user_id, true );
				wp_safe_redirect( esc_url( home_url( '/success' ) ) );
				exit;
			}
		}
	}
}
?>

<main id="main-content" role="main" style="min-height:80vh;padding:3rem 1rem;">
  <div style="max-width:40rem;margin:0 auto;">

    <!-- Privacy Banner -->
    <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:0.75rem;padding:1rem 1.25rem;margin-bottom:2rem;display:flex;gap:0.75rem;align-items:flex-start;">
      <span style="font-size:1.25rem;flex-shrink:0;">🔒</span>
      <div>
        <strong style="display:block;font-size:0.875rem;color:#1e40af;"><?php esc_html_e( '100% Privacy Protection Guarantee', 'icapital-wyoming' ); ?></strong>
        <p style="font-size:0.8125rem;color:#1d4ed8;margin:0.25rem 0 0;"><?php esc_html_e( 'Your personal information is strictly confidential. It is encrypted, never shared with third-party databases, and heavily protected under Wyoming state privacy laws by iCapital Wyoming LLC.', 'icapital-wyoming' ); ?></p>
      </div>
    </div>

    <h1 style="font-size:1.875rem;font-weight:800;color:var(--text-primary);text-align:center;margin-bottom:0.5rem;"><?php esc_html_e( 'Start Your iCapital Wyoming LLC', 'icapital-wyoming' ); ?></h1>
    <p style="text-align:center;color:var(--text-secondary);margin-bottom:2rem;"><?php esc_html_e( 'Create your account and formulate your business securely.', 'icapital-wyoming' ); ?></p>

    <div class="feature-card" style="padding:2rem;">
      <?php if ( $error ) : ?>
        <div class="alert alert--error"><?php echo esc_html( $error ); ?></div>
      <?php endif; ?>

      <form method="POST" action="" novalidate>
        <?php wp_nonce_field( 'icapital_register' ); ?>

        <div class="form-section-title"><?php esc_html_e( 'Personal Information', 'icapital-wyoming' ); ?></div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
          <div class="form-group">
            <label class="form-label" for="firstName"><?php esc_html_e( 'First Name *', 'icapital-wyoming' ); ?></label>
            <input class="theme-input" type="text" id="firstName" name="firstName" required placeholder="John" value="<?php echo isset($_POST['firstName']) ? esc_attr(sanitize_text_field($_POST['firstName'])) : ''; ?>">
          </div>
          <div class="form-group">
            <label class="form-label" for="lastName"><?php esc_html_e( 'Last Name *', 'icapital-wyoming' ); ?></label>
            <input class="theme-input" type="text" id="lastName" name="lastName" required placeholder="Doe" value="<?php echo isset($_POST['lastName']) ? esc_attr(sanitize_text_field($_POST['lastName'])) : ''; ?>">
          </div>
        </div>
        <div class="form-group">
          <label class="form-label" for="reg-email"><?php esc_html_e( 'Email Address *', 'icapital-wyoming' ); ?></label>
          <input class="theme-input" type="email" id="reg-email" name="email" required placeholder="you@example.com" value="<?php echo isset($_POST['email']) ? esc_attr(sanitize_email($_POST['email'])) : ''; ?>">
        </div>
        <div class="form-group">
          <label class="form-label" for="phone"><?php esc_html_e( 'Phone Number', 'icapital-wyoming' ); ?></label>
          <input class="theme-input" type="tel" id="phone" name="phone" placeholder="+1 (555) 000-0000" value="<?php echo isset($_POST['phone']) ? esc_attr(sanitize_text_field($_POST['phone'])) : ''; ?>">
        </div>
        <div class="form-group">
          <label class="form-label" for="reg-password"><?php esc_html_e( 'Password *', 'icapital-wyoming' ); ?></label>
          <input class="theme-input" type="password" id="reg-password" name="password" required placeholder="Min. 8 characters">
        </div>

        <div class="form-section-title"><?php esc_html_e( 'LLC Details', 'icapital-wyoming' ); ?></div>
        <div class="form-group">
          <label class="form-label" for="companyName"><?php esc_html_e( 'Company Name *', 'icapital-wyoming' ); ?></label>
          <input class="theme-input" type="text" id="companyName" name="companyName" required placeholder="My Business LLC" value="<?php echo isset($_POST['companyName']) ? esc_attr(sanitize_text_field($_POST['companyName'])) : ''; ?>">
        </div>
        <div class="form-group">
          <label class="form-label" for="industry"><?php esc_html_e( 'Industry', 'icapital-wyoming' ); ?></label>
          <input class="theme-input" type="text" id="industry" name="industry" placeholder="e.g. Real Estate, Tech, Crypto" value="<?php echo isset($_POST['industry']) ? esc_attr(sanitize_text_field($_POST['industry'])) : ''; ?>">
        </div>
        <div class="form-group">
          <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;">
            <input type="checkbox" name="cryptoProtection" value="1" <?php checked( isset( $_POST['cryptoProtection'] ) ); ?>>
            <span style="font-size:0.875rem;color:var(--text-secondary);"><?php esc_html_e( 'Add Crypto Asset Security Protection (Premium)', 'icapital-wyoming' ); ?></span>
          </label>
        </div>

        <button type="submit" class="btn-primary" style="width:100%;margin-top:1rem;padding:0.875rem;">
          <?php esc_html_e( 'Form My Wyoming LLC →', 'icapital-wyoming' ); ?>
        </button>

        <p style="text-align:center;margin-top:1rem;font-size:0.8125rem;color:var(--text-muted);">
          <?php esc_html_e( 'Already have an account?', 'icapital-wyoming' ); ?>
          <a href="<?php echo esc_url( home_url( '/login' ) ); ?>" style="color:#2563eb;"><?php esc_html_e( 'Sign in', 'icapital-wyoming' ); ?></a>
        </p>
      </form>
    </div>
  </div>
</main>

<?php get_footer(); ?>
