<?php
/**
 * Education Hub helpers — shared query logic for page-education.php,
 * archive-guide.php, page-glossary.php and page-faq.php, so the "pull every
 * FAQ across every solution" and "list every glossary term" queries live in
 * one place rather than four.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Every glossary_term post, alphabetical by term (falls back to post_title).
 */
function itoi_edu_get_glossary_terms() {
	$query = new WP_Query(
		array(
			'post_type'      => 'glossary_term',
			'posts_per_page' => -1,
			'orderby'        => 'title',
			'order'          => 'ASC',
		)
	);
	return $query->posts;
}

/**
 * Every FAQ entry across every published solution, grouped by solution.
 * Solutions with an empty faqs repeater are simply absent from the
 * returned array — never a broken/empty group (CLAUDE.md's "empty states
 * are real states" rule).
 *
 * Returns: array of { solution_id, solution_title, solution_url, faqs: [ {q,a}, ... ] }
 */
function itoi_edu_get_all_faqs() {
	$groups = array();

	$query = new WP_Query(
		array(
			'post_type'      => 'solution',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'title',
			'order'          => 'ASC',
		)
	);

	foreach ( $query->posts as $solution ) {
		$faqs = get_field( 'faqs', $solution->ID );
		if ( empty( $faqs ) ) {
			continue;
		}
		$groups[] = array(
			'solution_id'    => $solution->ID,
			'solution_title' => get_field( 'headline', $solution->ID ) ?: get_the_title( $solution->ID ),
			'solution_url'   => get_permalink( $solution->ID ),
			'faqs'           => $faqs,
		);
	}

	return $groups;
}
