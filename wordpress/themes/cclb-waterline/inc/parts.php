<?php
/** Shared markup: the waterline hero, the header, the menu and the footer. */
defined( 'ABSPATH' ) || exit;

/** Navigation: label => path. */
function cclb_nav() {
	return array(
		'Give'    => '/give/',
		'Harvest' => '/harvest/',
		'Join'    => '/join/',
		'Visit'   => '/#visit',
	);
}

function cclb_socials() {
	$p   = cclb_parish();
	$out = '';
	foreach ( array( 'instagram' => array( 'Instagram', 'ig' ), 'youtube' => array( 'YouTube', 'yt' ) ) as $key => $v ) {
		if ( $p[ $key ] ) {
			$out .= '<a class="icon-link" href="' . esc_url( $p[ $key ] ) . '" target="_blank" rel="noopener noreferrer" aria-label="' . esc_attr( $v[0] ) . ' (opens in a new tab)">' . cclb_icon( $v[1] ) . '</a>';
		}
	}
	return $out;
}

/**
 * The waterline hero. $below is trusted HTML (built by the template).
 * $mode: home | inner | lost.
 */
function cclb_surface( $mode, $pre, $l1, $l2, $below, $tone = 'air', $id = 'title' ) {
	$n      = 9;
	$slices = '';
	for ( $i = 0; $i < $n; $i++ ) {
		$slices .= sprintf( '<span class="reflection__slice" style="clip-path:inset(%.2f%% -20%% %.2f%% -20%%)">%s</span>', $i * 100 / $n, 100 - ( $i + 1 ) * 100 / $n, esc_html( $l2 ) );
	}
	$title = ( $pre ? '<span class="pre">' . esc_html( $pre ) . '</span> ' : '' )
		. ( $l1 ? '<span class="ln ln--1">' . esc_html( $l1 ) . '</span> ' : '' )
		. '<span class="ln ln--2">' . esc_html( $l2 ) . '</span>';
	$class = 'surface surface--' . ( 'lost' === $mode ? 'inner lost' : $mode );
	ob_start();
	?>
<section class="<?php echo esc_attr( $class ); ?>" data-surface="<?php echo esc_attr( $mode ); ?>" data-tone="<?php echo esc_attr( $tone ); ?>" aria-labelledby="<?php echo esc_attr( $id ); ?>">
  <div class="surface__stage">
    <span class="surface__probe" aria-hidden="true"></span>
    <div class="surface__air">
      <h1 class="surface__title" id="<?php echo esc_attr( $id ); ?>" data-title><?php echo $title; // phpcs:ignore WordPress.Security.EscapeOutput ?></h1>
    </div>
    <div class="surface__water" data-water>
      <?php echo cclb_wave(); // phpcs:ignore WordPress.Security.EscapeOutput ?>
      <div class="surface__water-inner" data-water-inner>
        <div class="surface__title surface__title--wet" aria-hidden="true" data-title-wet><?php echo $title; // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
        <div class="reflection" aria-hidden="true"><div class="reflection__flip" data-reflection><?php echo $slices; // phpcs:ignore WordPress.Security.EscapeOutput ?></div></div>
        <div class="surface__below is-water"><?php echo $below; // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
      </div>
    </div>
  </div>
</section>
	<?php
	return ob_get_clean();
}

/** "1312487204" => "1312 4872 04" (same grouping the give page uses). */
function cclb_group_digits( $n ) {
	return preg_replace( '/(\d{3,4})(?=\d)/', '$1 ', preg_replace( '/\D/', '', $n ) );
}

/** Apostrophes in free text read as typographic ones, as the design sets them. */
function cclb_plain( $s ) {
	return preg_replace( "/(\w)'(\w)/u", "$1\u{2019}$2", $s );
}

/** A parish form (from the CCLB Parish plugin). Without the plugin, say how else to reach the parish. */
function cclb_render_form( $form ) {
	if ( function_exists( 'cclb_form' ) ) {
		return cclb_form( $form );
	}
	$p = cclb_parish();
	return '<div class="embed embed--card form-card"><p>This form is not available right now. Please contact the parish office on <a class="link" href="' . esc_attr( cclb_tel( $p['phone'] ) ) . '">' . esc_html( $p['phone'] ) . '</a> or <a class="link" href="mailto:' . esc_attr( $p['email'] ) . '">' . esc_html( $p['email'] ) . '</a>.</p></div>';
}
