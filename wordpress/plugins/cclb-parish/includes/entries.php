<?php
/** Parish Details > Entries: list, read, mark handled, delete, export CSV. */
defined( 'ABSPATH' ) || exit;

const CCLB_FORMS = array( 'join' => 'Registrations', 'harvest' => 'Harvest commitments' );

add_action( 'admin_menu', function () {
	global $wpdb;
	$new   = (int) $wpdb->get_var( 'SELECT COUNT(*) FROM ' . cclb_table() . " WHERE status = 'new'" ); // phpcs:ignore WordPress.DB.PreparedSQL
	$label = 'Entries' . ( $new ? ' <span class="awaiting-mod">' . $new . '</span>' : '' );
	add_submenu_page( 'cclb-parish', 'Entries', $label, CCLB_PARISH_CAP, 'cclb-entries', 'cclb_entries_screen' );
}, 20 );

function cclb_flatten( $data, $prefix = '' ) {
	$out = array();
	foreach ( $data as $k => $v ) {
		$key = $prefix . ( is_int( $k ) ? ' ' . ( $k + 1 ) : ( $prefix ? ' ' : '' ) . $k );
		if ( is_array( $v ) ) {
			$assoc = array_keys( $v ) !== range( 0, count( $v ) - 1 );
			if ( $v && ( $assoc || is_array( reset( $v ) ) ) ) {
				$out += cclb_flatten( $v, $key );
			} else {
				$out[ $key ] = implode( ', ', $v );
			}
		} else {
			$out[ $key ] = (string) $v;
		}
	}
	return $out;
}

/** Stop spreadsheet programs running a cell that starts with = + - @ */
function cclb_csv_safe( $v ) {
	$v = (string) $v;
	if ( preg_match( '/^\+?[\d\s().-]+$/', $v ) ) {
		return $v; // a phone number or plain figure, not a formula
	}
	return ( '' !== $v && in_array( $v[0], array( '=', '+', '-', '@', "\t", "\r" ), true ) ) ? "'" . $v : $v;
}

function cclb_entries_screen() {
	global $wpdb;
	if ( ! current_user_can( CCLB_PARISH_CAP ) ) {
		wp_die( 'Not allowed.' );
	}
	$t = cclb_table();
	echo '<div class="wrap"><h1>Entries</h1>';
	$msg = get_transient( 'cclb_entries_notice_' . get_current_user_id() );
	if ( $msg ) {
		delete_transient( 'cclb_entries_notice_' . get_current_user_id() );
		echo '<div class="notice notice-success"><p>' . esc_html( $msg ) . '</p></div>';
	}

	if ( ! empty( $_GET['entry'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $t WHERE id = %d", (int) $_GET['entry'] ) ); // phpcs:ignore
		if ( ! $row ) {
			echo '<p>Entry not found.</p></div>';
			return;
		}
		$flat = cclb_flatten( (array) json_decode( $row->data, true ) );
		echo '<p><a href="' . esc_url( admin_url( 'admin.php?page=cclb-entries&form=' . $row->form ) ) . '">&larr; Back to the list</a></p>';
		echo '<h2>' . esc_html( $row->name ) . '</h2><p class="description">Received ' . esc_html( wp_date( 'j F Y, g:i a', strtotime( $row->created_at ) ) ) . ' &middot; ' . esc_html( CCLB_FORMS[ $row->form ] ?? $row->form ) . '</p>';
		echo '<table class="widefat striped" style="max-width:820px"><tbody>';
		foreach ( $flat as $k => $v ) {
			if ( '' !== $v ) {
				echo '<tr><th style="width:240px">' . esc_html( $k ) . '</th><td>' . nl2br( esc_html( $v ) ) . '</td></tr>';
			}
		}
		echo '</tbody></table><p style="margin-top:1rem">';
		$nonce = wp_create_nonce( 'cclb_entry_' . $row->id );
		$base  = admin_url( 'admin-post.php?id=' . (int) $row->id . '&_wpnonce=' . $nonce );
		echo '<a class="button" href="' . esc_url( $base . '&action=cclb_entry_toggle' ) . '">' . ( 'new' === $row->status ? 'Mark as dealt with' : 'Mark as new' ) . '</a> ';
		echo '<a class="button button-link-delete" href="' . esc_url( $base . '&action=cclb_entry_delete' ) . '" onclick="return confirm(\'Delete this entry for good? This cannot be undone.\');">Delete this entry</a></p></div>';
		return;
	}

	$form = isset( $_GET['form'] ) && isset( CCLB_FORMS[ $_GET['form'] ] ) ? $_GET['form'] : 'join'; // phpcs:ignore
	echo '<h2 class="nav-tab-wrapper">';
	foreach ( CCLB_FORMS as $k => $l ) {
		echo '<a class="nav-tab' . ( $k === $form ? ' nav-tab-active' : '' ) . '" href="' . esc_url( admin_url( 'admin.php?page=cclb-entries&form=' . $k ) ) . '">' . esc_html( $l ) . '</a>';
	}
	echo '</h2>';
	$per   = 50;
	$page  = max( 1, (int) ( $_GET['paged'] ?? 1 ) ); // phpcs:ignore
	$total = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $t WHERE form = %s", $form ) ); // phpcs:ignore
	$rows  = $wpdb->get_results( $wpdb->prepare( "SELECT id, created_at, name, phone, summary, status FROM $t WHERE form = %s ORDER BY id DESC LIMIT %d OFFSET %d", $form, $per, ( $page - 1 ) * $per ) ); // phpcs:ignore
	$exp   = wp_nonce_url( admin_url( 'admin-post.php?action=cclb_entries_export&form=' . $form ), 'cclb_export' );
	echo '<p>' . (int) $total . ' entries. <a class="button" href="' . esc_url( $exp ) . '">Download as spreadsheet (CSV)</a></p>';
	echo '<table class="widefat striped"><thead><tr><th>Received</th><th>Name</th><th>Phone</th><th>Details</th><th>Status</th></tr></thead><tbody>';
	if ( ! $rows ) {
		echo '<tr><td colspan="5">Nothing here yet.</td></tr>';
	}
	foreach ( $rows as $r ) {
		$u = admin_url( 'admin.php?page=cclb-entries&entry=' . (int) $r->id );
		echo '<tr><td>' . esc_html( wp_date( 'j M Y, g:i a', strtotime( $r->created_at ) ) ) . '</td><td><a href="' . esc_url( $u ) . '"><strong>' . esc_html( $r->name ) . '</strong></a></td><td>' . esc_html( $r->phone ) . '</td><td>' . esc_html( $r->summary ) . '</td><td>' . ( 'new' === $r->status ? '<strong>New</strong>' : 'Dealt with' ) . '</td></tr>';
	}
	echo '</tbody></table>';
	$pages = (int) ceil( $total / $per );
	if ( $pages > 1 ) {
		echo '<p>' . wp_kses_post( paginate_links( array( 'base' => add_query_arg( 'paged', '%#%' ), 'format' => '', 'current' => $page, 'total' => $pages ) ) ) . '</p>';
	}
	echo '<p class="description" style="margin-top:1.5rem">These entries hold personal details of parishioners, including children. Keep downloaded spreadsheets private and delete them when finished.</p></div>';
}

add_action( 'admin_post_cclb_entry_toggle', function () {
	global $wpdb;
	$id = (int) ( $_GET['id'] ?? 0 ); // phpcs:ignore
	if ( ! current_user_can( CCLB_PARISH_CAP ) || ! wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'cclb_entry_' . $id ) ) { // phpcs:ignore
		wp_die( 'Not allowed.' );
	}
	$wpdb->query( $wpdb->prepare( 'UPDATE ' . cclb_table() . " SET status = IF(status = 'new', 'done', 'new') WHERE id = %d", $id ) ); // phpcs:ignore
	wp_safe_redirect( admin_url( 'admin.php?page=cclb-entries&entry=' . $id ) );
	exit;
} );

add_action( 'admin_post_cclb_entry_delete', function () {
	global $wpdb;
	$id = (int) ( $_GET['id'] ?? 0 ); // phpcs:ignore
	if ( ! current_user_can( CCLB_PARISH_CAP ) || ! wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'cclb_entry_' . $id ) ) { // phpcs:ignore
		wp_die( 'Not allowed.' );
	}
	$wpdb->delete( cclb_table(), array( 'id' => $id ) );
	set_transient( 'cclb_entries_notice_' . get_current_user_id(), 'Entry deleted.', 60 );
	wp_safe_redirect( admin_url( 'admin.php?page=cclb-entries' ) );
	exit;
} );

add_action( 'admin_post_cclb_entries_export', function () {
	global $wpdb;
	if ( ! current_user_can( CCLB_PARISH_CAP ) ) {
		wp_die( 'Not allowed.' );
	}
	check_admin_referer( 'cclb_export' );
	$form = isset( CCLB_FORMS[ $_GET['form'] ?? '' ] ) ? $_GET['form'] : 'join'; // phpcs:ignore
	$rows = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . cclb_table() . ' WHERE form = %s ORDER BY id ASC', $form ) ); // phpcs:ignore
	$all  = array();
	$flat = array();
	foreach ( $rows as $r ) {
		$f = array( 'Received' => $r->created_at, 'Status' => $r->status ) + cclb_flatten( (array) json_decode( $r->data, true ) );
		$flat[] = $f;
		foreach ( array_keys( $f ) as $k ) {
			$all[ $k ] = true;
		}
	}
	nocache_headers();
	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename="cclb-' . $form . '-' . gmdate( 'Y-m-d' ) . '.csv"' );
	$out = fopen( 'php://output', 'w' );
	fwrite( $out, "\xEF\xBB\xBF" ); // So Excel reads accents correctly.
	fputcsv( $out, array_keys( $all ) );
	foreach ( $flat as $f ) {
		$line = array();
		foreach ( array_keys( $all ) as $k ) {
			$line[] = cclb_csv_safe( $f[ $k ] ?? '' );
		}
		fputcsv( $out, $line );
	}
	fclose( $out );
	exit;
} );
