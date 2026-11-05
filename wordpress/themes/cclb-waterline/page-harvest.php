<?php
/** Harvest 2026: commitment form, harvest account, theme posters. Slug "harvest". */
defined( 'ABSPATH' ) || exit;
$p = cclb_parish();
cclb_page(
	"Harvest 2026 | Catholic Church of the Lord\u{2019}s Baptism, Lekki",
	"Make your Commitment Sunday pledge for Harvest 2026, give to the harvest account, and see the theme posters. Catholic Church of the Lord\u{2019}s Baptism, Lekki.",
	array( 'current' => 'Harvest', 'native_scroll' => true )
);
get_header();
$below = "<p class=\"lede\">Giving back from God\u{2019}s abundance. Be the strongest link for God.</p>"
	. '<a class="btn btn--solid cta" href="#commit">Make your commitment ' . cclb_icon( 'arrow' ) . '</a>';
echo cclb_surface( 'inner', 'Commitment Sunday 2026', "Harvest of God\u{2019}s", 'Faithfulness', $below ); // phpcs:ignore
$acct = cclb_fund_account( 'harvest' );
?>
<section class="band band--normal" id="commit" data-tone="air" aria-labelledby="commit-title">
  <?php echo cclb_edge( true ); // phpcs:ignore ?>
  <div class="wrap grid">
    <div class="section-head" style="grid-column:1/span 7;margin-bottom:2.5rem">
      <h2 class="h-l" id="commit-title" data-split>Make your commitment</h2>
      <p>Complete the form to make your Commitment Sunday pledge towards this year&rsquo;s Harvest of God&rsquo;s Faithfulness.</p>
    </div>
    <a class="skip-form" href="#account">Skip past the form</a>
    <?php echo cclb_render_form( 'harvest' ); // phpcs:ignore ?>
  </div>
</section>

<?php if ( $acct ) : ?>
<section class="band band--normal" id="account" data-tone="air" aria-labelledby="acct-title" tabindex="-1">
  <div class="wrap grid">
    <div class="section-head">
      <h2 class="h-l" id="acct-title" data-split>The harvest account</h2>
      <p>Give to this year&rsquo;s harvest by bank transfer. Tap to copy, then paste into your bank app.</p>
    </div>
    <div class="account-panel">
      <div class="account-card">
        <p class="account-card__name">Annual Harvest</p>
        <div class="acct">
          <div class="acct__row"><p class="label">Account name</p><p class="acct__value"><?php echo esc_html( cclb_plain( $acct[0] ) ); ?></p><button class="chip-btn" type="button" data-copy="<?php echo esc_attr( $acct[0] ); ?>"><span data-copy-label>Copy name</span></button></div>
          <div class="acct__row"><p class="label">Bank</p><p class="acct__value"><?php echo esc_html( $acct[1] ); ?></p></div>
          <div class="acct__row"><p class="label">Account number</p><p class="acct__number"><?php echo esc_html( cclb_group_digits( $acct[2] ) ); ?></p><button class="chip-btn" type="button" data-copy="<?php echo esc_attr( preg_replace( '/\D/', '', $acct[2] ) ); ?>"><?php echo cclb_icon( 'copy' ); // phpcs:ignore ?><span data-copy-label>Copy number</span></button></div>
        </div>
        <p class="verify">Before sending, check the account name reads <strong><?php echo esc_html( cclb_plain( $acct[0] ) ); ?></strong>. The parish never collects gifts through personal accounts.</p>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>

<section class="band band--airy" data-tone="water" aria-labelledby="verse">
  <?php echo cclb_edge(); // phpcs:ignore ?>
  <div class="wrap grid">
    <blockquote class="verse" id="verse" data-split>His faithfulness endures forever.<cite>Lamentations 3:23, the theme of Harvest 2026</cite></blockquote>
  </div>
</section>

<section class="band band--airy" id="theme" data-tone="air" aria-labelledby="theme-title">
  <?php echo cclb_edge( true ); // phpcs:ignore ?>
  <div class="wrap grid">
    <div class="section-head">
      <h2 class="h-l" id="theme-title" data-split>The harvest theme</h2>
      <p>Three posters for this year&rsquo;s celebration. Open one to see it in full.</p>
    </div>
    <ul class="posters" role="list"><?php
	for ( $i = 1; $i <= 3; $i++ ) {
		$base = 'assets/img/harvest/Harvest-of-Gods-Faithfulness-Poster-' . $i;
		$webp = esc_url( get_theme_file_uri( $base . '.webp' ) );
		$jpg  = esc_url( get_theme_file_uri( $base . '.jpg' ) );
		echo '<li><button class="poster" type="button" data-lightbox="' . $webp . '" data-alt="Harvest of God&rsquo;s Faithfulness, poster ' . $i . ' of 3"><picture><source srcset="' . $webp . '" type="image/webp"><img src="' . $jpg . '" width="519" height="1280" alt="Harvest of God&rsquo;s Faithfulness, poster ' . $i . ' of 3 for the 2026 parish harvest" loading="lazy" decoding="async"></picture><span class="poster__tag">View poster ' . $i . '</span></button></li>'; // phpcs:ignore
	}
	?></ul>
  </div>
</section>
<?php get_footer(); ?>
