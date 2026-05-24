<?php
/**
 * iCapital Wyoming LLC — front-page.php (Homepage)
 * Converts Next.js page.tsx into a Customizer-editable WordPress template.
 * @package icapital-wyoming
 */
get_header();
?>

<main id="main-content" role="main">

  <!-- ═══════════════════════════════════
       HERO SECTION
  ════════════════════════════════════ -->
  <section class="hero-section">
    <div class="hero__inner">

      <div class="hero__content">
        <h1 class="hero__heading">
          <?php echo esc_html( get_theme_mod( 'icapital_hero_heading', __( 'The Best State To Register Your Business', 'icapital-wyoming' ) ) ); ?>
        </h1>
        <p class="hero__sub">
          <?php echo esc_html( get_theme_mod( 'icapital_hero_sub', __( 'Form your Wyoming LLC quickly and securely with iCapital Wyoming LLC. Enjoy low annual fees, unmatched asset protection, and complete privacy.', 'icapital-wyoming' ) ) ); ?>
        </p>
        <div class="hero__ctas">
          <a href="<?php echo esc_url( get_theme_mod( 'icapital_cta_url', home_url( '/start' ) ) ); ?>" class="btn-primary">
            <?php echo esc_html( get_theme_mod( 'icapital_cta_text', __( 'Form a Wyoming LLC', 'icapital-wyoming' ) ) ); ?>
          </a>
          <a href="<?php echo esc_url( home_url( '/start' ) ); ?>" class="btn-outline">
            <?php esc_html_e( 'Learn More', 'icapital-wyoming' ); ?>
          </a>
        </div>
        <div class="hero__quote">
          <blockquote>
            &ldquo;<?php echo esc_html( get_theme_mod( 'icapital_hero_quote', __( 'I have used iCapital Wyoming LLC for several years now and I love the fact that they have live customer service. They are always very helpful and professional.', 'icapital-wyoming' ) ) ); ?>&rdquo;
          </blockquote>
        </div>
      </div>

      <div class="hero__card">
        <div class="feature-card" style="padding:2rem;">
          <h3 style="font-size:1.25rem;font-weight:700;color:var(--text-primary);margin-bottom:1.5rem;"><?php esc_html_e( 'Everything you need to launch', 'icapital-wyoming' ); ?></h3>
          <ul style="list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:1.25rem;">
            <?php
            $features = [
              [ 'icon' => '⏱', 'title' => __( '10 Minute Online Filing', 'icapital-wyoming' ),    'desc' => __( 'Quick and simple process from start to finish', 'icapital-wyoming' ) ],
              [ 'icon' => '✅', 'title' => __( '24 Hour Guarantee', 'icapital-wyoming' ),          'desc' => __( 'Your business will be up and running in no time', 'icapital-wyoming' ) ],
              [ 'icon' => '🛡', 'title' => __( 'Complete Package', 'icapital-wyoming' ),            'desc' => __( 'Everything included with no hidden fees', 'icapital-wyoming' ) ],
            ];
            foreach ( $features as $f ) : ?>
            <li style="display:flex;gap:1rem;">
              <span style="font-size:1.4rem;flex-shrink:0;margin-top:2px;"><?php echo esc_html( $f['icon'] ); ?></span>
              <div>
                <strong style="display:block;color:var(--text-primary);"><?php echo esc_html( $f['title'] ); ?></strong>
                <span style="font-size:0.875rem;color:var(--text-muted);"><?php echo esc_html( $f['desc'] ); ?></span>
              </div>
            </li>
            <?php endforeach; ?>
          </ul>
        </div>
      </div>

    </div>
  </section>

  <!-- ═══════════════════════════════════
       CRYPTO ASSET SECURITY BANNER
  ════════════════════════════════════ -->
  <section class="crypto-banner">
    <div class="section__inner" style="position:relative;z-index:1;">
      <div class="section__header" style="color:#fff;">
        <span style="display:inline-block;padding:0.2rem 0.75rem;border-radius:9999px;background:rgba(59,130,246,0.2);border:1px solid #60a5fa;color:#93c5fd;font-size:0.75rem;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;margin-bottom:1rem;">
          <?php esc_html_e( 'Premium Upgrade Tier', 'icapital-wyoming' ); ?>
        </span>
        <h2 class="section__title" style="color:#fff;font-size:clamp(1.5rem,4vw,2.5rem);"><?php esc_html_e( 'Crypto Asset Security Protection', 'icapital-wyoming' ); ?></h2>
        <p class="section__sub" style="color:#bfdbfe;"><?php esc_html_e( 'Integrate your digital wealth securely within your LLC structure. The ultimate protection strategy for modern investors and Web3 holdings.', 'icapital-wyoming' ); ?></p>
      </div>
      <div class="crypto-banner__grid">
        <?php
        $crypto_cards = [
          [ 'icon' => '📈', 'title' => __( 'Up to 20% ROI Strategies', 'icapital-wyoming' ),       'desc' => __( 'Gain access to exclusive automated high-yield tracking and strategies available only to our premium Crypto Protection clients through your secure dashboard.', 'icapital-wyoming' ) ],
          [ 'icon' => '💻', 'title' => __( 'Free Physical Hardware Wallet', 'icapital-wyoming' ), 'desc' => __( 'To guarantee your asset security, we dispatch a complimentary, top-tier cold storage hardware wallet directly to your physical address upon registration.', 'icapital-wyoming' ) ],
          [ 'icon' => '📄', 'title' => __( 'Complete Legal Documentation', 'icapital-wyoming' ),  'desc' => __( 'Automatically receive "Full Legal Asset Protection" documents specifically drafted to shield your crypto holdings under Wyoming\'s favorable jurisdiction.', 'icapital-wyoming' ) ],
        ];
        foreach ( $crypto_cards as $card ) : ?>
        <div class="crypto-card">
          <div style="font-size:2.5rem;margin-bottom:0.5rem;"><?php echo esc_html( $card['icon'] ); ?></div>
          <h3 class="crypto-card__title"><?php echo esc_html( $card['title'] ); ?></h3>
          <p class="crypto-card__desc"><?php echo esc_html( $card['desc'] ); ?></p>
        </div>
        <?php endforeach; ?>
      </div>
      <div style="text-align:center;">
        <a href="<?php echo esc_url( home_url( '/start' ) ); ?>" style="display:inline-flex;align-items:center;justify-content:center;padding:1rem 2rem;font-size:1.1rem;font-weight:700;border-radius:0.5rem;background:#fff;color:#1e3a8a;text-decoration:none;transition:background-color 0.2s;">
          <?php esc_html_e( 'Add Crypto Protection to Your LLC Today', 'icapital-wyoming' ); ?>
        </a>
      </div>
    </div>
  </section>

  <!-- ═══════════════════════════════════
       WHY WYOMING SECTION
  ════════════════════════════════════ -->
  <section class="section">
    <div class="section__inner">
      <div class="section__header">
        <h2 class="section__title"><?php esc_html_e( 'Why Form a Wyoming LLC?', 'icapital-wyoming' ); ?></h2>
        <p class="section__sub"><?php esc_html_e( 'Wyoming offers the best combination of asset protection laws, privacy benefits, and cost savings.', 'icapital-wyoming' ); ?></p>
      </div>
      <div class="feature-grid">
        <?php
        $why_features = [
          [ 'icon' => '🔒', 'title' => __( 'Private & Anonymous', 'icapital-wyoming' ),      'desc' => __( 'Owners and managers are not listed in public records. Your personal information remains completely private and secure.', 'icapital-wyoming' ) ],
          [ 'icon' => '🛡', 'title' => __( 'Asset Protection', 'icapital-wyoming' ),         'desc' => __( 'Strong charging order protection. Personal creditors cannot seize your LLC, and LLC creditors cannot seize personal assets.', 'icapital-wyoming' ) ],
          [ 'icon' => '✅', 'title' => __( 'Low Annual Fees', 'icapital-wyoming' ),           'desc' => __( 'Minimal ongoing costs with only a $60 annual report required. No state income tax, corporate tax, or franchise tax.', 'icapital-wyoming' ) ],
          [ 'icon' => '🏛', 'title' => __( 'No State Taxes', 'icapital-wyoming' ),           'desc' => __( 'Wyoming has no state income tax, corporate tax, or franchise tax. Significant savings for all business owners.', 'icapital-wyoming' ) ],
          [ 'icon' => '📝', 'title' => __( 'Simple Requirements', 'icapital-wyoming' ),      'desc' => __( 'Everything is handled online with no need to visit Wyoming. No residency requirements or wait times.', 'icapital-wyoming' ) ],
          [ 'icon' => '💼', 'title' => __( 'Operational Flexibility', 'icapital-wyoming' ), 'desc' => __( 'Conduct business in all 50 states. No minimum capital required and flexible management structure options.', 'icapital-wyoming' ) ],
        ];
        foreach ( $why_features as $f ) : ?>
        <div class="feature-card">
          <div class="feature-card__icon" style="font-size:2rem;"><?php echo esc_html( $f['icon'] ); ?></div>
          <h3 class="feature-card__title"><?php echo esc_html( $f['title'] ); ?></h3>
          <p class="feature-card__desc"><?php echo esc_html( $f['desc'] ); ?></p>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- ═══════════════════════════════════
       BASE SERVICES GRID
  ════════════════════════════════════ -->
  <section class="section section--subtle">
    <div class="section__inner">
      <div class="section__header">
        <h2 class="section__title"><?php esc_html_e( 'Included Base Services', 'icapital-wyoming' ); ?></h2>
        <p class="section__sub"><?php esc_html_e( 'We provide everything you need to start your business with complete peace of mind. No hidden fees, no surprises.', 'icapital-wyoming' ); ?></p>
      </div>
      <div class="services-grid">
        <?php
        $services = [
          [ 'icon' => '🛡', 'title' => __( 'Registered Agent Service', 'icapital-wyoming' ), 'desc' => __( 'Professional representation', 'icapital-wyoming' ) ],
          [ 'icon' => '✉️', 'title' => __( 'Free Mail Scanning', 'icapital-wyoming' ),       'desc' => __( '5 pieces monthly', 'icapital-wyoming' ) ],
          [ 'icon' => '🏢', 'title' => __( 'Business Address', 'icapital-wyoming' ),         'desc' => __( 'Use our address', 'icapital-wyoming' ) ],
          [ 'icon' => '📄', 'title' => __( 'Operating Agreements', 'icapital-wyoming' ),     'desc' => __( 'Single & multi-member', 'icapital-wyoming' ) ],
          [ 'icon' => '⏱', 'title' => __( 'Meeting Minutes', 'icapital-wyoming' ),           'desc' => __( 'Organizational docs', 'icapital-wyoming' ) ],
          [ 'icon' => '🏛', 'title' => __( 'Articles of Organization', 'icapital-wyoming' ), 'desc' => __( 'Official filing', 'icapital-wyoming' ) ],
          [ 'icon' => '✅', 'title' => __( 'Certificate of Formation', 'icapital-wyoming' ), 'desc' => __( 'Proof of existence', 'icapital-wyoming' ) ],
          [ 'icon' => '💼', 'title' => __( 'Bank Account Resolution', 'icapital-wyoming' ), 'desc' => __( 'Banking documentation', 'icapital-wyoming' ) ],
          [ 'icon' => '📚', 'title' => __( 'Operations Manual', 'icapital-wyoming' ),        'desc' => __( 'Complete guide', 'icapital-wyoming' ) ],
        ];
        foreach ( $services as $s ) : ?>
        <div class="service-item">
          <span class="service-item__icon" style="font-size:1.3rem;"><?php echo esc_html( $s['icon'] ); ?></span>
          <div>
            <div class="service-item__title"><?php echo esc_html( $s['title'] ); ?></div>
            <div class="service-item__desc"><?php echo esc_html( $s['desc'] ); ?></div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- ═══════════════════════════════════
       TESTIMONIALS
  ════════════════════════════════════ -->
  <section class="section" style="border-top:1px solid var(--border-default);">
    <div class="section__inner">
      <div class="section__header">
        <h2 class="section__title"><?php esc_html_e( 'Trusted by Forward-Thinking Investors', 'icapital-wyoming' ); ?></h2>
        <p class="section__sub"><?php esc_html_e( 'See what our clients are saying about our premium asset protection and holding company structures.', 'icapital-wyoming' ); ?></p>
      </div>
      <div class="testimonials-grid">
        <?php
        $testimonials = [
          [ 'text' => __( 'The Crypto Asset Security package is unparalleled. I received my hardware wallet within 3 days and the legal docs gave me immense peace of mind. Highly recommend their Wyoming LLC structures for crypto holdings.', 'icapital-wyoming' ),   'author' => 'Michael T.', 'role' => __( 'Web3 Investor', 'icapital-wyoming' ) ],
          [ 'text' => __( "I was initially just looking for a simple LLC, but the 20% ROI strategies through the dashboard made the premium upgrade a no-brainer. The privacy protection in Wyoming is the cherry on top.", 'icapital-wyoming' ),                     'author' => 'Sarah K.',   'role' => __( 'E-commerce Founder', 'icapital-wyoming' ) ],
          [ 'text' => __( 'iCapital Wyoming LLC made the entire process seamless. Their support team actually answered the phone, and getting my digital assets properly documented under the LLC was easier than I ever thought possible.', 'icapital-wyoming' ), 'author' => 'David L.',   'role' => __( 'Real Estate & Crypto Portfolio Manager', 'icapital-wyoming' ) ],
        ];
        foreach ( $testimonials as $t ) : ?>
        <div class="testimonial-card">
          <div class="testimonial-card__stars">★★★★★</div>
          <p class="testimonial-card__text">&ldquo;<?php echo esc_html( $t['text'] ); ?>&rdquo;</p>
          <div>
            <div class="testimonial-card__author"><?php echo esc_html( $t['author'] ); ?></div>
            <div class="testimonial-card__role"><?php echo esc_html( $t['role'] ); ?></div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- ═══════════════════════════════════
       FAQ
  ════════════════════════════════════ -->
  <section class="section section--subtle">
    <div class="section__inner" style="max-width:56rem;">
      <div class="section__header">
        <h2 class="section__title"><?php esc_html_e( 'Frequently Asked Questions', 'icapital-wyoming' ); ?></h2>
      </div>
      <h3 style="font-size:1.25rem;font-weight:700;color:var(--text-primary);margin-bottom:1.5rem;padding-bottom:0.5rem;border-bottom:1px solid var(--border-default);"><?php esc_html_e( 'Privacy & Anonymity FAQs', 'icapital-wyoming' ); ?></h3>
      <?php
      $faqs = [
        [ 'q' => __( 'Do Wyoming LLCs offer privacy?', 'icapital-wyoming' ),          'a' => __( 'Yes, Wyoming LLCs offer exceptional privacy and anonymity. Unlike many other states, Wyoming does not require the disclosure of member or manager information in public filings. Your personal information remains private and is not entered into any public database.', 'icapital-wyoming' ) ],
        [ 'q' => __( 'Is my personal information in a public database?', 'icapital-wyoming' ), 'a' => __( 'No, your personal information is not entered into any public database when you form a Wyoming LLC. The Secretary of State only knows who organized the company (which is us as your registered agent), but your name does not appear on the formation documents and is not made public.', 'icapital-wyoming' ) ],
        [ 'q' => __( 'Is desiring anonymity wrong?', 'icapital-wyoming' ),             'a' => __( 'Absolutely not. Desiring anonymity is not wrong at all. You have a legal right to keep your business affairs private, and there\'s nothing to gain by displaying your wealth or business activities publicly. Privacy is a legitimate business strategy for protection and security.', 'icapital-wyoming' ) ],
      ];
      foreach ( $faqs as $faq ) : ?>
      <div class="faq-item">
        <dt class="faq-item__q"><?php echo esc_html( $faq['q'] ); ?></dt>
        <dd class="faq-item__a"><?php echo esc_html( $faq['a'] ); ?></dd>
      </div>
      <?php endforeach; ?>
    </div>
  </section>

</main>

<?php get_footer(); ?>
