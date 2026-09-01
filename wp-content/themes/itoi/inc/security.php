<?php
/**
 * Baseline HTTP security headers + a few small WordPress hardening
 * defaults. Deliberately scoped to things safe to set unconditionally from
 * theme code, with no site-specific configuration required:
 *
 * - No Content-Security-Policy or Strict-Transport-Security here — both
 *   need site-specific input (CSP has to enumerate every real external
 *   resource this site loads; HSTS is only safe to set once HTTPS is
 *   confirmed enforced everywhere) and belong at the server/reverse-proxy
 *   level or a dedicated follow-up with the site operator, not guessed at
 *   here.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * X-Content-Type-Options / X-Frame-Options / Referrer-Policy /
 * X-XSS-Protection — sent on every response via send_headers, same hook
 * WordPress core's own itoi_preload_fonts-style header additions use.
 */
function itoi_security_headers() {
	header( 'X-Content-Type-Options: nosniff' );
	header( 'X-Frame-Options: SAMEORIGIN' );
	header( 'Referrer-Policy: strict-origin-when-cross-origin' );
	// Legacy-browser support only — modern browsers ignore this header (it
	// was removed from Chrome/Edge/Safari years ago) or it's superseded by
	// CSP, but it's a harmless no-op default-on for the few clients that
	// still read it.
	header( 'X-XSS-Protection: 1; mode=block' );
}
add_action( 'send_headers', 'itoi_security_headers' );

// Stop advertising the exact WordPress version in <meta name="generator">
// — a small reconnaissance signal for anyone probing for version-specific
// known vulnerabilities.
remove_action( 'wp_head', 'wp_generator' );

// XML-RPC isn't used by this theme/site (no remote publishing, no
// pingback/trackback workflow relied on) and is a common brute-force /
// amplification target — disabled outright rather than left open unused.
add_filter( 'xmlrpc_enabled', '__return_false' );

// Removes the REST API discovery <link> from wp_head for anonymous
// visitors. The REST API itself is untouched and still works (this theme
// relies on it for nothing public-facing) — only the auto-discovery link
// tag in page <head> markup is removed.
remove_action( 'wp_head', 'rest_output_link_wp_head', 10 );

/**
 * 2026-08-31 pre-launch pass — the discovery-link removal above only
 * hides the <link> tag; the endpoint itself was still fully queryable
 * directly (confirmed live: /wp-json/wp/v2/users returned a real name +
 * login slug with zero authentication — a ready-made target list for a
 * login brute-force attempt). This actually removes the endpoint for
 * anonymous requests. Logged-in requests are untouched — the block
 * editor's author picker and similar admin UI keep working normally.
 *
 * (XML-RPC was checked in the same pass and is already correctly closed
 * above: any XML-RPC method that requires login — publishing, deleting
 * files, brute-forcing credentials — hits wp_xmlrpc_server::login()'s own
 * `$this->is_enabled` check and gets a 405 fault, confirmed live with a
 * real request. Only harmless method-introspection calls like
 * system.listMethods still respond, since that path never calls login()
 * — a minor fingerprint, not a real gap, so nothing further needed here.)
 */
function itoi_block_anonymous_user_enumeration( $endpoints ) {
	if ( is_user_logged_in() ) {
		return $endpoints;
	}
	foreach ( array( '/wp/v2/users', '/wp/v2/users/(?P<id>[\d]+)' ) as $itoi_route ) {
		unset( $endpoints[ $itoi_route ] );
	}
	return $endpoints;
}
add_filter( 'rest_endpoints', 'itoi_block_anonymous_user_enumeration' );

/**
 * Login rate-limiting.
 *
 * No security/firewall plugin is active on this site — confirmed via a
 * live test (5 rapid wrong-password attempts at wp-login.php, all
 * returned 200 with zero throttling) before writing this. This is a
 * minimal, self-contained lockout: 5 failed attempts from the same IP
 * within 15 minutes locks that IP out of login attempts for 15 minutes.
 * Deliberately simple (transients, no new DB table) rather than a full
 * plugin — if a real security plugin gets installed later, this becomes
 * redundant and can be removed.
 *
 * Uses REMOTE_ADDR directly. If this site ever sits behind a reverse
 * proxy/CDN (Cloudflare, a load balancer, etc.), REMOTE_ADDR will be the
 * proxy's own IP for every visitor, not the real client — that would need
 * the proxy's trusted forwarded-for header instead, which isn't safe to
 * trust blindly without knowing the real proxy setup in advance (a
 * spoofable header otherwise). Flagging here rather than guessing.
 */
function itoi_login_lockout_key() {
	$itoi_ip = $_SERVER['REMOTE_ADDR'] ?? '';
	return 'itoi_login_fail_' . md5( $itoi_ip );
}

function itoi_check_login_lockout( $user, $username, $password ) {
	if ( empty( $username ) && empty( $password ) ) {
		return $user; // Not an actual login submission (e.g. just visiting wp-login.php).
	}
	$itoi_attempts = (int) get_transient( itoi_login_lockout_key() );
	if ( $itoi_attempts >= 5 ) {
		return new WP_Error(
			'itoi_login_locked_out',
			__( '<strong>Error:</strong> Too many failed login attempts. Please try again in 15 minutes.', 'itoi' )
		);
	}
	return $user;
}
add_filter( 'authenticate', 'itoi_check_login_lockout', 30, 3 ); // after core's own username/password checks (priority 20)

function itoi_record_login_failure() {
	$itoi_key      = itoi_login_lockout_key();
	$itoi_attempts = (int) get_transient( $itoi_key );
	set_transient( $itoi_key, $itoi_attempts + 1, 15 * MINUTE_IN_SECONDS );
}
add_action( 'wp_login_failed', 'itoi_record_login_failure' );

function itoi_clear_login_lockout( $user_login ) {
	delete_transient( itoi_login_lockout_key() );
}
add_action( 'wp_login', 'itoi_clear_login_lockout' );
