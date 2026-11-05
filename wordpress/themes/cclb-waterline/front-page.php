<?php
/** Home. */
defined( 'ABSPATH' ) || exit;
$p    = cclb_parish();
$sun  = cclb_sunday_mass();
cclb_page(
	$p['name'] . ' | Osapa, Lekki',
	'A Catholic parish in Osapa London, Lekki, Lagos.' . ( $sun ? ' Sunday Mass at ' . $sun . ',' : '' ) . ' weekday Masses, confession, giving, Harvest 2026 and parish registration.',
	array( 'current' => 'Home' )
);
get_header();

$below = '<p class="where"><span class="label">Where</span>' . esc_html( $p['address'] ) . '</p>'
	. '<p class="next"><span class="label">Next Mass</span><span class="next__when" data-next-mass>' . ( $sun ? 'Sunday, ' . cclb_time_html( $sun ) : 'See the parish week' ) . '</span></p>'
	. '<a class="btn btn--solid cta" href="#week">See the parish week ' . cclb_icon( 'arrow' ) . '</a>';
echo cclb_surface( 'home', 'Catholic Church of', "the Lord\u{2019}s", 'Baptism', $below ); // phpcs:ignore
?>
<section class="band band--normal" id="week" data-tone="water" aria-labelledby="week-title">
  <div class="wrap grid">
    <div class="week__head">
      <h2 class="h-xl" id="week-title" data-split>The parish week</h2>
      <p>Mass, confession and the priest&rsquo;s office, Sunday to Saturday. All times are Lagos time.</p>
    </div>
    <ol class="week__grid" role="list"><?php foreach ( $p['week'] as $d => $day ) : ?>
      <li class="day" data-day="<?php echo (int) $d; ?>"><p class="day__name"><?php echo esc_html( $day[0] ); ?><span class="day__today">Today</span></p><div><?php
		foreach ( $day[1] as $s ) {
			$quiet = in_array( $s[2], array( 'quiet' ), true ) ? ' slot--quiet' : '';
			echo '<p class="slot' . $quiet . '"><span class="slot__time tnum">' . cclb_time_html( $s[0] ) . '</span>' . ( $s[1] ? '<span class="slot__what">' . esc_html( $s[1] ) . '</span>' : '' ) . '</p>'; // phpcs:ignore
		}
	  ?></div></li>
    <?php endforeach; ?></ol>
    <?php if ( $p['holiday'] ) : ?><p class="week__foot"><?php echo cclb_time_html( $p['holiday'] ); ?></p><?php endif; ?>
  </div>
</section>

<section class="band band--tight" data-tone="air" aria-labelledby="doors-title">
  <?php echo cclb_edge(); // phpcs:ignore ?>
  <div class="wrap grid">
    <div class="doors__head"><h2 class="h-l" id="doors-title" data-split>Three ways in</h2></div>
    <ul class="doors__list" role="list"><?php
	$doors = array(
		array( '/give/', 'Give', 'Offertory, tithe, projects, harvest and societies', 'Every parish account in one place, with one tap to copy the number.' ),
		array( '/harvest/', 'Harvest', "Harvest of God\u{2019}s Faithfulness, 2026", "Make your Commitment Sunday pledge and see this year\u{2019}s theme." ),
		array( '/join/', 'Join', 'Register your household', 'Add your family to the parish register so we can welcome and serve you.' ),
	);
	foreach ( $doors as $door ) {
		echo '<li><a class="door" href="' . esc_url( home_url( $door[0] ) ) . '" data-dest="' . esc_attr( $door[1] ) . '"><span class="door__name">' . esc_html( $door[1] ) . '</span><span class="door__what"><strong>' . esc_html( $door[2] ) . '</strong><span>' . esc_html( $door[3] ) . '</span></span><span class="door__go" aria-hidden="true">' . cclb_icon( 'arrow_ur' ) . '</span></a></li>'; // phpcs:ignore
	}
	?></ul>
  </div>
</section>

<section class="band band--airy" data-tone="air" aria-labelledby="month-title">
  <div class="wrap grid">
    <div class="month__head">
      <h2 class="h-xl" id="month-title" data-split>Through the month</h2>
      <p>Sacraments, devotions and meetings that come round once or twice a month.</p>
    </div>
    <dl class="month__list"><?php foreach ( $p['month'] as $r ) : ?><div class="rite"><dt><?php echo esc_html( $r[0] ); ?></dt><dd><?php echo cclb_time_html( $r[1] ); ?></dd></div><?php endforeach; ?></dl>
  </div>
</section>

<?php if ( ! empty( $p['gallery'] ) ) : ?>
<section class="band band--normal" id="gallery" data-tone="water" aria-labelledby="gallery-title">
  <?php echo cclb_edge(); // phpcs:ignore ?>
  <div class="wrap gallery__head">
    <div><h2 class="h-xl" id="gallery-title" data-split>From the parish</h2><p>Photos and recordings from parish life, posted by the parish office.</p></div>
    <div class="strip-ctrl"><button class="round-btn" type="button" data-dir="prev" aria-label="Previous"><svg viewBox="0 0 20 20" aria-hidden="true"><path d="M16 10H5M9 5.5 4.5 10 9 14.5" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg></button><button class="round-btn" type="button" data-dir="next" aria-label="Next"><svg viewBox="0 0 20 20" aria-hidden="true"><path d="M4 10h11M11 5.5 15.5 10 11 14.5" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg></button></div>
  </div>
  <ul class="strip" role="list" data-strip aria-label="Parish photos and videos"><li class="strip-empty">Loading the parish gallery.</li></ul>
</section>
<?php endif; ?>

<section class="band band--airy" id="visit" data-tone="air" aria-labelledby="visit-title">
  <?php echo cclb_edge( true ); // phpcs:ignore ?>
  <div class="wrap grid">
    <div class="visit__addr">
      <p class="label">Visit</p>
      <h2 class="h-xl" id="visit-title" data-split><?php echo esc_html( $p['address_h'] ); ?></h2>
      <p class="meta" style="margin-top:1rem"><?php echo esc_html( $p['country'] ); ?></p>
      <a class="btn" href="<?php echo esc_url( $p['maps_url'] ); ?>" target="_blank" rel="noopener noreferrer">Open in Google Maps <?php echo cclb_icon( 'arrow' ); // phpcs:ignore ?><span class="sr-only"> (opens in a new tab)</span></a>
    </div>
    <div class="visit__facts">
      <div class="fact"><p class="label">Phone</p><a href="<?php echo esc_attr( cclb_tel( $p['phone'] ) ); ?>"><?php echo esc_html( $p['phone'] ); ?></a></div>
      <div class="fact"><p class="label">Email</p><a href="mailto:<?php echo esc_attr( $p['email'] ); ?>"><?php echo esc_html( $p['email'] ); ?></a></div>
      <div class="fact"><p class="label">Priest&rsquo;s office</p><p><?php echo cclb_time_html( $p['office'] ); ?></p></div>
    </div>
  </div>
</section>
<?php get_footer(); ?>
