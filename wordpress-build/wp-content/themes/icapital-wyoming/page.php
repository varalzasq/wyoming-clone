<?php get_header(); ?>
<main id="main-content" role="main" style="max-width:56rem;margin:3rem auto;padding:0 1rem;">
  <?php while ( have_posts() ) : the_post(); ?>
    <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
      <h1 style="font-size:2rem;font-weight:800;color:var(--text-primary);margin-bottom:1.5rem;"><?php the_title(); ?></h1>
      <div style="color:var(--text-secondary);line-height:1.8;"><?php the_content(); ?></div>
    </article>
  <?php endwhile; ?>
</main>
<?php get_footer(); ?>
