<?php
/**
 * wp-admin dashboard/menu customization — 2026-08-06 wp-admin content audit
 * (see docs/acf-content-checklist.md). Nothing here touches the front end;
 * it's entirely about making the admin easier for ITOI staff to work in —
 * fewer irrelevant widgets, CPT counts at a glance, a welcome panel with
 * the links staff actually need, and a top-level menu ordered by how often
 * each screen gets used rather than WordPress's registration order.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ---------------------------------------------------------------------
// 4a. Remove dashboard widgets that don't apply to this site.
// ---------------------------------------------------------------------
add_action( 'wp_dashboard_setup', function () {
	remove_meta_box( 'dashboard_primary', 'dashboard', 'normal' ); // "WordPress Events and News"
	remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' ); // "Quick Draft" — this site's content lives on CPTs, not Posts
	// "At a Glance" (dashboard_right_now) and "Activity" (dashboard_activity)
	// are left in place, per the instruction — At a Glance is customized
	// below (4b) rather than removed.
}, 20 ); // after 20 so this runs after wp_add_dashboard_widgets (priority 10) has added them.

// ---------------------------------------------------------------------
// 4b. "At a Glance" — add a count + link for every CPT this theme
// registers, not just WP's own Posts/Pages/Comments.
// ---------------------------------------------------------------------
add_filter( 'dashboard_glance_items', function ( $items ) {
	$post_types = array(
		'solution'   => 'Solutions',
		'product'    => 'Products',
		'industry'   => 'Industries',
		'case_study' => 'Case Studies',
		'use_case'   => 'Use Cases',
		'insight'    => 'Insights',
		'guide'      => 'Guides',
		'sb_lead'    => 'Solution Builder Leads',
	);
	foreach ( $post_types as $post_type => $plural_label ) {
		$post_type_object = get_post_type_object( $post_type );
		if ( ! $post_type_object || ! current_user_can( $post_type_object->cap->edit_posts ) ) {
			continue;
		}
		$count = (int) ( wp_count_posts( $post_type )->publish ?? 0 );
		$label = 1 === $count ? $post_type_object->labels->singular_name : $plural_label;
		$items[] = sprintf(
			'<a href="%s">%s %s</a>',
			esc_url( admin_url( 'edit.php?post_type=' . $post_type ) ),
			esc_html( number_format_i18n( $count ) ),
			esc_html( $label )
		);
	}
	return $items;
} );

// ---------------------------------------------------------------------
// 4c. Custom welcome panel — replaces core's default (which links to
// Customizer/widgets/menus, none of which this theme's editors use day to
// day) with links to what they actually edit. Dismissible via the same
// "show_welcome_panel" user-meta mechanism core's own panel uses — nothing
// new to wire up for that. Swapped on load-index.php (the dashboard's own
// load hook) so it runs after core has already registered its callback on
// the 'welcome_panel' action — removing it any earlier would no-op.
// ---------------------------------------------------------------------
add_action( 'load-index.php', function () {
	remove_action( 'welcome_panel', 'wp_welcome_panel' );
	add_action( 'welcome_panel', 'itoi_welcome_panel' );
} );

function itoi_welcome_panel() {
	// TODO(content): no dedicated "welcome panel message" ACF field exists
	// yet — this is plain hardcoded copy (not a get_field() call), unlike
	// everything else this audit moved to ACF, since it's admin-only chrome
	// a site visitor never sees, not front-end content. Move to a Site
	// Settings field if staff ever want to edit it without a code change.
	$message = "Manage ITOI's solutions, products, industries and Solution Builder leads from here.";
	?>
	<div class="welcome-panel-content">
		<h2><?php echo esc_html( sprintf( 'Welcome to %s', get_bloginfo( 'name' ) ) ); ?></h2>
		<p class="about-description"><?php echo esc_html( $message ); ?></p>
		<div class="welcome-panel-column-container">
			<div class="welcome-panel-column">
				<h3>Content</h3>
				<ul>
					<li><a class="welcome-icon welcome-edit-page" href="<?php echo esc_url( admin_url( 'edit.php?post_type=solution' ) ); ?>">Edit Solutions</a></li>
					<li><a class="welcome-icon welcome-edit-page" href="<?php echo esc_url( admin_url( 'edit.php?post_type=product' ) ); ?>">Edit Products</a></li>
					<li><a class="welcome-icon welcome-edit-page" href="<?php echo esc_url( admin_url( 'edit.php?post_type=industry' ) ); ?>">Edit Industries</a></li>
				</ul>
			</div>
			<div class="welcome-panel-column">
				<h3>Solution Builder</h3>
				<ul>
					<li><a class="welcome-icon welcome-view-site" href="<?php echo esc_url( admin_url( 'edit.php?post_type=sb_lead' ) ); ?>">View Solution Builder leads</a></li>
					<li><a class="welcome-icon welcome-widgets-menus" href="<?php echo esc_url( admin_url( 'admin.php?page=find-your-fit-settings' ) ); ?>">Edit Solution Builder Settings</a></li>
				</ul>
			</div>
			<div class="welcome-panel-column welcome-panel-last">
				<h3>Site-wide</h3>
				<ul>
					<li><a class="welcome-icon welcome-widgets-menus" href="<?php echo esc_url( admin_url( 'admin.php?page=site-settings' ) ); ?>">Edit Site Settings</a></li>
					<li><a class="welcome-icon welcome-widgets-menus" href="<?php echo esc_url( admin_url( 'nav-menus.php' ) ); ?>">Edit navigation menus</a></li>
				</ul>
			</div>
		</div>
	</div>
	<?php
}

// ---------------------------------------------------------------------
// 4d. Admin footer text.
// ---------------------------------------------------------------------
add_filter( 'admin_footer_text', function ( $text ) {
	$phone = get_field( 'support_phone', 'option' ) ?: '+61 468 765 815';
	return sprintf(
		'%s &mdash; for support contact %s',
		esc_html( get_field( 'company_name', 'option' ) ?: 'ITOI Solutions' ),
		esc_html( $phone )
	);
} );

// ---------------------------------------------------------------------
// 4e. Admin menu order — most-used CPTs first. Any top-level item this
// list doesn't name (Comments, Appearance, Plugins, Users, Settings,
// other options pages not listed, etc.) keeps its normal WP position,
// appended after the ones named here in whatever order WP already had
// them — nothing is hidden or removed, only reordered.
// ---------------------------------------------------------------------
add_filter( 'custom_menu_order', '__return_true' );
add_filter( 'menu_order', function ( $menu_order ) {
	if ( empty( $menu_order ) ) {
		return $menu_order;
	}
	$preferred = array(
		'index.php', // Dashboard
		'edit.php?post_type=solution',
		'edit.php?post_type=product',
		'edit.php?post_type=industry',
		'edit.php?post_type=case_study',
		'edit.php?post_type=use_case',
		'edit.php?post_type=insight',
		'edit.php?post_type=guide',
		'edit.php?post_type=sb_lead',
		'admin.php?page=site-settings',
		'admin.php?page=find-your-fit-settings', // Solution Builder Settings — see inc/acf.php for why the slug still says find-your-fit
	);
	$ordered = array_values( array_intersect( $preferred, $menu_order ) );
	$rest    = array_values( array_diff( $menu_order, $preferred ) );
	return array_merge( $ordered, $rest );
} );
