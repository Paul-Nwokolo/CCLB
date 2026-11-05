<?php
/**
 * CCLB Waterline: theme setup. See README.md for how the pieces fit.
 */
defined( 'ABSPATH' ) || exit;

define( 'CCLB_VERSION', '0.3.0' );

require_once get_template_directory() . '/inc/icons.php';
require_once get_template_directory() . '/inc/parish.php';
require_once get_template_directory() . '/inc/parts.php';

add_action( 'after_setup_theme', function () {
	add_theme_support( 'title-tag' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'post-thumbnails' );
} );

/* Tidy head: the design does not use these. */
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
remove_action( 'wp_head', 'wp_generator' );
remove_action( 'wp_head', 'wlwmanifest_link' );
remove_action( 'wp_head', 'rsd_link' );

add_action( 'wp_enqueue_scripts', function () {
	// The design styles everything itself. Block and global styles would fight it.
	wp_dequeue_style( 'wp-block-library' );
	wp_dequeue_style( 'wp-block-library-theme' );
	wp_dequeue_style( 'global-styles' );
	wp_dequeue_style( 'classic-theme-styles' );
	wp_enqueue_style( 'cclb-site', get_theme_file_uri( 'assets/css/site.css' ), array(), CCLB_VERSION );
}, 20 );

/** Keep the admin bar from covering the fixed header. */
add_action( 'wp_head', function () {
	if ( is_admin_bar_showing() ) {
		echo '<style>body.admin-bar .site-header{top:32px}@media(max-width:782px){body.admin-bar .site-header{top:46px}}</style>' . "\n";
	}
}, 99 );

/**
 * Scripts, printed by hand so their order is exact: the vendor libraries
 * (plain, deferred) must run before main.js (a module). Do not let a
 * performance plugin combine, delay or defer these.
 */
add_action( 'wp_head', function () {
	$u = function ( $p ) {
		return esc_url( get_theme_file_uri( $p ) ) . '?ver=' . CCLB_VERSION;
	};
	echo '<script>(function(){var d=document.documentElement;d.classList.add(\'js\');if(matchMedia(\'(prefers-reduced-motion: reduce)\').matches)return;d.classList.add(\'motion\');try{if(sessionStorage.getItem(\'cclb:arrive\'))d.classList.add(\'arriving\')}catch(e){}setTimeout(function(){if(!window.__booted)d.classList.remove(\'motion\',\'arriving\')},4000)})();</script>' . "\n";
	echo '<script defer src="' . $u( 'assets/js/vendor/gsap.min.js' ) . '"></script>' . "\n";
	echo '<script defer src="' . $u( 'assets/js/vendor/ScrollTrigger.min.js' ) . '"></script>' . "\n";
	echo '<script defer src="' . $u( 'assets/js/vendor/SplitText.min.js' ) . '"></script>' . "\n";
	if ( ! cclb_native_scroll() ) {
		echo '<script defer src="' . $u( 'assets/js/vendor/lenis.min.js' ) . '"></script>' . "\n";
	}
	echo '<script type="module" src="' . $u( 'assets/js/main.js' ) . '"></script>' . "\n";
	$data = array( 'masses' => cclb_masses(), 'gallery' => array_values( (array) cclb_parish( 'gallery' ) ) );
	echo '<script type="application/json" id="cclb-data">' . wp_json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP ) . '</script>' . "\n";
}, 5 );

add_filter( 'language_attributes', function ( $attrs ) {
	return cclb_native_scroll() ? $attrs . ' data-native-scroll' : $attrs;
} );

/** Pages that hold an embedded form scroll natively (set `native_scroll` in cclb_page()). */
function cclb_native_scroll() {
	global $cclb_page;
	return ! empty( $cclb_page['native_scroll'] );
}

/**
 * Each template declares its title, description and options before get_header().
 *
 * @param string $title Page title.
 * @param string $desc  Meta description.
 * @param array  $opts  noindex (bool), native_scroll (bool), current (nav label).
 */
function cclb_page( $title, $desc, $opts = array() ) {
	global $cclb_page;
	$cclb_page = array_merge( array( 'title' => $title, 'desc' => $desc, 'current' => '' ), $opts );
}

add_filter( 'pre_get_document_title', function ( $t ) {
	global $cclb_page;
	return $cclb_page ? $cclb_page['title'] : $t;
} );

add_action( 'wp_head', function () {
	global $cclb_page;
	if ( ! $cclb_page ) {
		return;
	}
	$p     = cclb_parish();
	$title = $cclb_page['title'];
	$desc  = $cclb_page['desc'];
	$img   = get_theme_file_uri( 'assets/img/og.png' );
	echo '<meta name="description" content="' . esc_attr( $desc ) . '">' . "\n";
	if ( ! empty( $cclb_page['noindex'] ) ) {
		echo '<meta name="robots" content="noindex">' . "\n";
	}
	echo '<meta name="theme-color" content="#0D3A5A">' . "\n";
	echo '<meta property="og:type" content="website">' . "\n";
	echo '<meta property="og:site_name" content="' . esc_attr( $p['name'] ) . '">' . "\n";
	echo '<meta property="og:title" content="' . esc_attr( $title ) . '">' . "\n";
	echo '<meta property="og:description" content="' . esc_attr( $desc ) . '">' . "\n";
	echo '<meta property="og:url" content="' . esc_url( trailingslashit( home_url( $GLOBALS['wp']->request ) ) ) . '">' . "\n";
	echo '<meta property="og:image" content="' . esc_url( $img ) . '">' . "\n";
	echo '<meta property="og:image:width" content="1200">' . "\n" . '<meta property="og:image:height" content="630">' . "\n";
	echo '<meta property="og:image:alt" content="The parish name set on a waterline, river blue below">' . "\n";
	echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
	echo '<meta name="twitter:title" content="' . esc_attr( $title ) . '">' . "\n";
	echo '<meta name="twitter:description" content="' . esc_attr( $desc ) . '">' . "\n";
	echo '<meta name="twitter:image" content="' . esc_url( $img ) . '">' . "\n";
	echo '<link rel="icon" href="' . esc_url( get_theme_file_uri( 'assets/img/favicon.ico' ) ) . '" sizes="48x48">' . "\n";
	echo '<link rel="icon" href="' . esc_url( get_theme_file_uri( 'assets/img/favicon-32.png' ) ) . '" type="image/png" sizes="32x32">' . "\n";
	echo '<link rel="apple-touch-icon" href="' . esc_url( get_theme_file_uri( 'assets/img/apple-touch-icon.png' ) ) . '">' . "\n";
	foreach ( array( 'gambetta-400-normal', 'general-sans-500-normal' ) as $f ) {
		echo '<link rel="preload" href="' . esc_url( get_theme_file_uri( "assets/fonts/$f.woff2" ) ) . '" as="font" type="font/woff2" crossorigin>' . "\n";
	}
	$ld = array(
		'@context'      => 'https://schema.org',
		'@type'         => 'Church',
		'name'          => $p['name'],
		'alternateName' => 'CCLB Lekki',
		'url'           => home_url( '/' ),
		'logo'          => get_theme_file_uri( 'assets/img/crest-192.png' ),
		'image'         => $img,
		'telephone'     => $p['phone'],
		'email'         => $p['email'],
		'address'       => array(
			'@type'           => 'PostalAddress',
			'streetAddress'   => 'Osapa London, off Beach Resort',
			'addressLocality' => 'Lekki',
			'addressRegion'   => 'Lagos',
			'addressCountry'  => 'NG',
		),
		'sameAs'        => array_values( array_filter( array( $p['instagram'], $p['youtube'] ) ) ),
	);
	echo '<script type="application/ld+json">' . wp_json_encode( $ld, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG ) . '</script>' . "\n";
}, 6 );

/* The design runs its own page transitions and hover prefetch; WordPress's speculation rules would duplicate them. */
add_filter( 'wp_speculation_rules_configuration', '__return_null' );

/**
 * Create the Give, Harvest and Join pages when the theme is activated (only if the slug is free),
 * so the page templates (page-give.php etc.) are used automatically.
 */
add_action( 'after_switch_theme', function () {
	foreach ( array( 'give' => 'Give', 'harvest' => 'Harvest', 'join' => 'Join' ) as $slug => $title ) {
		if ( ! get_page_by_path( $slug ) ) {
			wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_name' => $slug, 'post_title' => $title ) );
		}
	}
	flush_rewrite_rules();
} );
