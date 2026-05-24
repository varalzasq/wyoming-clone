<?php
/**
 * Template Name: Login Page
 * @package icapital-wyoming
 */
if ( is_user_logged_in() ) {
	wp_safe_redirect( esc_url( home_url( '/dashboard' ) ) );
	exit;
}
get_header();
$redirect_to = isset( $_GET['redirect_to'] ) ? sanitize_url( wp_unslash( $_GET['redirect_to'] ) ) : home_url( '/dashboard' );
$error       = '';

if ( 'POST' === $_SERVER['REQUEST_METHOD'] ) {
	// Verify nonce
	if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'icapital_login' ) ) {
		$error = __( 'Security check failed. Please try again.', 'icapital-wyoming' );
	} else {
		$email    = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
		$password = isset( $_POST['password'] ) ? wp_unslash( $_POST['password'] ) : '';

		$user = wp_authenticate( $email, $password );
		if ( is_wp_error( $user ) ) {
			$error = __( 'Invalid email or password.', 'icapital-wyoming' );
		} else {
			wp_set_auth_cookie( $user->ID, true );
			wp_safe_redirect( esc_url( $redirect_to ) );
			exit;
		}
	}
}
?>

<main id="main-content" role="main" style="min-height:80vh;display:flex;align-items:center;justify-content:center;padding:2rem 1rem;">
  <div style="width:100%;max-width:26rem;">
    <div style="text-align:center;margin-bottom:2rem;">
      <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="icapital-logo" style="justify-content:center;display:inline-flex;">
        <div class="icapital-logo__badge">iC</div>
        <span class="icapital-logo__name">iCapital</span>
        <span class="icapital-logo__sub">&nbsp;Wyoming LLC</span>
      </a>
      <h1 style="margin-top:1.5rem;font-size:1.5rem;font-weight:800;color:var(--text-primary);"><?php esc_html_e( 'Sign in to your account', 'icapital-wyoming' ); ?></h1>
    </div>

    <div class="feature-card" style="padding:2rem;">
      <?php if ( $error ) : ?>
        <div class="alert alert--error"><?php echo esc_html( $error ); ?></div>
      <?php endif; ?>

      <form method="POST" action="" novalidate>
        <?php wp_nonce_field( 'icapital_login' ); ?>
        <input type="hidden" name="redirect_to" value="<?php echo esc_attr( $redirect_to ); ?>">

        <div class="form-group">
          <label class="form-label" for="email"><?php esc_html_e( 'Email address', 'icapital-wyoming' ); ?></label>
          <input class="theme-input" type="email" id="email" name="email" required
            value="<?php echo isset( $_POST['email'] ) ? esc_attr( sanitize_email( $_POST['email'] ) ) : ''; ?>"
            placeholder="you@example.com" autocomplete="email">
        </div>

        <div class="form-group">
          <label class="form-label" for="password"><?php esc_html_e( 'Password', 'icapital-wyoming' ); ?></label>
          <input class="theme-input" type="password" id="password" name="password" required
            placeholder="••••••••" autocomplete="current-password">
        </div>

        <button type="submit" class="btn-primary" style="width:100%;margin-top:0.5rem;">
          <?php esc_html_e( 'Sign In', 'icapital-wyoming' ); ?>
        </button>
      </form>

      <p style="text-align:center;margin-top:1.5rem;font-size:0.875rem;color:var(--text-muted);">
        <?php esc_html_e( "Don't have an account?", 'icapital-wyoming' ); ?>
        <a href="<?php echo esc_url( home_url( '/start' ) ); ?>" style="color:#2563eb;font-weight:600;">
          <?php esc_html_e( 'Form your LLC', 'icapital-wyoming' ); ?>
        </a>
      </p>
    </div>
  </div>
</main>

<?php get_footer(); ?>
