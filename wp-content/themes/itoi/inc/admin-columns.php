<?php
/**
 * Custom wp-admin list-table columns for the theme's CPTs — 2026-08-06
 * wp-admin content audit (see docs/acf-content-checklist.md). Every CPT
 * already had menu_icon + full labels + (where relevant) show_admin_column
 * on its taxonomy (inc/post-types.php) — confirmed during this audit, no
 * changes needed there. What was missing: several list screens showed only
 * the post title, which for CPTs whose title is really a slug/internal
 * label (solution, sb_lead) or whose most useful property lives on a
 * relationship field (case_study's client, insight's author) made the list
 * screen a poor way to actually find anything.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Inserts $new_columns into $columns right after the 'title' column (or at
 * the end, if for some reason a list doesn't have one) — every list here
 * wants its custom column(s) directly after the title, before Date.
 */
function itoi_admin_columns_insert_after_title( array $columns, array $new_columns ) {
	$result = array();
	foreach ( $columns as $key => $label ) {
		$result[ $key ] = $label;
		if ( 'title' === $key ) {
			$result += $new_columns;
		}
	}
	// Defensive: if there was somehow no 'title' column, don't silently drop the new ones.
	if ( ! isset( $columns['title'] ) ) {
		$result += $new_columns;
	}
	return $result;
}

// ---------------------------------------------------------------------
// Solution — post title is the CPT slug (e.g. "cctv-video-loss-prevention"),
// not what staff actually recognize; the ACF `headline` field is the real
// display title used everywhere on the front end.
// ---------------------------------------------------------------------
add_filter( 'manage_solution_posts_columns', function ( $columns ) {
	return itoi_admin_columns_insert_after_title( $columns, array( 'itoi_headline' => 'Headline' ) );
} );
add_action( 'manage_solution_posts_custom_column', function ( $column, $post_id ) {
	if ( 'itoi_headline' === $column ) {
		echo esc_html( get_field( 'headline', $post_id ) ?: '—' );
	}
}, 10, 2 );

// ---------------------------------------------------------------------
// Industry — how many use_case posts point at this industry. use_case's
// `industry` field is a post_object (single), so a meta_query on that
// field's post ID is a direct, accurate count — there is no use_cases
// repeater on the industry CPT itself (that would double-maintain the
// same relationship in two places).
// ---------------------------------------------------------------------
add_filter( 'manage_industry_posts_columns', function ( $columns ) {
	return itoi_admin_columns_insert_after_title( $columns, array( 'itoi_use_case_count' => 'Use cases' ) );
} );
add_action( 'manage_industry_posts_custom_column', function ( $column, $post_id ) {
	if ( 'itoi_use_case_count' !== $column ) {
		return;
	}
	$count = new WP_Query( array(
		'post_type'      => 'use_case',
		'post_status'    => 'any',
		'posts_per_page' => 1, // only need found_posts, not the actual rows
		'fields'         => 'ids',
		'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- admin list column, low-traffic screen, no viable non-meta alternative (industry is a plain post_object field).
			array(
				'key'   => 'industry',
				'value' => $post_id,
			),
		),
	) );
	echo esc_html( (string) $count->found_posts );
}, 10, 2 );

// ---------------------------------------------------------------------
// Case Study — client_name is a plain text field (not a relationship to
// the `client` CPT — that relationship runs the other way, client.php's
// own `case_study` post_object field points back at a case study), and
// `industry` is a relationship field. Both are useful at a glance.
// ---------------------------------------------------------------------
add_filter( 'manage_case_study_posts_columns', function ( $columns ) {
	return itoi_admin_columns_insert_after_title( $columns, array(
		'itoi_client'   => 'Client',
		'itoi_industry' => 'Industry',
	) );
} );
add_action( 'manage_case_study_posts_custom_column', function ( $column, $post_id ) {
	if ( 'itoi_client' === $column ) {
		echo esc_html( get_field( 'client_name', $post_id ) ?: '—' );
		return;
	}
	if ( 'itoi_industry' === $column ) {
		$industries = get_field( 'industry', $post_id );
		if ( empty( $industries ) ) {
			echo '—';
			return;
		}
		$titles = array_map( 'get_the_title', (array) $industries );
		echo esc_html( implode( ', ', $titles ) );
	}
}, 10, 2 );

// ---------------------------------------------------------------------
// Product — whether a featured image is set. Not shown by default for
// any CPT regardless of 'thumbnail' support (that only controls whether
// the *edit screen* has a Featured Image box) — WP core only adds this
// column automatically to Posts/Pages.
// ---------------------------------------------------------------------
add_filter( 'manage_product_posts_columns', function ( $columns ) {
	return itoi_admin_columns_insert_after_title( $columns, array( 'itoi_thumbnail' => 'Featured image' ) );
} );
add_action( 'manage_product_posts_custom_column', function ( $column, $post_id ) {
	if ( 'itoi_thumbnail' === $column ) {
		echo has_post_thumbnail( $post_id )
			? '<span class="dashicons dashicons-yes" aria-hidden="true"></span><span class="screen-reader-text">Yes</span>'
			: '<span aria-hidden="true">—</span><span class="screen-reader-text">No</span>';
	}
}, 10, 2 );

// ---------------------------------------------------------------------
// Insight — author is a relationship field to team_member (not WP's own
// post_author, which this theme doesn't use for insights).
// ---------------------------------------------------------------------
add_filter( 'manage_insight_posts_columns', function ( $columns ) {
	return itoi_admin_columns_insert_after_title( $columns, array( 'itoi_author' => 'Author' ) );
} );
add_action( 'manage_insight_posts_custom_column', function ( $column, $post_id ) {
	if ( 'itoi_author' !== $column ) {
		return;
	}
	$authors = get_field( 'author', $post_id );
	if ( empty( $authors ) ) {
		echo '—';
		return;
	}
	$names = array_map( 'get_the_title', (array) $authors );
	echo esc_html( implode( ', ', $names ) );
}, 10, 2 );

// ---------------------------------------------------------------------
// Guide — published_date is an ACF date_picker, separate from WP's own
// post date (guides can be authored/scheduled in wp-admin on one date but
// carry an earlier/different "published" editorial date).
// ---------------------------------------------------------------------
add_filter( 'manage_guide_posts_columns', function ( $columns ) {
	return itoi_admin_columns_insert_after_title( $columns, array( 'itoi_published_date' => 'Published date' ) );
} );
add_action( 'manage_guide_posts_custom_column', function ( $column, $post_id ) {
	if ( 'itoi_published_date' !== $column ) {
		return;
	}
	// ACF's date_picker stores/returns Ymd (e.g. "20260723") — reformatted
	// for the list table rather than shown raw.
	$raw = get_field( 'published_date', $post_id );
	if ( ! $raw ) {
		echo '—';
		return;
	}
	$date = DateTime::createFromFormat( 'Ymd', $raw );
	echo esc_html( $date ? $date->format( 'j M Y' ) : $raw );
}, 10, 2 );

// ---------------------------------------------------------------------
// Use Case — which industry it's assigned to (post_object). Bulk-edit
// screen (inc/use-case-bulk-edit.php) already shows this per-row, grouped
// as a heading, but that's a separate custom screen — this is the column
// on the *standard* wp-admin list (Edit -> Use Cases), which had no way to
// see or sort by industry at all before this.
// ---------------------------------------------------------------------
add_filter( 'manage_use_case_posts_columns', function ( $columns ) {
	return itoi_admin_columns_insert_after_title( $columns, array( 'itoi_industry' => 'Industry' ) );
} );
add_action( 'manage_use_case_posts_custom_column', function ( $column, $post_id ) {
	if ( 'itoi_industry' !== $column ) {
		return;
	}
	$industry_id = get_field( 'industry', $post_id );
	echo $industry_id ? esc_html( get_the_title( $industry_id ) ) : '—';
}, 10, 2 );

// ---------------------------------------------------------------------
// Solution Builder Lead — the whole point of this CPT is the lead data,
// so the list screen should show it directly rather than a name-only title
// column (leads are titled by inc/solution-builder.php's AJAX handler with
// whatever's convenient there, not a human-chosen title).
// ---------------------------------------------------------------------
add_filter( 'manage_sb_lead_posts_columns', function ( $columns ) {
	return itoi_admin_columns_insert_after_title( $columns, array(
		'itoi_sb_name'          => 'Name',
		'itoi_sb_email'         => 'Email',
		'itoi_sb_business_type' => 'Business type',
	) );
} );
add_action( 'manage_sb_lead_posts_custom_column', function ( $column, $post_id ) {
	$map = array(
		'itoi_sb_name'          => 'sb_name',
		'itoi_sb_email'         => 'sb_email',
		'itoi_sb_business_type' => 'sb_business_type',
	);
	if ( isset( $map[ $column ] ) ) {
		echo esc_html( get_field( $map[ $column ], $post_id ) ?: '—' );
	}
}, 10, 2 );

// Date column is already sortable by default on every post list (WP core)
// — sb_lead needs nothing extra for "sortable by date, most recent first",
// which is also list_table's default order.

/**
 * A quick filter dropdown above the Solution Builder Leads list, so staff
 * can narrow to one business type without opening every lead. Business
 * type values come from the same option list the Solution Builder itself
 * uses (inc/solution-builder.php), not a hardcoded second copy.
 */
add_action( 'restrict_manage_posts', function ( $post_type ) {
	if ( 'sb_lead' !== $post_type ) {
		return;
	}
	$current  = isset( $_GET['itoi_sb_business_type'] ) ? sanitize_text_field( wp_unslash( $_GET['itoi_sb_business_type'] ) ) : '';
	$industries = itoi_solution_builder_industries();
	?>
	<select name="itoi_sb_business_type">
		<option value="">All business types</option>
		<?php foreach ( $industries as $industry ) : ?>
			<option value="<?php echo esc_attr( $industry['slug'] ); ?>" <?php selected( $current, $industry['slug'] ); ?>><?php echo esc_html( $industry['title'] ); ?></option>
		<?php endforeach; ?>
	</select>
	<?php
} );

add_filter( 'parse_query', function ( $query ) {
	if ( ! is_admin() || ! $query->is_main_query() ) {
		return;
	}
	if ( 'sb_lead' !== $query->get( 'post_type' ) ) {
		return;
	}
	if ( empty( $_GET['itoi_sb_business_type'] ) ) {
		return;
	}
	$query->set( 'meta_query', array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- admin list filter, low-traffic screen.
		array(
			'key'   => 'sb_business_type',
			'value' => sanitize_text_field( wp_unslash( $_GET['itoi_sb_business_type'] ) ),
		),
	) );
} );

/**
 * sb_lead's "Add New" is already blocked at the capability level
 * (inc/post-types.php sets create_posts => 'do_not_allow', which is why
 * the "Add New" button doesn't render in the first place) — leads are
 * only ever created by the Solution Builder's own AJAX handler. This adds
 * a second, visible layer: hides the button via CSS in case any future
 * plugin/filter changes the capability check, and redirects away from
 * post-new.php?post_type=sb_lead if someone still lands on it directly
 * (e.g. a bookmarked URL from before the capability was locked down).
 */
add_action( 'admin_head-edit.php', function () {
	if ( 'sb_lead' === ( $_GET['post_type'] ?? '' ) ) {
		echo '<style>.page-title-action{display:none;}</style>';
	}
} );
add_action( 'load-post-new.php', function () {
	if ( 'sb_lead' === ( $_GET['post_type'] ?? '' ) ) {
		wp_safe_redirect( admin_url( 'edit.php?post_type=sb_lead' ) );
		exit;
	}
} );
