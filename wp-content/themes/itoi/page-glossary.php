<?php
/**
 * Glossary (/education/glossary/) — full alphabetical list, client-side
 * filter-as-you-type via the shared [data-filter-root] pattern
 * (assets/js/main.js, initItoiFilterLists). glossary_term is a non-public
 * CPT (same "pure data" pattern as client/team_member) queried directly.
 *
 * Light-glass test, 2026-07-27 (see NOTES.md): aurora-bg-light on the main
 * content section. Search input's CONTAINER is glass (a padded frame
 * around it), the input itself stays its own solid white pill, unchanged,
 * per the test spec's explicit usability carve-out. Term entries had no
 * individual card shape before this (a single divide-y list) — added one
 * (rounded-2xl, own padding) and made it the glass surface, same
 * "add the missing card shape, then glass it" precedent the dark-glass
 * system used in earlier waves. See the [data-filter-item].glass-element-light
 * override in src/tailwind.css for why the item's transition value was
 * touched — it's what keeps the search filter's fade animation intact.
 *
 * @package ITOI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$itoi_terms = itoi_edu_get_glossary_terms();

while ( have_posts() ) :
	the_post();
	?>

	<section class="border-b border-line bg-hero-bg px-8 pt-[168px] pb-16 min-[640px]:pt-[206px] min-[980px]:pb-[70px]">
		<div class="mx-auto max-w-[840px]">
			<div class="mb-2 text-[13.5px] font-bold uppercase tracking-wide text-teal-800"><a href="<?php echo esc_url( home_url( '/education/' ) ); ?>" class="hover:underline">Education Hub</a> / Glossary</div>
			<h1 class="max-w-[20ch]"><?php the_title(); ?></h1>
			<p class="mt-4 max-w-[62ch] text-text-muted">Every analytics, security and operations term ITOI uses, defined in plain English.</p>
		</div>
	</section>

	<section class="aurora-bg-light px-8 py-16 min-[980px]:py-[70px]" data-filter-root>
		<div class="mx-auto max-w-[840px]">
			<label for="glossarySearch" class="sr-only">Search the glossary</label>
			<div class="glass-element-light relative mb-10 rounded-full p-2">
				<div class="relative">
					<svg class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-text-muted" width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true"><circle cx="7" cy="7" r="5.5" stroke="currentColor" stroke-width="1.5"/><path d="M11.5 11.5L15 15" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
					<input type="text" id="glossarySearch" data-filter-input class="w-full rounded-full border-[1.5px] border-line bg-white py-3.5 pl-11 pr-5 text-[15px] focus:border-ink focus:outline-none" placeholder="Search a term&hellip;">
				</div>
			</div>

			<?php if ( ! empty( $itoi_terms ) ) : ?>
				<div class="flex flex-col gap-4">
					<?php
					foreach ( $itoi_terms as $itoi_term_post ) :
						$itoi_term_name  = get_field( 'term', $itoi_term_post->ID ) ?: $itoi_term_post->post_title;
						$itoi_definition = get_field( 'definition', $itoi_term_post->ID );
						$itoi_related    = get_field( 'related_guides', $itoi_term_post->ID );
						$itoi_slug       = sanitize_title( $itoi_term_name );
						?>
						<div
							id="term-<?php echo esc_attr( $itoi_slug ); ?>"
							data-filter-item
							data-filter-text="<?php echo esc_attr( strtolower( $itoi_term_name ) ); ?>"
							class="glass-element-light overflow-hidden rounded-2xl p-5 transition-all duration-200 motion-reduce:transition-none scroll-mt-24"
						>
							<h2 class="text-[17px] font-extrabold"><?php echo esc_html( $itoi_term_name ); ?></h2>
							<?php if ( $itoi_definition ) : ?>
								<p class="mt-1.5 text-[14.5px] text-text-muted"><?php echo esc_html( $itoi_definition ); ?></p>
							<?php endif; ?>
							<?php if ( ! empty( $itoi_related ) ) : ?>
								<div class="mt-2.5 flex flex-wrap items-center gap-2 text-[13px]">
									<span class="font-bold text-ink">See also:</span>
									<?php
									foreach ( $itoi_related as $itoi_guide_id ) :
										if ( 'publish' !== get_post_status( $itoi_guide_id ) ) {
											continue;
										}
										?>
										<a href="<?php echo esc_url( get_permalink( $itoi_guide_id ) ); ?>" class="font-semibold text-ink underline underline-offset-4"><?php echo esc_html( get_field( 'title', $itoi_guide_id ) ?: get_the_title( $itoi_guide_id ) ); ?> &rarr;</a>
									<?php endforeach; ?>
								</div>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				</div>
				<p class="hidden py-8 text-center text-text-muted" data-filter-empty>No terms match your search.</p>
			<?php else : ?>
				<p class="text-text-muted">Glossary terms are being added — check back soon.</p>
			<?php endif; ?>
		</div>
	</section>

	<div class="mx-4 mb-[60px] flex flex-wrap items-center justify-between gap-9 rounded-[20px] bg-ink px-6 py-9 text-white min-[980px]:mx-8 min-[980px]:mb-[90px] min-[980px]:px-[60px] min-[980px]:py-[60px]">
		<div>
			<h2 class="mb-2 max-w-[16ch] text-[clamp(22px,2.6vw,32px)] text-white">Want the full picture?</h2>
			<p class="m-0 max-w-[34ch] text-white/60">Read the guides these terms come from.</p>
		</div>
		<a href="<?php echo esc_url( home_url( '/education/guides/' ) ); ?>" class="rounded-full bg-white px-[22px] py-[11px] text-sm font-bold text-ink">Browse guides</a>
	</div>

	<?php
endwhile;

get_footer();
