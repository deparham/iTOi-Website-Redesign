<?php
/**
 * Team page (PROJECT.md §5). `team_member` is a non-public CPT with no
 * archive/single URL of its own (registered `public => false` in
 * inc/post-types.php) — it exists purely as data queried directly here.
 *
 * Both confirmed members (Sean Kiely, Michael Stark — PROJECT.md §4/§6)
 * are currently `draft` status themselves, same as this page, since
 * their bio/photo fields are still real placeholders pending real copy
 * (see NOTES.md). Queried with 'any' status rather than filtered to
 * `publish`, because the draft flag here means "profile incomplete,"
 * not "hide this confirmed person" — once this page is published, the
 * two members should appear honestly labeled, not silently vanish.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();
	?>

	<section class="border-b border-line bg-hero-bg px-8 pt-[168px] pb-section-md min-[640px]:pt-[206px]">
		<div class="mx-auto max-w-[840px]">
			<div class="mb-2 text-[13.5px] font-bold uppercase tracking-wide text-teal-800">Team</div>
			<h1 class="max-w-[20ch]"><?php the_title(); ?></h1>
		</div>
	</section>

	<?php
	/**
	 * Grouped by the `department` field (free text — any value an editor
	 * types creates its own section, no template change needed to add a
	 * new department). Management/IT/Marketing are pinned first in that
	 * order since they're the known departments as of 2026-07-28; any
	 * other department an editor adds later is appended alphabetically
	 * after them. Members with no department set land in a trailing
	 * "Team" catch-all rather than being silently dropped.
	 */
	$itoi_team_query = new WP_Query(
		array(
			'post_type'      => 'team_member',
			'posts_per_page' => -1,
			'post_status'    => 'any',
			'orderby'        => 'title',
			'order'          => 'ASC',
		)
	);

	$itoi_dept_order  = array( 'Management', 'IT', 'Marketing' );
	$itoi_by_dept     = array();
	if ( $itoi_team_query->have_posts() ) {
		while ( $itoi_team_query->have_posts() ) {
			$itoi_team_query->the_post();
			$itoi_dept = get_field( 'department' ) ?: 'Team';
			if ( ! isset( $itoi_by_dept[ $itoi_dept ] ) ) {
				$itoi_by_dept[ $itoi_dept ] = array();
			}
			$itoi_by_dept[ $itoi_dept ][] = get_the_ID();
		}
		wp_reset_postdata();
	}

	$itoi_dept_extra = array_diff( array_keys( $itoi_by_dept ), $itoi_dept_order );
	sort( $itoi_dept_extra );
	$itoi_dept_final_order = array_merge( $itoi_dept_order, $itoi_dept_extra );
	?>

	<?php if ( ! empty( $itoi_by_dept ) ) : ?>
		<?php foreach ( $itoi_dept_final_order as $itoi_dept_name ) : ?>
			<?php if ( empty( $itoi_by_dept[ $itoi_dept_name ] ) ) : continue; endif; ?>
			<section class="px-8 py-section-md">
				<div class="mx-auto max-w-[1280px]">
					<h2 class="mb-8 text-2xl"><?php echo esc_html( $itoi_dept_name ); ?></h2>
					<div class="grid grid-cols-1 gap-6 min-[640px]:grid-cols-2 min-[980px]:grid-cols-3">
						<?php foreach ( $itoi_by_dept[ $itoi_dept_name ] as $itoi_member_id ) :
							$itoi_name     = get_field( 'name', $itoi_member_id ) ?: get_the_title( $itoi_member_id );
							$itoi_role     = get_field( 'role', $itoi_member_id );
							$itoi_bio      = get_field( 'bio', $itoi_member_id );
							$itoi_email    = get_field( 'email', $itoi_member_id );
							$itoi_li       = get_field( 'linkedin_url', $itoi_member_id );
							$itoi_photo_id = get_field( 'photo', $itoi_member_id );
							$itoi_photo    = $itoi_photo_id ? wp_get_attachment_image_url( $itoi_photo_id, 'team-photo' ) : '';
							$itoi_video    = get_field( 'video', $itoi_member_id );
							$itoi_member_media = itoi_media_cover( $itoi_photo, $itoi_video, $itoi_name, 'absolute inset-0 h-full w-full object-cover' );
							?>
							<div class="rounded-2xl border border-line bg-white p-6">
								<!-- Liquid glass, wave 5 (see NOTES.md): name/role moved off
								     the card body onto a glass badge over the photo itself —
								     same pattern as single-solution.php's capability-name
								     flip-card badges (wave 3), including the dark bottom-fade
								     gradient behind it for legibility, applied here regardless
								     of whether the photo is real or still the placeholder
								     gradient (see NOTES.md — real bios/photos not supplied
								     yet, but the badge should already be correct once they
								     are). Small pill only, not a whole-card conversion — bio/
								     email/LinkedIn below are untouched. -->
								<div class="relative mb-4 aspect-square w-full overflow-hidden rounded-xl bg-[linear-gradient(135deg,#e2e7ee,#cfd7e0)] after:absolute after:inset-0 after:bg-[linear-gradient(to_top,rgba(14,17,22,0.55)_0%,rgba(14,17,22,0)_45%)]">
									<?php if ( $itoi_member_media ) : ?>
										<?php echo $itoi_member_media; ?>
									<?php else : ?>
										<div class="absolute inset-0 flex items-center justify-center text-center text-[9px] uppercase tracking-[0.06em] text-[#8f99a6]">Photo TODO</div>
									<?php endif; ?>
									<div class="team-badge-glass absolute bottom-2.5 left-2.5 z-[2] max-w-[calc(100%-20px)] rounded-lg px-3 py-2">
										<h3 class="m-0 text-[15px] font-extrabold leading-tight"><?php echo esc_html( $itoi_name ); ?></h3>
										<?php if ( $itoi_role ) : ?>
											<div class="mt-0.5 text-[12.5px] font-semibold leading-tight"><?php echo esc_html( $itoi_role ); ?></div>
										<?php endif; ?>
									</div>
								</div>
								<?php if ( $itoi_bio ) : ?>
									<p class="mb-4 text-[14.5px] text-text-muted"><?php echo esc_html( $itoi_bio ); ?></p>
								<?php endif; ?>
								<div class="flex flex-wrap gap-2.5">
									<?php if ( $itoi_email ) : ?>
										<a href="mailto:<?php echo esc_attr( $itoi_email ); ?>" class="rounded-full border border-line px-4 py-2 text-[13px] font-semibold hover:border-ink">Email</a>
									<?php endif; ?>
									<?php if ( $itoi_li ) : ?>
										<a href="<?php echo esc_url( $itoi_li ); ?>" target="_blank" rel="noopener" class="rounded-full border border-line px-4 py-2 text-[13px] font-semibold hover:border-ink">LinkedIn</a>
									<?php endif; ?>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			</section>
		<?php endforeach; ?>
	<?php else : ?>
		<section class="px-8 py-section-md">
			<div class="mx-auto max-w-[1280px]">
				<p class="text-text-muted">No team members published yet.</p>
			</div>
		</section>
	<?php endif; ?>

<?php
endwhile;

get_footer();
