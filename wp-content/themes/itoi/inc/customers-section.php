<?php
/**
 * Shared "Customers" section renderer — spotlight case-study card + logo
 * marquee/badge rows. Extracted 2026-07-30 (see NOTES.md) from
 * single-industry.php's long-form Customers section (built and shipping on
 * Retail only) so the homepage's own Customers section can reuse the exact
 * same component instead of a second hand-copied implementation drifting
 * out of sync. Both callers pass raw ACF field values; this file owns all
 * the resolution logic (logo-strip taxonomy/relationship lookups, the
 * spotlight client's linked case-study lookup) and the render.
 *
 * Split into resolve (itoi_get_customers_section_data) + render
 * (itoi_render_customers_section_data) rather than one do-everything
 * function: single-industry.php needs to know whether the section will
 * show *before* it renders (to decide whether to include a sub-nav link
 * pointing at it) — resolving once and reusing the result avoids running
 * the taxonomy/relationship queries twice. front-page.php has no sub-nav,
 * so it uses the itoi_render_customers_section() convenience wrapper that
 * does both in one call.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @param array $args {
 *     @type string $heading             Section heading.
 *     @type int    $spotlight_client_id `client` CPT post ID, or 0/empty for none.
 *     @type int    $spotlight_photo_id  Attachment ID.
 *     @type array|null $spotlight_video Raw ACF file-field value.
 *     @type bool   $spotlight_is_stock  Whether spotlight_photo_id is a stock stand-in.
 *     @type array  $logo_strip_groups   Raw `logo_strip_groups` repeater rows
 *                                       (each: category, clients, label, direction).
 *     @type string $empty_message       Shown when there's no spotlight and no logo rows.
 * }
 * @return array Resolved data, including `show` (bool) — pass straight to
 *               itoi_render_customers_section_data().
 */
function itoi_get_customers_section_data( $args ) {
	$itoi_spotlight_client_id = $args['spotlight_client_id'] ?? 0;
	$itoi_logo_groups         = $args['logo_strip_groups'] ?? array();
	$itoi_empty_message       = $args['empty_message'] ?? '';

	// Resolve each logo-strip row's actual client names. A row can be
	// driven by a live taxonomy pull ("category") or a direct hand-picked
	// list ("clients"). Rows with zero resulting names (e.g. an
	// unpublished taxonomy term) are dropped rather than rendered empty.
	$itoi_resolved_logo_rows = array();
	foreach ( $itoi_logo_groups as $itoi_lg_row ) {
		$itoi_lg_names = array();

		if ( ! empty( $itoi_lg_row['category'] ) ) {
			$itoi_lg_query = new WP_Query(
				array(
					'post_type'      => 'client',
					'posts_per_page' => -1,
					'orderby'        => 'title',
					'order'          => 'ASC',
					'tax_query'      => array(
						array(
							'taxonomy' => 'client_category',
							'field'    => 'term_id',
							'terms'    => $itoi_lg_row['category'],
						),
					),
				)
			);
			while ( $itoi_lg_query->have_posts() ) {
				$itoi_lg_query->the_post();
				$itoi_lg_names[] = get_the_title();
			}
			wp_reset_postdata();
		} elseif ( ! empty( $itoi_lg_row['clients'] ) ) {
			foreach ( $itoi_lg_row['clients'] as $itoi_lg_client_id ) {
				if ( 'publish' === get_post_status( $itoi_lg_client_id ) ) {
					$itoi_lg_names[] = get_the_title( $itoi_lg_client_id );
				}
			}
		}

		if ( empty( $itoi_lg_names ) ) {
			continue;
		}

		$itoi_resolved_logo_rows[] = array(
			'label'     => $itoi_lg_row['label'],
			'direction' => $itoi_lg_row['direction'],
			'names'     => $itoi_lg_names,
		);
	}

	$itoi_spotlight_case_id = null;
	$itoi_spotlight_case_ok = false;
	$itoi_case_pending      = true;
	$itoi_case_narrative    = '';
	$itoi_case_headline     = '';
	$itoi_case_permalink    = '';

	if ( $itoi_spotlight_client_id ) {
		$itoi_spotlight_case_id = get_field( 'case_study', $itoi_spotlight_client_id );
		$itoi_spotlight_case_ok = $itoi_spotlight_case_id && 'publish' === get_post_status( $itoi_spotlight_case_id );
		if ( $itoi_spotlight_case_ok ) {
			$itoi_case_narrative = get_field( 'narrative', $itoi_spotlight_case_id );
			// Honest empty-state check: several of this site's case studies
			// (Drakes included) currently hold a TODO(fact-check) stub instead
			// of a real narrative — do not excerpt a fabricated summary from
			// that stub; render the honest "pending" framing instead. The case
			// study's own `headline` field is an internal status marker in
			// that state ("... — narrative pending"), not public-facing copy,
			// so the spotlight headline falls back to the real client name.
			$itoi_case_pending   = ! $itoi_case_narrative || false !== strpos( $itoi_case_narrative, 'TODO(fact-check)' );
			$itoi_case_headline  = $itoi_case_pending ? get_the_title( $itoi_spotlight_client_id ) : ( get_field( 'headline', $itoi_spotlight_case_id ) ?: get_the_title( $itoi_spotlight_case_id ) );
			$itoi_case_permalink = get_permalink( $itoi_spotlight_case_id );
		}
	}

	return array(
		'heading'              => $args['heading'] ?? '',
		'spotlight_client_id'  => $itoi_spotlight_client_id,
		'spotlight_photo_id'   => $args['spotlight_photo_id'] ?? 0,
		'spotlight_video'      => $args['spotlight_video'] ?? null,
		'spotlight_is_stock'   => ! empty( $args['spotlight_is_stock'] ),
		'resolved_logo_rows'   => $itoi_resolved_logo_rows,
		'empty_message'        => $itoi_empty_message,
		'show'                 => (bool) ( $itoi_spotlight_client_id || ! empty( $itoi_resolved_logo_rows ) || $itoi_empty_message ),
		'case_ok'              => $itoi_spotlight_case_ok,
		'case_pending'         => $itoi_case_pending,
		'case_headline'        => $itoi_case_headline,
		'case_permalink'       => $itoi_case_permalink,
		'case_narrative'       => $itoi_case_narrative,
	);
}

/**
 * @param array $data Result of itoi_get_customers_section_data(). Caller is
 *                     expected to have already checked $data['show'].
 */
function itoi_render_customers_section_data( $data ) {
	?>
	<section id="portfolio" class="aurora-bg-light scroll-mt-[128px] bg-hero-bg px-8 py-section-md">
		<div class="mx-auto max-w-[1280px]">
			<h2 class="mb-8 text-2xl"><?php echo esc_html( $data['heading'] ); ?></h2>

			<?php if ( $data['spotlight_client_id'] ) : ?>
				<?php
				$itoi_spotlight_photo_url  = $data['spotlight_photo_id'] ? wp_get_attachment_image_url( $data['spotlight_photo_id'], 'large' ) : '';
				$itoi_spotlight_client_name = get_the_title( $data['spotlight_client_id'] );
				$itoi_spotlight_video_url  = ! empty( $data['spotlight_video']['url'] ) ? $data['spotlight_video']['url'] : '';
				$itoi_spotlight_media      = itoi_media_cover( $itoi_spotlight_photo_url, $data['spotlight_video'], $itoi_spotlight_client_name . ' store', 'absolute inset-0 h-full w-full object-cover' );
				?>
				<div class="glass-element-light mb-14 grid grid-cols-1 overflow-hidden rounded-2xl min-[900px]:grid-cols-2">
					<div class="relative aspect-[4/3] w-full overflow-hidden bg-[linear-gradient(135deg,#e2e7ee,#cfd7e0)] min-[900px]:aspect-auto">
						<?php if ( $itoi_spotlight_media ) : ?>
							<?php echo $itoi_spotlight_media; ?>
							<?php // Stock-photo disclaimer only applies to the placeholder photo, never to an uploaded video. ?>
							<?php if ( $data['spotlight_is_stock'] && ! $itoi_spotlight_video_url ) : ?>
								<div class="absolute inset-x-0 bottom-0 bg-black/70 px-4 py-2.5 text-center text-[12.5px] font-semibold leading-snug text-white">
									Representative image &mdash; not an actual photo of <?php echo esc_html( $itoi_spotlight_client_name ); ?>&rsquo;s site
								</div>
							<?php endif; ?>
						<?php else : ?>
							<div class="absolute inset-0 flex items-center justify-center p-4 text-center text-[11px] uppercase tracking-[0.06em] text-[#8f99a6]">Photo &mdash; <?php echo esc_html( $itoi_spotlight_client_name ); ?> store (TODO)</div>
						<?php endif; ?>
					</div>
					<div class="relative flex flex-col justify-center p-8 min-[900px]:p-12">
						<div class="mb-2 text-[13.5px] font-bold uppercase tracking-wide text-teal-800">Case Study</div>
						<?php if ( $data['case_ok'] ) : ?>
							<h3 class="text-[22px] min-[980px]:text-[26px]"><?php echo esc_html( $data['case_headline'] ); ?></h3>
							<?php if ( $data['case_pending'] ) : ?>
								<p class="mt-3 text-[14.5px] text-text-muted">ITOI has worked with <?php echo esc_html( $itoi_spotlight_client_name ); ?> on an active deployment. The full project write-up &mdash; including scope and outcomes &mdash; is still being prepared and isn&rsquo;t published yet.</p>
							<?php else : ?>
								<p class="mt-3 text-[14.5px] text-text-muted"><?php echo esc_html( wp_trim_words( wp_strip_all_tags( $data['case_narrative'] ), 40 ) ); ?></p>
							<?php endif; ?>
							<a href="<?php echo esc_url( $data['case_permalink'] ); ?>" class="mt-6 inline-block w-fit rounded-full bg-cta px-[22px] py-[11px] text-sm font-bold text-white transition-colors hover:bg-cta-hover">Read case study</a>
						<?php endif; ?>
					</div>
				</div>
			<?php endif; ?>

			<?php if ( ! empty( $data['resolved_logo_rows'] ) ) : ?>
				<div class="flex flex-col gap-10">
					<?php foreach ( $data['resolved_logo_rows'] as $itoi_lg_row ) :
						$itoi_lg_names      = $itoi_lg_row['names'];
						$itoi_lg_is_scroll  = count( $itoi_lg_names ) > 5;
						$itoi_lg_anim_class = 'right' === $itoi_lg_row['direction'] ? 'animate-itoi-marquee-reverse' : 'animate-itoi-marquee';
						?>
						<div>
							<?php if ( ! empty( $itoi_lg_row['label'] ) ) : ?>
								<div class="mb-4 text-[13.5px] font-bold uppercase tracking-wide text-teal-800"><?php echo esc_html( $itoi_lg_row['label'] ); ?></div>
							<?php endif; ?>
							<?php if ( $itoi_lg_is_scroll ) : ?>
								<?php
								// Rendered once, echoed twice below (primary + an
								// aria-hidden duplicate) for the seamless-loop
								// technique — see src/tailwind.css.
								ob_start();
								foreach ( $itoi_lg_names as $itoi_lg_name ) :
									?>
									<span class="glass-element-light inline-block whitespace-nowrap rounded-full px-5 py-2.5 text-[13.5px] font-bold"><?php echo esc_html( $itoi_lg_name ); ?></span>
								<?php endforeach;
								$itoi_lg_pills_html = ob_get_clean();
								?>
								<div class="longform-marquee-viewport overflow-hidden">
									<div class="longform-marquee-track flex w-max <?php echo esc_attr( $itoi_lg_anim_class ); ?>">
										<div class="longform-marquee-group flex flex-none gap-3 pr-3" data-copy="primary"><?php echo $itoi_lg_pills_html; ?></div>
										<div class="longform-marquee-group flex flex-none gap-3 pr-3" data-copy="duplicate" aria-hidden="true"><?php echo $itoi_lg_pills_html; ?></div>
									</div>
								</div>
							<?php else : ?>
								<!-- Too few names to scroll sensibly — plain static
								     row instead of a marquee. -->
								<div class="flex flex-wrap gap-3">
									<?php foreach ( $itoi_lg_names as $itoi_lg_name ) : ?>
										<span class="glass-element-light inline-block whitespace-nowrap rounded-full px-5 py-2.5 text-[13.5px] font-bold"><?php echo esc_html( $itoi_lg_name ); ?></span>
									<?php endforeach; ?>
								</div>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				</div>
			<?php elseif ( ! $data['spotlight_client_id'] && $data['empty_message'] ) : ?>
				<div class="glass-element-light rounded-2xl px-8 py-10 text-center">
					<p class="mx-auto max-w-[52ch] text-[15px] text-text-muted"><?php echo esc_html( $data['empty_message'] ); ?></p>
					<a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" class="mt-5 inline-block w-fit rounded-full bg-cta px-[22px] py-[11px] text-sm font-bold text-white transition-colors hover:bg-cta-hover">Get in touch</a>
				</div>
			<?php endif; ?>
		</div>
	</section>
	<?php
}

/**
 * Convenience wrapper for callers with no need to know `show` before
 * rendering (e.g. front-page.php, which has no sub-nav pointing at this
 * section) — resolves and renders in one call, doing nothing if empty.
 *
 * @param array $args See itoi_get_customers_section_data().
 */
function itoi_render_customers_section( $args ) {
	$itoi_data = itoi_get_customers_section_data( $args );
	if ( ! $itoi_data['show'] ) {
		return;
	}
	itoi_render_customers_section_data( $itoi_data );
}

/**
 * Real client-logo marquee row — every published `client` CPT post, one
 * flat continuously-scrolling row of plain light pills (or a static
 * wrapped row when there are too few to scroll sensibly). No section
 * wrapper, no heading — just the row itself, so a caller can drop it
 * directly inside its own section alongside other content.
 *
 * Originally shipped 2026-07-30 as itoi_render_trusted_by_band() (its own
 * full `<section>` + "Trusted by:" heading, added as a standalone homepage
 * section). Consolidated 2026-08-05 into the Trust & Credibility section
 * (front-page.php, id="trustCredibility", directly below the mega-hero) —
 * that section's own heading already carries the "trusted by" framing, so
 * a second "Trusted by:" heading directly above this row would have been
 * redundant. This also replaced that section's temporary ACF-driven
 * placeholder-logo repeater (added 2026-08-04) — real client logos now render
 * there instead, same mechanic, no separate "placeholder vs real" split
 * to maintain going forward.
 */
function itoi_render_client_logo_row() {
	$itoi_clients_query = new WP_Query(
		array(
			'post_type'      => 'client',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'title',
			'order'          => 'ASC',
		)
	);

	if ( ! $itoi_clients_query->have_posts() ) {
		return;
	}

	$itoi_names = array();
	while ( $itoi_clients_query->have_posts() ) {
		$itoi_clients_query->the_post();
		$itoi_names[] = get_the_title();
	}
	wp_reset_postdata();

	$itoi_is_scroll = count( $itoi_names ) > 5;
	?>
	<?php if ( $itoi_is_scroll ) : ?>
		<?php
		// Rendered once, echoed twice below (primary + an aria-hidden
		// duplicate) for the seamless-loop technique — same mechanic as the
		// logo rows in itoi_render_customers_section_data(), just with
		// plain light pills instead of dark-glass ones.
		ob_start();
		foreach ( $itoi_names as $itoi_name ) :
			?>
			<span class="inline-block whitespace-nowrap rounded-full border border-line bg-white px-5 py-2.5 text-[13.5px] font-bold text-ink shadow-sm"><?php echo esc_html( $itoi_name ); ?></span>
		<?php endforeach;
		$itoi_pills_html = ob_get_clean();
		?>
		<div class="longform-marquee-viewport overflow-hidden">
			<div class="longform-marquee-track flex w-max animate-itoi-marquee">
				<div class="longform-marquee-group flex flex-none gap-3 pr-3" data-copy="primary"><?php echo $itoi_pills_html; ?></div>
				<div class="longform-marquee-group flex flex-none gap-3 pr-3" data-copy="duplicate" aria-hidden="true"><?php echo $itoi_pills_html; ?></div>
			</div>
		</div>
	<?php else : ?>
		<div class="flex flex-wrap gap-3">
			<?php foreach ( $itoi_names as $itoi_name ) : ?>
				<span class="inline-block whitespace-nowrap rounded-full border border-line bg-white px-5 py-2.5 text-[13.5px] font-bold text-ink shadow-sm"><?php echo esc_html( $itoi_name ); ?></span>
			<?php endforeach; ?>
		</div>
	<?php endif;
}
