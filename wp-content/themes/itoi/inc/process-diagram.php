<?php
/**
 * Shared process-step icon strip, added 2026-07-31 (see NOTES.md) for the
 * two-column Overview redesign on single-solution.php and
 * single-industry.php's long-form Overview section. Icon set follows the
 * same hand-authored "stroke=currentColor, no icon library" convention as
 * itoi_solution_builder_icon() (inc/solution-builder.php) — a separate
 * function, not an extension of that one, since these are generic process-
 * step icons, not the 8 fixed solution-CPT-slug icons that function owns.
 *
 * Extended same day to support a second visual treatment ($style param on
 * itoi_render_process_diagram()) rather than a second component: "lines"
 * (the original CAPTURE/ANALYSE/ACT look — line+arrowhead connector, bold
 * uppercase ink/teal-800 labels, 36px icons) and "dots" (dot connector,
 * regular-weight muted-grey labels, 48px icons, no per-step accent color)
 * — same underlying $steps data shape either way, so it stays one editable
 * field structure sitewide instead of forking into two disconnected
 * systems for what a visitor experiences as the same kind of component.
 *
 * @package ITOI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @param string $slug    One of: camera-pos, processor-node, dashboard-arrow, shield, database,
 *                        network, floor-plan-nodes, switch-poe, lifecycle-shield, headset-clock.
 * @param string $classes Tailwind size classes for the <svg>.
 */
function itoi_process_diagram_icon( $slug, $classes = 'h-8 w-8' ) {
	$common = 'fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"';

	$paths = array(
		'camera-pos'       => '<rect x="1" y="8" width="10" height="7" rx="1.3"/><circle cx="6" cy="11.5" r="1.8"/><path d="M4 8V6.5a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1V8"/><rect x="13" y="4" width="9" height="13" rx="1.3"/><path d="M15 7h5M15 10h5M15 13h3"/>',
		'processor-node'   => '<rect x="7" y="7" width="10" height="10" rx="1.5"/><rect x="10" y="10" width="4" height="4" rx="0.5"/><path d="M9 3v4M15 3v4M9 17v4M15 17v4M3 9h4M3 15h4M17 9h4M17 15h4"/>',
		'dashboard-arrow'  => '<rect x="3" y="4" width="18" height="14" rx="2"/><path d="M3 9h18"/><path d="M7 15l3-3 2 2 4-4"/><path d="M14 10h2v2"/>',
		'shield'           => '<path d="M12 2.5 20 5.5V11c0 5-3.5 9-8 10.5-4.5-1.5-8-5.5-8-10.5V5.5L12 2.5Z"/>',
		'database'         => '<ellipse cx="12" cy="5" rx="8" ry="3"/><path d="M4 5v14c0 1.7 3.6 3 8 3s8-1.3 8-3V5"/><path d="M4 12c0 1.7 3.6 3 8 3s8-1.3 8-3"/>',
		'network'          => '<circle cx="12" cy="4" r="2"/><circle cx="5" cy="19" r="2"/><circle cx="19" cy="19" r="2"/><path d="M12 6v5M12 11 6 17M12 11l6 6"/>',
		// IT & Network Infrastructure's 4-step "dots" diagram (2026-07-31, see NOTES.md).
		'floor-plan-nodes' => '<rect x="2" y="3" width="20" height="18" rx="1"/><circle cx="7" cy="8" r="1.3"/><circle cx="17" cy="8" r="1.3"/><circle cx="12" cy="16" r="1.3"/><path d="M8.2 8.6h7.6M7.5 9.3l4 6M16.5 9.3l-4 6" stroke-dasharray="2 2"/>',
		'switch-poe'       => '<rect x="5" y="8" width="16" height="8" rx="1"/><path d="M8 8V6M12 8V6M16 8V6"/><path d="M1 12h4"/><path d="M2.5 9.5 1 12.5h1.8L2 15l3-4H3.2Z"/>',
		'lifecycle-shield' => '<path d="M4 12a8 8 0 0 1 13.6-5.7M20 12a8 8 0 0 1-13.6 5.7"/><path d="M17 3v4h-4M7 21v-4h4"/><path d="M12 9.7 14 10.5v2.3c0 1.5-.8 2.5-2 2.9-1.2-.4-2-1.4-2-2.9v-2.3Z"/>',
		'headset-clock'    => '<path d="M4 13v-1a8 8 0 0 1 14.6-4.6"/><rect x="2.5" y="13" width="4" height="6" rx="1.5"/><rect x="15.5" y="13" width="4" height="6" rx="1.5"/><circle cx="18" cy="5" r="3"/><path d="M18 3.5V5l1 .8"/>',
	);

	if ( ! isset( $paths[ $slug ] ) ) {
		return;
	}

	printf( '<svg class="%s" viewBox="0 0 24 24" %s>%s</svg>', esc_attr( $classes ), $common, $paths[ $slug ] ); // phpcs:ignore -- $common/$paths are hardcoded above, not user input
}

/**
 * Renders nothing at all if $steps is empty — the entire empty-state
 * handling, matching the sitewide "optional section, never renders
 * broken" convention. No numbered badges, no card/border wrapper — sits
 * directly on the section's own background per the design spec.
 *
 * @param array  $steps Raw repeater rows: step_label, step_caption, step_icon, step_accent.
 * @param string $style 'lines' (default — line+arrowhead connector, bold uppercase ink/teal-800
 *                       labels, 36px icons) or 'dots' (dot connector, regular-weight muted-grey
 *                       labels, 48px icons, no per-step accent color — added 2026-07-31 for the
 *                       IT & Network Infrastructure 4-step diagram, see NOTES.md).
 */
function itoi_render_process_diagram( $steps, $style = 'lines' ) {
	if ( empty( $steps ) ) {
		return;
	}
	$itoi_pd_is_dots      = 'dots' === $style;
	$itoi_pd_icon_classes = $itoi_pd_is_dots ? 'h-12 w-12' : 'h-9 w-9';
	$itoi_pd_top_margin   = $itoi_pd_is_dots ? 'mt-10 min-[980px]:mt-10' : 'mt-12 min-[980px]:mt-16';
	?>
	<div class="mx-auto <?php echo esc_attr( $itoi_pd_top_margin ); ?> flex max-w-[700px] items-start justify-center">
		<?php foreach ( $steps as $itoi_pd_i => $itoi_pd_step ) : ?>
			<?php if ( $itoi_pd_i > 0 ) : ?>
				<div class="process-diagram-connector<?php echo $itoi_pd_is_dots ? ' style-dots' : ''; ?>" aria-hidden="true"></div>
			<?php endif; ?>
			<?php
			// step_accent only distinguishes color in the "lines" style — the
			// "dots" spec describes every icon in one uniform charcoal color,
			// no per-step accent, even though the field stays on the row.
			$itoi_pd_is_accent = ! $itoi_pd_is_dots && ! empty( $itoi_pd_step['step_accent'] );
			?>
			<div class="flex w-[220px] flex-shrink-0 flex-col items-center gap-3 text-center">
				<div class="<?php echo $itoi_pd_is_accent ? 'text-teal-800' : 'text-ink'; ?>">
					<?php itoi_process_diagram_icon( $itoi_pd_step['step_icon'] ?? '', $itoi_pd_icon_classes ); ?>
				</div>
				<div>
					<?php if ( $itoi_pd_is_dots ) : ?>
						<div class="text-[14px] font-normal text-text-muted"><?php echo esc_html( $itoi_pd_step['step_label'] ?? '' ); ?></div>
					<?php else : ?>
						<div class="text-[13px] font-bold uppercase tracking-[0.08em] <?php echo $itoi_pd_is_accent ? 'text-teal-800' : 'text-ink'; ?>"><?php echo esc_html( $itoi_pd_step['step_label'] ?? '' ); ?></div>
					<?php endif; ?>
					<?php if ( ! empty( $itoi_pd_step['step_caption'] ) ) : ?>
						<div class="mt-1 text-[13px] text-text-muted"><?php echo esc_html( $itoi_pd_step['step_caption'] ); ?></div>
					<?php endif; ?>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
	<?php
}
