<?php
/**
 * iCapital Wyoming LLC — footer.php
 * @package icapital-wyoming
 */
?>
<footer class="site-footer" role="contentinfo">
  <div class="site-footer__inner">
    <div class="site-footer__grid">

      <!-- Brand Column -->
      <div>
        <div style="display:flex;align-items:center;gap:0.5rem;margin-bottom:0.25rem;">
          <div class="icapital-logo__badge">iC</div>
          <span class="icapital-logo__name">iCapital</span>
          <span class="icapital-logo__sub">&nbsp;Wyoming LLC</span>
        </div>
        <p style="margin-top:1rem;font-size:0.875rem;color:var(--text-muted);line-height:1.6;">
          <?php echo esc_html( get_theme_mod( 'icapital_footer_tagline', __( 'Professional Wyoming LLC formation and registered agent services by iCapital Wyoming LLC.', 'icapital-wyoming' ) ) ); ?>
        </p>
      </div>

      <!-- Services Column -->
      <div>
        <h3 class="site-footer__col-title"><?php esc_html_e( 'Services', 'icapital-wyoming' ); ?></h3>
        <ul class="site-footer__links">
          <li><a href="<?php echo esc_url( home_url( '/start' ) ); ?>"><?php esc_html_e( 'Form a Wyoming LLC', 'icapital-wyoming' ); ?></a></li>
          <li><a href="<?php echo esc_url( home_url( '/start' ) ); ?>"><?php esc_html_e( 'Registered Agent', 'icapital-wyoming' ); ?></a></li>
          <li><a href="<?php echo esc_url( home_url( '/start' ) ); ?>"><?php esc_html_e( 'Virtual Office', 'icapital-wyoming' ); ?></a></li>
          <li><a href="<?php echo esc_url( home_url( '/start' ) ); ?>"><?php esc_html_e( 'Mail Forwarding', 'icapital-wyoming' ); ?></a></li>
        </ul>
      </div>

      <!-- Resources Column -->
      <div>
        <h3 class="site-footer__col-title"><?php esc_html_e( 'Resources', 'icapital-wyoming' ); ?></h3>
        <ul class="site-footer__links">
          <li><a href="<?php echo esc_url( home_url( '/start' ) ); ?>"><?php esc_html_e( 'Series LLC', 'icapital-wyoming' ); ?></a></li>
          <li><a href="<?php echo esc_url( home_url( '/start' ) ); ?>"><?php esc_html_e( 'Close LLC', 'icapital-wyoming' ); ?></a></li>
          <li><a href="<?php echo esc_url( home_url( '/start' ) ); ?>"><?php esc_html_e( 'Holding Company Setup', 'icapital-wyoming' ); ?></a></li>
          <li><a href="<?php echo esc_url( home_url( '/start' ) ); ?>"><?php esc_html_e( 'WY vs. NV, DE &amp; NM', 'icapital-wyoming' ); ?></a></li>
        </ul>
      </div>

      <!-- Contact Column -->
      <div>
        <h3 class="site-footer__col-title"><?php esc_html_e( 'Contact', 'icapital-wyoming' ); ?></h3>
        <ul class="site-footer__links" style="list-style:none;padding:0;margin:0;">
          <li style="font-size:0.875rem;color:var(--text-muted);margin-bottom:0.4rem;">icapitalwyomingllc.com</li>
          <li style="font-size:0.875rem;color:var(--text-muted);margin-bottom:0.4rem;">
            <?php echo esc_html( get_theme_mod( 'icapital_address_line1', '1309 Coffeen Ave' ) ); ?>
          </li>
          <li style="font-size:0.875rem;color:var(--text-muted);">
            <?php echo esc_html( get_theme_mod( 'icapital_address_line2', 'Sheridan, WY 82801' ) ); ?>
          </li>
        </ul>
      </div>

    </div>

    <!-- Bottom Bar -->
    <div class="site-footer__bottom">
      <p class="site-footer__copyright">
        &copy; <?php echo esc_html( date( 'Y' ) ); ?> <?php esc_html_e( 'iCapital Wyoming LLC. All rights reserved.', 'icapital-wyoming' ); ?>
      </p>
      <div class="site-footer__policy-links">
        <a href="<?php echo esc_url( home_url( '/privacy-policy' ) ); ?>"><?php esc_html_e( 'Privacy Policy', 'icapital-wyoming' ); ?></a>
        <a href="<?php echo esc_url( home_url( '/terms-of-service' ) ); ?>"><?php esc_html_e( 'Terms of Service', 'icapital-wyoming' ); ?></a>
      </div>
    </div>

    <!-- Legal Disclaimer -->
    <p class="site-footer__legal">
      <?php esc_html_e( 'icapitalwyomingllc.com is not a law firm and does not give legal advice. This website and any associated content is generalized, should not be considered applicable to your particular situation and does not provide advice concerning the particulars of your situation.', 'icapital-wyoming' ); ?>
    </p>

    <!-- Hidden Admin Link -->
    <div style="text-align:center;margin-top:0.5rem;">
      <button onclick="window.location.href='<?php echo esc_url( admin_url() ); ?>'" style="background:none;border:none;cursor:pointer;font-size:0.65rem;color:rgba(107,114,128,0.25);letter-spacing:0.05em;" tabindex="-1">
        ADMIN
      </button>
    </div>

  </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
