<?php
/**
 * Old-URL redirects (PROJECT.md §5). Deviation flagged: §5 says these
 * should live in the Redirection plugin or .htaccess, not hardcoded in
 * PHP. Neither was available this session — /var/www/html/.htaccess is
 * owned by www-data with no group/other write and no passwordless sudo
 * for itoi-temp, and no Redirection-style plugin is installed. User
 * chose this PHP fallback explicitly over fixing permissions or adding
 * a new plugin (see NOTES.md) — move this to .htaccess or a redirect
 * plugin once one of those becomes available, don't leave it here
 * permanently.
 *
 * Only the confident, unambiguous 1:1 old-page -> new-CPT-entry
 * mappings are included. Deliberately NOT redirected here, same as
 * they'd have been left out of the .htaccess version — flagged in
 * NOTES.md instead of guessed:
 *   /theft-detection-system-and-loss-prevention-solutions/,
 *   /foot-traffic-counter/, /door-counter/ — likely map to use_case
 *   entries, but no use_case single template exists yet; redirecting
 *   now would point visitors at a 404.
 *   /home/ — front-page.php already serves "/" regardless of this old
 *   page's own existence; low-priority loose end, not a broken link.
 *
 * SEO audit, 2026-07-23: /our-portfolio/ now redirects to /customers/
 * below — its "unclear successor" blocker is resolved now that the
 * `client` CPT + page-customers.php exist (added 2026-07-20, after the
 * note above was written). It's a summary/filtered view, not identical
 * page-for-page, but it's the direct, unambiguous successor of the old
 * portfolio page's purpose (see PROJECT.md §6 client list).
 *
 * 2026-08-10: page renamed "Customers" -> "Portfolio" (slug `customers`
 * -> `portfolio`, file page-customers.php -> page-portfolio.php). The
 * /our-portfolio/ mapping below now points straight at /portfolio/
 * rather than the now-defunct /customers/ hop, and /customers/ itself
 * gets its own new entry so existing backlinks/bookmarks still resolve.
 *
 * 2026-07-23 — Solutions restructure (7 -> 8 categories, see NOTES.md).
 * Old solution slugs now redirect to their new-category successor.
 * 5 of the 7 (the in-place post renames) are already covered by WP
 * core's own `wp_old_slug_redirect()` (fires automatically off the
 * `_wp_old_slug` postmeta WP writes when a published post's slug
 * changes) — still listed here explicitly rather than relying on that
 * implicit behaviour, since it isn't guaranteed to keep working (e.g.
 * if a future post ever reused one of these old slugs) and an explicit,
 * auditable map is the point of this file. The other 2 (facial-recognition,
 * cleaning-robots) were trashed/merged rather than renamed, so WP's
 * native mechanism doesn't cover them at all — this file is the only
 * thing standing between those two old URLs and a 404.
 * The pre-existing pre-launch legacy URLs below (retail-and-venue-analytics,
 * facial-recognition-demographics) are updated to point straight at the
 * final new-category destination rather than the now-defunct intermediate
 * old solution slug, to avoid a needless double redirect hop.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function itoi_old_url_redirects() {
	$map = array(
		// Solutions restructure, 2026-07-23 — old /solutions/{slug}/ URLs
		// (live until this restructure) -> new category.
		'solutions/retail-analytics'                           => '/solutions/intelligence-analytics/',
		'solutions/customer-engagement'                        => '/solutions/customer-engagement-signage/',
		'solutions/security-robot'                             => '/solutions/workforce-ops-robotics/',
		'solutions/cleaning-robots'                             => '/solutions/workforce-ops-robotics/',
		'solutions/smart-security'                             => '/solutions/security-access-inventory/',
		'solutions/facial-recognition'                         => '/solutions/security-access-inventory/',
		'solutions/liquor-management'                          => '/solutions/back-of-house-integration/',
		// Bare pre-launch legacy URLs (pre-dating the /solutions/ prefix
		// entirely) — updated to point straight at the final new category
		// rather than the now-defunct intermediate /solutions/{old-slug}/.
		'smart-security'                                       => '/solutions/security-access-inventory/',
		'liquor-management'                                    => '/solutions/back-of-house-integration/',
		'security-robot'                                       => '/solutions/workforce-ops-robotics/',
		'cleaning-robots'                                      => '/solutions/workforce-ops-robotics/',
		'customer-engagement'                                  => '/solutions/customer-engagement-signage/',
		'retail-and-venue-analytics'                           => '/solutions/intelligence-analytics/',
		'facial-recognition-demographics'                      => '/solutions/security-access-inventory/',
		'about-us'                                              => '/about/',
		'contact-us'                                            => '/contact/',
		// SEO audit, 2026-07-23 — resolved now that the client-list page
		// exists. Updated 2026-08-10 to point straight at /portfolio/
		// (that page's current slug) rather than the old /customers/ hop.
		'our-portfolio'                                        => '/portfolio/',
		// 2026-08-10 — Customers -> Portfolio rename (see file header).
		'customers'                                            => '/portfolio/',
		// Products restructure, 2026-07-31 (see NOTES.md) — Aurora moved off
		// its one-off WP Page onto the now-public `product` CPT's own routing.
		'aurora'                                                => '/products/aurora/',
	);

	$path = trim( parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH ) ?? '', '/' );

	if ( isset( $map[ $path ] ) ) {
		wp_safe_redirect( home_url( $map[ $path ] ), 301 );
		exit;
	}
}
add_action( 'template_redirect', 'itoi_old_url_redirects', 1 );
