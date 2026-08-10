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
 *
 * @package ITOI
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
