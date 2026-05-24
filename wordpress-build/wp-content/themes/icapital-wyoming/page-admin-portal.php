<?php
/**
 * Template Name: Admin Portal
 * @package icapital-wyoming
 */
if ( ! current_user_can( 'manage_options' ) ) {
	wp_safe_redirect( esc_url( home_url( '/login' ) ) );
	exit;
}
get_header();
?>
<main id="main-content" role="main">
  <div id="icapital-app" data-page="admin"></div>
</main>
<?php get_footer(); ?>
