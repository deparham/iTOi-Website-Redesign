<?php
/**
 * ITOI theme bootstrap. Stays thin — logic lives in inc/.
 *
 * @package ITOI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'ITOI_THEME_VERSION', '0.1.0' );
define( 'ITOI_THEME_DIR', get_template_directory() );
define( 'ITOI_THEME_URI', get_template_directory_uri() );

require ITOI_THEME_DIR . '/inc/post-types.php';
require ITOI_THEME_DIR . '/inc/theme-setup.php';
require ITOI_THEME_DIR . '/inc/enqueue.php';
require ITOI_THEME_DIR . '/inc/acf.php';
require ITOI_THEME_DIR . '/inc/nav-walker.php';
require ITOI_THEME_DIR . '/inc/schema.php';
require ITOI_THEME_DIR . '/inc/redirects.php';
require ITOI_THEME_DIR . '/inc/reveal.php';
require ITOI_THEME_DIR . '/inc/longform-icons.php';
require ITOI_THEME_DIR . '/inc/solution-spec-icons.php';
require ITOI_THEME_DIR . '/inc/platform-demo.php';
require ITOI_THEME_DIR . '/inc/education.php';
require ITOI_THEME_DIR . '/inc/use-cases.php';
require ITOI_THEME_DIR . '/inc/use-case-bulk-edit.php';
require ITOI_THEME_DIR . '/inc/solution-builder.php';
require ITOI_THEME_DIR . '/inc/highlight-panel.php';
require ITOI_THEME_DIR . '/inc/media.php';
require ITOI_THEME_DIR . '/inc/customers-section.php';
require ITOI_THEME_DIR . '/inc/process-diagram.php';
require ITOI_THEME_DIR . '/inc/template-helpers.php';
require ITOI_THEME_DIR . '/inc/home-icons.php';
require ITOI_THEME_DIR . '/inc/security.php';
