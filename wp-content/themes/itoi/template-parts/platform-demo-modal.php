<?php
/**
 * Homepage-only interactive platform demo — 6-tab dashboard modal behind
 * the "One integrated platform" teaser's "Learn more" / play-button
 * triggers. Ground truth: platform-demo.html (attached working reference).
 *
 * Modal open/close JS reuses the exact pattern already established for
 * the site's other modal (Find Your Fit, footer.php + assets/js/main.js
 * initFinder()) — body scroll lock via document.body.style.overflow,
 * same open/close function shape — rather than inventing new modal logic.
 * See assets/js/main.js initPlatformDemo().
 *
 * All stat/chart/table values below are illustrative demo content, not
 * real ACF-backed copy — deliberate scoping decision, see NOTES.md and
 * PROJECT.md platform-demo spec Part 5.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$itoi_pd_tabs = array(
	'operations'   => 'Operations',
	'traffic'      => 'Traffic',
	'performance'  => 'Performance',
	'optimization' => 'Optimization',
	'asset'        => 'Asset Protection',
	'benchmarks'   => 'Benchmarks',
);
?>

<div class="pd-overlay" id="platformDemoOverlay">
	<div class="pd-modal glass-panel-light" role="dialog" aria-modal="true" aria-labelledby="platformDemoTitle">
		<div class="pd-header-glass flex items-center justify-between px-5 py-3 text-white">
			<span class="text-[14px] font-bold" id="platformDemoTitle">ITOI Platform &mdash; Live Demo (Illustrative Data)</span>
			<button type="button" class="pd-close flex h-[30px] w-[30px] flex-none items-center justify-center rounded-full border border-white/25 bg-transparent text-white" id="platformDemoClose" aria-label="Close">&times;</button>
		</div>

		<div class="pd-tabs no-scrollbar flex gap-0.5 overflow-x-auto border-b border-line bg-[#f4f5f6] px-3.5 pt-2" role="tablist" tabindex="0" aria-label="Demo sections, scroll for more">
			<?php $itoi_pd_first = true; ?>
			<?php foreach ( $itoi_pd_tabs as $itoi_pd_key => $itoi_pd_label ) : ?>
				<button type="button" class="pd-tab whitespace-nowrap px-3.5 py-2.5 text-[11.5px] font-bold uppercase tracking-[0.03em] <?php echo $itoi_pd_first ? 'active' : ''; ?>" data-tab="<?php echo esc_attr( $itoi_pd_key ); ?>" role="tab" aria-selected="<?php echo $itoi_pd_first ? 'true' : 'false'; ?>"><?php echo esc_html( $itoi_pd_label ); ?></button>
				<?php $itoi_pd_first = false; ?>
			<?php endforeach; ?>
		</div>

		<div class="pd-body flex-1 overflow-y-auto bg-[#fafbfb] p-5" tabindex="0" role="region" aria-label="Demo content, scroll for more">

			<!-- ============ TAB 1 — OPERATIONS ============ -->
			<div class="pd-panel active" data-panel="operations" role="tabpanel">
				<div class="mb-3 grid grid-cols-5 gap-3 max-[800px]:grid-cols-2">
					<?php
					itoi_pd_stat_tile( '5.6k', 'Traffic', '&#9660; 11%', 'down' );
					itoi_pd_stat_tile( '$1,240', 'Sales', '&#9650; 4%', 'up' );
					itoi_pd_stat_tile( '3.2%', 'Conversion', '&#9650; 0.4pt', 'up' );
					itoi_pd_stat_tile( '$68', 'ATV', '&#9660; 2%', 'down' );
					itoi_pd_stat_tile( '$217', 'Shopper Yield', '&#9650; 6%', 'up' );
					?>
				</div>
				<div class="mb-3 grid grid-cols-3 gap-3 max-[800px]:grid-cols-1">
					<?php
					itoi_pd_stat_tile( '0.68', 'UPT' );
					itoi_pd_stat_tile( '$34', 'AUR' );
					itoi_pd_stat_tile( '62%', 'Capture Rate' );
					?>
				</div>
				<div class="mb-3 grid grid-cols-[1.3fr_1fr] gap-3 max-[800px]:grid-cols-1">
					<div class="rounded-[10px] border border-line bg-white p-4">
						<div class="mb-3 flex items-center justify-between text-xs font-bold">
							Performance Trend
							<span class="flex gap-2.5 text-[10px] font-semibold text-text-muted">
								<span class="inline-flex items-center gap-1"><i class="inline-block h-2 w-2 rounded-full bg-teal-700" aria-hidden="true"></i>Traffic</span>
								<span class="inline-flex items-center gap-1"><i class="inline-block h-2 w-2 rounded-full bg-signature" aria-hidden="true"></i>Conversion</span>
							</span>
						</div>
						<?php itoi_pd_bars( array( 10, 25, 40, 55, 70, 85, 95, 80, 60 ), array( 6 => 'amber' ) ); ?>
					</div>
					<div class="rounded-[10px] border border-line bg-white p-4">
						<div class="mb-3 text-xs font-bold">Conversion by Store</div>
						<?php itoi_pd_bars( array( 40, 65, 80, 30, 55, 90 ), array( 2 => 'amber' ) ); ?>
					</div>
				</div>
				<div class="rounded-[10px] border border-line bg-white p-4">
					<div class="mb-3 text-xs font-bold">Sales / Conversion / Shopper Yield Performance</div>
					<div class="mb-2 flex items-center gap-2.5 text-[11px]">
						<div class="w-[110px] flex-none text-text-muted">Sales</div>
						<?php itoi_pd_mini_dots( 8, 5 ); ?>
					</div>
					<div class="mb-2 flex items-center gap-2.5 text-[11px]">
						<div class="w-[110px] flex-none text-text-muted">Conversion</div>
						<?php itoi_pd_mini_dots( 8, 3 ); ?>
					</div>
					<div class="flex items-center gap-2.5 text-[11px]">
						<div class="w-[110px] flex-none text-text-muted">Shopper Yield</div>
						<?php itoi_pd_mini_dots( 8, 6 ); ?>
					</div>
				</div>
				<?php itoi_pd_illustrative_note(); ?>
			</div>

			<!-- ============ TAB 2 — TRAFFIC ============ -->
			<div class="pd-panel" data-panel="traffic" role="tabpanel">
				<div class="mb-3 grid grid-cols-5 gap-3 max-[800px]:grid-cols-2">
					<?php
					itoi_pd_stat_tile( '5.6k', 'Today', '&#9660; 11%', 'down' );
					itoi_pd_stat_tile( '11.6k', 'Yesterday', '&#9660; 6%', 'down' );
					itoi_pd_stat_tile( '17.3k', 'This Week', '&#9660; 8%', 'down' );
					itoi_pd_stat_tile( '228.2k', 'This Month', '&#9650; 12%', 'up' );
					itoi_pd_stat_tile( '93.7%', 'Capture Rate', '&#9650; 1.1pt', 'up' );
					?>
				</div>
				<div class="mb-3 overflow-x-auto rounded-[10px] border border-line bg-white p-4" tabindex="0" role="region" aria-label="Weekly Traffic By Hour table, scroll for more columns">
					<div class="mb-3 text-xs font-bold">Weekly Traffic By Hour</div>
					<?php
					$itoi_pd_days  = array( 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun' );
					$itoi_pd_hours = array( '6am', '8am', '10am', '12pm', '2pm', '4pm', '6pm', '8pm' );
					?>
					<table class="pd-data-table w-full min-w-[520px] border-collapse text-[10.5px]">
						<tr>
							<th>Hour</th>
							<?php foreach ( $itoi_pd_days as $itoi_pd_day ) : ?>
								<th><?php echo esc_html( $itoi_pd_day ); ?></th>
							<?php endforeach; ?>
						</tr>
						<?php foreach ( $itoi_pd_hours as $itoi_pd_hour ) : ?>
							<tr>
								<td><?php echo esc_html( $itoi_pd_hour ); ?></td>
								<?php foreach ( $itoi_pd_days as $itoi_pd_day ) : ?>
									<?php $itoi_pd_traffic = wp_rand( 0, 1200 ); ?>
									<td class="<?php echo esc_attr( itoi_pd_heat_class( $itoi_pd_traffic ) ); ?>"><?php echo esc_html( $itoi_pd_traffic ); ?></td>
								<?php endforeach; ?>
							</tr>
						<?php endforeach; ?>
					</table>
				</div>
				<div class="grid grid-cols-3 gap-3 max-[800px]:grid-cols-1">
					<div class="rounded-[10px] border border-line bg-white p-4">
						<div class="mb-3 text-xs font-bold">Traffic By Hour</div>
						<?php itoi_pd_bars( array( 2, 5, 15, 45, 80, 95, 70, 40, 20 ), array( 4 => 'amber' ) ); ?>
					</div>
					<div class="rounded-[10px] border border-line bg-white p-4">
						<div class="mb-3 text-xs font-bold">Traffic By Day</div>
						<?php itoi_pd_bars( array( 70, 75, 60, 80, 55, 90, 65 ), array( 5 => 'amber' ) ); ?>
					</div>
					<div class="rounded-[10px] border border-line bg-white p-4">
						<div class="mb-3 text-xs font-bold">Traffic By Month</div>
						<?php itoi_pd_bars( array( 20, 15, 30, 10, 45, 25, 60, 80, 55, 35, 20, 15 ), array( 7 => 'amber' ) ); ?>
					</div>
				</div>
				<?php itoi_pd_illustrative_note(); ?>
			</div>

			<!-- ============ TAB 3 — PERFORMANCE ============ -->
			<div class="pd-panel" data-panel="performance" role="tabpanel">
				<div class="mb-3 overflow-x-auto rounded-[10px] border border-line bg-white p-4" tabindex="0" role="region" aria-label="This Period vs. Previous Period table, scroll for more columns">
					<div class="mb-3 text-xs font-bold">This Period vs. Previous Period</div>
					<table class="pd-data-table w-full min-w-[480px] border-collapse text-[10.5px]">
						<tr><th><span class="sr-only">Metric</span></th><th>Traffic</th><th>Sales</th><th>Conversion</th><th>ATV</th><th>Shopper Yield</th></tr>
						<tr><td>This Period</td><td>17.3k</td><td>$8,420</td><td>3.4%</td><td>$71</td><td>$224</td></tr>
						<tr><td>Previous Period</td><td>18.9k</td><td>$7,960</td><td>2.9%</td><td>$66</td><td>$198</td></tr>
						<tr>
							<td>Change</td>
							<td class="pd-delta down font-bold">&#9660; 8%</td>
							<td class="pd-delta up font-bold">&#9650; 6%</td>
							<td class="pd-delta up font-bold">&#9650; 0.5pt</td>
							<td class="pd-delta up font-bold">&#9650; 8%</td>
							<td class="pd-delta up font-bold">&#9650; 13%</td>
						</tr>
					</table>
				</div>
				<div class="grid grid-cols-2 gap-3 max-[800px]:grid-cols-1">
					<div class="rounded-[10px] border border-line bg-white p-4">
						<div class="mb-3 text-xs font-bold">Traffic vs. Conversion Trend</div>
						<?php itoi_pd_bars( array( 30, 45, 60, 50, 75, 90, 70 ), array( 5 => 'amber' ) ); ?>
					</div>
					<div class="rounded-[10px] border border-line bg-white p-4">
						<div class="mb-3 text-xs font-bold">Revenue by Category</div>
						<?php itoi_pd_bars( array( 55, 80, 40, 65, 90, 30 ), array( 4 => 'amber' ) ); ?>
					</div>
				</div>
				<?php itoi_pd_illustrative_note(); ?>
			</div>

			<!-- ============ TAB 4 — OPTIMIZATION ============ -->
			<div class="pd-panel" data-panel="optimization" role="tabpanel">
				<div class="mb-3 overflow-x-auto rounded-[10px] border border-line bg-white p-4" tabindex="0" role="region" aria-label="Past Performance Traffic In table, scroll for more columns">
					<div class="mb-3 text-xs font-bold">Past Performance &mdash; Traffic In</div>
					<table class="pd-data-table pd-data-table--uniform w-full min-w-[420px] border-collapse text-[10.5px]">
						<tr>
							<?php foreach ( array( 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun' ) as $itoi_pd_day ) : ?>
								<th><?php echo esc_html( $itoi_pd_day ); ?></th>
							<?php endforeach; ?>
						</tr>
						<tr>
							<?php for ( $itoi_pd_i = 0; $itoi_pd_i < 7; $itoi_pd_i++ ) : ?>
								<td><?php echo esc_html( wp_rand( 8000, 11000 ) ); ?></td>
							<?php endfor; ?>
						</tr>
					</table>
				</div>
				<div class="rounded-[10px] border border-line bg-white p-4">
					<div class="mb-3 text-xs font-bold">Staff Planning</div>
					<div class="mb-3 flex items-center gap-2.5">
						<label for="platformDemoLaborHours" class="text-[11px] font-bold text-text-muted">Available Labor Hours</label>
						<input type="number" id="platformDemoLaborHours" value="120" class="w-[100px] rounded-md border border-line px-2.5 py-1.5 text-xs">
					</div>
					<div class="text-xs text-text-muted" id="platformDemoStaffOutput">Enter available labor hours to see recommended staffing by day.</div>
				</div>
				<?php itoi_pd_illustrative_note(); ?>
			</div>

			<!-- ============ TAB 5 — ASSET PROTECTION ============ -->
			<div class="pd-panel" data-panel="asset" role="tabpanel">
				<div class="mb-3 grid grid-cols-3 gap-3 max-[800px]:grid-cols-1">
					<?php
					$itoi_pd_shrink = array(
						'Post Void'      => array( '2', '$146', '$73' ),
						'Line Item Void' => array( '1', '$52', '$52' ),
						'Cash Refund'    => array( '4', '$310', '$78' ),
					);
					foreach ( $itoi_pd_shrink as $itoi_pd_title => $itoi_pd_row ) :
						?>
						<div class="rounded-[10px] border border-line bg-white p-4">
							<div class="mb-3 text-xs font-bold"><?php echo esc_html( $itoi_pd_title ); ?></div>
							<table class="pd-data-table w-full border-collapse text-[10.5px]">
								<tr><td>Count</td><td><?php echo esc_html( $itoi_pd_row[0] ); ?></td></tr>
								<tr><td>Total</td><td><?php echo esc_html( $itoi_pd_row[1] ); ?></td></tr>
								<tr><td>Avg</td><td><?php echo esc_html( $itoi_pd_row[2] ); ?></td></tr>
							</table>
						</div>
					<?php endforeach; ?>
				</div>
				<div class="mb-3 rounded-[10px] border border-line bg-white p-4">
					<div class="mb-3 text-xs font-bold">Highest Risk Stores</div>
					<?php
					itoi_pd_risk_row( 'Park Centre', 78, 'high', 'Overall Risk: High' );
					itoi_pd_risk_row( 'Eastland', 52, 'med', 'Overall Risk: Medium' );
					itoi_pd_risk_row( 'Hyperdome', 24, '', 'Overall Risk: Low' );
					?>
				</div>
				<div class="rounded-[10px] border border-line bg-white p-4">
					<div class="mb-3 text-xs font-bold">Highest Risk Cashiers</div>
					<?php
					itoi_pd_risk_row( 'Register 4', 65, 'high', 'Overall Risk: High' );
					itoi_pd_risk_row( 'Register 2', 38, 'med', 'Overall Risk: Medium' );
					?>
				</div>
				<?php itoi_pd_illustrative_note(); ?>
			</div>

			<!-- ============ TAB 6 — BENCHMARKS ============ -->
			<div class="pd-panel" data-panel="benchmarks" role="tabpanel">
				<div class="mb-3 rounded-[10px] border border-line bg-white p-4">
					<div class="mb-3 flex items-center justify-between text-xs font-bold">
						Your Site vs. Similar Sites
						<span class="flex gap-2.5 text-[10px] font-semibold text-text-muted">
							<span class="inline-flex items-center gap-1"><i class="inline-block h-2 w-2 rounded-full bg-teal-700" aria-hidden="true"></i>Your Site</span>
							<span class="inline-flex items-center gap-1"><i class="inline-block h-2 w-2 rounded-full bg-signature" aria-hidden="true"></i>Benchmark Avg</span>
						</span>
					</div>
					<?php
					$itoi_pd_bench_special = array();
					foreach ( range( 0, 7 ) as $itoi_pd_i ) {
						if ( 1 === $itoi_pd_i % 2 ) {
							$itoi_pd_bench_special[ $itoi_pd_i ] = 'amber';
						}
					}
					itoi_pd_bars( array( 72, 58, 44, 63, 81, 66, 32, 41 ), $itoi_pd_bench_special );
					?>
				</div>
				<div class="overflow-x-auto rounded-[10px] border border-line bg-white p-4" tabindex="0" role="region" aria-label="Your Site vs. Similar Sites percentile table, scroll for more columns">
					<table class="pd-data-table w-full min-w-[420px] border-collapse text-[10.5px]">
						<tr><th>Metric</th><th>Your Site</th><th>Benchmark Avg</th><th>Percentile</th></tr>
						<tr><td>Conversion Rate</td><td>3.2%</td><td>2.7%</td><td class="pd-heat-4">78th</td></tr>
						<tr><td>ATV</td><td>$68</td><td>$71</td><td class="pd-heat-2">44th</td></tr>
						<tr><td>Shopper Yield</td><td>$217</td><td>$190</td><td class="pd-heat-4">81st</td></tr>
						<tr><td>Capture Rate</td><td>93.7%</td><td>88.2%</td><td class="pd-heat-3">66th</td></tr>
					</table>
				</div>
				<?php itoi_pd_illustrative_note(); ?>
			</div>

		</div>
	</div>
</div>
