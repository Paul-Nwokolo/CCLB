<?php
/** Give: fund chooser, account details, WhatsApp confirmation. Slug "give". Data: Parish Details > Giving. */
defined( 'ABSPATH' ) || exit;
$p = cclb_parish();
cclb_page(
	"Give | Catholic Church of the Lord\u{2019}s Baptism, Osapa Lekki",
	"Give to the Catholic Church of the Lord\u{2019}s Baptism, Osapa Lekki: offertory, Mass booking and tithe, donation projects, the annual harvest and parish societies.",
	array( 'current' => 'Give' )
);
add_action( 'wp_footer', function () {
	echo '<script type="application/json" id="cclb-giving">' . wp_json_encode( cclb_giving_for_js(), JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP ) . '</script>' . "\n";
	echo '<script defer src="' . esc_url( get_theme_file_uri( 'assets/js/give.js' ) ) . '?ver=' . CCLB_VERSION . '"></script>' . "\n";
}, 5 );
get_header();
$below = '<p class="lede" id="hero-lede">Every gift sustains the worship, works of mercy, and growth of our parish family. Thank you for giving.</p>'
	. '<a class="btn btn--solid cta" href="#give">See ways to give ' . cclb_icon( 'arrow' ) . '</a>';
echo cclb_surface( 'inner', 'Give to the parish', 'Your generosity', 'ripples outward', $below ); // phpcs:ignore
?>
<section class="band band--tight" data-tone="air" aria-labelledby="how-title" style="padding-top:calc(var(--u)*12)">
  <?php echo cclb_edge( true ); // phpcs:ignore ?>
  <div class="wrap grid">
    <div class="section-head"><h2 class="h-l" id="how-title" data-split>Giving takes one minute</h2></div>
    <ol class="steps" role="list">
      <li class="step"><h3><span class="rubric">Choose a fund.</span></h3><p>Offertory, a project, the harvest, or one of the parish societies.</p></li>
      <li class="step"><h3><span class="rubric">Copy the account number.</span></h3><p>One tap copies it, so there are no typing mistakes.</p></li>
      <li class="step"><h3><span class="rubric">Transfer in your bank app.</span></h3><p>Paste it into your bank app or USSD and send your gift.</p></li>
    </ol>
  </div>
</section>

<section class="band band--normal" id="give" data-tone="air" aria-labelledby="give-title">
  <div class="wrap grid">
    <div class="section-head" style="grid-column:1/span 6;margin-bottom:2.5rem">
      <h2 class="h-l" id="give-title" data-split>Choose a fund</h2>
      <p>Select a fund to see its bank details.</p>
    </div>
    <ul class="funds" id="fund-list" role="list" aria-label="Parish funds"></ul>
    <div class="account-panel" id="fund-detail" aria-live="polite">
      <noscript><div class="account-card"><h3 class="account-card__name">Parish accounts</h3><?php
		foreach ( (array) $p['giving']['funds'] as $f ) {
			echo '<p class="label" style="margin-top:1.5rem">' . esc_html( $f['name'] ) . '</p>';
			$rows = ! empty( $f['societies'] ) ? array_map( function ( $s ) { return array( $s[1], $s[2], $s[3] ); }, $f['societies'] ) : (array) $f['accounts'];
			foreach ( $rows as $a ) {
				echo '<p>' . esc_html( cclb_plain( $a[0] ) ) . ', ' . esc_html( $a[1] ) . ', ' . esc_html( cclb_group_digits( $a[2] ) ) . '</p>'; // phpcs:ignore
			}
		}
	  ?></div></noscript>
    </div>
  </div>
</section>

<section class="band band--normal" id="confirm" data-tone="water" aria-labelledby="confirm-title">
  <?php echo cclb_edge(); // phpcs:ignore ?>
  <div class="wrap grid">
    <div class="section-head">
      <h2 class="h-l" id="confirm-title" data-split>Let us know</h2>
      <p>Already sent your gift? Send the parish a quick confirmation on WhatsApp so we can thank you and keep our records right. This is optional.</p>
    </div>
    <form class="form-grid" id="confirm-form" novalidate>
      <div class="field"><label for="cf-name">Your name</label><input class="input" id="cf-name" type="text" autocomplete="name" placeholder="e.g. Mary Okafor"></div>
      <div class="field"><label for="cf-fund">Which fund</label><select class="input" id="cf-fund"></select></div>
      <div class="field"><label for="cf-amount">Amount (optional)</label><input class="input" id="cf-amount" type="text" inputmode="numeric" placeholder="e.g. 10,000"></div>
      <div class="field"><label for="cf-ref">Transfer reference or teller (optional)</label><input class="input" id="cf-ref" type="text" placeholder="e.g. TRX-48213"></div>
      <button class="btn btn--solid btn-wa" type="submit"><?php echo cclb_icon( 'wa' ); // phpcs:ignore ?> Send confirmation on WhatsApp</button>
      <p class="form-tip">You can attach a photo of your receipt in the WhatsApp chat after it opens.</p>
    </form>
  </div>
</section>

<section class="band band--airy" id="contact" data-tone="air" aria-labelledby="contact-title">
  <?php echo cclb_edge( true ); // phpcs:ignore ?>
  <div class="wrap grid">
    <div class="section-head"><h2 class="h-l" id="contact-title" data-split>Questions about giving?</h2><p>The parish office can help with receipts, Mass bookings and society accounts.</p></div>
    <div class="visit__facts" id="contact-grid">
      <div class="fact"><p class="label">Phone</p><a href="<?php echo esc_attr( cclb_tel( $p['phone'] ) ); ?>"><?php echo esc_html( $p['phone'] ); ?></a></div>
      <div class="fact"><p class="label">Email</p><a href="mailto:<?php echo esc_attr( $p['email'] ); ?>"><?php echo esc_html( $p['email'] ); ?></a></div>
      <div class="fact"><p class="label">Priest&rsquo;s office</p><p><?php echo cclb_time_html( $p['office'] ); ?></p></div>
    </div>
  </div>
</section>
<?php get_footer(); ?>
