<?php
/**
 * Template Name: Secure Asset Portal
 * @package icapital-wyoming
 */
if ( ! is_user_logged_in() ) {
	wp_safe_redirect( esc_url( home_url( '/login' ) ) );
	exit;
}
get_header();
?>
<main id="main-content" role="main">
  <div id="icapital-app" data-page="secure-asset"></div>
  <noscript>
    <div style="text-align:center;padding:4rem 1rem;color:var(--text-secondary);">
      <p>JavaScript is required to use the Secure Asset Portal.</p>
      <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn-primary" style="margin-top:1.5rem;display:inline-flex;">Return Home</a>
    </div>
  </noscript>
</main>
<?php get_footer(); ?>
