<?php
/**
 * Homepage platform-demo modal — render helpers for the dense data-UI
 * markup (stat tiles, bar charts, heat-shaded cells, risk bars, mini-dot
 * rows) shared across the 6 tabs in template-parts/platform-demo-modal.php.
 *
 * All values here are illustrative demo content, not real site copy — per
 * PROJECT.md's platform-demo spec Part 5, this is a deliberate scoping
 * decision to keep it in template/JS logic rather than ACF (see NOTES.md).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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
 * $special is an array of index => 'amber'|'light' for the highlighted /
 * secondary-series bars (PROJECT.md platform-demo spec Part 3's ".bars"
 * component — one designated bar per chart marks a highlight point).
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

function itoi_pd_mini_dots( $count, $amber_index ) {
	echo '<div class="pd-mini-dots flex flex-1 items-center gap-[5px]">';
	for ( $i = 0; $i < $count; $i++ ) {
		$class = 'pd-d' . ( $i === $amber_index ? ' amber' : '' );
		echo '<span class="' . esc_attr( $class ) . '"></span>';
	}
	echo '</div>';
}

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

function itoi_pd_illustrative_note() {
	echo '<div class="mt-2.5 text-right text-[10.5px] text-text-muted">Illustrative data &mdash; not a real client dashboard.</div>';
}
