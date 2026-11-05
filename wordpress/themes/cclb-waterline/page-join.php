<?php
/** Join the parish register (Tally form). Created automatically with the slug "join". */
defined( 'ABSPATH' ) || exit;
$p = cclb_parish();
cclb_page(
	"Join the parish register | " . $p['name'],
	"Register your household with the Catholic Church of the Lord\u{2019}s Baptism, Osapa, Lekki, so the parish can welcome you and keep in touch.",
	array( 'current' => 'Join', 'native_scroll' => true )
);
get_header();
$below = '<p class="lede">Add your household to our parish register so we can welcome you, keep in touch, and serve your family well.</p>'
	. '<a class="btn btn--solid cta" href="#register">Start the form ' . cclb_icon( 'arrow' ) . '</a>';
echo cclb_surface( 'inner', 'New to the parish?', 'Join the', 'parish register', $below ); // phpcs:ignore
?>
<section class="band band--normal" id="register" data-tone="air" aria-labelledby="register-title">
  <?php echo cclb_edge( true ); // phpcs:ignore ?>
  <div class="wrap grid">
    <div class="section-head" style="grid-column:1/span 7;margin-bottom:2.5rem">
      <h2 class="h-l" id="register-title" data-split>Register your household</h2>
      <p>One form for the whole family. It takes about five minutes.</p>
    </div>
    <a class="skip-form" href="#after-form">Skip past the form</a>
    <?php echo cclb_render_form( 'join' ); // phpcs:ignore ?>
  </div>
  <div class="wrap grid" style="margin-top:3.5rem" id="after-form" tabindex="-1">
    <div class="notice" role="note">
      <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M12 3 4.5 6v5.5c0 4.6 3.2 8.4 7.5 9.5 4.3-1.1 7.5-4.9 7.5-9.5V6L12 3Z" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/><path d="m8.8 12 2.2 2.2 4.2-4.4" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
      <h2>How we use your information</h2>
      <p>The <strong>Catholic Church of the Lord&rsquo;s Baptism</strong> collects these details only to keep our parish register and to contact you about parish life. They are stored securely in our parish records and are never sold or shared outside the parish.</p>
      <p>By submitting the form you consent to the parish holding and using these details for those purposes. You can ask to view, correct or delete your information at any time by contacting the parish office at <a class="link" style="display:inline-block;padding-block:0.2rem" href="mailto:<?php echo esc_attr( $p['email'] ); ?>"><?php echo esc_html( $p['email'] ); ?></a>.</p>
    </div>
  </div>
</section>
<?php get_footer(); ?>
