<?php
/**
 * Custom post types — PROJECT.md §4. Registered in the theme, not a plugin,
 * for portability.
 *
 * @package ITOI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers every custom post type this theme defines (PROJECT.md §4).
 */
function itoi_register_post_types() {

	register_post_type(
		'solution',
		array(
			'labels'       => array(
				'name'          => __( 'Solutions', 'itoi' ),
				'singular_name' => __( 'Solution', 'itoi' ),
				'add_new_item'  => __( 'Add New Solution', 'itoi' ),
				'edit_item'     => __( 'Edit Solution', 'itoi' ),
			),
			'public'       => true,
			'has_archive'  => true,
			'show_in_rest' => true,
			'menu_icon'    => 'dashicons-shield',
			'supports'     => array( 'title', 'thumbnail' ),
			'rewrite'      => array(
				'slug'       => 'solutions',
				'with_front' => false,
			),
		)
	);

	// Not publicly routed — same "pure data" pattern as team_member/client
	// below. Migrated 2026-07-30 (see NOTES.md): this used to be `public` +
	// `has_archive` with its own /use-cases/ archive template, but no real
	// WP Page ever backed that URL, which made the page's own headline/
	// intro copy uneditable in wp-admin. /use-cases/ is now a real Page
	// (page-use-cases.php) that queries these posts via
	// itoi_get_industry_use_cases() (inc/use-cases.php) — individual
	// use_case posts still don't need their own single URL, only the admin
	// list/edit screens (show_ui/show_in_menu) for editing each one.
	register_post_type(
		'use_case',
		array(
			'labels'              => array(
				'name'          => __( 'Use Cases', 'itoi' ),
				'singular_name' => __( 'Use Case', 'itoi' ),
				'add_new_item'  => __( 'Add New Use Case', 'itoi' ),
				'edit_item'     => __( 'Edit Use Case', 'itoi' ),
			),
			'public'              => false,
			'publicly_queryable'  => false,
			'exclude_from_search' => true,
			'has_archive'         => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_rest'        => true,
			'menu_icon'           => 'dashicons-analytics',
			'supports'            => array( 'title' ),
			'rewrite'             => false,
		)
	);

	register_post_type(
		'case_study',
		array(
			'labels'       => array(
				'name'          => __( 'Case Studies', 'itoi' ),
				'singular_name' => __( 'Case Study', 'itoi' ),
				'add_new_item'  => __( 'Add New Case Study', 'itoi' ),
				'edit_item'     => __( 'Edit Case Study', 'itoi' ),
			),
			'public'       => true,
			'has_archive'  => true,
			'show_in_rest' => true,
			'menu_icon'    => 'dashicons-chart-line',
			'supports'     => array( 'title', 'thumbnail' ),
			'rewrite'      => array(
				'slug'       => 'case-studies',
				'with_front' => false,
			),
		)
	);

	register_post_type(
		'industry',
		array(
			'labels'       => array(
				'name'          => __( 'Industries', 'itoi' ),
				'singular_name' => __( 'Industry', 'itoi' ),
				'add_new_item'  => __( 'Add New Industry', 'itoi' ),
				'edit_item'     => __( 'Edit Industry', 'itoi' ),
			),
			'public'       => true,
			'has_archive'  => true,
			'show_in_rest' => true,
			'menu_icon'    => 'dashicons-building',
			'supports'     => array( 'title', 'thumbnail' ),
			'rewrite'      => array(
				'slug'       => 'industries',
				'with_front' => false,
			),
		)
	);

	// Not publicly routed — referenced via relationship fields (insight author)
	// and listed on /team/ (page-team.php), not given its own URL.
	register_post_type(
		'team_member',
		array(
			'labels'              => array(
				'name'          => __( 'Team Members', 'itoi' ),
				'singular_name' => __( 'Team Member', 'itoi' ),
				'add_new_item'  => __( 'Add New Team Member', 'itoi' ),
				'edit_item'     => __( 'Edit Team Member', 'itoi' ),
			),
			'public'              => false,
			'publicly_queryable'  => false,
			'exclude_from_search' => true,
			'has_archive'         => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			// Internal-only PII/staff data — never publicly queryable via the
			// REST API (2026-08-06 security fix, see NOTES.md). show_ui/
			// show_in_menu above are untouched, so the wp-admin list/edit
			// screens keep working exactly as before.
			'show_in_rest'        => false,
			'menu_icon'           => 'dashicons-groups',
			'supports'            => array( 'title', 'thumbnail' ),
			'rewrite'             => false,
		)
	);

	// Not publicly routed — no single-client page exists (PROJECT.md doesn't
	// spec one). Queried directly by page-customers.php and the homepage
	// preview section, same "pure data" pattern as team_member above.
	register_post_type(
		'client',
		array(
			'labels'              => array(
				'name'          => __( 'Clients', 'itoi' ),
				'singular_name' => __( 'Client', 'itoi' ),
				'add_new_item'  => __( 'Add New Client', 'itoi' ),
				'edit_item'     => __( 'Edit Client', 'itoi' ),
			),
			'public'              => false,
			'publicly_queryable'  => false,
			'exclude_from_search' => true,
			'has_archive'         => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			// Internal-only client business data — never publicly queryable
			// via the REST API (2026-08-06 security fix, see NOTES.md).
			'show_in_rest'        => false,
			'menu_icon'           => 'dashicons-store',
			'supports'            => array( 'title' ),
			'rewrite'             => false,
		)
	);

	// Education Hub — Guides. Publicly routed, nested under /education/guides/
	// so it sits alongside the Education Hub landing page (a WP Page at
	// /education/) without colliding with it — has_archive/rewrite both use
	// the nested slug rather than a bare 'guides'.
	register_post_type(
		'guide',
		array(
			'labels'       => array(
				'name'          => __( 'Guides', 'itoi' ),
				'singular_name' => __( 'Guide', 'itoi' ),
				'add_new_item'  => __( 'Add New Guide', 'itoi' ),
				'edit_item'     => __( 'Edit Guide', 'itoi' ),
			),
			'public'       => true,
			'has_archive'  => 'education/guides',
			'show_in_rest' => true,
			'menu_icon'    => 'dashicons-book-alt',
			'supports'     => array( 'title', 'thumbnail' ),
			'rewrite'      => array(
				'slug'       => 'education/guides',
				'with_front' => false,
			),
		)
	);

	// Education Hub — Glossary terms. Not publicly routed, same "pure data"
	// pattern as client/team_member above: PROJECT.md's Education Hub spec
	// calls for one alphabetical /education/glossary/ listing page, not an
	// individual URL per term, and a CPT (rather than an Options Page
	// repeater) is needed here specifically so `related_guides` can be a
	// real relationship field pointing at actual `guide` posts.
	register_post_type(
		'glossary_term',
		array(
			'labels'              => array(
				'name'          => __( 'Glossary Terms', 'itoi' ),
				'singular_name' => __( 'Glossary Term', 'itoi' ),
				'add_new_item'  => __( 'Add New Glossary Term', 'itoi' ),
				'edit_item'     => __( 'Edit Glossary Term', 'itoi' ),
			),
			'public'              => false,
			'publicly_queryable'  => false,
			'exclude_from_search' => true,
			'has_archive'         => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_rest'        => true,
			'menu_icon'           => 'dashicons-book',
			'supports'            => array( 'title' ),
			'rewrite'             => false,
		)
	);

	// Solution Builder lead capture — not publicly routed, same "pure data"
	// pattern as client/team_member above. Written by the Solution Builder's
	// AJAX submit handler (inc/solution-builder.php), read by staff in
	// wp-admin; visitors never see a single-lead URL.
	register_post_type(
		'sb_lead',
		array(
			'labels'              => array(
				'name'          => __( 'Solution Builder Leads', 'itoi' ),
				'singular_name' => __( 'Solution Builder Lead', 'itoi' ),
				'add_new_item'  => __( 'Add New Lead', 'itoi' ),
				'edit_item'     => __( 'Edit Lead', 'itoi' ),
			),
			'public'              => false,
			'publicly_queryable'  => false,
			'exclude_from_search' => true,
			'has_archive'         => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			// Visitor-submitted PII (name/email/phone/business type/7-question
			// answers) — never publicly queryable via the REST API, with no
			// authentication, which is exactly what `show_in_rest => true`
			// did before this fix (2026-08-06 security fix, see NOTES.md).
			'show_in_rest'        => false,
			'menu_icon'           => 'dashicons-clipboard',
			'supports'            => array( 'title' ),
			'capabilities'        => array(
				'create_posts' => 'do_not_allow', // Created only via the AJAX handler, not wp-admin "Add New".
			),
			'map_meta_cap'        => true,
			'rewrite'             => false,
		)
	);

	// Products — hardware SKUs (Aurora being the first), distinct from the
	// `solution` CPT above (software/service categories, publicly routed at
	// /solutions/{slug}/). Superseded 2026-07-31 (see NOTES.md, "Products
	// turnstile + per-product pages" entry): now a real public CPT with its
	// own routing (/products/{slug}/, archive /products/), NOT the earlier
	// one-off "separate WP Page + page-{slug}.php" pairing Aurora briefly
	// used — that pattern didn't scale to "every product gets its own page"
	// without a manual WP Page + template file per product. single-
	// product.php + archive-product.php (both new) render every product
	// from its own `page_sections` Flexible Content field (acf-json/ —
	// "Product Page Content"), giving non-technical staff full control over
	// which sections a product's page has and what order they're in.
	register_post_type(
		'product',
		array(
			'labels'       => array(
				'name'          => __( 'Products', 'itoi' ),
				'singular_name' => __( 'Product', 'itoi' ),
				'add_new_item'  => __( 'Add New Product', 'itoi' ),
				'edit_item'     => __( 'Edit Product', 'itoi' ),
			),
			'public'       => true,
			'has_archive'  => 'products',
			'show_ui'      => true,
			'show_in_menu' => true,
			'show_in_rest' => true,
			'menu_icon'    => 'dashicons-camera',
			'supports'     => array( 'title', 'thumbnail' ),
			'rewrite'      => array(
				'slug'       => 'products',
				'with_front' => false,
			),
		)
	);

	register_post_type(
		'insight',
		array(
			'labels'       => array(
				'name'          => __( 'Insights', 'itoi' ),
				'singular_name' => __( 'Insight', 'itoi' ),
				'add_new_item'  => __( 'Add New Insight', 'itoi' ),
				'edit_item'     => __( 'Edit Insight', 'itoi' ),
			),
			'public'       => true,
			'has_archive'  => true,
			'show_in_rest' => true,
			'menu_icon'    => 'dashicons-admin-post',
			'supports'     => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
			'rewrite'      => array(
				'slug'       => 'insights',
				'with_front' => false,
			),
		)
	);
}
add_action( 'init', 'itoi_register_post_types' );

/**
 * Client categories — the live site's own 6 portfolio section headings
 * (Shopping Centres, Retail, Hospitality & Industry, Councils &
 * Commercial, Financial Institutions, Hotels), not PROJECT.md §4's locked
 * 7-slug `industry` CPT. Those don't map onto this data cleanly (e.g.
 * "Shopping Centres"/"Hotels" have no equivalent industry slug), so this
 * stays a separate, honest, source-backed taxonomy rather than forcing an
 * invented mapping.
 */
function itoi_register_taxonomies() {

	register_taxonomy(
		'client_category',
		array( 'client' ),
		array(
			'labels'             => array(
				'name'          => __( 'Client Categories', 'itoi' ),
				'singular_name' => __( 'Client Category', 'itoi' ),
			),
			'hierarchical'       => true,
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_admin_column'  => true,
			'show_in_rest'       => true,
			'rewrite'            => false,
		)
	);
}
add_action( 'init', 'itoi_register_taxonomies' );
