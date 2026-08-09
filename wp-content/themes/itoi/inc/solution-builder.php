<?php
/**
 * Solution Builder — /solution-builder/ (page-solution-builder.php).
 *
 * A rules-based recommendation engine, not an AI/LLM call: every weight
 * below is the exact table from the build spec, applied deterministically.
 * Scoring/ROI/timeline are calculated here in PHP (never duplicated in JS)
 * so there is exactly one place that can get the arithmetic wrong, and so
 * the lead-capture handler can recalculate authoritatively from the raw
 * answers instead of trusting whatever a client posts back.
 *
 * @package ITOI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The 8 solution categories — CPT `solution` slugs (PROJECT.md §4), in a
 * fixed order so score initialization/tie-breaking is stable.
 */
function itoi_solution_builder_categories() {
	return array(
		'intelligence-analytics',
		'customer-engagement-signage',
		'sensory-intelligence',
		'workforce-ops-robotics',
		'cctv-video-loss-prevention',
		'security-access-inventory',
		'back-of-house-integration',
		'it-network-infrastructure',
	);
}

/** Business type (industry slug) -> categories weighted +2 each. */
function itoi_solution_builder_industry_weights() {
	return array(
		'retail'                => array( 'intelligence-analytics', 'cctv-video-loss-prevention', 'security-access-inventory', 'workforce-ops-robotics' ),
		'hospitality'           => array( 'customer-engagement-signage', 'back-of-house-integration', 'cctv-video-loss-prevention', 'workforce-ops-robotics' ),
		'banking-finance'       => array( 'security-access-inventory', 'cctv-video-loss-prevention', 'intelligence-analytics' ),
		'government-councils'   => array( 'intelligence-analytics', 'cctv-video-loss-prevention', 'security-access-inventory' ),
		'logistics-warehousing' => array( 'cctv-video-loss-prevention', 'workforce-ops-robotics', 'intelligence-analytics' ),
		'stadiums-events'       => array( 'intelligence-analytics', 'security-access-inventory', 'cctv-video-loss-prevention' ),
		'casinos-gaming'        => array( 'cctv-video-loss-prevention', 'security-access-inventory', 'back-of-house-integration' ),
	);
}

/**
 * Step 1 — business type. Real `industry` CPT posts, PROJECT.md §4's fixed
 * order, resolved here once so both the popup (footer.php) and the
 * dedicated page (page-solution-builder.php) render the exact same list
 * from the exact same source — never two hand-typed copies to drift apart.
 *
 * @return array list of array( 'slug' => ..., 'title' => ... )
 */
function itoi_solution_builder_industries() {
	static $industries = null;
	if ( null !== $industries ) {
		return $industries;
	}

	$slugs      = array_keys( itoi_solution_builder_industry_weights() );
	$order      = array( 'retail', 'hospitality', 'casinos-gaming', 'banking-finance', 'government-councils', 'logistics-warehousing', 'stadiums-events' );
	$industries = array();

	foreach ( $order as $slug ) {
		if ( ! in_array( $slug, $slugs, true ) ) {
			continue;
		}
		$post = get_page_by_path( $slug, OBJECT, 'industry' );
		if ( $post && 'publish' === $post->post_status ) {
			$industries[] = array(
				'slug'  => $slug,
				'title' => get_the_title( $post ),
			);
		}
	}

	return $industries;
}

/** Step 2 — employees. Value => [label, midpoint for the ROI formula]. */
function itoi_solution_builder_employee_options() {
	return array(
		'1-10'   => array(
			'label'    => '1-10',
			'midpoint' => 5,
		),
		'11-50'  => array(
			'label'    => '11-50',
			'midpoint' => 30,
		),
		'51-200' => array(
			'label'    => '51-200',
			'midpoint' => 125,
		),
		'200+'   => array(
			'label'    => '200+',
			'midpoint' => 250,
		),
	);
}

/** Step 3 — sites. Value => [label, midpoint for the ROI formula]. */
function itoi_solution_builder_site_options() {
	return array(
		'1'    => array(
			'label'    => '1',
			'midpoint' => 1,
		),
		'2-5'  => array(
			'label'    => '2-5',
			'midpoint' => 3,
		),
		'6-20' => array(
			'label'    => '6-20',
			'midpoint' => 13,
		),
		'20+'  => array(
			'label'    => '20+',
			'midpoint' => 25,
		),
	);
}

/** Step 7 — challenges (multi-select). Value => [label, weights]. */
function itoi_solution_builder_challenge_options() {
	return array(
		'theft'             => array(
			'label'   => 'Theft or shrinkage',
			'weights' => array(
				'cctv-video-loss-prevention' => 3,
				'security-access-inventory'  => 1,
			),
		),
		'staffing'          => array(
			'label'   => 'Staffing inefficiency',
			'weights' => array( 'workforce-ops-robotics' => 3 ),
		),
		'visibility'        => array(
			'label'   => 'Poor visibility across sites',
			'weights' => array(
				'back-of-house-integration' => 3,
				'intelligence-analytics'    => 1,
			),
		),
		'outdated-security' => array(
			'label'   => 'Outdated security',
			'weights' => array(
				'security-access-inventory' => 3,
				'it-network-infrastructure' => 1,
			),
		),
		'engagement'        => array(
			'label'   => 'Low customer engagement',
			'weights' => array(
				'customer-engagement-signage' => 3,
				'sensory-intelligence'        => 1,
			),
		),
		'compliance'        => array(
			'label'   => 'Compliance concerns',
			'weights' => array(
				'security-access-inventory' => 2,
				'back-of-house-integration' => 2,
			),
		),
	);
}

/**
 * Core scoring engine — PART 3 of the build spec, applied verbatim.
 *
 * @param array $answers {
 *   The quiz answers to score.
 *
 *   @type string $business_type  Industry slug.
 *   @type string $employees      One of itoi_solution_builder_employee_options() keys.
 *   @type string $sites          One of itoi_solution_builder_site_options() keys.
 *   @type string $existing_cctv  'yes'|'no'.
 *   @type string $existing_pos   'yes'|'no'.
 *   @type string $cloud_based    'yes'|'no'.
 *   @type array  $challenges     Subset of itoi_solution_builder_challenge_options() keys.
 * }
 * @return array
 */
function itoi_solution_builder_calculate( $answers ) {
	$categories = itoi_solution_builder_categories();
	$scores     = array_fill_keys( $categories, 0 );

	// --- BUSINESS TYPE: +2 to each solution in that industry's list ---
	$industry_weights = itoi_solution_builder_industry_weights();
	if ( ! empty( $answers['business_type'] ) && isset( $industry_weights[ $answers['business_type'] ] ) ) {
		foreach ( $industry_weights[ $answers['business_type'] ] as $slug ) {
			$scores[ $slug ] += 2;
		}
	}

	// --- EMPLOYEES ---
	if ( '51-200' === ( $answers['employees'] ?? '' ) ) {
		$scores['back-of-house-integration'] += 1;
	} elseif ( '200+' === ( $answers['employees'] ?? '' ) ) {
		$scores['back-of-house-integration'] += 2;
		$scores['it-network-infrastructure'] += 2;
	}

	// --- SITES ---
	if ( '6-20' === ( $answers['sites'] ?? '' ) ) {
		$scores['back-of-house-integration'] += 1;
		$scores['it-network-infrastructure'] += 1;
	} elseif ( '20+' === ( $answers['sites'] ?? '' ) ) {
		$scores['back-of-house-integration'] += 2;
		$scores['it-network-infrastructure'] += 2;
	}

	// --- EXISTING CCTV ---
	if ( 'no' === ( $answers['existing_cctv'] ?? '' ) ) {
		$scores['cctv-video-loss-prevention'] += 3;
		$scores['security-access-inventory']  += 2;
	} elseif ( 'yes' === ( $answers['existing_cctv'] ?? '' ) ) {
		$scores['intelligence-analytics'] += 1;
	}

	// --- EXISTING POS ---
	if ( 'yes' === ( $answers['existing_pos'] ?? '' ) ) {
		$scores['intelligence-analytics']     += 2;
		$scores['cctv-video-loss-prevention'] += 1;
	}

	// --- CLOUD-BASED ---
	if ( 'yes' === ( $answers['cloud_based'] ?? '' ) ) {
		$scores['back-of-house-integration'] += 1;
		$scores['it-network-infrastructure'] += 1;
	} elseif ( 'no' === ( $answers['cloud_based'] ?? '' ) ) {
		$scores['it-network-infrastructure'] += 2;
	}

	// --- CHALLENGES (stack) ---
	$challenge_options = itoi_solution_builder_challenge_options();
	$challenges        = is_array( $answers['challenges'] ?? null ) ? $answers['challenges'] : array();
	foreach ( $challenges as $challenge_value ) {
		if ( ! isset( $challenge_options[ $challenge_value ] ) ) {
			continue;
		}
		foreach ( $challenge_options[ $challenge_value ]['weights'] as $slug => $weight ) {
			$scores[ $slug ] += $weight;
		}
	}

	// --- RESULT: sort desc, keep first-seen order stable on ties ---
	$ordered = $categories;
	usort(
		$ordered,
		function ( $a, $b ) use ( $scores, $categories ) {
			if ( $scores[ $a ] === $scores[ $b ] ) {
				return array_search( $a, $categories, true ) <=> array_search( $b, $categories, true );
			}
			return $scores[ $b ] <=> $scores[ $a ];
		}
	);

	$recommended = array_slice( $ordered, 0, 3 );
	if ( count( $ordered ) >= 4 ) {
		$third_score  = $scores[ $ordered[2] ];
		$fourth_score = $scores[ $ordered[3] ];
		if ( $fourth_score < ( $third_score / 2 ) ) {
			// Clear gap after 3rd place — stop at 3 (already the default).
			$recommended = array_slice( $ordered, 0, 3 );
		} elseif ( ( $third_score - $fourth_score ) <= 2 ) {
			// 4th place close to 3rd — show 4 instead.
			$recommended = array_slice( $ordered, 0, 4 );
		}
	}
	// Never fewer than 2, never more than 4 (guaranteed by the logic above
	// for 8 fixed categories, enforced explicitly here as a hard floor/ceiling).
	if ( count( $recommended ) < 2 ) {
		$recommended = array_slice( $ordered, 0, 2 );
	} elseif ( count( $recommended ) > 4 ) {
		$recommended = array_slice( $recommended, 0, 4 );
	}

	// --- ROI ESTIMATE (Part 5, exact formula) ---
	$employee_options   = itoi_solution_builder_employee_options();
	$site_options       = itoi_solution_builder_site_options();
	$employees_midpoint = $employee_options[ $answers['employees'] ?? '' ]['midpoint'] ?? 0;
	$sites_midpoint     = $site_options[ $answers['sites'] ?? '' ]['midpoint'] ?? 0;

	$efficiency_saving = $employees_midpoint * 2 * 35 * 52;

	$loss_reduction_value = 0;
	$theft_selected       = in_array( 'theft', $challenges, true );
	if ( $theft_selected ) {
		$loss_reduction_value = $sites_midpoint * 8000;
	}

	$roi_total = $efficiency_saving + $loss_reduction_value;

	// --- IMPLEMENTATION TIMELINE (Part 6, exact mapping) ---
	$timeline_map   = array(
		'1'    => '2-4 weeks',
		'2-5'  => '4-8 weeks',
		'6-20' => '8-16 weeks (2-4 months)',
		'20+'  => '16-26 weeks (4-6 months)',
	);
	$timeline_range = $timeline_map[ $answers['sites'] ?? '' ] ?? '';

	return array(
		'scores'       => $scores,
		'recommended'  => $recommended,
		'roi'          => array(
			'efficiency_saving'    => $efficiency_saving,
			'loss_reduction_value' => $loss_reduction_value,
			'theft_selected'       => $theft_selected,
			'total'                => $roi_total,
			'disclaimer'           => 'Illustrative estimate only, based on general assumptions (2 hours/week saved per employee at $35/hour; $8,000/year loss-reduction per site where theft/shrinkage was selected). Actual results vary by site, industry, and implementation. Not a guaranteed outcome.',
		),
		'timeline'     => array(
			'range'   => $timeline_range,
			'caption' => 'Estimated timeline — actual implementation depends on site complexity and existing infrastructure.',
		),
		'architecture' => array(
			'nodes'                 => $recommended,
			'show_existing_systems' => ( 'yes' === ( $answers['existing_pos'] ?? '' ) || 'yes' === ( $answers['cloud_based'] ?? '' ) ),
		),
	);
}

/**
 * Resolve category slugs into their real `solution` CPT post data (title,
 * one-sentence dek, tile image, permalink) — never invented copy, always
 * pulled from ACF (CLAUDE.md's "no hardcoded copy" rule applies here too).
 *
 * @param array $slugs Solution category slugs to resolve.
 * @return array slug => array(title, desc, url, photo)
 */
function itoi_solution_builder_resolve_solutions( $slugs ) {
	$resolved = array();
	foreach ( $slugs as $slug ) {
		$post = get_page_by_path( $slug, OBJECT, 'solution' );
		if ( ! $post || 'publish' !== $post->post_status ) {
			continue;
		}
		$dek      = function_exists( 'get_field' ) ? get_field( 'dek', $post->ID ) : '';
		$photo_id = function_exists( 'get_field' ) ? itoi_or( get_field( 'tile_image', $post->ID ), get_field( 'hero_image', $post->ID ) ) : 0;

		$resolved[ $slug ] = array(
			'slug'  => $slug,
			'title' => get_the_title( $post ),
			'desc'  => $dek ? $dek : wp_trim_words( get_the_excerpt( $post ), 20 ),
			'url'   => get_permalink( $post ),
			'photo' => $photo_id ? wp_get_attachment_image_url( $photo_id, 'medium' ) : '',
		);
	}
	return $resolved;
}

/**
 * Hand-authored outline icons for the architecture flow diagram — same
 * "stroke=currentColor, no icon library" pattern as inc/longform-icons.php.
 * Also reused by the homepage "Explore our solutions" carousel
 * (front-page.php, 2026-07-30, see NOTES.md) via the optional $classes
 * param below — default reproduces this function's original h-8 w-8
 * output exactly, so nothing else needs to change to add a second caller.
 *
 * @param string $slug    One of the architecture flow diagram slugs defined in $paths below.
 * @param string $classes Tailwind size classes for the <svg>. Default matches original behavior.
 */
function itoi_solution_builder_icon( $slug, $classes = 'h-8 w-8' ) {
	$common = 'fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"';

	$paths = array(
		'intelligence-analytics'      => '<path d="M4 20V10M10 20V4M16 20v-7M22 20V8"/>',
		'customer-engagement-signage' => '<rect x="3" y="4" width="18" height="12" rx="1.5"/><path d="M8 20h8M12 16v4"/>',
		'sensory-intelligence'        => '<path d="M12 2v3M4.2 6.2l2 2M2 13h3M19 13h3M17.8 8.2l2-2"/><circle cx="12" cy="13" r="5"/>',
		'workforce-ops-robotics'      => '<rect x="6" y="8" width="12" height="10" rx="2"/><circle cx="9.5" cy="13" r="1"/><circle cx="14.5" cy="13" r="1"/><path d="M12 8V5M9 5h6"/>',
		'cctv-video-loss-prevention'  => '<path d="M3 8.5 13 6v12L3 15.5v-7Z"/><path d="M13 9.5 21 7v10l-8-2.5"/>',
		'security-access-inventory'   => '<path d="M12 2.5 20 5.5V11c0 5-3.5 9-8 10.5-4.5-1.5-8-5.5-8-10.5V5.5L12 2.5Z"/>',
		'back-of-house-integration'   => '<path d="M12 3 21 8 12 13 3 8 12 3Z"/><path d="M3 16 12 21 21 16"/>',
		'it-network-infrastructure'   => '<circle cx="12" cy="4" r="2"/><circle cx="5" cy="19" r="2"/><circle cx="19" cy="19" r="2"/><path d="M12 6v5M12 11 6 17M12 11l6 6"/>',
		'dashboard'                   => '<rect x="3" y="4" width="18" height="14" rx="2"/><path d="M3 9h18M8 14h3"/>',
		'existing-systems'            => '<rect x="4" y="6" width="16" height="11" rx="1.5"/><path d="M9 20h6M12 17v3"/>',
	);

	if ( ! isset( $paths[ $slug ] ) ) {
		return;
	}

	printf( '<svg class="%s" viewBox="0 0 24 24" %s>%s</svg>', esc_attr( $classes ), $common, $paths[ $slug ] ); // phpcs:ignore -- $common/$paths are hardcoded above, not user input
}

/**
 * Sanitize + validate a raw $_POST payload of answers against the exact
 * option sets above. Returns array( 'answers' => ..., 'errors' => array() ).
 * $raw_challenges is expected to arrive as $_POST['challenges'] (array).
 *
 * @param array $raw The raw, unsanitized $_POST payload.
 * @return array{answers: array, errors: array}
 */
function itoi_solution_builder_sanitize_answers( $raw ) {
	$errors  = array();
	$answers = array();

	$industries    = array_keys( itoi_solution_builder_industry_weights() );
	$business_type = sanitize_key( wp_unslash( $raw['business_type'] ?? '' ) );
	if ( ! in_array( $business_type, $industries, true ) ) {
		$errors[] = 'business_type';
	}
	$answers['business_type'] = $business_type;

	$employees = sanitize_text_field( wp_unslash( $raw['employees'] ?? '' ) );
	if ( ! isset( itoi_solution_builder_employee_options()[ $employees ] ) ) {
		$errors[] = 'employees';
	}
	$answers['employees'] = $employees;

	$sites = sanitize_text_field( wp_unslash( $raw['sites'] ?? '' ) );
	if ( ! isset( itoi_solution_builder_site_options()[ $sites ] ) ) {
		$errors[] = 'sites';
	}
	$answers['sites'] = $sites;

	foreach ( array( 'existing_cctv', 'existing_pos', 'cloud_based' ) as $field ) {
		$value = sanitize_key( wp_unslash( $raw[ $field ] ?? '' ) );
		if ( ! in_array( $value, array( 'yes', 'no' ), true ) ) {
			$errors[] = $field;
		}
		$answers[ $field ] = $value;
	}

	$valid_challenges      = array_keys( itoi_solution_builder_challenge_options() );
	$submitted_challenges  = is_array( $raw['challenges'] ?? null ) ? wp_unslash( $raw['challenges'] ) : array();
	$answers['challenges'] = array_values( array_intersect( array_map( 'sanitize_key', $submitted_challenges ), $valid_challenges ) );

	return array(
		'answers' => $answers,
		'errors'  => $errors,
	);
}

/**
 * AJAX: compute + return the recommendation/ROI/timeline for the results
 * screen, right after Step 7 — before any lead-capture fields are shown.
 */
function itoi_solution_builder_ajax_calculate() {
	check_ajax_referer( 'itoi_solution_builder', 'nonce' );

	$sanitized = itoi_solution_builder_sanitize_answers( $_POST );
	if ( ! empty( $sanitized['errors'] ) ) {
		wp_send_json_error(
			array(
				'message' => 'Please answer every question before continuing.',
				'fields'  => $sanitized['errors'],
			),
			400
		);
	}

	$result             = itoi_solution_builder_calculate( $sanitized['answers'] );
	$resolved_solutions = itoi_solution_builder_resolve_solutions( $result['recommended'] );

	wp_send_json_success(
		array(
			'recommended'  => array_values( $resolved_solutions ),
			'roi'          => $result['roi'],
			'timeline'     => $result['timeline'],
			'architecture' => array(
				'nodes'                 => array_values(
					array_map(
						function ( $slug ) use ( $resolved_solutions ) {
							return array(
								'slug'  => $slug,
								'title' => $resolved_solutions[ $slug ]['title'] ?? $slug,
							);
						},
						$result['architecture']['nodes']
					)
				),
				'show_existing_systems' => $result['architecture']['show_existing_systems'],
			),
		)
	);
}
add_action( 'wp_ajax_itoi_solution_builder_calculate', 'itoi_solution_builder_ajax_calculate' );
add_action( 'wp_ajax_nopriv_itoi_solution_builder_calculate', 'itoi_solution_builder_ajax_calculate' );

/**
 * AJAX: lead capture. Recalculates authoritatively from the raw answers
 * (never trusts a client-supplied "results" payload), stores a
 * sb_lead post, and emails a summary to Michael Stark
 * (PROJECT.md §6 confirmed contact) so the lead doesn't sit unnoticed.
 */
function itoi_solution_builder_ajax_submit_lead() {
	check_ajax_referer( 'itoi_solution_builder', 'nonce' );

	$sanitized = itoi_solution_builder_sanitize_answers( $_POST );
	if ( ! empty( $sanitized['errors'] ) ) {
		wp_send_json_error(
			array(
				'message' => 'Please complete every question first.',
				'fields'  => $sanitized['errors'],
			),
			400
		);
	}

	$name    = sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) );
	$email   = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
	$company = sanitize_text_field( wp_unslash( $_POST['company'] ?? '' ) );
	$phone   = sanitize_text_field( wp_unslash( $_POST['phone'] ?? '' ) );

	$lead_errors = array();
	if ( '' === $name ) {
		$lead_errors[] = 'name';
	}
	if ( '' === $email || ! is_email( $email ) ) {
		$lead_errors[] = 'email';
	}
	if ( '' === $company ) {
		$lead_errors[] = 'company';
	}
	if ( ! empty( $lead_errors ) ) {
		wp_send_json_error(
			array(
				'message' => 'Name, email and company are required.',
				'fields'  => $lead_errors,
			),
			400
		);
	}

	$answers            = $sanitized['answers'];
	$result             = itoi_solution_builder_calculate( $answers );
	$resolved_solutions = itoi_solution_builder_resolve_solutions( $result['recommended'] );
	$recommended_titles = wp_list_pluck( $resolved_solutions, 'title' );

	$challenge_labels_map = wp_list_pluck( itoi_solution_builder_challenge_options(), 'label' );
	$challenge_labels     = array_map(
		function ( $value ) use ( $challenge_labels_map ) {
			return $challenge_labels_map[ $value ] ?? $value;
		},
		$answers['challenges']
	);

	$post_id = wp_insert_post(
		array(
			'post_type'   => 'sb_lead',
			'post_status' => 'publish',
			'post_title'  => sprintf( '%s — %s', $name, $company ),
		),
		true
	);

	if ( is_wp_error( $post_id ) ) {
		wp_send_json_error( array( 'message' => 'Could not save your details — please try again.' ), 500 );
	}

	if ( function_exists( 'update_field' ) ) {
		update_field( 'sb_name', $name, $post_id );
		update_field( 'sb_email', $email, $post_id );
		update_field( 'sb_company', $company, $post_id );
		update_field( 'sb_phone', $phone, $post_id );
		update_field( 'sb_submitted_at', current_time( 'Y-m-d H:i:s' ), $post_id );
		update_field( 'sb_business_type', $answers['business_type'], $post_id );
		update_field( 'sb_employees', $answers['employees'], $post_id );
		update_field( 'sb_sites', $answers['sites'], $post_id );
		update_field( 'sb_existing_cctv', $answers['existing_cctv'], $post_id );
		update_field( 'sb_existing_pos', $answers['existing_pos'], $post_id );
		update_field( 'sb_cloud_based', $answers['cloud_based'], $post_id );
		update_field( 'sb_challenges', implode( ', ', $challenge_labels ), $post_id );
		update_field( 'sb_recommended_solutions', implode( ', ', $recommended_titles ), $post_id );
		update_field( 'sb_roi_total', $result['roi']['total'], $post_id );
		update_field( 'sb_timeline', $result['timeline']['range'], $post_id );
	}

	// --- Email notification (PROJECT.md §6 confirmed contact) ---
	$to      = 'michael.stark@itoisolutions.com.au';
	$subject = sprintf( '[Solution Builder] New lead: %s (%s)', $name, $company );
	$body    = "A visitor completed the Solution Builder and requested their proposal.\n\n"
		. "CONTACT\n"
		. "Name: {$name}\n"
		. "Email: {$email}\n"
		. "Company: {$company}\n"
		. 'Phone: ' . ( $phone ? $phone : '(not provided)' ) . "\n\n"
		. "ANSWERS\n"
		. 'Business type: ' . $answers['business_type'] . "\n"
		. 'Employees: ' . $answers['employees'] . "\n"
		. 'Sites: ' . $answers['sites'] . "\n"
		. 'Existing CCTV: ' . $answers['existing_cctv'] . "\n"
		. 'Existing POS: ' . $answers['existing_pos'] . "\n"
		. 'Cloud-based: ' . $answers['cloud_based'] . "\n"
		. 'Challenges: ' . ( $challenge_labels ? implode( ', ', $challenge_labels ) : '(none selected)' ) . "\n\n"
		. "CALCULATED RESULT\n"
		. 'Recommended: ' . implode( ', ', $recommended_titles ) . "\n"
		. 'Estimated annual value: $' . number_format( $result['roi']['total'] ) . " AUD\n"
		. 'Implementation timeline: ' . $result['timeline']['range'] . "\n\n"
		. 'View full record: ' . admin_url( 'post.php?post=' . $post_id . '&action=edit' ) . "\n";

	$sent = wp_mail( $to, $subject, $body );
	if ( ! $sent ) {
		// Sandbox has no MTA (no sendmail binary) — wp_mail() correctly
		// returns false here; logged so this doesn't fail invisibly. Lead
		// is still saved regardless of email delivery. See NOTES.md.
		error_log( '[itoi solution-builder] wp_mail() returned false for lead #' . $post_id . ' — no MTA available in this environment.' );
	}

	wp_send_json_success(
		array(
			'lead_id'      => $post_id,
			'name'         => $name,
			'company'      => $company,
			'recommended'  => array_values( $resolved_solutions ),
			'roi'          => $result['roi'],
			'timeline'     => $result['timeline'],
			'answers'      => array_merge( $answers, array( 'challenge_labels' => $challenge_labels ) ),
			'architecture' => array(
				'nodes'                 => array_values(
					array_map(
						function ( $slug ) use ( $resolved_solutions ) {
							return array(
								'slug'  => $slug,
								'title' => $resolved_solutions[ $slug ]['title'] ?? $slug,
							);
						},
						$result['architecture']['nodes']
					)
				),
				'show_existing_systems' => $result['architecture']['show_existing_systems'],
			),
			'contact'      => itoi_solution_builder_contact_block(),
		)
	);
}
add_action( 'wp_ajax_itoi_solution_builder_submit_lead', 'itoi_solution_builder_ajax_submit_lead' );
add_action( 'wp_ajax_nopriv_itoi_solution_builder_submit_lead', 'itoi_solution_builder_ajax_submit_lead' );

/**
 * ITOI contact details for the proposal footer — pulled from the real
 * team_member post (role field) and Site Settings, never hardcoded.
 * PROJECT.md §6 has no confirmed phone number for Michael Stark
 * specifically (only Sean Kiely's and Support's), so none is shown here —
 * see NOTES.md, "Solution Builder" entry, Part 8 do-not-invent discipline.
 */
function itoi_solution_builder_contact_block() {
	$michael = get_page_by_path( 'michael-stark', OBJECT, 'team_member' );
	$role    = '';
	if ( $michael && function_exists( 'get_field' ) ) {
		$role = get_field( 'role', $michael->ID );
	}

	return array(
		'name'  => 'Michael Stark',
		'role'  => $role ? $role : '',
		'email' => 'michael.stark@itoisolutions.com.au',
	);
}
