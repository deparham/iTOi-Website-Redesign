<?php
/**
 * Homepage platform-demo modal — render helpers for the dense data-UI
 * markup (stat tiles, bar charts, heat-shaded cells, risk bars, mini-dot
 * rows) shared across the 6 tabs in template-parts/platform-demo-modal.php.
 *
 * All values here are illustrative demo content, not real site copy — per
 * PROJECT.md's platform-demo spec Part 5, this is a deliberate scoping
 * decision to keep it in template/JS logic rather than ACF (see NOTES.md).
 *
 * @package ITOI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * A single stat tile: a big number, its label, and an optional up/down
 * delta badge.
 *
 * @param string $number    The headline figure, e.g. '2,481'.
 * @param string $label     The label under the number.
 * @param string $delta     Optional delta text, e.g. '+12%'. Omit to hide the badge.
 * @param string $direction Optional modifier class for the delta badge (e.g. 'up'/'down').
 */
function itoi_pd_stat_tile( $number, $label, $delta = '', $direction = '' ) {
	?>
	<div class="pd-stat-tile rounded-[10px] border border-line bg-white p-3.5 text-center">
		<div class="pd-num text-[19px] font-extrabold"><?php echo esc_html( $number ); ?></div>
		<div class="mt-[3px] text-[10.5px] uppercase tracking-[0.02em] text-text-muted"><?php echo esc_html( $label ); ?></div>
		<?php if ( $delta ) : ?>
			<div class="pd-delta <?php echo esc_attr( $direction ); ?> mt-[3px] text-[10px] font-bold"><?php echo esc_html( $delta ); ?></div>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Renders a bar chart from raw values, each bar's height scaled relative
 * to the tallest value.
 *
 * @param array $values  The bar values, in display order.
 * @param array $special Optional index => 'amber'|'light' map for the highlighted /
 *                        secondary-series bars (PROJECT.md platform-demo spec Part 3's
 *                        ".bars" component — one designated bar per chart marks a
 *                        highlight point).
 */
function itoi_pd_bars( array $values, array $special = array() ) {
	$max = max( $values );
	echo '<div class="pd-bars">';
	foreach ( $values as $i => $value ) {
		$classes = array( 'pd-bar' );
		if ( isset( $special[ $i ] ) ) {
			$classes[] = $special[ $i ];
		}
		$height = $max > 0 ? round( $value / $max * 100, 1 ) : 0;
		printf(
			'<div class="%1$s" style="height:%2$s%%"></div>',
			esc_attr( implode( ' ', $classes ) ),
			esc_attr( $height )
		);
	}
	echo '</div>';
}

/**
 * A row of small dots, one of which (amber_index) is highlighted amber.
 *
 * @param int $count       Total number of dots to render.
 * @param int $amber_index The zero-based index of the dot to highlight.
 */
function itoi_pd_mini_dots( $count, $amber_index ) {
	echo '<div class="pd-mini-dots flex flex-1 items-center gap-[5px]">';
	for ( $i = 0; $i < $count; $i++ ) {
		$class = 'pd-d' . ( $i === $amber_index ? ' amber' : '' );
		echo '<span class="' . esc_attr( $class ) . '"></span>';
	}
	echo '</div>';
}

/**
 * A single labeled risk bar row.
 *
 * @param string $label   The row's label.
 * @param int    $percent The bar's fill percentage (0-100).
 * @param string $level   Modifier class appended to 'pd-risk-bar', e.g. 'low'/'medium'/'high'.
 * @param string $text    Text rendered inside the filled portion of the bar.
 */
function itoi_pd_risk_row( $label, $percent, $level, $text ) {
	$bar_class = 'pd-risk-bar' . ( $level ? ' ' . $level : '' );
	?>
	<div class="mb-2.5 flex items-center gap-2.5">
		<div class="w-[100px] flex-none text-[11px] font-bold"><?php echo esc_html( $label ); ?></div>
		<div class="pd-risk-bar-wrap flex-1">
			<div class="<?php echo esc_attr( $bar_class ); ?>" style="width:<?php echo (int) $percent; ?>%"><?php echo esc_html( $text ); ?></div>
		</div>
	</div>
	<?php
}

/**
 * Weekly Traffic By Hour heat-shade breakpoints — exact thresholds per
 * PROJECT.md platform-demo spec Part 3, Tab 2 Row 2.
 *
 * @param int $value The raw traffic count for this cell.
 * @return string The heat-shade class name for this value, 'pd-heat-0' through 'pd-heat-4'.
 */
function itoi_pd_heat_class( $value ) {
	if ( $value < 200 ) {
		return 'pd-heat-0';
	}
	if ( $value < 500 ) {
		return 'pd-heat-1';
	}
	if ( $value < 800 ) {
		return 'pd-heat-2';
	}
	if ( $value < 1100 ) {
		return 'pd-heat-3';
	}
	return 'pd-heat-4';
}

/**
 * The "Illustrative data — not a real client dashboard" disclaimer shown
 * under each demo panel.
 */
function itoi_pd_illustrative_note() {
	echo '<div class="mt-2.5 text-right text-[10.5px] text-text-muted">Illustrative data &mdash; not a real client dashboard.</div>';
}
