<?php
/** Footer, toast and lightbox. */
defined( 'ABSPATH' ) || exit;
$p    = cclb_parish();
$week = $p['week'];
?>
</main>
<footer class="site-footer" data-tone="water">
  <?php echo cclb_edge( true ); // phpcs:ignore ?>
  <div class="wrap">
    <p class="motto"><span>Lord baptise us</span> <span>with your Spirit</span></p>
    <div class="foot">
      <div class="foot__col"><p class="label">Sunday Mass</p><?php
		if ( isset( $week[0] ) ) {
			foreach ( $week[0][1] as $s ) {
				if ( in_array( $s[2], array( 'prep', 'mass' ), true ) ) {
					echo '<p>' . esc_html( 'mass' === $s[2] ? 'Holy Mass' : 'Preparation' ) . ' ' . cclb_time_html( $s[0] ) . '</p>'; // phpcs:ignore
				}
			}
		}
	  ?></div>
      <div class="foot__col"><p class="label">Find us</p><p><?php echo esc_html( $p['address'] ); ?></p><a class="link" href="<?php echo esc_url( $p['maps_url'] ); ?>" target="_blank" rel="noopener noreferrer">Directions<span class="sr-only"> (opens Google Maps)</span></a></div>
      <div class="foot__col"><p class="label">Reach the office</p><a href="<?php echo esc_attr( cclb_tel( $p['phone'] ) ); ?>"><?php echo esc_html( $p['phone'] ); ?></a><a href="mailto:<?php echo esc_attr( $p['email'] ); ?>"><?php echo esc_html( $p['email'] ); ?></a></div>
      <div class="foot__col"><p class="label">Follow</p><?php
		foreach ( array( 'instagram' => 'Instagram', 'youtube' => 'YouTube' ) as $k => $label ) {
			if ( $p[ $k ] ) {
				echo '<a href="' . esc_url( $p[ $k ] ) . '" target="_blank" rel="noopener noreferrer">' . esc_html( $label ) . '<span class="sr-only"> (opens in a new tab)</span></a>';
			}
		}
	  ?></div>
    </div>
  </div>
  <div class="foot__fine-wrap"><div class="wrap foot__fine"><p>&copy; <span data-year><?php echo esc_html( gmdate( 'Y' ) ); ?></span> <?php echo esc_html( $p['name'] ); ?>, Osapa Lekki</p><p>Built and maintained by CCLB Lekki</p></div></div>
</footer>
<div class="toast" id="toast" role="status" aria-live="polite"></div>
<dialog class="lightbox" id="lightbox" aria-label="Image, larger">
  <button class="lightbox__close" type="button" data-lightbox-close>Close</button>
  <img alt="" width="1200" height="900" decoding="async">
</dialog>
<?php wp_footer(); ?>
</body>
</html>
