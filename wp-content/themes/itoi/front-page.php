<?php
/**
 * Homepage. Ground truth: preview-verkada-match.html.
 *
 * Thin orchestrator — each section's markup/data-prep lives in its own
 * template-parts/home/*.php file (split out 2026-08-06; see NOTES.md).
 * Section order here is the one real thing this file controls; each part
 * itself already handles its own "nothing to show" cases (Use Cases
 * returns early with no query results, Traffic-Demo returns early when
 * its Site Settings toggle is off).
 *
 * 2026-08-21: the "Meet Our Products" section (template-parts/home/products.php,
 * 2 real `product` posts) replaced by the "Real Use Cases" flip-card
 * carousel (template-parts/home/use-cases.php, 7 real featured use cases)
 * — explicit instruction, not a parallel addition. products.php and its
 * carousel JS/CSS removed as dead code, not just unlinked.
 *
 * 2026-08-24: "Delivery Model" (the 4-step "Supply & Stage / Install &
 * Integrate / Support / Optimize" rail) removed sitewide — explicit
 * instruction. template-parts/home/delivery-model.php, its Delivery
 * Model options page/ACF group, and its .delivery-rail/.delivery-step*
 * CSS (src/tailwind.css) all removed as dead code, same as products.php
 * above — not left unlinked.
 *
 * 2026-08-24 (same day): "Video Showcase" added — full-bleed background
 * photo/video + dot-grid overlay + "Play video" lightbox.
 * template-parts/home/video-showcase.php.
 *
 * 2026-08-26: swapped with Trust & Stats — Video Showcase is now the
 * section directly after the hero, Trust & Stats ("proof point") third.
 * Explicit instruction; no content/logic change to either section itself.
 *
 * 2026-08-26: "Our Partners" (template-parts/partners.php) moved here from
 * footer.php, where it rendered sitewide on every page — homepage-only
 * content, so it belongs in this file's section list, not the global
 * footer. Its carousel JS (initPartnersCarousel()) moved out of
 * assets/js/core.js into assets/js/homepage.js for the same reason (see
 * that function's comment).
 *
 * 2026-09-01: "Door Reveal" (template-parts/home/hero-door-reveal.php)
 * takes the slot directly after the hero. Unlike the products/delivery-model
 * replacements above, the section it displaces — Video Showcase — was
 * explicitly NOT removed: it keeps its template, its ACF fields and its
 * uploaded video, and is simply switched off by default behind a new Site
 * Settings toggle (show_video_showcase). It still renders here, in its
 * original position below the new section, the moment that's switched back
 * on — same "kept as a real toggle rather than deleted" treatment
 * traffic-demo.php already had.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<?php get_template_part( 'template-parts/home/hero' ); ?>
<?php get_template_part( 'template-parts/home/hero-door-reveal' ); ?>
<?php get_template_part( 'template-parts/home/video-showcase' ); ?>
<?php get_template_part( 'template-parts/home/trust-credibility' ); ?>
<?php get_template_part( 'template-parts/home/industries' ); ?>
<?php get_template_part( 'template-parts/home/use-cases' ); ?>
<?php get_template_part( 'template-parts/home/core-outcomes' ); ?>
<?php get_template_part( 'template-parts/home/integrated-platform' ); ?>
<?php get_template_part( 'template-parts/home/traffic-demo' ); ?>
<?php get_template_part( 'template-parts/home/why-choose' ); ?>
<?php get_template_part( 'template-parts/partners' ); ?>

<?php
get_footer();
