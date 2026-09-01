<?php
/**
 * Header: rotating ticker banner + sticky nav with dropdowns.
 * Ground truth: preview-verkada-match.html (.promo, header, nav.main).
 *
 * Phase 2 scope: static structure/first-state only, matched pixel-for-pixel
 * to the mockup. Ticker rotation, dropdown-on-hover already works via CSS,
 * but the JS-driven pieces (ticker auto-advance) are wired up in Phase 3
 * per PROJECT.md §10 — this renders the ticker's first message only.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link" href="#main-content">Skip to main content</a>

<?php
/**
 * Top-level Primary menu items + their children, used by BOTH the flat
 * desktop nav (hover dropdowns, below) and the mobile full-screen overlay
 * further down — computed once, up here, so neither has to duplicate the
 * "Resources"-style dead-link fallback or the Use Cases special-case.
 */
$itoi_mega_locations = get_nav_menu_locations();
$itoi_mega_items     = array();
if ( ! empty( $itoi_mega_locations['primary'] ) ) {
	$itoi_mega_menu_obj = wp_get_nav_menu_object( $itoi_mega_locations['primary'] );
	if ( $itoi_mega_menu_obj ) {
		$itoi_all_items = wp_get_nav_menu_items( $itoi_mega_menu_obj->term_id );
		if ( $itoi_all_items ) {
			foreach ( $itoi_all_items as $itoi_item ) {
				if ( 0 == $itoi_item->menu_item_parent ) {
					// 2026-08-31: "Products" pulled from the primary nav for
					// now (desktop bar + mobile mega-menu both iterate
					// $itoi_mega_items, so one skip here covers both). The item
					// is left untouched in the real WP menu (Appearance ->
					// Menus) — reverting is deleting this block, and adding the
					// `nav-keep-hidden` CSS class to any menu item hides it the
					// same way. Matched on destination URL, the same stable
					// identifier the Use Cases special-case below keys off.
					$itoi_hide_from_nav = in_array( 'nav-keep-hidden', (array) $itoi_item->classes, true )
						|| trailingslashit( $itoi_item->url ) === trailingslashit( home_url( '/products/' ) );
					if ( $itoi_hide_from_nav ) {
						continue;
					}

					$itoi_url = $itoi_item->url;
					// A parent used only to group children (e.g. "Resources")
					// has no real destination of its own — send it to its
					// first child instead of a dead "#" link.
					if ( ! $itoi_url || '#' === $itoi_url ) {
						foreach ( $itoi_all_items as $itoi_child ) {
							if ( $itoi_child->menu_item_parent == $itoi_item->ID ) {
								$itoi_url = $itoi_child->url;
								break;
							}
						}
					}
					// Use Cases is special-cased: its real WP menu children still
					// point at old placeholder URLs from before the 2026-07-23
					// use-cases consolidation (see NOTES.md, inc/use-cases.php)
					// — the real 42 use cases live as their own `use_case` CPT
					// posts (migrated 2026-07-30, see NOTES.md). Rather than
					// edit the real WP Menu (Appearance -> Menus) to match, the
					// dropdown is populated dynamically here from the same
					// featured_in_nav-curated set the homepage teaser uses,
					// plus a "View all" link.
					//
					// 2026-08-05: detection switched from matching this item's
					// visible title text (broke the moment anyone renamed the
					// menu label) to its destination URL, which is the actual
					// stable identifier here — /use-cases/ is a real WP Page
					// (page-use-cases.php), not just a placeholder link. A
					// `dynamic-use-cases` CSS class on the menu item (Appearance
					// -> Menus -> Screen Options -> CSS Classes) overrides this
					// with no code change, for if the URL itself ever moves.
					$itoi_is_use_cases_item = in_array( 'dynamic-use-cases', (array) $itoi_item->classes, true )
						|| trailingslashit( $itoi_item->url ) === trailingslashit( home_url( '/use-cases/' ) );
					if ( $itoi_is_use_cases_item ) {
						$itoi_children = itoi_get_nav_use_case_children();
					} else {
						$itoi_children = array();
						foreach ( $itoi_all_items as $itoi_child ) {
							if ( $itoi_child->menu_item_parent == $itoi_item->ID ) {
								$itoi_children[] = array(
									'title' => $itoi_child->title,
									'url'   => $itoi_child->url,
								);
							}
						}
					}
					$itoi_mega_items[] = array(
						'title'    => $itoi_item->title,
						'url'      => $itoi_url,
						'children' => $itoi_children,
					);
				}
			}
		}
	}
}
$itoi_mega_previews = get_field( 'mega_menu_previews', 'option' ) ?: array();

/**
 * Every page: the ticker+nav block "combines" with whatever section sits
 * at the top of that page — fixed overlay instead of normal sticky flow,
 * so that section's own background shows through around and behind it.
 * Each template gives its own first section matching top padding (168px
 * mobile / 206px desktop) so CONTENT still clears the fixed nav while the
 * section's BACKGROUND runs full-height underneath, same mechanism
 * megaHero in front-page.php originated. Generalized 2026-08-03 (see
 * NOTES.md) — previously this was homepage-only and every other page used
 * a plain sticky nav sitting in normal flow above its hero, leaving a gap.
 *
 * Ticker translucency still varies: pages whose top section is a dark/
 * photo hero (home, the Use Cases hub, a product page) use the same
 * translucent bg-black/20 the homepage always has, so that dark background
 * shows through. Every other page's top section is light (bg-hero-bg or
 * the page's own base background), so the ticker stays solid --ink —
 * white ticker text over a translucent black/20 on a light background
 * would fail WCAG contrast.
 */
$itoi_dark_hero        = is_front_page() || is_page( 'use-cases' ) || is_singular( 'product' );
// 2026-08-21 — front page only: this block is step 1 of the mega-hero's
// page-load stagger reveal (itoiStaggerReveal(), assets/js/homepage.js,
// front-page-only bundle — see hero.php). Gated to is_front_page() alone,
// not the broader $itoi_dark_hero, so every other page's header keeps
// rendering fully visible with no JS dependency — only homepage.js ever
// adds the .is-visible class this needs, and only homepage.js is enqueued
// here.
$itoi_header_wrap_cls  = 'fixed inset-x-0 top-0 z-[60]' . ( is_front_page() ? ' itoi-stagger-item' : '' );
$itoi_ticker_cls       = $itoi_dark_hero ? 'bg-black/20 backdrop-blur-sm' : 'bg-ink';
$itoi_header_cls       = 'mt-3 min-[640px]:mt-4';

// 2026-08-05: the ticker no longer rotates (external improvement plan
// Phase 3.4/5.6 — see NOTES.md; user explicitly authorized overriding
// CLAUDE.md's mechanics rule for this one reduction). Was 3 hardcoded
// messages stacked + auto-advanced by JS every 4s; now renders one static
// message. Reads the real `ticker_messages` option field's first row
// (finally closing the gap that field's own instructions flagged — it
// existed but no template ever read it) instead of hardcoding a 4th copy
// of the same string.
$itoi_ticker_messages = get_field( 'ticker_messages', 'option' );
$itoi_ticker_text     = ! empty( $itoi_ticker_messages[0]['text'] ) ? $itoi_ticker_messages[0]['text'] : 'Live foot-traffic detection now ships on every ITOI camera.';
?>
<div id="siteHeaderFixed" class="<?php echo esc_attr( $itoi_header_wrap_cls ); ?>">
	<div class="flex h-[38px] items-center justify-center gap-3.5 overflow-hidden px-4 text-[13.5px] font-semibold text-white <?php echo esc_attr( $itoi_ticker_cls ); ?>" role="region" aria-label="Announcements">
		<div class="relative h-5 overflow-hidden" id="promoTrack">
			<div class="promo-line absolute whitespace-nowrap" style="top:0px"><?php echo esc_html( $itoi_ticker_text ); ?></div>
		</div>
	</div>

<header class="<?php echo esc_attr( $itoi_header_cls ); ?> px-3 min-[640px]:px-6 min-[980px]:px-8">
	<div class="nav-glass mx-auto flex h-[72px] max-w-[1280px] items-center gap-6 rounded-full px-6 min-[640px]:px-8">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="flex shrink-0 items-center" aria-label="<?php bloginfo( 'name' ); ?>">
			<?php echo wp_get_attachment_image( get_theme_mod( 'custom_logo' ), 'full', false, array( 'class' => 'h-9 w-auto', 'alt' => get_bloginfo( 'name' ) ) ); ?>
		</a>

		<?php
		/**
		 * 2026-08-05: dropped from a custom min-[1180px] to the sitewide
		 * min-[980px] breakpoint used everywhere else in this file. The
		 * custom breakpoint was sized for the Primary menu's old 8 top-level
		 * items; the menu was restructured to 6 the same day (see
		 * docs/navigation.md, NOTES.md) and a Puppeteer check confirmed all
		 * 6 flat-nav links fit on one row at 980px with no wrap.
		 */
		?>
		<?php // 2026-08-31: centered in the middle band (was left-packed next to
		// the logo). With "Products" pulled the bar was down to 4 short items,
		// which left a wide dead gap between them and the right-hand CTAs;
		// justify-center distributes that slack evenly on both sides so the
		// bar reads balanced. gap-1 (was gap-0.5) gives the fewer items a bit
		// more air. Revert both if "Products" comes back and the row refills. ?>
		<nav class="hidden min-[980px]:flex min-[980px]:flex-1 min-[980px]:items-center min-[980px]:justify-center min-[980px]:gap-1" aria-label="Primary">
			<?php foreach ( $itoi_mega_items as $itoi_flat_i => $itoi_flat_item ) : ?>
				<?php if ( ! empty( $itoi_flat_item['children'] ) ) : ?>
					<?php $itoi_dropdown_id = 'navDropdownPanel' . $itoi_flat_i; ?>
					<?php
					/**
					 * 2026-08-05 ("10/10" pass): split into a real link + a
					 * separate disclosure button, rather than one element
					 * doing both jobs. Previously aria-haspopup/aria-expanded
					 * lived on the link itself — functional, but ambiguous
					 * for a keyboard/AT user (does Enter navigate, or open
					 * the submenu?). The button now owns aria-expanded/
					 * aria-controls and is what Enter/Space/click toggles
					 * (initDesktopDropdowns(), assets/js/main.js); the link
					 * still navigates normally and the panel still opens on
					 * hover/focus (CSS group-hover/group-focus-within,
					 * unchanged) for mouse/trackpad users who never touch
					 * the button.
					 */
					?>
					<div class="nav-dropdown group relative flex items-center">
						<a href="<?php echo esc_url( $itoi_flat_item['url'] ); ?>" class="flex items-center whitespace-nowrap rounded-full py-2.5 pl-3 text-[14px] font-semibold text-ink transition-colors hover:bg-hero-bg">
							<?php echo esc_html( $itoi_flat_item['title'] ); ?>
						</a>
						<button type="button" class="nav-dropdown-toggle flex items-center rounded-full py-2.5 pl-1 pr-3 text-ink transition-colors hover:bg-hero-bg" aria-expanded="false" aria-controls="<?php echo esc_attr( $itoi_dropdown_id ); ?>" aria-label="<?php echo esc_attr( sprintf( 'Open %s menu', $itoi_flat_item['title'] ) ); ?>">
							<svg width="10" height="6" viewBox="0 0 10 6" fill="none" aria-hidden="true"><path d="M1 1l4 4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
						</button>
						<div class="invisible absolute left-1/2 top-full z-10 w-60 -translate-x-1/2 translate-y-1 rounded-2xl border border-line bg-white p-2 opacity-0 shadow-[0_16px_40px_-12px_rgba(0,0,0,0.18)] transition-[opacity,transform] duration-150 group-hover:visible group-hover:translate-y-2 group-hover:opacity-100 group-focus-within:visible group-focus-within:translate-y-2 group-focus-within:opacity-100" id="<?php echo esc_attr( $itoi_dropdown_id ); ?>">
							<ul class="flex flex-col">
								<?php foreach ( $itoi_flat_item['children'] as $itoi_flat_child ) : ?>
									<li><a href="<?php echo esc_url( $itoi_flat_child['url'] ); ?>" class="block rounded-lg px-3.5 py-2.5 text-[13.5px] font-semibold text-ink transition-colors hover:bg-hero-bg"><?php echo esc_html( $itoi_flat_child['title'] ); ?></a></li>
								<?php endforeach; ?>
							</ul>
						</div>
					</div>
				<?php else : ?>
					<a href="<?php echo esc_url( $itoi_flat_item['url'] ); ?>" class="whitespace-nowrap rounded-full px-3 py-2.5 text-[14px] font-semibold text-ink transition-colors hover:bg-hero-bg"><?php echo esc_html( $itoi_flat_item['title'] ); ?></a>
				<?php endif; ?>
			<?php endforeach; ?>
		</nav>

		<div class="ml-auto flex shrink-0 items-center gap-2.5">
			<?php // 2026-08-06: replaces front-page.php's Final CTA band ("Not sure where to start? Build your solution"), removed sitewide-nav-adjacent instead — same destination, always reachable rather than only after scrolling to the bottom of the homepage. ?>
			<a href="<?php echo esc_url( home_url( '/solution-builder/' ) ); ?>" class="hidden rounded-full border border-line px-[22px] py-[11px] text-sm font-bold text-ink transition-colors hover:bg-hero-bg min-[980px]:inline-block">Build your solution</a>
			<a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" class="hidden rounded-full bg-cta px-[22px] py-[11px] text-sm font-bold text-white transition-colors hover:bg-cta-hover min-[980px]:inline-block">Contact</a>
			<button type="button" id="menuTrigger" class="menu-trigger flex items-center gap-2.5 rounded-full border border-line px-4 py-2.5 text-sm font-bold text-ink transition-colors hover:bg-hero-bg min-[980px]:hidden" aria-expanded="false" aria-controls="megaMenu">
				<span class="menu-trigger-bars" aria-hidden="true"></span>
				Menu
			</button>
		</div>
	</div>
</header>
</div>

<!-- aria-hidden defaults true (external improvement plan Phase 5.7): this
     dialog was previously only ever CSS-hidden (.open class), never
     removed from the accessibility tree while closed — its #megaMenuHeadline
     <h2> sat before every page's real H1 in DOM order, both breaking
     heading-order (axe, solution-builder page) and reachable via a screen
     reader's heading-navigation shortcut while invisible. initMegaMenu()
     (assets/js/main.js) toggles this alongside the existing .open class. -->
<div class="mega-menu aurora-bg" id="megaMenu" role="dialog" aria-modal="true" aria-label="Site menu" aria-hidden="true">
	<div class="mx-auto max-w-[1280px] px-8 py-10 min-[980px]:py-16">
		<div class="mb-10 flex items-center justify-between min-[980px]:mb-16">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="flex items-center gap-2 text-[19px] font-extrabold tracking-[-0.02em] text-white">
				<span class="h-[9px] w-[9px] rounded-full bg-signature-bright"></span>Image to Intelligence
			</a>
			<button type="button" id="megaMenuClose" class="mega-menu-close flex h-11 w-11 items-center justify-center rounded-full border border-white/25 text-white" aria-label="Close menu">
				<svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M1 1L15 15M15 1L1 15" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
			</button>
		</div>

		<div class="grid grid-cols-1 gap-10 min-[980px]:grid-cols-[1.1fr_1fr] min-[980px]:gap-16">
			<nav aria-label="Site menu">
				<ul class="flex flex-col gap-5 min-[980px]:gap-7">
					<?php foreach ( $itoi_mega_items as $itoi_index => $itoi_nav_item ) :
						$itoi_preview = $itoi_mega_previews[ $itoi_index ] ?? array();
						?>
						<li>
							<a
								href="<?php echo esc_url( $itoi_nav_item['url'] ); ?>"
								class="mega-menu-item text-[22px] font-extrabold min-[980px]:text-[28px]"
								data-eyebrow="<?php echo esc_attr( $itoi_preview['eyebrow'] ?? '' ); ?>"
								data-headline="<?php echo esc_attr( $itoi_preview['headline'] ?? '' ); ?>"
								data-desc="<?php echo esc_attr( $itoi_preview['description'] ?? '' ); ?>"
							>
								<span class="mega-menu-item-index"><?php echo esc_html( sprintf( '%02d', $itoi_index + 1 ) ); ?></span>
								<?php echo esc_html( $itoi_nav_item['title'] ); ?>
							</a>
							<?php if ( ! empty( $itoi_nav_item['children'] ) ) : ?>
								<ul class="mt-3 flex flex-col gap-2 pl-1 min-[980px]:mt-4 min-[980px]:gap-2.5">
									<?php foreach ( $itoi_nav_item['children'] as $itoi_child_item ) : ?>
										<li><a href="<?php echo esc_url( $itoi_child_item['url'] ); ?>" class="text-[14px] font-semibold text-white/60 transition-colors hover:text-white min-[980px]:text-[15px]"><?php echo esc_html( $itoi_child_item['title'] ); ?></a></li>
									<?php endforeach; ?>
								</ul>
							<?php endif; ?>
						</li>
					<?php endforeach; ?>
				</ul>
			</nav>

			<div class="mega-menu-preview mega-menu-preview-glass hidden self-start min-[980px]:block" id="megaMenuPreview">
				<div class="mb-3 text-[13px] font-bold uppercase tracking-wide text-signature-bright" id="megaMenuEyebrow"></div>
				<h2 class="mb-4 max-w-[16ch] text-[clamp(24px,2.6vw,34px)] text-white" id="megaMenuHeadline"></h2>
				<p class="max-w-[38ch] text-white/70" id="megaMenuDesc"></p>
			</div>
		</div>
	</div>
</div>

<main id="main-content" tabindex="-1">
