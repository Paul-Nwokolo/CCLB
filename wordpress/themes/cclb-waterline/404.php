<?php
/** Not found: the page has gone under. */
defined( 'ABSPATH' ) || exit;
cclb_page(
	"Page not found | Catholic Church of the Lord\u{2019}s Baptism",
	"This page could not be found. Return to the home page of the Catholic Church of the Lord\u{2019}s Baptism, Osapa, Lekki.",
	array( 'noindex' => true )
);
get_header();
$below = '<p class="lede">The link may be old, or the page has moved. Everything else is still here.</p>'
	. '<a class="btn btn--solid cta" href="' . esc_url( home_url( '/' ) ) . '" data-dest="Home">Come up to the home page ' . cclb_icon( 'arrow' ) . '</a>';
echo cclb_surface( 'lost', 'Page not found', 'This page has', 'gone under', $below ); // phpcs:ignore
get_footer();
