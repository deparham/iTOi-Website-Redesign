<?php
/**
 * Education Hub landing page (slug `education`). Intro + links into the
 * three sub-sections (Guides/Glossary/FAQ) + a top-level quick-search that
 * live-filters a combined preview of guide titles, glossary terms and FAQ
 * questions — reuses the same [data-filter-root] pattern built for the
 * Glossary/FAQ pages (assets/js/main.js, initItoiFilterLists), just applied
 * to a merged, capped list rather than the full content of any one page.
 *
 * @package ITOI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$itoi_guides_query = new WP_Query(
	array(
		'post_type'      => 'guide',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'orderby'        => 'title',
		'order'          => 'ASC',
	)
);
$itoi_guide_count  = $itoi_guides_query->found_posts;

$itoi_glossary_terms = itoi_edu_get_glossary_terms();
$itoi_glossary_count = count( $itoi_glossary_terms );

$itoi_faq_groups = itoi_edu_get_all_faqs();
$itoi_faq_count  = 0;
foreach ( $itoi_faq_groups as $itoi_group ) {
	$itoi_faq_count += count( $itoi_group['faqs'] );
}

while ( have_posts() ) :
	the_post();
	?>

	<section class="border-b border-line bg-hero-bg px-8 pt-[168px] pb-section-md min-[640px]:pt-[206px]">
		<div class="mx-auto max-w-[840px]">
			<div class="mb-2 text-[13.5px] font-bold uppercase tracking-wide text-teal-800">Education Hub</div>
			<h1 class="max-w-[20ch]"><?php the_title(); ?></h1>
			<p class="mt-4 max-w-[62ch] text-text-muted">Plain-language guides, a searchable glossary, and answers to the questions we hear most — everything you need to understand computer-vision analytics before you talk to us.</p>

			<div class="mt-8 max-w-[520px]" data-filter-root>
				<label for="eduQuickSearch" class="sr-only">Search guides, glossary and FAQ</label>
				<div class="relative">
					<svg class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-text-muted" width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true"><circle cx="7" cy="7" r="5.5" stroke="currentColor" stroke-width="1.5"/><path d="M11.5 11.5L15 15" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
					<input type="text" id="eduQuickSearch" data-filter-input class="w-full rounded-full border-[1.5px] border-line bg-white py-3 pl-11 pr-5 text-[15px] focus:border-ink focus:outline-none" placeholder="Search guides, glossary terms and FAQs&hellip;">
				</div>

				<div class="mt-3 max-h-[360px] overflow-y-auto rounded-2xl border border-line bg-white p-2 text-left">
					<?php
					$itoi_quick_items = array();
					while ( $itoi_guides_query->have_posts() ) :
						$itoi_guides_query->the_post();
						$itoi_quick_items[] = array(
							'label' => ( get_field( 'title' ) ?: get_the_title() ) . ' — Guide',
							'url'   => get_permalink(),
						);
					endwhile;
					wp_reset_postdata();

					foreach ( $itoi_glossary_terms as $itoi_term_post ) :
						$itoi_term_name     = get_field( 'term', $itoi_term_post->ID ) ?: $itoi_term_post->post_title;
						$itoi_quick_items[] = array(
							'label' => $itoi_term_name . ' — Glossary',
							'url'   => home_url( '/education/glossary/#term-' . sanitize_title( $itoi_term_name ) ),
						);
					endforeach;

					foreach ( $itoi_faq_groups as $itoi_group ) :
						foreach ( $itoi_group['faqs'] as $itoi_faq_index => $itoi_faq_row ) :
							$itoi_quick_items[] = array(
								'label' => $itoi_faq_row['q'] . ' — FAQ',
								'url'   => home_url( '/education/faq/#faq-' . $itoi_group['solution_id'] . '-' . $itoi_faq_index ),
							);
						endforeach;
					endforeach;

					if ( empty( $itoi_quick_items ) ) :
						?>
						<p class="px-3 py-2 text-[13.5px] text-text-muted">Content is being added — check back soon.</p>
						<?php
					else :
						foreach ( $itoi_quick_items as $itoi_item ) :
							?>
							<a
								href="<?php echo esc_url( $itoi_item['url'] ); ?>"
								data-filter-item
								data-filter-text="<?php echo esc_attr( strtolower( $itoi_item['label'] ) ); ?>"
								class="block overflow-hidden rounded-lg px-3 py-2.5 text-[13.5px] font-semibold text-ink transition-all duration-200 motion-reduce:transition-none hover:bg-hero-bg"
							><?php echo esc_html( $itoi_item['label'] ); ?></a>
							<?php
						endforeach;
					endif;
					?>
					<p class="hidden px-3 py-2 text-[13.5px] text-text-muted" data-filter-empty>No matches — try a different term.</p>
				</div>
			</div>
		</div>
	</section>

	<section class="px-8 py-section-md">
		<div class="mx-auto grid max-w-[1280px] grid-cols-1 gap-6 min-[640px]:grid-cols-3">
			<a href="<?php echo esc_url( home_url( '/education/guides/' ) ); ?>" class="group flex flex-col justify-between rounded-2xl border border-line bg-white p-7 transition-all hover:-translate-y-[3px] hover:border-ink">
				<div>
					<div class="mb-4 flex h-11 w-11 items-center justify-center rounded-full bg-teal-800/10 text-teal-800" aria-hidden="true">
						<svg width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M3 4a1 1 0 0 1 1-1h4.5a2 2 0 0 1 2 2v11.5a1.5 1.5 0 0 0-1.5-1.5H3V4Z" stroke="currentColor" stroke-width="1.4"/><path d="M17 4a1 1 0 0 0-1-1h-4.5a2 2 0 0 0-2 2v11.5a1.5 1.5 0 0 1 1.5-1.5H17V4Z" stroke="currentColor" stroke-width="1.4"/></svg>
					</div>
					<h2 class="text-xl">Guides</h2>
					<p class="mt-2 text-[14.5px] text-text-muted">Plain-language explainers on analytics, security and more — organised by industry.</p>
				</div>
				<span class="mt-6 inline-flex items-center gap-2 text-[13.5px] font-bold text-ink">Browse <?php echo (int) $itoi_guide_count; ?> guide<?php echo 1 === $itoi_guide_count ? '' : 's'; ?> &rarr;</span>
			</a>
			<a href="<?php echo esc_url( home_url( '/education/glossary/' ) ); ?>" class="group flex flex-col justify-between rounded-2xl border border-line bg-white p-7 transition-all hover:-translate-y-[3px] hover:border-ink">
				<div>
					<div class="mb-4 flex h-11 w-11 items-center justify-center rounded-full bg-teal-800/10 text-teal-800" aria-hidden="true">
						<svg width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M4 3h9a3 3 0 0 1 3 3v11H7a3 3 0 0 1-3-3V3Z" stroke="currentColor" stroke-width="1.4"/><path d="M4 14a3 3 0 0 1 3-3h9" stroke="currentColor" stroke-width="1.4"/></svg>
					</div>
					<h2 class="text-xl">Glossary</h2>
					<p class="mt-2 text-[14.5px] text-text-muted">Every analytics, security and operations term ITOI uses, defined in plain English.</p>
				</div>
				<span class="mt-6 inline-flex items-center gap-2 text-[13.5px] font-bold text-ink">Browse <?php echo (int) $itoi_glossary_count; ?> term<?php echo 1 === $itoi_glossary_count ? '' : 's'; ?> &rarr;</span>
			</a>
			<a href="<?php echo esc_url( home_url( '/education/faq/' ) ); ?>" class="group flex flex-col justify-between rounded-2xl border border-line bg-white p-7 transition-all hover:-translate-y-[3px] hover:border-ink">
				<div>
					<div class="mb-4 flex h-11 w-11 items-center justify-center rounded-full bg-teal-800/10 text-teal-800" aria-hidden="true">
						<svg width="20" height="20" viewBox="0 0 20 20" fill="none"><circle cx="10" cy="10" r="7.5" stroke="currentColor" stroke-width="1.4"/><path d="M7.8 7.8a2.2 2.2 0 1 1 3.1 2c-.8.5-1.4 1-1.4 2" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/><circle cx="9.9" cy="14.2" r="0.9" fill="currentColor"/></svg>
					</div>
					<h2 class="text-xl">FAQ</h2>
					<p class="mt-2 text-[14.5px] text-text-muted">Common questions about every ITOI solution, in one searchable place.</p>
				</div>
				<span class="mt-6 inline-flex items-center gap-2 text-[13.5px] font-bold text-ink">Browse <?php echo (int) $itoi_faq_count; ?> question<?php echo 1 === $itoi_faq_count ? '' : 's'; ?> &rarr;</span>
			</a>
		</div>
	</section>

	<div class="mx-4 mb-[60px] flex flex-wrap items-center justify-between gap-9 rounded-[20px] bg-ink px-6 py-9 text-white min-[980px]:mx-8 min-[980px]:mb-[90px] min-[980px]:px-[60px] min-[980px]:py-[60px]">
		<div>
			<h2 class="mb-2 max-w-[16ch] text-[clamp(22px,2.6vw,32px)] text-white">Still have a question?</h2>
			<p class="m-0 max-w-[34ch] text-white/60">Talk to the team directly — we'll walk you through how ITOI fits your business.</p>
		</div>
		<a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" class="rounded-full bg-white px-[22px] py-[11px] text-sm font-bold text-ink">Get demo</a>
	</div>

	<?php
endwhile;

get_footer();
