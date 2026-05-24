<?php get_header(); ?>
<main id="main-content" role="main">
  <div class="page-404">
    <div>
      <div class="page-404__code">404</div>
      <h1 class="page-404__title"><?php esc_html_e( 'Page Not Found', 'icapital-wyoming' ); ?></h1>
      <p class="page-404__sub"><?php esc_html_e( "The page you're looking for doesn't exist or has been moved.", 'icapital-wyoming' ); ?></p>
      <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn-primary"><?php esc_html_e( 'Return Home', 'icapital-wyoming' ); ?></a>
    </div>
  </div>
</main>
<?php get_footer(); ?>
