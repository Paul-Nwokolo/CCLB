<?php
/** Document head, curtain, header and mobile menu. */
defined( 'ABSPATH' ) || exit;
global $cclb_page;
$p       = cclb_parish();
$current = $cclb_page['current'] ?? '';
$crest   = esc_url( get_theme_file_uri( 'assets/img/crest-96.webp' ) );
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link" href="#main">Skip to content</a>
<div id="curtain" aria-hidden="true"><div class="curtain__panel"><?php echo cclb_wave(); // phpcs:ignore ?><span class="curtain__label"></span></div></div>
<header class="site-header">
  <div class="wrap">
    <a class="brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" data-dest="Home"<?php echo 'Home' === $current ? ' aria-current="page"' : ''; ?>><img src="<?php echo $crest; ?>" alt="" width="40" height="40"><span><?php echo esc_html( $p['short'] ); ?><span class="sr-only">, Catholic Church, home</span></span></a>
    <nav class="nav" aria-label="Main">
      <ul class="nav__list" role="list"><?php foreach ( cclb_nav() as $label => $path ) : ?><li><a class="nav__link" href="<?php echo esc_url( home_url( $path ) ); ?>" data-dest="<?php echo esc_attr( $label ); ?>"<?php echo $label === $current ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $label ); ?></a></li><?php endforeach; ?></ul>
      <div class="socials"><?php echo cclb_socials(); // phpcs:ignore ?></div>
    </nav>
    <button class="menu-btn" type="button" data-menu-open aria-expanded="false" aria-controls="menu">Menu</button>
  </div>
</header>
<div class="menu" id="menu" role="dialog" aria-modal="true" aria-label="Menu" hidden>
  <div class="menu__top">
    <span class="brand"><img src="<?php echo $crest; ?>" alt="" width="36" height="36"><?php echo esc_html( $p['short'] ); ?></span>
    <button class="menu-btn" type="button" data-menu-close style="display:inline-flex">Close</button>
  </div>
  <nav aria-label="Menu"><ul class="menu__list" role="list"><?php foreach ( array_merge( array( 'Home' => '/' ), cclb_nav() ) as $label => $path ) : ?><li><a href="<?php echo esc_url( home_url( $path ) ); ?>" data-dest="<?php echo esc_attr( $label ); ?>"<?php echo $label === $current ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $label ); ?></a></li><?php endforeach; ?></ul></nav>
  <div class="menu__foot">
    <p><?php if ( cclb_sunday_mass() ) : ?>Sunday Mass <?php echo cclb_time_html( cclb_sunday_mass() ); ?><br><?php endif; echo esc_html( $p['area'] ); ?></p>
    <div class="socials"><?php echo cclb_socials(); // phpcs:ignore ?></div>
  </div>
</div>
<main id="main" tabindex="-1">
