<?php
/**
 * Parish details: the one place the theme reads Mass times, contact details and
 * the monthly rites from.
 *
 * Defaults live below. The companion plugin "CCLB Parish" adds a settings screen
 * (Settings > Parish Details) that saves an override in the option `cclb_parish`.
 * Without the plugin the defaults are used, so the theme always renders.
 *
 * Slot types in the week: 'mass', 'prep' (shown with Mass in the footer),
 * 'other' (a normal line) and 'quiet' (muted line, e.g. office hours).
 * `at` (24 hour HH:MM) is only needed for 'mass' slots: it drives "Next Mass".
 */
defined( 'ABSPATH' ) || exit;

function cclb_defaults() {
	return array(
		'name'      => "Catholic Church of the Lord\u{2019}s Baptism",
		'short'     => "The Lord\u{2019}s Baptism",
		'phone'     => '+234 913 222 2286',
		'email'     => 'info@cclblekki.com',
		'address'   => 'Osapa London, off Beach Resort, Lekki, Lagos',
		'area'      => 'Osapa London, Lekki',
		'address_h' => 'Osapa London, off Beach Resort, Lekki',
		'country'   => 'Lagos, Nigeria',
		'maps_url'  => 'https://maps.google.com/?q=Catholic+Church+of+the+Lord%27s+Baptism+Osapa+Lekki+Lagos',
		'office'    => 'Tuesday, Wednesday and Friday, 9:00 AM to 2:00 PM',
		'instagram' => 'https://www.instagram.com/cclblekki/',
		'youtube'   => 'https://www.youtube.com/@cclblekki',
		'holiday'   => 'Public holidays: Mass at 8:00 AM.',
		'week'      => array(
			0 => array( 'Sunday', array(
				array( '8:00 AM', 'Preparation', 'prep', '' ),
				array( '8:30 AM', 'Holy Mass', 'mass', '08:30' ),
				array( '11:00 AM', 'RCIA catechism', 'other', '' ),
			) ),
			1 => array( 'Monday', array(
				array( '6:30 AM', 'Mass', 'mass', '06:30' ),
			) ),
			2 => array( 'Tuesday', array(
				array( '9 AM to 2 PM', "Priest\u{2019}s office open", 'quiet', '' ),
			) ),
			3 => array( 'Wednesday', array(
				array( '6:30 AM', 'Mass', 'mass', '06:30' ),
				array( '9 AM to 2 PM', "Priest\u{2019}s office open", 'quiet', '' ),
			) ),
			4 => array( 'Thursday', array(
				array( 'No Mass listed', '', 'quiet', '' ),
			) ),
			5 => array( 'Friday', array(
				array( '6:30 AM', 'Mass', 'mass', '06:30' ),
				array( '9 AM to 2 PM', "Priest\u{2019}s office open", 'quiet', '' ),
			) ),
			6 => array( 'Saturday', array(
				array( '8:00 AM', 'Mass', 'mass', '08:00' ),
				array( '8:45 AM', 'Confession', 'other', '' ),
				array( '9:00 AM', "Children\u{2019}s catechism", 'other', '' ),
			) ),
		),
		'month'     => array(
			array( 'Infant baptism', 'First Saturday, at the 8:00 AM Mass' ),
			array( 'Pre-baptism instructions', 'Parents and godparents. Last Saturday, 9:00 AM' ),
			array( 'Communion to the sick and aged', 'First Friday, from 9:00 AM' ),
			array( 'Eucharistic adoration', 'Last Friday, 6:30 PM' ),
			array( 'Small Christian Communities', 'First and third Sundays, 5:00 to 6:00 PM' ),
			array( 'Organisations meeting', 'Second Sunday, 10:30 AM' ),
		),
		'giving'    => array(
			'default_fund' => 'harvest',
			'whatsapp'     => '2349132222286',
			'funds'        => array(
				array(
					'id' => 'offertory', 'name' => 'Offertory, Mass Booking & Tithe', 'tag' => '',
					'blurb' => 'Your weekly offertory, Mass intentions, and tithe.',
					'accounts' => array(
						array( "Catholic Church of the Lord's Baptism", 'Globus Bank', '1000008403' ),
					),
				),
				array(
					'id' => 'projects', 'name' => 'Donation Projects', 'tag' => '',
					'blurb' => 'Support parish projects and development.',
					'accounts' => array(
						array( "Catholic Church of the Lord's Baptism", 'Zenith Bank', '1015067231' ),
					),
				),
				array(
					'id' => 'harvest', 'name' => 'Annual Harvest', 'tag' => 'This season',
					'blurb' => 'Our yearly harvest thanksgiving — supporting the parish for the year ahead.',
					'accounts' => array(
						array( "Catholic Church of the Lord's Baptism", 'Zenith Bank', '1312487204' ),
					),
				),
				array(
					'id' => 'societies', 'name' => 'Other Societies', 'tag' => '',
					'blurb' => 'Give directly to one of our parish societies and ministries. Use the menu below to find yours.',
					'societies' => array(
						array( 'Catholic Men Organization', 'CCLB - Catholic Men Organization', 'Zenith Bank', '1312535088' ),
						array( 'Catholic Women Organization', 'CCLB - Catholic Women Organization', 'Zenith Bank', '1312524871' ),
						array( 'Catholic Youth Organization of Nigeria', 'CCLB - Catholic Youth Organization', 'Zenith Bank', '1312524857' ),
						array( 'Missionary Childhood Association', 'CCLB - Missionary Childhood Association', 'Zenith Bank', '1229456010' ),
						array( 'St. Vincent De Paul', 'CCLB - St. Vincent De Paul', 'Zenith Bank', '1312524826' ),
						array( 'Confraternity of Christian Mothers', 'CCLB - Confraternity of Christian Mothers', 'Zenith Bank', '1229456742' ),
						array( 'Guild of Faith Formators', 'CCLB - Guild of Faith Formators', 'Zenith Bank', '1229456979' ),
						array( 'Ministry of Lectors', 'CCLB - Ministry of Lectors', 'Zenith Bank', '1229457505' ),
						array( 'Ministry of Choir', 'CCLB - Ministry of Choir', 'Zenith Bank', '1229457653' ),
						array( 'Ministry of Altar Servers', 'CCLB - Ministry of Altar Servers', 'Zenith Bank', '1229457732' ),
						array( 'Parish Laity Council', 'CCLB - Parish Laity Council', 'Zenith Bank', '1229456780' ),
						array( 'Parish Pastoral Council', 'CCLB - Parish Pastoral Council', 'Zenith Bank', '1229456917' ),
					),
				),
			),
		),
		'gallery'   => array(), // items: array( 'image' => url, 'video' => embed url, 'alt' => text )
	);
}

/** Saved settings laid over the defaults. Cached per request. */
function cclb_parish( $key = null ) {
	static $data = null;
	if ( null === $data ) {
		$saved = get_option( 'cclb_parish', array() );
		$data  = array_merge( cclb_defaults(), is_array( $saved ) ? $saved : array() );
	}
	if ( null === $key ) {
		return $data;
	}
	return isset( $data[ $key ] ) ? $data[ $key ] : '';
}

/** Text safe for HTML with a non-breaking space before AM / PM, as the design sets times. */
function cclb_time_html( $text ) {
	return preg_replace( '/ (AM|PM)\b/', '&nbsp;$1', esc_html( $text ) );
}

/** The Mass slots as the JavaScript wants them: [{d,h,m}]. */
function cclb_masses() {
	$out = array();
	foreach ( cclb_parish( 'week' ) as $d => $day ) {
		foreach ( $day[1] as $slot ) {
			if ( 'mass' === $slot[2] && preg_match( '/^(\d{1,2}):(\d{2})$/', $slot[3], $m ) ) {
				$out[] = array( 'd' => (int) $d, 'h' => (int) $m[1], 'm' => (int) $m[2] );
			}
		}
	}
	return $out;
}

/** The Sunday Mass slot, for the menu and the page description. */
function cclb_sunday_mass() {
	$week = cclb_parish( 'week' );
	if ( isset( $week[0] ) ) {
		foreach ( $week[0][1] as $slot ) {
			if ( 'mass' === $slot[2] ) {
				return $slot[0];
			}
		}
	}
	return '';
}

function cclb_tel( $phone ) {
	return 'tel:' . preg_replace( '/[^\d+]/', '', $phone );
}

/** One fund as the give page JavaScript wants it (objects, not the compact stored rows). */
function cclb_giving_for_js() {
	$g     = cclb_parish( 'giving' );
	$funds = array();
	foreach ( (array) $g['funds'] as $f ) {
		$o = array( 'id' => $f['id'], 'name' => $f['name'], 'tag' => $f['tag'] ?? '', 'blurb' => $f['blurb'] ?? '' );
		if ( ! empty( $f['societies'] ) ) {
			$o['societies'] = array();
			foreach ( $f['societies'] as $s ) {
				$o['societies'][] = array( 'name' => $s[0], 'accountName' => $s[1], 'bankName' => $s[2], 'accountNumber' => $s[3] );
			}
		} else {
			$o['accounts'] = array();
			foreach ( (array) ( $f['accounts'] ?? array() ) as $a ) {
				$o['accounts'][] = array( 'accountName' => $a[0], 'bankName' => $a[1], 'accountNumber' => $a[2] );
			}
		}
		$funds[] = $o;
	}
	return array(
		'defaultFund'    => $g['default_fund'],
		'whatsappNumber' => $g['whatsapp'],
		'shortName'      => cclb_parish( 'short' ),
		'funds'          => $funds,
		'icons'          => array( 'copy' => cclb_icon( 'copy' ), 'wa' => cclb_icon( 'wa' ) ),
	);
}

/** The first account of a fund by id, e.g. the harvest account: array( name, bank, number ) or null. */
function cclb_fund_account( $id ) {
	foreach ( (array) cclb_parish( 'giving' )['funds'] as $f ) {
		if ( $f['id'] === $id && ! empty( $f['accounts'][0] ) ) {
			return $f['accounts'][0];
		}
	}
	return null;
}
