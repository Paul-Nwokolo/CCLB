<?php
/**
 * Parish forms: Join the parish register, and Harvest commitment.
 * Replaces the Tally forms. Entries are stored in the site's own database (table cclb_submissions),
 * listed under Parish Details > Entries, and the parish email is notified (no personal details in
 * the email, only a link to the entry).
 */
defined( 'ABSPATH' ) || exit;

const CCLB_DB_VERSION = '1';

/* -------------------------------------------------------------- storage */

function cclb_table() {
	global $wpdb;
	return $wpdb->prefix . 'cclb_submissions';
}

function cclb_install_table() {
	global $wpdb;
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	$t = cclb_table();
	dbDelta( "CREATE TABLE $t (
		id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		form varchar(20) NOT NULL,
		created_at datetime NOT NULL,
		name varchar(200) NOT NULL DEFAULT '',
		phone varchar(60) NOT NULL DEFAULT '',
		email varchar(200) NOT NULL DEFAULT '',
		summary varchar(255) NOT NULL DEFAULT '',
		data longtext NOT NULL,
		status varchar(20) NOT NULL DEFAULT 'new',
		PRIMARY KEY  (id),
		KEY form (form),
		KEY created_at (created_at)
	) " . $wpdb->get_charset_collate() . ';' );
	update_option( 'cclb_db_version', CCLB_DB_VERSION );
}
register_activation_hook( CCLB_PARISH_FILE, 'cclb_install_table' );
add_action( 'plugins_loaded', function () {
	if ( get_option( 'cclb_db_version' ) !== CCLB_DB_VERSION ) {
		cclb_install_table();
	}
} );

/* --------------------------------------------------------------- render */

function cclb_f_input( $name, $label, $o = array() ) {
	$type = $o['type'] ?? 'text';
	$id   = 'f-' . sanitize_key( str_replace( array( '[', ']' ), '-', $name ) );
	$req  = ! empty( $o['required'] );
	$h    = '<div class="field"><label for="' . esc_attr( $id ) . '">' . esc_html( $label ) . ( $req ? ' <span class="req" aria-hidden="true">*</span>' : '' ) . '</label>';
	if ( 'textarea' === $type ) {
		$h .= '<textarea class="input" rows="3" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '"' . ( $req ? ' required' : '' ) . '></textarea>';
	} elseif ( 'select' === $type ) {
		$h .= '<select class="input" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '"' . ( $req ? ' required' : '' ) . '><option value="">Choose</option>';
		foreach ( $o['options'] as $k => $v ) {
			$h .= '<option value="' . esc_attr( $k ) . '">' . esc_html( $v ) . '</option>';
		}
		$h .= '</select>';
	} else {
		$extra = '';
		if ( 'date' === $type ) {
			$extra = ' max="' . esc_attr( gmdate( 'Y-m-d' ) ) . '"';
		}
		if ( 'tel' === $type ) {
			$extra .= ' inputmode="tel" autocomplete="tel"';
		}
		if ( 'email' === $type ) {
			$extra .= ' autocomplete="email"';
		}
		if ( 'number' === $type ) {
			$extra .= ' inputmode="numeric" min="1" step="1"';
		}
		$h .= '<input class="input" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" type="' . esc_attr( $type ) . '"' . $extra . ( $req ? ' required' : '' ) . ( ! empty( $o['placeholder'] ) ? ' placeholder="' . esc_attr( $o['placeholder'] ) . '"' : '' ) . '>';
	}
	if ( ! empty( $o['help'] ) ) {
		$h .= '<p class="form-tip">' . esc_html( $o['help'] ) . '</p>';
	}
	return $h . '</div>';
}

/** Radios or checkboxes in a fieldset. */
function cclb_f_choice( $name, $legend, $options, $o = array() ) {
	$multi = ! empty( $o['multi'] );
	$req   = ! empty( $o['required'] );
	$h     = '<fieldset class="choice"><legend>' . esc_html( $legend ) . ( $req ? ' <span class="req" aria-hidden="true">*</span>' : '' ) . '</legend>';
	if ( ! empty( $o['help'] ) ) {
		$h .= '<p class="form-tip">' . esc_html( $o['help'] ) . '</p>';
	}
	$h .= '<div class="choice__list">';
	$i  = 0;
	foreach ( $options as $k => $v ) {
		$id = 'c-' . sanitize_key( $name ) . '-' . ( ++$i );
		$h .= '<label class="choice__opt" for="' . esc_attr( $id ) . '"><input id="' . esc_attr( $id ) . '" type="' . ( $multi ? 'checkbox' : 'radio' ) . '" name="' . esc_attr( $name . ( $multi ? '[]' : '' ) ) . '" value="' . esc_attr( $k ) . '"' . ( $req && ( ! $multi || 1 === count( $options ) ) && 1 === $i ? ' required' : '' ) . '><span>' . esc_html( $v ) . '</span></label>';
	}
	return $h . '</div></fieldset>';
}

function cclb_f_child( $i ) {
	$p = "children[$i]";
	return '<div class="child" data-row><p class="child__title">Child or dependent <span data-n></span></p><div class="form-cols">'
		. cclb_f_input( "{$p}[name]", 'Full name' )
		. cclb_f_input( "{$p}[dob]", 'Date of birth', array( 'type' => 'date' ) )
		. cclb_f_input( "{$p}[gender]", 'Gender', array( 'type' => 'select', 'options' => array( 'Male' => 'Male', 'Female' => 'Female' ) ) )
		. cclb_f_input( "{$p}[phone]", 'Child or parent/guardian phone number', array( 'type' => 'tel' ) )
		. cclb_f_input( "{$p}[email]", 'Child or parent/guardian email address', array( 'type' => 'email' ) )
		. '</div><button type="button" class="chip-btn" data-remove>Remove this child</button></div>';
}

/** The honeypot, the signed time stamp, and the action fields every form shares. */
function cclb_f_guard( $form ) {
	$ts = time();
	return '<input type="hidden" name="action" value="cclb_submit"><input type="hidden" name="cclb_form" value="' . esc_attr( $form ) . '">'
		. '<input type="hidden" name="cclb_ts" value="' . esc_attr( $ts ) . '"><input type="hidden" name="cclb_sig" value="' . esc_attr( wp_hash( $form . '|' . $ts ) ) . '">'
		. '<div class="hp" aria-hidden="true"><label>Leave this empty <input type="text" name="cclb_website" tabindex="-1" autocomplete="off"></label></div>';
}

/** Public: returns the HTML for 'join' or 'harvest'. */
function cclb_form( $form ) {
	$sent = isset( $_GET['cclb_sent'] ) && $form === $_GET['cclb_sent']; // phpcs:ignore WordPress.Security.NonceVerification
	if ( $sent ) {
		$msg = 'join' === $form
			? 'Thank you. Your registration has been received. The parish office will be in touch, and if you asked to join a Small Christian Community, you will be added to its WhatsApp group.'
			: 'Thank you. Your commitment has been received. May God bless your generosity.';
		return '<div class="embed embed--card form-card form-card--sent" id="form-top" tabindex="-1" role="status"><h3 class="form-card__title">Received</h3><p>' . esc_html( $msg ) . '</p><p><a class="link" href="' . esc_url( remove_query_arg( 'cclb_sent' ) ) . '">Send another</a></p></div>';
	}
	wp_enqueue_script( 'cclb-forms', plugins_url( 'assets/forms.js', CCLB_PARISH_FILE ), array(), '0.2.0', array( 'in_footer' => true, 'strategy' => 'defer' ) );
	ob_start();
	?>
<div class="embed embed--card form-card" id="form-top">
  <noscript><p class="form-tip">This form needs JavaScript. Please turn it on, or contact the parish office by phone or email.</p></noscript>
  <form class="pform" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" data-pform="<?php echo esc_attr( $form ); ?>">
	<?php echo cclb_f_guard( $form ); // phpcs:ignore ?>
	<?php if ( 'join' === $form ) : ?>
    <?php echo cclb_f_choice( 'reg_type', 'Registration type', array( 'individual' => 'Individual registration', 'family' => 'Family registration' ), array( 'required' => true ) ); // phpcs:ignore ?>

    <div data-show="individual" hidden>
      <h3 class="form-card__title">Individual registration</h3>
      <div class="form-cols">
		<?php
		echo cclb_f_input( 'i_first', 'First name', array( 'required' => true ) ); // phpcs:ignore
		echo cclb_f_input( 'i_last', 'Last name', array( 'required' => true ) ); // phpcs:ignore
		echo cclb_f_input( 'i_gender', 'Gender', array( 'type' => 'select', 'options' => array( 'Male' => 'Male', 'Female' => 'Female' ) ) ); // phpcs:ignore
		echo cclb_f_input( 'i_dob', 'Date of birth', array( 'type' => 'date' ) ); // phpcs:ignore
		echo cclb_f_input( 'i_phone', 'Phone number', array( 'type' => 'tel', 'required' => true ) ); // phpcs:ignore
		echo cclb_f_input( 'i_email', 'Email address', array( 'type' => 'email' ) ); // phpcs:ignore
		echo cclb_f_input( 'i_address', 'Address', array( 'wide' => true ) ); // phpcs:ignore
		?>
      </div>
    </div>

    <div data-show="family" hidden>
      <h3 class="form-card__title">Primary family contact</h3>
      <div class="form-cols">
		<?php
		echo cclb_f_input( 'f_surname', 'Family surname', array( 'required' => true ) ); // phpcs:ignore
		echo cclb_f_input( 'f_head', 'Head of family name', array( 'required' => true ) ); // phpcs:ignore
		echo cclb_f_input( 'f_head_dob', 'Date of birth', array( 'type' => 'date' ) ); // phpcs:ignore
		echo cclb_f_input( 'f_head_gender', 'Gender', array( 'type' => 'select', 'options' => array( 'Male' => 'Male', 'Female' => 'Female' ) ) ); // phpcs:ignore
		echo cclb_f_input( 'f_phone', 'Phone number', array( 'type' => 'tel', 'required' => true ) ); // phpcs:ignore
		echo cclb_f_input( 'f_email', 'Email address', array( 'type' => 'email' ) ); // phpcs:ignore
		echo cclb_f_input( 'f_address', 'Home address' ); // phpcs:ignore
		?>
      </div>
      <h3 class="form-card__title">Spouse information (optional)</h3>
      <div class="form-cols">
		<?php
		echo cclb_f_input( 's_name', 'Spouse name' ); // phpcs:ignore
		echo cclb_f_input( 's_dob', 'Date of birth', array( 'type' => 'date' ) ); // phpcs:ignore
		echo cclb_f_input( 's_gender', 'Gender', array( 'type' => 'select', 'options' => array( 'Male' => 'Male', 'Female' => 'Female' ) ) ); // phpcs:ignore
		echo cclb_f_input( 's_phone', 'Spouse phone number', array( 'type' => 'tel' ) ); // phpcs:ignore
		echo cclb_f_input( 's_email', 'Spouse email address', array( 'type' => 'email' ) ); // phpcs:ignore
		?>
      </div>
      <h3 class="form-card__title">Children and dependents</h3>
      <p class="form-tip">A child or dependent is someone who has not reached the age of majority (18 years). Add each child with the button below.</p>
      <div data-repeater>
        <div data-rows><?php echo cclb_f_child( 0 ); // phpcs:ignore ?></div>
        <template data-template><?php echo cclb_f_child( '__i__' ); // phpcs:ignore ?></template>
        <button type="button" class="chip-btn" data-add hidden>Add another child</button>
      </div>
	  <?php echo cclb_f_input( 'more_children', 'Other children (optional)', array( 'type' => 'textarea', 'help' => 'Name and date of birth, one child per line.' ) ); // phpcs:ignore ?>
    </div>

    <div data-show="individual family" hidden>
      <h3 class="form-card__title">Parish life</h3>
	  <?php
		echo cclb_f_choice( 'scc', 'Would you like to be added to an SCC (Small Christian Communities) WhatsApp group?', array( 'yes' => 'Yes, please add me to my local SCC WhatsApp group', 'no' => 'No, not at this time' ), array( 'required' => true, 'help' => 'SCCs are neighbourhood-based groups that meet regularly for prayer, fellowship and mutual support. Yours is assigned from your home address. If you say yes, we add you using the phone number above.' ) ); // phpcs:ignore
		echo cclb_f_choice( 'groups', 'Which parish groups are you interested in?', array( 'Choir' => 'Choir', 'Altar servers' => 'Altar servers', 'Catechism / Sunday school' => 'Catechism / Sunday school', 'Charity / outreach' => 'Charity / outreach', 'CMO' => 'CMO', 'CWO' => 'CWO', 'CYAA' => 'CYAA', 'CYON' => 'CYON', 'Others' => 'Others' ), array( 'multi' => true ) ); // phpcs:ignore
		echo cclb_f_input( 'groups_other', 'If you chose Others, which group?' ); // phpcs:ignore
		echo cclb_f_choice( 'consent', 'Consent', array( 'yes' => 'I consent to the parish storing these details for parish records and communication.' ), array( 'multi' => true, 'required' => true ) ); // phpcs:ignore
	  ?>
      <button class="btn btn--solid" type="submit">Submit registration <?php echo cclb_arrow(); // phpcs:ignore ?></button>
    </div>
	<?php else : ?>
    <div class="form-cols">
	  <?php
		echo cclb_f_input( 'h_name', 'Name', array( 'required' => true ) ); // phpcs:ignore
		echo cclb_f_input( 'h_family', 'Family or organisation name', array( 'required' => true ) ); // phpcs:ignore
		echo cclb_f_input( 'h_phone', 'Phone number', array( 'type' => 'tel', 'required' => true ) ); // phpcs:ignore
		echo cclb_f_input( 'h_unit', 'Parish unit, group or society' ); // phpcs:ignore
		echo cclb_f_input( 'h_email', 'Email address', array( 'type' => 'email' ) ); // phpcs:ignore
		echo cclb_f_input( 'h_address', 'Address' ); // phpcs:ignore
	  ?>
    </div>
	<?php
		echo cclb_f_choice( 'h_kind', 'My commitment', array( 'Cash donation (one time)' => 'Cash donation (one time)', 'Pledge (to be paid over time)' => 'Pledge (to be paid over time)', 'Thanksgiving offering' => 'Thanksgiving offering' ), array( 'required' => true ) ); // phpcs:ignore
	?>
    <div class="form-cols">
	  <?php echo cclb_f_input( 'h_amount', 'Amount (naira)', array( 'type' => 'number', 'required' => true, 'help' => 'For a pledge, the total you pledge.' ) ); // phpcs:ignore ?>
    </div>
	<?php
		echo cclb_f_choice( 'h_mode', 'Payment mode', array( 'Cash (on Commitment Day)' => 'Cash (on Commitment Day)', 'Bank deposit / transfer' => 'Bank deposit / transfer' ), array( 'required' => true ) ); // phpcs:ignore
		echo cclb_f_choice( 'consent', 'Agreement', array( 'yes' => 'I understand that this commitment is made freely and joyfully for the glory of God and the advancement of His kingdom through our parish.' ), array( 'multi' => true, 'required' => true ) ); // phpcs:ignore
	?>
    <button class="btn btn--solid" type="submit">Submit <?php echo cclb_arrow(); // phpcs:ignore ?></button>
	<?php endif; ?>
  </form>
</div>
	<?php
	return ob_get_clean();
}

function cclb_arrow() {
	return function_exists( 'cclb_icon' ) ? cclb_icon( 'arrow' ) : '';
}

/* --------------------------------------------------------------- submit */

function cclb_clean( $v, $max = 200 ) {
	return mb_substr( trim( sanitize_text_field( wp_unslash( (string) $v ) ) ), 0, $max );
}

function cclb_fail( $msg ) {
	wp_die( esc_html( $msg ) . ' <a href="javascript:history.back()">Go back</a> and try again, or contact the parish office.', 'Could not send', array( 'response' => 400, 'back_link' => false ) ); // phpcs:ignore
}

add_action( 'admin_post_nopriv_cclb_submit', 'cclb_handle_submit' );
add_action( 'admin_post_cclb_submit', 'cclb_handle_submit' );

function cclb_handle_submit() {
	global $wpdb;
	$r    = $_POST; // phpcs:ignore WordPress.Security.NonceVerification -- public form, guarded by honeypot, signed time stamp and rate limit (cached pages cannot carry a nonce).
	$form = in_array( $r['cclb_form'] ?? '', array( 'join', 'harvest' ), true ) ? $r['cclb_form'] : '';
	if ( ! $form ) {
		cclb_fail( 'Unknown form.' );
	}
	// Bots: filled honeypot, bad or too-fast signature.
	$ts = (int) ( $r['cclb_ts'] ?? 0 );
	if ( ! empty( $r['cclb_website'] ) || ! hash_equals( wp_hash( $form . '|' . $ts ), (string) ( $r['cclb_sig'] ?? '' ) ) || time() - $ts < 3 ) {
		cclb_fail( 'The form could not be checked. If you are a person, please wait a few seconds and send it again.' );
	}
	// Rate limit: 6 per hour per address.
	$key = 'cclb_rl_' . md5( $_SERVER['REMOTE_ADDR'] ?? '' ); // phpcs:ignore
	$n   = (int) get_transient( $key );
	if ( $n >= 6 ) {
		cclb_fail( 'Too many submissions from this connection. Please try again later.' );
	}
	set_transient( $key, $n + 1, HOUR_IN_SECONDS );

	if ( empty( $r['consent'] ) ) {
		cclb_fail( 'Please tick the agreement box.' );
	}
	$data = array();
	$c    = 'cclb_clean';

	if ( 'join' === $form ) {
		$type = in_array( $r['reg_type'] ?? '', array( 'individual', 'family' ), true ) ? $r['reg_type'] : '';
		if ( ! $type || ! in_array( $r['scc'] ?? '', array( 'yes', 'no' ), true ) ) {
			cclb_fail( 'Please choose a registration type and answer the SCC question.' );
		}
		$data['Registration type'] = ucfirst( $type );
		if ( 'individual' === $type ) {
			$name  = $c( $r['i_first'] ?? '', 80 ) . ' ' . $c( $r['i_last'] ?? '', 80 );
			$phone = $c( $r['i_phone'] ?? '', 40 );
			$email = sanitize_email( wp_unslash( $r['i_email'] ?? '' ) );
			$data += array( 'First name' => $c( $r['i_first'] ?? '', 80 ), 'Last name' => $c( $r['i_last'] ?? '', 80 ), 'Gender' => $c( $r['i_gender'] ?? '', 10 ), 'Date of birth' => $c( $r['i_dob'] ?? '', 10 ), 'Address' => $c( $r['i_address'] ?? '', 300 ), 'Phone' => $phone, 'Email' => $email );
		} else {
			$name  = $c( $r['f_head'] ?? '', 120 ) . ' (' . $c( $r['f_surname'] ?? '', 80 ) . ' family)';
			$phone = $c( $r['f_phone'] ?? '', 40 );
			$email = sanitize_email( wp_unslash( $r['f_email'] ?? '' ) );
			$data += array( 'Family surname' => $c( $r['f_surname'] ?? '', 80 ), 'Head of family' => $c( $r['f_head'] ?? '', 120 ), 'Head date of birth' => $c( $r['f_head_dob'] ?? '', 10 ), 'Head gender' => $c( $r['f_head_gender'] ?? '', 10 ), 'Phone' => $phone, 'Email' => $email, 'Home address' => $c( $r['f_address'] ?? '', 300 ),
				'Spouse name' => $c( $r['s_name'] ?? '', 120 ), 'Spouse date of birth' => $c( $r['s_dob'] ?? '', 10 ), 'Spouse gender' => $c( $r['s_gender'] ?? '', 10 ), 'Spouse phone' => $c( $r['s_phone'] ?? '', 40 ), 'Spouse email' => sanitize_email( wp_unslash( $r['s_email'] ?? '' ) ) );
			$kids = array();
			foreach ( array_slice( array_values( (array) ( $r['children'] ?? array() ) ), 0, 15 ) as $k ) {
				if ( '' === $c( $k['name'] ?? '' ) ) {
					continue;
				}
				$kids[] = array( 'Name' => $c( $k['name'] ?? '', 120 ), 'Date of birth' => $c( $k['dob'] ?? '', 10 ), 'Gender' => $c( $k['gender'] ?? '', 10 ), 'Contact phone' => $c( $k['phone'] ?? '', 40 ), 'Contact email' => sanitize_email( wp_unslash( $k['email'] ?? '' ) ) );
			}
			$data['Children'] = $kids;
			$data['Other children'] = mb_substr( trim( sanitize_textarea_field( wp_unslash( $r['more_children'] ?? '' ) ) ), 0, 1000 );
		}
		if ( '' === trim( $name, " ()" ) || '' === $phone ) {
			cclb_fail( 'Please give a name and a phone number.' );
		}
		$allowed = array( 'Choir', 'Altar servers', 'Catechism / Sunday school', 'Charity / outreach', 'CMO', 'CWO', 'CYAA', 'CYON', 'Others' );
		$data['SCC WhatsApp group'] = 'yes' === $r['scc'] ? 'Yes' : 'No';
		$data['Parish groups']      = array_values( array_intersect( $allowed, array_map( 'wp_unslash', (array) ( $r['groups'] ?? array() ) ) ) );
		$data['Other group']        = $c( $r['groups_other'] ?? '', 120 );
		$data['Consent']            = 'Given';
		$summary                    = $type . ( 'family' === $type ? ', ' . count( $data['Children'] ) . ' child(ren)' : '' ) . ', SCC: ' . $data['SCC WhatsApp group'];
	} else {
		$name  = $c( $r['h_name'] ?? '', 120 );
		$phone = $c( $r['h_phone'] ?? '', 40 );
		$email = sanitize_email( wp_unslash( $r['h_email'] ?? '' ) );
		$kinds = array( 'Cash donation (one time)', 'Pledge (to be paid over time)', 'Thanksgiving offering' );
		$modes = array( 'Cash (on Commitment Day)', 'Bank deposit / transfer' );
		$kind  = wp_unslash( $r['h_kind'] ?? '' );
		$mode  = wp_unslash( $r['h_mode'] ?? '' );
		$amt   = (int) preg_replace( '/\D/', '', (string) ( $r['h_amount'] ?? '' ) );
		if ( '' === $name || '' === $c( $r['h_family'] ?? '' ) || '' === $phone || ! in_array( $kind, $kinds, true ) || ! in_array( $mode, $modes, true ) || $amt < 1 ) {
			cclb_fail( 'Please fill in your name, family or organisation, phone, commitment, a positive amount and payment mode.' );
		}
		$data    = array( 'Name' => $name, 'Family or organisation' => $c( $r['h_family'] ?? '', 160 ), 'Phone' => $phone, 'Parish unit / group / society' => $c( $r['h_unit'] ?? '', 160 ), 'Email' => $email, 'Address' => $c( $r['h_address'] ?? '', 300 ), 'Commitment' => $kind, 'Amount (naira)' => $amt, 'Payment mode' => $mode, 'Agreement' => 'Given' );
		$summary = $kind . ', NGN ' . number_format( $amt );
	}

	$ok = $wpdb->insert( cclb_table(), array(
		'form'       => $form,
		'created_at' => current_time( 'mysql' ),
		'name'       => mb_substr( $name, 0, 200 ),
		'phone'      => $phone,
		'email'      => $email,
		'summary'    => mb_substr( $summary, 0, 255 ),
		'data'       => wp_json_encode( $data, JSON_UNESCAPED_UNICODE ),
	) );
	if ( ! $ok ) {
		cclb_fail( 'Your details could not be saved.' );
	}
	$id = $wpdb->insert_id;

	// Notify the parish. No personal details in the email, only a link to the entry.
	$to = function_exists( 'cclb_parish' ) ? cclb_parish( 'email' ) : get_option( 'admin_email' );
	wp_mail( $to ?: get_option( 'admin_email' ), ( 'join' === $form ? 'New parish registration' : 'New harvest commitment' ), "A new entry was received on the website.\nOpen it here (login required): " . admin_url( 'admin.php?page=cclb-entries&entry=' . $id ) . "\n" );

	wp_safe_redirect( add_query_arg( 'cclb_sent', $form, wp_get_referer() ?: home_url( '/' ) ) . '#form-top' );
	exit;
}
