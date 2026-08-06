<?php
/**
 * Custom JSON-LD not covered by Yoast SEO's built-in schema graph
 * (PROJECT.md §8): LocalBusiness site-wide, and Service on solutions —
 * Yoast core has no built-in "Service" page type (that's a Premium/
 * add-on feature), so it's emitted directly here instead. Organization
 * and Article (case studies/insights) are handled by Yoast itself via
 * its own settings (see NOTES.md) — not duplicated here, to avoid two
 * competing schema graphs on the same page.
 *
 * Article on case_study/insight is also handled here, not by Yoast:
 * Yoast free's Article schema generator hard-checks
 * `$context->indexable->object_type === 'post'` (see
 * wp-content/plugins/wordpress-seo/src/generators/schema/article.php)
 * and never fires for any custom post type, regardless of the
 * per-post-type "Article" setting in Search Appearance — confirmed by
 * reading that file directly rather than assuming the settings UI
 * option actually does anything for a CPT.
 *
 * Every value below is sourced from the Site Settings options page or
 * a post's own ACF/standard fields — nothing here is invented copy.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function itoi_local_business_schema() {
	$address = get_field( 'company_address', 'option' );
	if ( ! $address ) {
		return;
	}

	$phone = get_field( 'manager_1_phone', 'option' ) ?: get_field( 'support_phone', 'option' );

	$schema = array(
		'@context' => 'https://schema.org',
		'@type'    => 'LocalBusiness',
		'@id'      => home_url( '/#localbusiness' ),
		'name'     => 'ITOI Solutions',
		'url'      => home_url( '/' ),
		'address'  => array(
			'@type'          => 'PostalAddress',
			'streetAddress'  => $address,
			'addressCountry' => 'AU',
		),
	);

	if ( $phone ) {
		$schema['telephone'] = $phone;
	}

	$office_hours = get_field( 'office_hours', 'option' );
	if ( $office_hours ) {
		$schema['openingHours'] = $office_hours;
	}

	echo '<script type="application/ld+json">' . wp_json_encode( $schema ) . '</script>' . "\n";
}
add_action( 'wp_head', 'itoi_local_business_schema' );

function itoi_service_schema() {
	if ( ! is_singular( 'solution' ) ) {
		return;
	}

	$headline = get_field( 'headline' ) ?: get_the_title();
	$dek      = get_field( 'dek' );

	$schema = array(
		'@context'    => 'https://schema.org',
		'@type'       => 'Service',
		'@id'         => get_permalink() . '#service',
		'name'        => $headline,
		'url'         => get_permalink(),
		'provider'    => array(
			'@type' => 'Organization',
			'name'  => 'ITOI Solutions',
			'url'   => home_url( '/' ),
		),
	);

	if ( $dek ) {
		$schema['description'] = $dek;
	}

	echo '<script type="application/ld+json">' . wp_json_encode( $schema ) . '</script>' . "\n";
}
add_action( 'wp_head', 'itoi_service_schema' );

function itoi_article_schema() {
	if ( ! is_singular( array( 'case_study', 'insight' ) ) ) {
		return;
	}

	$post_id = get_the_ID();

	$schema = array(
		'@context'      => 'https://schema.org',
		'@type'         => 'Article',
		'@id'           => get_permalink() . '#article',
		'headline'      => get_the_title(),
		'url'           => get_permalink(),
		'datePublished' => get_the_date( 'c', $post_id ),
		'dateModified'  => get_the_modified_date( 'c', $post_id ),
		'publisher'     => array(
			'@type' => 'Organization',
			'name'  => 'ITOI Solutions',
			'url'   => home_url( '/' ),
		),
	);

	if ( 'case_study' === get_post_type( $post_id ) ) {
		$dek_source = get_field( 'headline', $post_id );
		$image_id   = get_field( 'hero_image', $post_id );
	} else {
		$dek_source = get_field( 'dek', $post_id );
		$image_id   = get_post_thumbnail_id( $post_id );

		$author_ids = get_field( 'author', $post_id );
		$author_id  = ! empty( $author_ids ) ? $author_ids[0] : null;
		if ( $author_id ) {
			$schema['author'] = array(
				'@type' => 'Person',
				'name'  => get_field( 'name', $author_id ) ?: get_the_title( $author_id ),
			);
		}
	}

	if ( $dek_source ) {
		$schema['description'] = $dek_source;
	}

	if ( ! empty( $image_id ) ) {
		$image_url = wp_get_attachment_image_url( $image_id, 'large' );
		if ( $image_url ) {
			$schema['image'] = $image_url;
		}
	}

	echo '<script type="application/ld+json">' . wp_json_encode( $schema ) . '</script>' . "\n";
}
add_action( 'wp_head', 'itoi_article_schema' );

/**
 * Product schema for the `product` CPT (2026-08-06 SEO pass — see NOTES.md).
 * Neither `dek` nor `product_price` exist as real ACF fields on `product`
 * today (confirmed: only one field group — "Product Page Content", with
 * teaser_* fields and a page_sections flexible-content block — targets
 * this CPT), so both checks below are honestly empty for the 2 real
 * products (Aurora, PC2SE Outdoor) right now; kept as real, forward-
 * compatible checks (same `if ( $value )` pattern as every other function
 * in this file) rather than removed, so a future `dek`/`product_price`
 * field starts emitting description/offers automatically with no code
 * change — never inventing a description or price that isn't actually in
 * a field.
 */
function itoi_product_schema() {
	if ( ! is_singular( 'product' ) ) {
		return;
	}

	$post_id     = get_the_ID();
	$description = get_field( 'dek', $post_id );

	$schema = array(
		'@context' => 'https://schema.org',
		'@type'    => 'Product',
		'@id'      => get_permalink() . '#product',
		'name'     => get_the_title(),
		'url'      => get_permalink(),
		'brand'    => array(
			'@type' => 'Organization',
			'name'  => 'ITOI Solutions',
		),
	);

	if ( $description ) {
		$schema['description'] = $description;
	}

	if ( has_post_thumbnail( $post_id ) ) {
		$image_url = get_the_post_thumbnail_url( $post_id, 'large' );
		if ( $image_url ) {
			$schema['image'] = $image_url;
		}
	}

	$price = get_field( 'product_price', $post_id );
	if ( ! empty( $price ) ) {
		$schema['offers'] = array(
			'@type'         => 'Offer',
			'price'         => $price,
			'priceCurrency' => 'AUD',
			'availability'  => 'https://schema.org/InStock',
		);
	}

	echo '<script type="application/ld+json">' . wp_json_encode( $schema ) . '</script>' . "\n";
}
add_action( 'wp_head', 'itoi_product_schema' );

/**
 * BlogPosting for the Education Hub's `guide` CPT (PROJECT.md §8: "Article
 * or BlogPosting, with author reference"). Not folded into
 * itoi_article_schema() above — guide's field names (dek/body/
 * published_date) don't match case_study/insight's. Guide has no
 * ACF author field and every existing guide's post_author is 0 (no real
 * WP user attached) — inventing a byline would violate the do-not-invent
 * rule (PROJECT.md §6), so ITOI Solutions (Organization) is used as the
 * author, same entity as the publisher, which is a valid schema.org
 * Article.author value and the honest one given the actual data.
 */
function itoi_guide_schema() {
	if ( ! is_singular( 'guide' ) ) {
		return;
	}

	$post_id        = get_the_ID();
	$dek            = get_field( 'dek', $post_id );
	$published_date = get_field( 'published_date', $post_id ); // ACF date_picker, return_format "Ymd" — not a MySQL date string.
	$image_id       = get_post_thumbnail_id( $post_id );

	$date_published = get_the_date( 'c', $post_id );
	if ( $published_date ) {
		$parsed = DateTime::createFromFormat( 'Ymd', $published_date );
		if ( $parsed ) {
			$date_published = $parsed->format( 'c' );
		}
	}

	$organization = array(
		'@type' => 'Organization',
		'name'  => 'ITOI Solutions',
		'url'   => home_url( '/' ),
	);

	$schema = array(
		'@context'      => 'https://schema.org',
		'@type'         => 'BlogPosting',
		'@id'           => get_permalink() . '#article',
		'headline'      => get_the_title(),
		'url'           => get_permalink(),
		'datePublished' => $date_published,
		'dateModified'  => get_the_modified_date( 'c', $post_id ),
		'author'        => $organization,
		'publisher'     => $organization,
	);

	if ( $dek ) {
		$schema['description'] = $dek;
	}

	if ( ! empty( $image_id ) ) {
		$image_url = wp_get_attachment_image_url( $image_id, 'large' );
		if ( $image_url ) {
			$schema['image'] = $image_url;
		}
	}

	echo '<script type="application/ld+json">' . wp_json_encode( $schema ) . '</script>' . "\n";
}
add_action( 'wp_head', 'itoi_guide_schema' );

/**
 * BreadcrumbList for every singular post (any CPT) and WP Page — not the
 * front page (a breadcrumb trail of just "Home" is meaningless there), not
 * archives/search/404 (is_singular() is already false for all of those).
 * Yoast free's own breadcrumb *feature* is opt-in shortcode/template-tag
 * output, not schema it emits automatically — this fills the structured-
 * data gap without needing that feature turned on.
 */
function itoi_breadcrumb_schema() {
	if ( is_front_page() || ! is_singular() ) {
		return;
	}

	$post_id = get_the_ID();
	if ( ! $post_id ) {
		return;
	}

	$items = array(
		array(
			'@type'    => 'ListItem',
			'position' => 1,
			'name'     => 'Home',
			'item'     => home_url( '/' ),
		),
	);

	if ( 'page' === get_post_type( $post_id ) ) {
		// Parent pages, root-first — get_post_ancestors() returns nearest-first.
		$ancestor_ids = array_reverse( get_post_ancestors( $post_id ) );
		foreach ( $ancestor_ids as $ancestor_id ) {
			$items[] = array(
				'@type'    => 'ListItem',
				'position' => count( $items ) + 1,
				'name'     => get_the_title( $ancestor_id ),
				'item'     => get_permalink( $ancestor_id ),
			);
		}
	} else {
		// CPT archive step — get_post_type_archive_link() is already false
		// for a CPT with no public archive (public/has_archive => false),
		// so that case correctly skips straight to the current page below
		// with no extra check needed.
		$post_type      = get_post_type( $post_id );
		$archive_link   = get_post_type_archive_link( $post_type );
		$post_type_obj  = get_post_type_object( $post_type );
		if ( $archive_link && $post_type_obj && ! empty( $post_type_obj->labels->name ) ) {
			$items[] = array(
				'@type'    => 'ListItem',
				'position' => count( $items ) + 1,
				'name'     => $post_type_obj->labels->name,
				'item'     => $archive_link,
			);
		}
	}

	$items[] = array(
		'@type'    => 'ListItem',
		'position' => count( $items ) + 1,
		'name'     => get_the_title( $post_id ),
		'item'     => get_permalink( $post_id ),
	);

	$schema = array(
		'@context'        => 'https://schema.org',
		'@type'           => 'BreadcrumbList',
		'itemListElement' => $items,
	);

	echo '<script type="application/ld+json">' . wp_json_encode( $schema ) . '</script>' . "\n";
}
add_action( 'wp_head', 'itoi_breadcrumb_schema' );
