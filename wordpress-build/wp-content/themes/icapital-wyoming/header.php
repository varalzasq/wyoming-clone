<?php
/**
 * iCapital Wyoming LLC — header.php
 * @package icapital-wyoming
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?> class="dark">
<head>
  <meta charset="<?php bloginfo( 'charset' ); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5">
  <link rel="profile" href="https://gmpg.org/xfn/11">
  <!-- Prevent theme flash -->
  <script>
    (function(){try{var t=localStorage.getItem('wyllc_theme')||'dark';document.documentElement.classList.toggle('dark',t==='dark');}catch(e){}})();
  </script>
  <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<header class="site-header" role="banner">
  <div class="site-header__inner">

    <!-- Logo -->
    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="icapital-logo" rel="home">
      <div class="icapital-logo__badge">iC</div>
      <span class="icapital-logo__name">iCapital</span>
      <span class="icapital-logo__sub">&nbsp;Wyoming LLC</span>
    </a>

    <!-- Desktop Navigation -->
    <nav class="site-header__nav" aria-label="<?php esc_attr_e( 'Primary Navigation', 'icapital-wyoming' ); ?>">

      <!-- Services Dropdown -->
      <div class="dropdown">
        <button class="dropdown__toggle" aria-haspopup="true" aria-expanded="false">
          <?php esc_html_e( 'Services', 'icapital-wyoming' ); ?>
          <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 9l6 6 6-6"/></svg>
        </button>
        <div class="dropdown__menu" role="menu">
          <a href="<?php echo esc_url( home_url( '/start' ) ); ?>" role="menuitem"><?php esc_html_e( 'Form a Wyoming LLC', 'icapital-wyoming' ); ?></a>
          <a href="<?php echo esc_url( home_url( '/start' ) ); ?>" role="menuitem"><?php esc_html_e( 'Registered Agent', 'icapital-wyoming' ); ?></a>
          <a href="<?php echo esc_url( home_url( '/start' ) ); ?>" role="menuitem"><?php esc_html_e( 'Virtual Office', 'icapital-wyoming' ); ?></a>
          <a href="<?php echo esc_url( home_url( '/start' ) ); ?>" role="menuitem"><?php esc_html_e( 'Mail Forwarding', 'icapital-wyoming' ); ?></a>
        </div>
      </div>

      <!-- Resources Dropdown -->
      <div class="dropdown">
        <button class="dropdown__toggle" aria-haspopup="true" aria-expanded="false">
          <?php esc_html_e( 'Resources', 'icapital-wyoming' ); ?>
          <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 9l6 6 6-6"/></svg>
        </button>
        <div class="dropdown__menu" role="menu">
          <a href="<?php echo esc_url( home_url( '/start' ) ); ?>" role="menuitem"><?php esc_html_e( 'Series LLC', 'icapital-wyoming' ); ?></a>
          <a href="<?php echo esc_url( home_url( '/start' ) ); ?>" role="menuitem"><?php esc_html_e( 'Close LLC', 'icapital-wyoming' ); ?></a>
          <a href="<?php echo esc_url( home_url( '/start' ) ); ?>" role="menuitem"><?php esc_html_e( 'Holding Company Setup', 'icapital-wyoming' ); ?></a>
          <a href="<?php echo esc_url( home_url( '/start' ) ); ?>" role="menuitem"><?php esc_html_e( 'WY vs. NV, DE &amp; NM', 'icapital-wyoming' ); ?></a>
        </div>
      </div>

      <a href="<?php echo esc_url( home_url( '/start' ) ); ?>" class="nav-link"><?php esc_html_e( 'Pricing', 'icapital-wyoming' ); ?></a>
      <a href="<?php echo esc_url( home_url( '/start' ) ); ?>" class="nav-link"><?php esc_html_e( 'About', 'icapital-wyoming' ); ?></a>
      <a href="<?php echo esc_url( home_url( '/start' ) ); ?>" class="nav-link"><?php esc_html_e( 'Contact', 'icapital-wyoming' ); ?></a>

      <div style="display:flex;align-items:center;gap:0.75rem;border-left:1px solid var(--border-default);padding-left:1rem;">
        <?php if ( is_user_logged_in() ) : ?>
          <a href="<?php echo esc_url( home_url( '/dashboard' ) ); ?>" class="nav-link" style="display:flex;align-items:center;gap:0.25rem;">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            <?php esc_html_e( 'Dashboard', 'icapital-wyoming' ); ?>
          </a>
        <?php else : ?>
          <a href="<?php echo esc_url( home_url( '/login' ) ); ?>" class="nav-link" style="display:flex;align-items:center;gap:0.25rem;">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            <?php esc_html_e( 'Login', 'icapital-wyoming' ); ?>
          </a>
        <?php endif; ?>

        <!-- Dark/Light Toggle -->
        <button class="theme-toggle-btn" data-theme-toggle aria-label="<?php esc_attr_e( 'Toggle theme', 'icapital-wyoming' ); ?>">
          <span class="icon-sun" style="display:none;">☀️</span>
          <span class="icon-moon">🌙</span>
        </button>

        <a href="<?php echo esc_url( home_url( '/start' ) ); ?>" class="btn-primary" style="padding:0.5rem 1rem;font-size:0.875rem;">
          <?php esc_html_e( 'Form LLC Now', 'icapital-wyoming' ); ?>
        </a>
      </div>
    </nav>

    <!-- Mobile Right Side -->
    <div style="display:flex;align-items:center;gap:0.5rem;" class="lg-hidden">
      <button class="theme-toggle-btn" data-theme-toggle aria-label="Toggle theme">
        <span class="icon-sun" style="display:none;">☀️</span>
        <span class="icon-moon">🌙</span>
      </button>
      <a href="<?php is_user_logged_in() ? esc_url( home_url('/dashboard') ) : esc_url( home_url('/login') ); ?>" style="color:var(--text-secondary);padding:0.5rem;">
        <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
      </a>
      <button id="mobile-menu-btn" class="mobile-menu-btn" aria-label="<?php esc_attr_e( 'Open menu', 'icapital-wyoming' ); ?>" aria-expanded="false" aria-controls="mobile-nav">
        <svg id="icon-menu" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
      </button>
    </div>
  </div>

  <!-- Mobile Nav -->
  <nav id="mobile-nav" class="mobile-nav" aria-label="<?php esc_attr_e( 'Mobile Navigation', 'icapital-wyoming' ); ?>">
    <div class="mobile-nav__group-label"><?php esc_html_e( 'Services', 'icapital-wyoming' ); ?></div>
    <a href="<?php echo esc_url( home_url( '/start' ) ); ?>"><?php esc_html_e( 'Form a Wyoming LLC', 'icapital-wyoming' ); ?></a>
    <a href="<?php echo esc_url( home_url( '/start' ) ); ?>"><?php esc_html_e( 'Registered Agent', 'icapital-wyoming' ); ?></a>
    <a href="<?php echo esc_url( home_url( '/start' ) ); ?>"><?php esc_html_e( 'Virtual Office', 'icapital-wyoming' ); ?></a>
    <a href="<?php echo esc_url( home_url( '/start' ) ); ?>"><?php esc_html_e( 'Mail Forwarding', 'icapital-wyoming' ); ?></a>
    <div class="mobile-nav__group-label" style="margin-top:0.75rem;"><?php esc_html_e( 'Resources', 'icapital-wyoming' ); ?></div>
    <a href="<?php echo esc_url( home_url( '/start' ) ); ?>"><?php esc_html_e( 'Series LLC', 'icapital-wyoming' ); ?></a>
    <a href="<?php echo esc_url( home_url( '/start' ) ); ?>"><?php esc_html_e( 'Close LLC', 'icapital-wyoming' ); ?></a>
    <a href="<?php echo esc_url( home_url( '/start' ) ); ?>"><?php esc_html_e( 'Holding Company', 'icapital-wyoming' ); ?></a>
    <hr style="border:none;border-top:1px solid var(--border-default);margin:0.75rem 0;">
    <a href="<?php echo esc_url( home_url( '/start' ) ); ?>"><?php esc_html_e( 'Pricing', 'icapital-wyoming' ); ?></a>
    <a href="<?php echo esc_url( home_url( '/start' ) ); ?>"><?php esc_html_e( 'About', 'icapital-wyoming' ); ?></a>
    <a href="<?php echo esc_url( home_url( '/start' ) ); ?>"><?php esc_html_e( 'Contact', 'icapital-wyoming' ); ?></a>
    <div style="padding:0.75rem 0.75rem 0;display:flex;flex-direction:column;gap:0.5rem;">
      <?php if ( is_user_logged_in() ) : ?>
        <a href="<?php echo esc_url( home_url( '/dashboard' ) ); ?>" class="btn-outline" style="text-align:center;"><?php esc_html_e( 'Dashboard', 'icapital-wyoming' ); ?></a>
      <?php else : ?>
        <a href="<?php echo esc_url( home_url( '/login' ) ); ?>" class="btn-outline" style="text-align:center;"><?php esc_html_e( 'Log In', 'icapital-wyoming' ); ?></a>
      <?php endif; ?>
      <a href="<?php echo esc_url( home_url( '/start' ) ); ?>" class="btn-primary" style="text-align:center;"><?php esc_html_e( 'Form LLC Now', 'icapital-wyoming' ); ?></a>
    </div>
  </nav>
</header>
