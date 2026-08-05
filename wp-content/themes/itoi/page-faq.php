<?php
/**
 * FAQ (/education/faq/) — every FAQ entry across every solution, aggregated
 * and grouped by originating solution, client-side filter-as-you-type via
 * the shared [data-filter-root] pattern (matches question text only, not
 * the answer body — see initItoiFilterLists in assets/js/main.js). A
 * solution with zero FAQs is simply absent (itoi_edu_get_all_faqs already
 * filters those out) — never an empty/broken group.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$itoi_faq_groups = itoi_edu_get_all_faqs();

while ( have_posts() ) :
	the_post();
	?>

	<section class="border-b border-line bg-hero-bg px-8 pt-[168px] pb-16 min-[640px]:pt-[206px] min-[980px]:pb-[70px]">
		<div class="mx-auto max-w-[840px]">
			<div class="mb-2 text-[13.5px] font-bold uppercase tracking-wide text-teal-800"><a href="<?php echo esc_url( home_url( '/education/' ) ); ?>" class="hover:underline">Education Hub</a> / FAQ</div>
			<h1 class="max-w-[20ch]"><?php the_title(); ?></h1>
			<p class="mt-4 max-w-[62ch] text-text-muted">Common questions about every ITOI solution, in one searchable place.</p>
		</div>
	</section>

	<section class="px-8 py-16 min-[980px]:py-[70px]" data-filter-root>
		<div class="mx-auto max-w-[840px]">
			<label for="faqSearch" class="sr-only">Search FAQs</label>
			<div class="relative mb-10">
				<svg class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-text-muted" width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true"><circle cx="7" cy="7" r="5.5" stroke="currentColor" stroke-width="1.5"/><path d="M11.5 11.5L15 15" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
				<input type="text" id="faqSearch" data-filter-input class="w-full rounded-full border-[1.5px] border-line bg-white py-3.5 pl-11 pr-5 text-[15px] focus:border-ink focus:outline-none" placeholder="Search a question&hellip;">
			</div>

			<?php if ( ! empty( $itoi_faq_groups ) ) : ?>
				<div class="flex flex-col gap-10">
					<?php foreach ( $itoi_faq_groups as $itoi_group ) : ?>
						<div data-filter-group>
							<div class="mb-3 flex items-center gap-2">
								<span class="rounded-full bg-teal-800/10 px-3 py-1 text-[11.5px] font-bold uppercase tracking-wide text-teal-800"><?php echo esc_html( $itoi_group['solution_title'] ); ?></span>
								<a href="<?php echo esc_url( $itoi_group['solution_url'] ); ?>" class="text-[12.5px] font-semibold text-ink underline underline-offset-4">View solution &rarr;</a>
							</div>
							<div class="divide-y divide-line border-y border-line">
								<?php foreach ( $itoi_group['faqs'] as $itoi_faq_index => $itoi_faq_row ) : ?>
									<details
										id="faq-<?php echo (int) $itoi_group['solution_id']; ?>-<?php echo (int) $itoi_faq_index; ?>"
										data-filter-item
										data-filter-text="<?php echo esc_attr( strtolower( $itoi_faq_row['q'] ) ); ?>"
										class="group overflow-hidden py-4 transition-all duration-200 motion-reduce:transition-none scroll-mt-24"
									>
										<summary class="cursor-pointer list-none text-[15px] font-bold marker:content-none"><?php echo esc_html( $itoi_faq_row['q'] ); ?></summary>
										<p class="mt-2.5 text-[14.5px] text-text-muted"><?php echo esc_html( $itoi_faq_row['a'] ); ?></p>
									</details>
								<?php endforeach; ?>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
				<p class="hidden py-8 text-center text-text-muted" data-filter-empty>No questions match your search.</p>
			<?php else : ?>
				<p class="text-text-muted">FAQs are being added — check back soon.</p>
			<?php endif; ?>
		</div>
	</section>

	<div class="mx-4 mb-[60px] flex flex-wrap items-center justify-between gap-9 rounded-[20px] bg-ink px-6 py-9 text-white min-[980px]:mx-8 min-[980px]:mb-[90px] min-[980px]:px-[60px] min-[980px]:py-[60px]">
		<div>
			<h2 class="mb-2 max-w-[16ch] text-[clamp(22px,2.6vw,32px)] text-white">Can't find your answer?</h2>
			<p class="m-0 max-w-[34ch] text-white/60">Ask the team directly — we respond fast.</p>
		</div>
		<a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" class="rounded-full bg-white px-[22px] py-[11px] text-sm font-bold text-ink">Contact us</a>
	</div>

<?php
endwhile;

get_footer();
