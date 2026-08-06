<?php
/**
 * "Edit All Use Cases" — a single wp-admin screen that lists every
 * `use_case` post with its title/photo/video editable inline and saves all
 * of them in one submit. WordPress's own Quick Edit / Bulk Edit only apply
 * one shared value to every selected post, which doesn't work here since
 * each of the 42 rows needs its own title/photo/video — this is a genuine
 * custom screen, not a core feature. Industry/Solution/Featured stay
 * single-post-only (edit_item screen) since only content+media was asked
 * for here; shown read-only per row so each row is still identifiable.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function itoi_use_case_bulk_edit_menu() {
	$itoi_hook = add_submenu_page(
		'edit.php?post_type=use_case',
		'Edit All Use Cases',
		'Edit All',
		'edit_posts',
		'itoi-use-case-bulk-edit',
		'itoi_use_case_bulk_edit_render'
	);

	// Form processing has to happen on the `load-` hook (before any admin
	// header HTML is sent) so wp_safe_redirect() after saving actually
	// works — doing it inside the render callback itself would be too late.
	add_action( 'load-' . $itoi_hook, 'itoi_use_case_bulk_edit_save' );
	add_action( 'load-' . $itoi_hook, 'itoi_use_case_bulk_edit_enqueue' );
}
add_action( 'admin_menu', 'itoi_use_case_bulk_edit_menu' );

function itoi_use_case_bulk_edit_enqueue() {
	wp_enqueue_media();
	wp_enqueue_script(
		'itoi-use-case-bulk-edit',
		ITOI_THEME_URI . '/assets/js/admin-use-case-bulk-edit.js',
		array(),
		ITOI_THEME_VERSION,
		true
	);
}

/**
 * @return array[] Ordered like the front end (itoi_get_industry_use_cases()) —
 *                  menu_order groups them by industry already (2026-07-30
 *                  migration set it that way), status 'any' so drafts show
 *                  up here too, not just published ones.
 */
function itoi_use_case_bulk_edit_get_posts() {
	return get_posts(
		array(
			'post_type'      => 'use_case',
			'post_status'    => array( 'publish', 'draft', 'pending' ),
			'posts_per_page' => -1,
			'orderby'        => 'menu_order title',
			'order'          => 'ASC',
		)
	);
}

function itoi_use_case_bulk_edit_save() {
	if ( 'POST' !== ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) {
		return;
	}

	if ( ! isset( $_POST['itoi_use_case_bulk_nonce'] )
		|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['itoi_use_case_bulk_nonce'] ) ), 'itoi_use_case_bulk_edit' ) ) {
		wp_die( esc_html__( 'Security check failed — please go back and try again.', 'itoi' ) );
	}

	if ( ! current_user_can( 'edit_posts' ) ) {
		wp_die( esc_html__( 'You do not have permission to do this.', 'itoi' ) );
	}

	$itoi_rows = isset( $_POST['use_case'] ) && is_array( $_POST['use_case'] ) ? wp_unslash( $_POST['use_case'] ) : array();

	foreach ( $itoi_rows as $itoi_post_id => $itoi_fields ) {
		$itoi_post_id = (int) $itoi_post_id;

		if ( 'use_case' !== get_post_type( $itoi_post_id ) || ! current_user_can( 'edit_post', $itoi_post_id ) ) {
			continue;
		}

		wp_update_post(
			array(
				'ID'         => $itoi_post_id,
				'post_title' => sanitize_text_field( $itoi_fields['title'] ?? '' ),
			)
		);

		update_field( 'photo', (int) ( $itoi_fields['photo'] ?? 0 ), $itoi_post_id );
		update_field( 'video', (int) ( $itoi_fields['video'] ?? 0 ), $itoi_post_id );
	}

	wp_safe_redirect(
		add_query_arg(
			array(
				'post_type'  => 'use_case',
				'page'       => 'itoi-use-case-bulk-edit',
				'itoi_saved' => 1,
			),
			admin_url( 'edit.php' )
		)
	);
	exit;
}

function itoi_use_case_bulk_edit_render() {
	if ( ! current_user_can( 'edit_posts' ) ) {
		wp_die( esc_html__( 'You do not have permission to access this page.', 'itoi' ) );
	}

	$itoi_posts = itoi_use_case_bulk_edit_get_posts();
	?>
	<div class="wrap">
		<h1>Edit All Use Cases</h1>
		<p>Title, photo and video for every use case, saved together. To change which Industry or Solution a use case belongs to, or its "Featured in nav" flag, still open that one individually — <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=use_case' ) ); ?>">back to the Use Cases list</a>.</p>

		<?php if ( isset( $_GET['itoi_saved'] ) ) : ?>
			<div class="notice notice-success is-dismissible"><p>Saved.</p></div>
		<?php endif; ?>

		<?php if ( empty( $itoi_posts ) ) : ?>
			<p>No use cases yet.</p>
		<?php else : ?>
			<form method="post" action="<?php echo esc_url( admin_url( 'edit.php?post_type=use_case&page=itoi-use-case-bulk-edit' ) ); ?>">
				<?php wp_nonce_field( 'itoi_use_case_bulk_edit', 'itoi_use_case_bulk_nonce' ); ?>

				<div class="itoi-uc-bulk-grid">
					<?php
					$itoi_current_industry = null;
					foreach ( $itoi_posts as $itoi_post ) :
						$itoi_industry_id   = get_field( 'industry', $itoi_post->ID );
						$itoi_industry_name = $itoi_industry_id ? get_the_title( $itoi_industry_id ) : '—';
						$itoi_solution_id   = get_field( 'solution', $itoi_post->ID );
						$itoi_solution_name = $itoi_solution_id ? get_the_title( $itoi_solution_id ) : '—';

						if ( $itoi_industry_name !== $itoi_current_industry ) :
							$itoi_current_industry = $itoi_industry_name;
							?>
							<h2 class="itoi-uc-bulk-industry-heading"><?php echo esc_html( $itoi_current_industry ); ?></h2>
						<?php endif; ?>

						<?php
						$itoi_photo_id   = get_field( 'photo', $itoi_post->ID );
						$itoi_photo_url  = $itoi_photo_id ? wp_get_attachment_image_url( $itoi_photo_id, 'thumbnail' ) : '';
						$itoi_video      = get_field( 'video', $itoi_post->ID );
						$itoi_video_id   = ! empty( $itoi_video['ID'] ) ? $itoi_video['ID'] : ( ! empty( $itoi_video['id'] ) ? $itoi_video['id'] : 0 );
						$itoi_video_name = ! empty( $itoi_video['filename'] ) ? $itoi_video['filename'] : '';
						$itoi_row_key    = 'uc-' . $itoi_post->ID;
						?>
						<div class="itoi-uc-bulk-row">
							<div class="itoi-uc-bulk-title-col">
								<label for="title-<?php echo esc_attr( $itoi_row_key ); ?>">Title</label>
								<input type="text" id="title-<?php echo esc_attr( $itoi_row_key ); ?>" name="use_case[<?php echo (int) $itoi_post->ID; ?>][title]" value="<?php echo esc_attr( get_the_title( $itoi_post ) ); ?>" class="widefat">
								<p class="description">Solution: <?php echo esc_html( $itoi_solution_name ); ?></p>
							</div>

							<div class="itoi-uc-bulk-media-col">
								<span class="itoi-uc-bulk-media-label">Photo</span>
								<div class="itoi-uc-bulk-media-preview" id="preview-photo-<?php echo esc_attr( $itoi_row_key ); ?>">
									<?php if ( $itoi_photo_url ) : ?>
										<img src="<?php echo esc_url( $itoi_photo_url ); ?>" alt="">
									<?php endif; ?>
								</div>
								<input type="hidden" id="input-photo-<?php echo esc_attr( $itoi_row_key ); ?>" name="use_case[<?php echo (int) $itoi_post->ID; ?>][photo]" value="<?php echo (int) $itoi_photo_id; ?>">
								<button type="button" class="button itoi-media-select" data-media-type="image" data-target="photo-<?php echo esc_attr( $itoi_row_key ); ?>">Select</button>
								<button type="button" class="button-link itoi-media-remove" data-target="photo-<?php echo esc_attr( $itoi_row_key ); ?>">Remove</button>
							</div>

							<div class="itoi-uc-bulk-media-col">
								<span class="itoi-uc-bulk-media-label">Video</span>
								<div class="itoi-uc-bulk-media-preview itoi-uc-bulk-media-preview-text" id="preview-video-<?php echo esc_attr( $itoi_row_key ); ?>"><?php echo esc_html( $itoi_video_name ); ?></div>
								<input type="hidden" id="input-video-<?php echo esc_attr( $itoi_row_key ); ?>" name="use_case[<?php echo (int) $itoi_post->ID; ?>][video]" value="<?php echo (int) $itoi_video_id; ?>">
								<button type="button" class="button itoi-media-select" data-media-type="video" data-target="video-<?php echo esc_attr( $itoi_row_key ); ?>">Select</button>
								<button type="button" class="button-link itoi-media-remove" data-target="video-<?php echo esc_attr( $itoi_row_key ); ?>">Remove</button>
							</div>
						</div>
					<?php endforeach; ?>
				</div>

				<?php submit_button( 'Save all' ); ?>
			</form>
		<?php endif; ?>
	</div>
	<style>
		.itoi-uc-bulk-industry-heading { margin-top: 28px; }
		.itoi-uc-bulk-row { display: grid; grid-template-columns: 1fr 200px 200px; gap: 20px; align-items: start; padding: 16px 0; border-bottom: 1px solid #dcdcde; }
		.itoi-uc-bulk-title-col label { font-weight: 600; display: block; margin-bottom: 4px; }
		.itoi-uc-bulk-media-label { font-weight: 600; display: block; margin-bottom: 4px; }
		.itoi-uc-bulk-media-preview { min-height: 60px; margin-bottom: 6px; }
		.itoi-uc-bulk-media-preview img { max-width: 80px; max-height: 80px; object-fit: cover; display: block; border: 1px solid #dcdcde; }
		.itoi-uc-bulk-media-preview-text { font-size: 12px; color: #646970; word-break: break-all; }
		.itoi-uc-bulk-media-col .button { margin-right: 6px; }
	</style>
	<?php
}
