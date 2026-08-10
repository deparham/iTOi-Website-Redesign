<?php
/**
 * Homepage. Ground truth: preview-verkada-match.html.
 *
 * Thin orchestrator — each section's markup/data-prep lives in its own
 * template-parts/home/*.php file (split out 2026-08-06; see NOTES.md).
 * Section order here is the one real thing this file controls; each part
 * itself already handles its own "nothing to show" cases (Products/Delivery
 * Model return early with no query results, Traffic-Demo returns early when
 * its Site Settings toggle is off).
 *
 * @package ITOI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<?php get_template_part( 'template-parts/home/hero' ); ?>
<?php get_template_part( 'template-parts/home/trust-credibility' ); ?>
<?php get_template_part( 'template-parts/home/products' ); ?>
<?php get_template_part( 'template-parts/home/how-it-works' ); ?>
<?php get_template_part( 'template-parts/home/core-outcomes' ); ?>
<?php get_template_part( 'template-parts/home/integrated-platform' ); ?>
<?php get_template_part( 'template-parts/home/traffic-demo' ); ?>
<?php get_template_part( 'template-parts/home/why-choose' ); ?>
<?php get_template_part( 'template-parts/home/industries' ); ?>
<?php get_template_part( 'template-parts/home/delivery-model' ); ?>
<?php get_template_part( 'template-parts/home/final-cta' ); ?>

<?php
get_footer();
