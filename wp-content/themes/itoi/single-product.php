<?php
/**
 * Single product page (/products/{slug}/) — replaces the earlier one-off
 * "separate WP Page + page-{slug}.php" pairing Aurora briefly used (see
 * NOTES.md, 2026-07-30 "Meet Aurora" + 2026-07-31 "Products turnstile"
 * entries). `product` is now a real public CPT (inc/post-types.php); every
 * product's page is built here from its own `page_sections` Flexible
 * Content field (acf-json/ "Product Page Content") — editors add/remove/
 * reorder section blocks per product in wp-admin, no template edit needed
 * to add a new product or change one's page layout.
 *
 * Color usage: ordinary structural elements (eyebrows outside the dark
 * hero, CTAs, active states) use --ink/--teal-800/--teal-700, matching
 * every other section on the site. --signature/--signature-bright is used
 * ONLY inside the dark hero (eyebrow dot + device-stage placeholder
 * label), extending the site's existing "Live Detection" dark-hero
 * identity — not a new sanctioned spot for the signature color elsewhere
 * (CLAUDE.md's hard rule). Font is Inter (self-hosted), not Manrope.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

/**
 * Renders a photo-or-video-or-honest-placeholder block from an already-
 * resolved ACF image ID + file-field array (both top-level and repeater/
 * flexible-content sub-field values arrive pre-resolved this way). Never
 * silently falls back to stock imagery: an unset photo/video always shows
 * the TODO label instead.
 *
 * @param int|false  $photo_id      Attachment ID (return_format 'id'), or empty.
 * @param array|null $video_field   Raw ACF file-field value, or empty.
 * @param string     $caption       Placeholder caption text.
 * @param string     $todo_label    e.g. "TODO(photo)" or "TODO(illustration)".
 * @param string     $media_classes Classes applied to the rendered <img>/<video>.
 * @param string     $placeholder_classes Classes applied to the placeholder wrapper.
 */
function itoi_product_visual( $photo_id, $video_field, $caption, $todo_label, $media_classes, $placeholder_classes ) {
	$photo_url = $photo_id ? wp_get_attachment_image_url( $photo_id, 'large' ) : '';
	$photo_alt = $photo_id ? ( get_post_meta( $photo_id, '_wp_attachment_image_alt', true ) ?: '' ) : '';
	$media     = itoi_media_cover( $photo_url, $video_field, $photo_alt, $media_classes );

	if ( $media ) {
		echo $media; // phpcs:ignore -- itoi_media_cover() already escapes.
		return;
	}
	?>
	<div class="<?php echo esc_attr( $placeholder_classes ); ?>">
		<span class="text-[11px] font-bold uppercase tracking-[0.08em] text-signature-bright"><?php echo esc_html( $todo_label ); ?></span>
		<?php if ( $caption ) : ?>
			<p class="m-0 text-[13px] leading-snug text-white/65"><?php echo esc_html( $caption ); ?></p>
		<?php endif; ?>
	</div>
	<?php
}

while ( have_posts() ) :
	the_post();

	$itoi_product_id = get_the_ID();
	$itoi_sections    = get_field( 'page_sections', $itoi_product_id ) ?: array();
	// Hero's primary CTA scrolls to #comparison — only real if this product
	// actually has that section (not every product has a real, sourced
	// comparison to make; e.g. a partner-hardware page like PC2SE Outdoor).
	$itoi_has_comparison = in_array( 'comparison', wp_list_pluck( $itoi_sections, 'acf_fc_layout' ), true );
	?>

	<?php foreach ( $itoi_sections as $itoi_section ) :
		$itoi_layout = $itoi_section['acf_fc_layout'] ?? '';

		switch ( $itoi_layout ) :

			// ================= HERO =================
			case 'hero':
				$itoi_eyebrow    = $itoi_section['hero_eyebrow'] ?: 'Introducing ' . get_the_title();
				$itoi_emphasis   = $itoi_section['hero_headline_emphasis'] ?? '';
				$itoi_muted      = $itoi_section['hero_headline_muted'] ?? '';
				$itoi_lede       = $itoi_section['hero_lede'] ?? '';
				$itoi_cta_p      = $itoi_section['hero_cta_primary_label'] ?: 'See how it compares';
				$itoi_cta_s      = $itoi_section['hero_cta_secondary_label'] ?: 'View specifications';
				$itoi_photo      = $itoi_section['hero_device_photo'] ?? 0;
				$itoi_video      = $itoi_section['hero_device_video'] ?? null;
				$itoi_caption    = $itoi_section['hero_device_placeholder_caption'] ?? '';
				?>
				<section class="relative overflow-hidden px-5 pt-[168px] pb-14 min-[640px]:px-8 min-[640px]:pt-[206px] min-[640px]:pb-20" id="productHero">
					<div class="absolute inset-0 bg-[linear-gradient(160deg,#0a1720,#122b38_55%,#1b3a48)]"></div>
					<div class="relative z-[1] mx-auto max-w-[1280px]">
						<div class="mx-auto max-w-[760px] text-center">
							<div class="mb-4 flex items-center justify-center gap-2">
								<span class="h-2 w-2 rounded-full bg-signature-bright"></span>
								<span class="text-xs font-bold uppercase tracking-wide text-signature-bright"><?php echo esc_html( $itoi_eyebrow ); ?></span>
							</div>
							<h1 class="mb-5 text-[clamp(30px,5.2vw,52px)] leading-[1.1] text-white">
								<?php echo esc_html( $itoi_emphasis ); ?>
								<?php if ( $itoi_muted ) : ?><span class="font-medium text-white/55"> <?php echo esc_html( $itoi_muted ); ?></span><?php endif; ?>
							</h1>
							<?php if ( $itoi_lede ) : ?>
								<p class="mx-auto mb-8 max-w-[58ch] text-[15.5px] text-white/80 min-[1024px]:text-[16.5px]"><?php echo esc_html( $itoi_lede ); ?></p>
							<?php endif; ?>
							<div class="flex flex-wrap items-center justify-center gap-3">
								<?php if ( $itoi_has_comparison ) : ?>
									<a href="#comparison" class="hero-cta-primary whitespace-nowrap px-[26px] py-[13px] text-[14.5px] font-bold"><?php echo esc_html( $itoi_cta_p ); ?> &rarr;</a>
									<a href="#specs" class="hero-cta-secondary whitespace-nowrap px-[26px] py-[13px] text-[14.5px] font-semibold text-white"><?php echo esc_html( $itoi_cta_s ); ?></a>
								<?php else : ?>
									<a href="#specs" class="hero-cta-primary whitespace-nowrap px-[26px] py-[13px] text-[14.5px] font-bold"><?php echo esc_html( $itoi_cta_s ); ?> &rarr;</a>
								<?php endif; ?>
							</div>
						</div>
						<div class="aurora-device-stage relative mx-auto mt-14 flex aspect-[16/8] w-full max-w-[900px] items-center justify-center overflow-hidden rounded-2xl border border-white/10 min-[640px]:mt-16">
							<?php
							itoi_product_visual(
								$itoi_photo, $itoi_video, $itoi_caption, 'TODO(photo)',
								'absolute inset-0 h-full w-full object-cover',
								'relative z-[1] flex max-w-[380px] flex-col items-center gap-2 rounded-xl border border-dashed border-white/25 bg-black/20 px-6 py-7 text-center'
							);
							?>
						</div>
					</div>
				</section>
				<?php
				break;

			// ================= STAT STRIP =================
			case 'stat_strip':
				$itoi_stats = $itoi_section['stat_items'] ?: array();
				if ( empty( $itoi_stats ) ) {
					break;
				}
				?>
				<section class="border-b border-line bg-white px-8 py-section-sm">
					<div class="mx-auto max-w-[1280px]">
						<h2 class="sr-only"><?php echo esc_html( sprintf( 'Why %s', get_the_title() ) ); ?></h2>
						<div class="grid grid-cols-1 divide-y divide-line border border-line rounded-2xl min-[860px]:grid-cols-<?php echo esc_attr( min( count( $itoi_stats ), 4 ) ); ?> min-[860px]:divide-x min-[860px]:divide-y-0">
							<?php foreach ( $itoi_stats as $itoi_stat ) : ?>
								<div class="flex flex-col gap-1.5 px-7 py-7 text-center min-[860px]:text-left">
									<h3 class="m-0 text-[19px]"><?php echo esc_html( $itoi_stat['title'] ?? '' ); ?></h3>
									<p class="m-0 text-[14px] text-text-muted"><?php echo esc_html( $itoi_stat['description'] ?? '' ); ?></p>
								</div>
							<?php endforeach; ?>
						</div>
					</div>
				</section>
				<?php
				break;

			// ================= HOW IT WORKS =================
			case 'how_it_works':
				$itoi_steps = $itoi_section['how_it_works_steps'] ?: array();
				if ( empty( $itoi_steps ) ) {
					break;
				}
				?>
				<section class="border-b border-line bg-hero-bg px-8 py-section-md">
					<div class="mx-auto max-w-[1280px]">
						<div class="relative inline-block <?php echo esc_attr( itoi_reveal_class() ); ?>">
							<?php if ( ! empty( $itoi_section['how_it_works_eyebrow'] ) ) : ?>
								<div class="mb-2 text-[13.5px] font-bold uppercase tracking-wide text-teal-800"><?php echo esc_html( $itoi_section['how_it_works_eyebrow'] ); ?></div>
							<?php endif; ?>
							<h2 class="mb-10 max-w-[24ch] text-[clamp(26px,3vw,38px)]"><?php echo esc_html( $itoi_section['how_it_works_headline'] ?? '' ); ?></h2>
						</div>
						<div class="grid grid-cols-1 gap-8 min-[860px]:grid-cols-3">
							<?php foreach ( $itoi_steps as $itoi_step_i => $itoi_step ) :
								$itoi_step_todo = 'illustration' === ( $itoi_step['step_visual_type'] ?? 'photo' ) ? 'TODO(illustration)' : 'TODO(photo)';
								?>
								<div class="flex flex-col gap-4 rounded-2xl border border-line bg-white p-6">
									<span class="flex h-9 w-9 items-center justify-center rounded-full bg-ink text-[13px] font-extrabold text-white"><?php echo esc_html( sprintf( '%02d', $itoi_step_i + 1 ) ); ?></span>
									<h3 class="m-0 text-[19px]"><?php echo esc_html( $itoi_step['step_name'] ?? '' ); ?></h3>
									<p class="m-0 text-[14.5px] text-text-muted"><?php echo esc_html( $itoi_step['step_description'] ?? '' ); ?></p>
									<div class="flex aspect-[4/3] w-full flex-col items-center justify-center gap-1.5 overflow-hidden rounded-xl border border-dashed border-line bg-hero-bg px-4 text-center">
										<?php
										itoi_product_visual(
											$itoi_step['step_visual_photo'] ?? 0,
											$itoi_step['step_visual_video'] ?? null,
											$itoi_step['step_visual_placeholder_caption'] ?? '',
											$itoi_step_todo,
											'h-full w-full object-cover',
											'flex h-full w-full flex-col items-center justify-center gap-1.5 px-4 text-center'
										);
										?>
									</div>
								</div>
							<?php endforeach; ?>
						</div>
					</div>
				</section>
				<?php
				break;

			// ================= COMPARISON =================
			case 'comparison':
				$itoi_cmp_rows = $itoi_section['comparison_rows'] ?: array();
				if ( empty( $itoi_cmp_rows ) ) {
					break;
				}
				$itoi_col_a = $itoi_section['comparison_column_a_label'] ?: '2D Camera';
				$itoi_col_b = $itoi_section['comparison_column_b_label'] ?: get_the_title();
				?>
				<section class="border-b border-line bg-white px-8 py-section-lg min-[980px]:scroll-mt-[96px]" id="comparison">
					<div class="mx-auto max-w-[1080px]">
						<div class="relative inline-block <?php echo esc_attr( itoi_reveal_class() ); ?>">
							<?php if ( ! empty( $itoi_section['comparison_eyebrow'] ) ) : ?>
								<div class="mb-2 text-[13.5px] font-bold uppercase tracking-wide text-teal-800"><?php echo esc_html( $itoi_section['comparison_eyebrow'] ); ?></div>
							<?php endif; ?>
							<h2 class="mb-4 max-w-[20ch] text-[clamp(26px,3vw,38px)]"><?php echo esc_html( $itoi_section['comparison_headline'] ?? '' ); ?></h2>
						</div>
						<?php if ( ! empty( $itoi_section['comparison_intro'] ) ) : ?>
							<p class="mb-10 max-w-[62ch] text-[15px] text-text-muted"><?php echo esc_html( $itoi_section['comparison_intro'] ); ?></p>
						<?php endif; ?>
						<div class="glass-element-light overflow-hidden rounded-2xl">
							<div class="overflow-x-auto" tabindex="0" role="region" aria-label="Comparison table, scroll for more">
								<table class="w-full min-w-[560px] border-collapse text-left">
									<thead>
										<tr class="border-b border-[var(--glass-border-on-light)]">
											<th class="w-[22%] px-5 py-4 text-[12.5px] font-bold uppercase tracking-wide text-text-muted min-[640px]:px-7"><span class="sr-only">Category</span></th>
											<th class="px-5 py-4 text-[13.5px] font-bold text-text-muted min-[640px]:px-7"><?php echo esc_html( $itoi_col_a ); ?></th>
											<th class="px-5 py-4 text-[13.5px] font-extrabold text-ink min-[640px]:px-7"><?php echo esc_html( $itoi_col_b ); ?></th>
										</tr>
									</thead>
									<tbody>
										<?php foreach ( $itoi_cmp_rows as $itoi_row_i => $itoi_row ) : ?>
											<tr <?php echo $itoi_row_i < count( $itoi_cmp_rows ) - 1 ? 'class="border-b border-[var(--glass-border-on-light)]"' : ''; ?>>
												<th scope="row" class="px-5 py-5 align-top text-[13.5px] font-bold text-ink min-[640px]:px-7"><?php echo esc_html( $itoi_row['row_label'] ?? '' ); ?></th>
												<td class="px-5 py-5 align-top text-[13.5px] text-text-muted min-[640px]:px-7"><span class="mb-1 block font-bold text-ink"><?php echo esc_html( $itoi_row['column_a_headline'] ?? '' ); ?></span><?php echo esc_html( $itoi_row['column_a_description'] ?? '' ); ?></td>
												<td class="px-5 py-5 align-top text-[13.5px] text-text-muted min-[640px]:px-7"><span class="mb-1 block font-bold text-ink"><?php echo esc_html( $itoi_row['column_b_headline'] ?? '' ); ?></span><?php echo esc_html( $itoi_row['column_b_description'] ?? '' ); ?></td>
											</tr>
										<?php endforeach; ?>
									</tbody>
								</table>
							</div>
						</div>
						<?php if ( ! empty( $itoi_section['comparison_callout'] ) ) : ?>
							<div class="mt-8 rounded-2xl border border-line bg-hero-bg px-6 py-6 min-[640px]:px-8">
								<p class="m-0 text-[14px] text-text-muted"><?php echo esc_html( $itoi_section['comparison_callout'] ); ?> <a href="<?php echo esc_url( home_url( '/privacy/' ) ); ?>" class="font-bold text-ink underline underline-offset-4 hover:opacity-70">Read our Privacy Policy &rarr;</a></p>
							</div>
						<?php endif; ?>
					</div>
				</section>
				<?php
				break;

			// ================= SPECIFICATIONS =================
			case 'specifications':
				$itoi_specs = $itoi_section['specs'] ?: array();
				if ( empty( $itoi_specs ) ) {
					break;
				}
				?>
				<section class="border-b border-line bg-hero-bg px-8 py-section-md min-[980px]:scroll-mt-[96px]" id="specs">
					<div class="mx-auto max-w-[1280px]">
						<div class="relative inline-block <?php echo esc_attr( itoi_reveal_class() ); ?>">
							<?php if ( ! empty( $itoi_section['specs_eyebrow'] ) ) : ?>
								<div class="mb-2 text-[13.5px] font-bold uppercase tracking-wide text-teal-800"><?php echo esc_html( $itoi_section['specs_eyebrow'] ); ?></div>
							<?php endif; ?>
							<h2 class="mb-2 max-w-[20ch] text-[clamp(26px,3vw,38px)]"><?php echo esc_html( $itoi_section['specs_headline'] ?? 'Specifications' ); ?></h2>
						</div>
						<?php if ( ! empty( $itoi_section['specs_pending_note'] ) ) : ?>
							<div class="mb-8 inline-flex items-center gap-2 rounded-full border border-dashed border-line bg-white px-4 py-2 text-[12.5px] font-bold text-text-muted">
								<span aria-hidden="true">&#9888;</span> <?php echo esc_html( $itoi_section['specs_pending_note'] ); ?>
							</div>
						<?php endif; ?>
						<div class="grid grid-cols-1 gap-4 min-[640px]:grid-cols-2 min-[980px]:grid-cols-4">
							<?php foreach ( $itoi_specs as $itoi_spec ) : ?>
								<div class="rounded-xl border border-dashed border-line bg-white px-5 py-5">
									<div class="mb-1.5 text-[11.5px] font-bold uppercase tracking-wide text-text-muted"><?php echo esc_html( $itoi_spec['label'] ?? '' ); ?></div>
									<div class="text-[15px] font-extrabold text-ink">
										<?php echo esc_html( $itoi_spec['value'] ?? '' ); ?>
										<?php if ( ! empty( $itoi_spec['is_draft'] ) ) : ?>
											<span class="ml-1 align-middle text-[10px] font-bold uppercase tracking-wide text-text-muted">(draft)</span>
										<?php endif; ?>
									</div>
								</div>
							<?php endforeach; ?>
						</div>
					</div>
				</section>
				<?php
				break;

			// ================= USE CASES =================
			case 'use_cases':
				$itoi_chips = $itoi_section['use_case_chips'] ?: array();
				if ( empty( $itoi_chips ) ) {
					break;
				}
				$itoi_cap_label = $itoi_section['use_cases_capabilities_label'] ?? '';
				$itoi_caps      = $itoi_section['use_cases_capabilities'] ?: array();
				?>
				<section class="border-b border-line bg-white px-8 py-section-sm">
					<div class="mx-auto max-w-[1280px]">
						<div class="mb-6 relative inline-block <?php echo esc_attr( itoi_reveal_class() ); ?>">
							<h2 class="m-0 text-xl"><?php echo esc_html( $itoi_section['use_cases_heading'] ?? '' ); ?></h2>
							<?php if ( ! empty( $itoi_section['use_cases_intro'] ) ) : ?>
								<p class="mt-2 max-w-[54ch] text-[14.5px] text-text-muted"><?php echo esc_html( $itoi_section['use_cases_intro'] ); ?></p>
							<?php endif; ?>
						</div>
						<div class="flex flex-wrap gap-3">
							<?php foreach ( $itoi_chips as $itoi_chip ) : ?>
								<span class="inline-flex items-center gap-2.5 whitespace-nowrap rounded-xl border border-line bg-hero-bg px-[18px] py-3.5 text-[13.5px] font-bold text-ink">
									<span class="flex h-[26px] w-[26px] flex-none items-center justify-center rounded-[7px] bg-white text-xs text-teal-800">&#9723;</span>
									<?php echo esc_html( $itoi_chip['label'] ?? '' ); ?>
								</span>
							<?php endforeach; ?>
						</div>
						<?php if ( $itoi_cap_label && ! empty( $itoi_caps ) ) : ?>
							<div class="mt-8 flex flex-wrap items-center gap-2.5 text-[13px] text-text-muted">
								<span class="font-bold text-ink"><?php echo esc_html( $itoi_cap_label ); ?></span>
								<?php foreach ( $itoi_caps as $itoi_cap_i => $itoi_cap ) : ?>
									<?php if ( $itoi_cap_i > 0 ) : ?><span aria-hidden="true">&middot;</span><?php endif; ?>
									<span><?php echo esc_html( $itoi_cap['label'] ?? '' ); ?></span>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>
					</div>
				</section>
				<?php
				break;

			// ================= FINAL CTA =================
			case 'final_cta':
				if ( empty( $itoi_section['final_cta_headline'] ) ) {
					break;
				}
				?>
				<div class="mx-4 mb-[60px] mt-[60px] flex flex-wrap items-center justify-between gap-9 rounded-[20px] bg-ink px-6 py-9 text-white min-[980px]:mx-8 min-[980px]:mb-[90px] min-[980px]:mt-[90px] min-[980px]:px-[60px] min-[980px]:py-[60px]">
					<div>
						<h2 class="mb-2 max-w-[20ch] text-[clamp(22px,2.6vw,32px)] text-white"><?php echo esc_html( $itoi_section['final_cta_headline'] ); ?></h2>
						<?php if ( ! empty( $itoi_section['final_cta_text'] ) ) : ?>
							<p class="m-0 max-w-[40ch] text-white/60"><?php echo esc_html( $itoi_section['final_cta_text'] ); ?></p>
						<?php endif; ?>
					</div>
					<a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" class="rounded-full bg-white px-[22px] py-[11px] text-sm font-bold text-ink"><?php echo esc_html( $itoi_section['final_cta_button_label'] ?: 'Get demo' ); ?> &rarr;</a>
				</div>
				<?php
				break;

		endswitch;
	endforeach;

endwhile;

get_footer();
