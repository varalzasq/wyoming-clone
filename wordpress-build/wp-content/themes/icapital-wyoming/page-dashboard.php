<?php
/**
 * Template Name: Client Dashboard
 * Mounts the React Web3 dashboard app.
 * Redirects to login if not authenticated.
 * @package icapital-wyoming
 */

// Gate: redirect unauthenticated users to login
if ( ! is_user_logged_in() ) {
	wp_safe_redirect( esc_url( home_url( '/login' ) ) );
	exit;
}

get_header();
?>

<main id="main-content" role="main">
  <!--
    React app mounts here.
    window.icapitalData is injected by functions.php (wp_localize_script).
    The data-page attribute tells app-entry.tsx which component to render.
  -->
  <div id="icapital-app" data-page="dashboard"></div>

  <!-- Fallback if JS bundle hasn't loaded yet -->
  <noscript>
    <div style="text-align:center;padding:4rem 1rem;color:var(--text-secondary);">
      <p style="font-size:1.125rem;">JavaScript is required to use the iCapital Wyoming LLC dashboard.</p>
      <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn-primary" style="margin-top:1.5rem;display:inline-flex;">Return Home</a>
    </div>
  </noscript>
</main>

<?php get_footer(); ?>
