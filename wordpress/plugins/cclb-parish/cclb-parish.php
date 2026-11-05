<?php
/**
 * Plugin Name: CCLB Parish
 * Description: Parish Details screen for the Catholic Church of the Lord's Baptism: Mass times, contacts, monthly rites, giving accounts and gallery, plus the Join and Harvest forms with their saved entries. Works with the CCLB Waterline theme.
 * Version: 0.2.0
 * Requires at least: 6.4
 * Requires PHP: 8.1
 * Author: Paul Nwokolo
 * License: GPL-2.0-or-later
 * Text Domain: cclb-parish
 *
 * Everything is saved in one option, `cclb_parish`, which the theme reads (see the theme's
 * inc/parish.php for the defaults and the exact shape). The last 10 versions are kept in
 * `cclb_parish_history` so a wrong save can be undone from the screen.
 */
defined( 'ABSPATH' ) || exit;

const CCLB_PARISH_CAP = 'edit_pages'; // Editors and Administrators.
define( 'CCLB_PARISH_FILE', __FILE__ );

require_once __DIR__ . '/includes/forms.php';
require_once __DIR__ . '/includes/entries.php';

add_action( 'admin_menu', function () {
	add_menu_page( 'Parish Details', 'Parish Details', CCLB_PARISH_CAP, 'cclb-parish', 'cclb_parish_screen', 'dashicons-admin-home', 3 );
} );

add_action( 'admin_enqueue_scripts', function ( $hook ) {
	if ( 'toplevel_page_cclb-parish' !== $hook ) {
		return;
	}
	wp_enqueue_media();
	wp_enqueue_style( 'cclb-parish-admin', plugins_url( 'assets/admin.css', __FILE__ ), array(), '0.2.0' );
	wp_enqueue_script( 'cclb-parish-admin', plugins_url( 'assets/admin.js', __FILE__ ), array(), '0.2.0', true );
} );

/* ---------------------------------------------------------------- helpers */

function cclb_p_defaults() {
	return function_exists( 'cclb_defaults' ) ? cclb_defaults() : array();
}

function cclb_p_current() {
	$saved = get_option( 'cclb_parish', array() );
	return array_merge( cclb_p_defaults(), is_array( $saved ) ? $saved : array() );
}

function cclb_p_field( $name, $label, $value, $opts = array() ) {
	$type = $opts['type'] ?? 'text';
	$id   = 'f-' . sanitize_html_class( $name );
	echo '<div class="cclb-field' . ( ! empty( $opts['wide'] ) ? ' cclb-field--wide' : '' ) . '"><label for="' . esc_attr( $id ) . '">' . esc_html( $label ) . '</label>';
	echo '<input id="' . esc_attr( $id ) . '" type="' . esc_attr( $type ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '"' . ( ! empty( $opts['placeholder'] ) ? ' placeholder="' . esc_attr( $opts['placeholder'] ) . '"' : '' ) . '>';
	if ( ! empty( $opts['help'] ) ) {
		echo '<p class="description">' . esc_html( $opts['help'] ) . '</p>';
	}
	echo '</div>';
}

/** One week slot row. $n is the field name prefix up to the index. */
function cclb_p_slot_row( $prefix, $i, $s ) {
	$types = array( 'mass' => 'Mass (counts for "Next Mass")', 'prep' => 'Preparation', 'other' => 'Other line', 'quiet' => 'Quiet note (e.g. office hours)' );
	echo '<div class="cclb-row" data-row>';
	echo '<input type="text" name="' . esc_attr( "{$prefix}[$i][time]" ) . '" value="' . esc_attr( $s[0] ) . '" placeholder="6:30 AM" aria-label="Time as shown">';
	echo '<input type="text" name="' . esc_attr( "{$prefix}[$i][label]" ) . '" value="' . esc_attr( $s[1] ) . '" placeholder="Mass" aria-label="What happens">';
	echo '<select name="' . esc_attr( "{$prefix}[$i][type]" ) . '" aria-label="Kind">';
	foreach ( $types as $k => $l ) {
		echo '<option value="' . esc_attr( $k ) . '"' . selected( $s[2], $k, false ) . '>' . esc_html( $l ) . '</option>';
	}
	echo '</select>';
	echo '<input type="time" name="' . esc_attr( "{$prefix}[$i][at]" ) . '" value="' . esc_attr( $s[3] ) . '" aria-label="Start time (Masses only)">';
	echo '<button type="button" class="button-link-delete" data-remove>Remove</button></div>';
}

function cclb_p_pair_row( $prefix, $i, $r ) {
	echo '<div class="cclb-row cclb-row--pair" data-row>';
	echo '<input type="text" name="' . esc_attr( "{$prefix}[$i][a]" ) . '" value="' . esc_attr( $r[0] ) . '" aria-label="Name">';
	echo '<input type="text" name="' . esc_attr( "{$prefix}[$i][b]" ) . '" value="' . esc_attr( $r[1] ) . '" aria-label="When">';
	echo '<button type="button" class="button-link-delete" data-remove>Remove</button></div>';
}

function cclb_p_gallery_row( $prefix, $i, $g ) {
	echo '<div class="cclb-row cclb-row--gallery" data-row>';
	echo '<input type="url" name="' . esc_attr( "{$prefix}[$i][image]" ) . '" value="' . esc_attr( $g['image'] ?? '' ) . '" placeholder="Photo address" aria-label="Photo address" data-image>';
	echo '<button type="button" class="button" data-pick>Choose photo</button>';
	echo '<input type="url" name="' . esc_attr( "{$prefix}[$i][video]" ) . '" value="' . esc_attr( $g['video'] ?? '' ) . '" placeholder="or a YouTube embed address" aria-label="YouTube embed address">';
	echo '<input type="text" name="' . esc_attr( "{$prefix}[$i][alt]" ) . '" value="' . esc_attr( $g['alt'] ?? '' ) . '" placeholder="Describe what it shows" aria-label="Description">';
	echo '<button type="button" class="button-link-delete" data-remove>Remove</button></div>';
}

/** Lines for the account / society text boxes. */
function cclb_p_lines( $rows ) {
	return implode( "\n", array_map( function ( $r ) { return implode( ' | ', $r ); }, (array) $rows ) );
}

function cclb_p_fund_block( $i, $f ) {
	$societies = ! empty( $f['societies'] );
	$lines     = $societies ? cclb_p_lines( $f['societies'] ) : cclb_p_lines( $f['accounts'] ?? array() );
	echo '<div class="cclb-fund" data-row>';
	echo '<div class="cclb-grid">';
	cclb_p_field( "giving_funds[$i][name]", 'Fund name', $f['name'] ?? '' );
	cclb_p_field( "giving_funds[$i][id]", 'Short code', $f['id'] ?? '', array( 'help' => 'Lower case letters, no spaces. Do not change for existing funds.' ) );
	cclb_p_field( "giving_funds[$i][tag]", 'Small label (optional)', $f['tag'] ?? '', array( 'placeholder' => 'This season' ) );
	cclb_p_field( "giving_funds[$i][blurb]", 'One-line description', $f['blurb'] ?? '', array( 'wide' => true ) );
	echo '</div>';
	echo '<div class="cclb-field cclb-field--wide"><label>This fund has</label><select name="' . esc_attr( "giving_funds[$i][mode]" ) . '"><option value="accounts"' . selected( ! $societies, true, false ) . '>One or more bank accounts</option><option value="societies"' . selected( $societies, true, false ) . '>A list of societies, each with its own account</option></select></div>';
	echo '<div class="cclb-field cclb-field--wide"><label for="' . esc_attr( "fl-$i" ) . '">Accounts, one per line</label><textarea id="' . esc_attr( "fl-$i" ) . '" rows="' . ( $societies ? 8 : 3 ) . '" name="' . esc_attr( "giving_funds[$i][lines]" ) . '">' . esc_textarea( $lines ) . '</textarea>';
	echo '<p class="description">Accounts: <code>Account name | Bank | Account number</code>. Societies: <code>Society | Account name | Bank | Account number</code>. Account numbers are 10 digits.</p></div>';
	echo '<button type="button" class="button-link-delete" data-remove>Remove this fund</button></div>';
}

/* ----------------------------------------------------------------- screen */

function cclb_parish_screen() {
	if ( ! current_user_can( CCLB_PARISH_CAP ) ) {
		wp_die( 'Not allowed.' );
	}
	if ( ! function_exists( 'cclb_defaults' ) ) {
		echo '<div class="wrap"><h1>Parish Details</h1><div class="notice notice-error"><p>The CCLB Waterline theme must be active to use this screen.</p></div></div>';
		return;
	}
	$d    = cclb_p_current();
	$meta = (array) get_option( 'cclb_parish_meta', array() );
	$hist = get_option( 'cclb_parish_history', array() );
	$msgs = get_transient( 'cclb_parish_notice_' . get_current_user_id() );
	delete_transient( 'cclb_parish_notice_' . get_current_user_id() );
	?>
<div class="wrap cclb-admin">
  <h1>Parish Details</h1>
  <p class="description">Change Mass times, contact details, giving accounts and more here. The website updates when you press Save. <?php
	if ( ! empty( $meta['at'] ) ) {
		echo 'Last saved by ' . esc_html( $meta['by'] ) . ' on ' . esc_html( wp_date( 'j F Y, g:i a', (int) $meta['at'] ) ) . '.';
	}
	?></p>
	<?php
	foreach ( (array) $msgs as $m ) {
		echo '<div class="notice notice-' . esc_attr( $m[0] ) . '"><p>' . esc_html( $m[1] ) . '</p></div>';
	}
	?>
  <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
    <input type="hidden" name="action" value="cclb_parish_save">
	<?php wp_nonce_field( 'cclb_parish_save' ); ?>

    <h2>Contact and social</h2>
    <div class="cclb-grid">
	<?php
	cclb_p_field( 'phone', 'Phone', $d['phone'] );
	cclb_p_field( 'email', 'Email', $d['email'], array( 'type' => 'email' ) );
	cclb_p_field( 'address', 'Full address', $d['address'], array( 'wide' => true ) );
	cclb_p_field( 'address_h', 'Address as the big heading on the home page', $d['address_h'], array( 'wide' => true ) );
	cclb_p_field( 'office', "Priest's office hours (sentence)", $d['office'], array( 'wide' => true ) );
	cclb_p_field( 'maps_url', 'Google Maps link', $d['maps_url'], array( 'type' => 'url', 'wide' => true ) );
	cclb_p_field( 'instagram', 'Instagram address', $d['instagram'], array( 'type' => 'url' ) );
	cclb_p_field( 'youtube', 'YouTube address', $d['youtube'], array( 'type' => 'url' ) );
	cclb_p_field( 'holiday', 'Note under the week', $d['holiday'], array( 'wide' => true ) );
	?>
    </div>

    <h2>The parish week</h2>
    <p class="description">Add a line for anything that happens that day. Tuesday and Thursday have no Mass at present; to add one, press "Add a line" under that day, choose <em>Mass</em> and set the start time. Times are Lagos time. "Next Mass" on the home page is worked out from the lines marked Mass.</p>
	<?php foreach ( $d['week'] as $di => $day ) : ?>
    <fieldset class="cclb-day" data-repeater>
      <legend><?php echo esc_html( $day[0] ); ?></legend>
      <div class="cclb-rows" data-rows>
		<?php foreach ( $day[1] as $si => $s ) { cclb_p_slot_row( "week[$di]", $si, $s ); } ?>
      </div>
      <template data-template><?php cclb_p_slot_row( "week[$di]", '__i__', array( '', '', 'mass', '' ) ); ?></template>
      <button type="button" class="button" data-add>Add a line</button>
    </fieldset>
	<?php endforeach; ?>

    <h2>Through the month</h2>
    <div data-repeater>
      <div class="cclb-rows" data-rows><?php foreach ( $d['month'] as $i => $r ) { cclb_p_pair_row( 'month', $i, $r ); } ?></div>
      <template data-template><?php cclb_p_pair_row( 'month', '__i__', array( '', '' ) ); ?></template>
      <button type="button" class="button" data-add>Add a line</button>
    </div>

    <h2>Giving</h2>
    <p class="description">These are the bank accounts shown on the Give page and the Harvest page. Check every digit after saving: open the Give page and compare with the bank.</p>
    <div class="cclb-grid">
	<?php
	cclb_p_field( 'giving_whatsapp', 'WhatsApp number for confirmations', $d['giving']['whatsapp'], array( 'help' => 'Digits only with country code, no plus. Example: 2349132222286' ) );
	cclb_p_field( 'giving_default', 'Fund shown first (its short code)', $d['giving']['default_fund'], array( 'help' => 'During harvest: harvest. After harvest: offertory.' ) );
	?>
    </div>
    <div data-repeater>
      <div data-rows><?php foreach ( $d['giving']['funds'] as $i => $f ) { cclb_p_fund_block( $i, $f ); } ?></div>
      <template data-template><?php cclb_p_fund_block( '__i__', array( 'accounts' => array() ) ); ?></template>
      <button type="button" class="button" data-add>Add a fund</button>
    </div>

    <h2>Photos and videos</h2>
    <p class="description">Shown in "From the parish" on the home page. The section stays hidden while this list is empty. Use real parish photos.</p>
    <div data-repeater>
      <div class="cclb-rows" data-rows><?php foreach ( (array) $d['gallery'] as $i => $g ) { cclb_p_gallery_row( 'gallery', $i, $g ); } ?></div>
      <template data-template><?php cclb_p_gallery_row( 'gallery', '__i__', array() ); ?></template>
      <button type="button" class="button" data-add>Add a photo or video</button>
    </div>

	<?php submit_button( 'Save' ); ?>
  </form>

	<?php if ( ! empty( $hist ) ) : ?>
  <hr>
  <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" onsubmit="return confirm('Go back to the version before the last save? The version on the website will change immediately.');">
    <input type="hidden" name="action" value="cclb_parish_restore">
	<?php wp_nonce_field( 'cclb_parish_restore' ); ?>
    <p>Saved something wrong? <button class="button">Undo the last save</button></p>
  </form>
	<?php endif; ?>
</div>
	<?php
}

/* ------------------------------------------------------------------- save */

function cclb_p_text( $v, $max = 300 ) {
	return mb_substr( trim( sanitize_text_field( wp_unslash( (string) $v ) ) ), 0, $max );
}

function cclb_p_save_history( $old ) {
	$h   = get_option( 'cclb_parish_history', array() );
	$h[] = array( 'at' => time(), 'by' => wp_get_current_user()->user_login, 'data' => $old );
	update_option( 'cclb_parish_history', array_slice( $h, -10 ), false );
}

function cclb_p_parse_lines( $raw, $cols, &$errors, $fund ) {
	$rows = array();
	foreach ( preg_split( '/\R/', (string) wp_unslash( $raw ) ) as $n => $line ) {
		$line = trim( $line );
		if ( '' === $line ) {
			continue;
		}
		$c = array_map( 'trim', explode( '|', $line ) );
		if ( count( $c ) !== $cols ) {
			$errors[] = "$fund, line " . ( $n + 1 ) . ": expected $cols parts separated by |, not saved: $line";
			continue;
		}
		$c = array_map( 'sanitize_text_field', $c );
		$num = preg_replace( '/\D/', '', $c[ $cols - 1 ] );
		if ( 10 !== strlen( $num ) ) {
			$errors[] = "$fund, line " . ( $n + 1 ) . ": the account number must be 10 digits, not saved: $line";
			continue;
		}
		$c[ $cols - 1 ] = $num;
		$rows[]         = $c;
	}
	return $rows;
}

add_action( 'admin_post_cclb_parish_save', function () {
	if ( ! current_user_can( CCLB_PARISH_CAP ) ) {
		wp_die( 'Not allowed.' );
	}
	check_admin_referer( 'cclb_parish_save' );
	$old    = cclb_p_current();
	$r      = $_POST; // phpcs:ignore WordPress.Security.NonceVerification -- checked above.
	$errors = array();
	$new    = $old;

	foreach ( array( 'phone', 'address', 'address_h', 'office', 'holiday' ) as $k ) {
		$new[ $k ] = cclb_p_text( $r[ $k ] ?? '' );
	}
	$new['email']     = sanitize_email( wp_unslash( $r['email'] ?? '' ) ) ?: $old['email'];
	$new['maps_url']  = esc_url_raw( wp_unslash( $r['maps_url'] ?? '' ) );
	$new['instagram'] = esc_url_raw( wp_unslash( $r['instagram'] ?? '' ) );
	$new['youtube']   = esc_url_raw( wp_unslash( $r['youtube'] ?? '' ) );

	// Week: keep the seven fixed day names, replace the lines.
	$week = array();
	foreach ( $old['week'] as $di => $day ) {
		$slots = array();
		foreach ( (array) ( $r['week'][ $di ] ?? array() ) as $s ) {
			$time = cclb_p_text( $s['time'] ?? '', 40 );
			$type = in_array( $s['type'] ?? '', array( 'mass', 'prep', 'other', 'quiet' ), true ) ? $s['type'] : 'other';
			$at   = preg_match( '/^\d{2}:\d{2}$/', (string) ( $s['at'] ?? '' ) ) ? $s['at'] : '';
			if ( '' === $time && '' === cclb_p_text( $s['label'] ?? '' ) ) {
				continue;
			}
			if ( 'mass' === $type && '' === $at ) {
				$errors[] = $day[0] . ': the Mass at "' . $time . '" has no start time, so it is not counted for "Next Mass". Set the start time and save again.';
			}
			$slots[] = array( $time, cclb_p_text( $s['label'] ?? '', 80 ), $type, $at );
		}
		$week[ $di ] = array( $day[0], $slots );
	}
	$new['week'] = $week;

	$month = array();
	foreach ( (array) ( $r['month'] ?? array() ) as $m ) {
		$a = cclb_p_text( $m['a'] ?? '', 120 );
		if ( '' !== $a ) {
			$month[] = array( $a, cclb_p_text( $m['b'] ?? '', 160 ) );
		}
	}
	$new['month'] = $month;

	// Giving.
	$funds = array();
	foreach ( (array) ( $r['giving_funds'] ?? array() ) as $f ) {
		$name = cclb_p_text( $f['name'] ?? '', 120 );
		if ( '' === $name ) {
			continue;
		}
		$id  = sanitize_key( $f['id'] ?? '' ) ?: sanitize_key( $name );
		$fnd = array( 'id' => $id, 'name' => $name, 'tag' => cclb_p_text( $f['tag'] ?? '', 40 ), 'blurb' => cclb_p_text( $f['blurb'] ?? '', 200 ) );
		if ( 'societies' === ( $f['mode'] ?? '' ) ) {
			$fnd['societies'] = cclb_p_parse_lines( $f['lines'] ?? '', 4, $errors, $name );
		} else {
			$fnd['accounts'] = cclb_p_parse_lines( $f['lines'] ?? '', 3, $errors, $name );
		}
		if ( empty( $fnd['accounts'] ) && empty( $fnd['societies'] ) ) {
			$errors[] = $name . ': no valid account, so this fund was not saved.';
			continue;
		}
		$funds[] = $fnd;
	}
	if ( $funds ) {
		$ids = wp_list_pluck( $funds, 'id' );
		$def = sanitize_key( $r['giving_default'] ?? '' );
		$new['giving'] = array(
			'default_fund' => in_array( $def, $ids, true ) ? $def : $ids[0],
			'whatsapp'     => preg_replace( '/\D/', '', (string) ( $r['giving_whatsapp'] ?? '' ) ) ?: $old['giving']['whatsapp'],
			'funds'        => $funds,
		);
	} else {
		$errors[] = 'No valid funds were found, so the Giving section was left as it was.';
	}

	$gallery = array();
	foreach ( (array) ( $r['gallery'] ?? array() ) as $g ) {
		$img = esc_url_raw( wp_unslash( $g['image'] ?? '' ) );
		$vid = esc_url_raw( wp_unslash( $g['video'] ?? '' ) );
		if ( $vid && ! preg_match( '#^https://www\.youtube(-nocookie)?\.com/embed/[\w-]{6,}#', $vid ) ) {
			$errors[] = 'Video address not saved (use a YouTube embed address like https://www.youtube.com/embed/ID): ' . $vid;
			$vid      = '';
		}
		if ( $img || $vid ) {
			$gallery[] = array( 'image' => $img, 'video' => $vid, 'alt' => cclb_p_text( $g['alt'] ?? '', 160 ) );
		}
	}
	$new['gallery'] = $gallery;

	cclb_p_save_history( get_option( 'cclb_parish', array() ) );
	update_option( 'cclb_parish', $new, false );
	update_option( 'cclb_parish_meta', array( 'by' => wp_get_current_user()->display_name, 'at' => time() ), false );

	$msgs = array( array( 'success', 'Saved. The website now shows these details.' ) );
	foreach ( $errors as $e ) {
		$msgs[] = array( 'warning', $e );
	}
	set_transient( 'cclb_parish_notice_' . get_current_user_id(), $msgs, 60 );
	wp_safe_redirect( admin_url( 'admin.php?page=cclb-parish' ) );
	exit;
} );

add_action( 'admin_post_cclb_parish_restore', function () {
	if ( ! current_user_can( CCLB_PARISH_CAP ) ) {
		wp_die( 'Not allowed.' );
	}
	check_admin_referer( 'cclb_parish_restore' );
	$h = get_option( 'cclb_parish_history', array() );
	if ( $h ) {
		$last = array_pop( $h );
		update_option( 'cclb_parish', $last['data'], false );
		update_option( 'cclb_parish_history', $h, false );
		update_option( 'cclb_parish_meta', array( 'by' => wp_get_current_user()->display_name . ' (undo)', 'at' => time() ), false );
		set_transient( 'cclb_parish_notice_' . get_current_user_id(), array( array( 'success', 'Went back to the version before the last save.' ) ), 60 );
	}
	wp_safe_redirect( admin_url( 'admin.php?page=cclb-parish' ) );
	exit;
} );
