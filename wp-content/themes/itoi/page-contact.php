<?php
/**
 * Contact page (PROJECT.md §5) — contact details pulled from the Site
 * Settings options page (never hardcoded, per §4), plus the active
 * Contact Form 7 form (§2's required form plugin).
 *
 * The existing CF7 form (ID 24, "Contact form 1") is a real, working,
 * pre-configured form carried over from the live site's WP install —
 * it already has mail routing/autoresponder/Flamingo logging wired up.
 * Its own markup uses old Bootstrap grid/class names (`row`, `col-md-6`,
 * `form-control`, `btn-secondary`) that don't exist in this Tailwind
 * build, so it's restyled here via scoped CSS targeting those exact
 * classes rather than editing the form's stored config — that keeps
 * the working mail setup untouched and only changes presentation.
 *
 * Liquid glass wave 6, 2026-07-28 (see NOTES.md): aurora-bg-light on the
 * main content section. The 4 contact-info groups (Head office, Management,
 * Support, Office hours) had no card shape before this (a single plain
 * <dl>, no per-item border/background) — wrapped each in its own glass
 * card, same "add the missing card shape, then glass it" precedent used
 * throughout this wave. The form's own outer card (`.itoi-contact-form`)
 * also becomes glass; the CF7 form's actual inputs/textarea/button stay on
 * their existing solid `.form-control`/`.btn-secondary` styling, untouched
 * — same rule as the Glossary search input in the earlier test wave.
 *
 * @package ITOI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$itoi_address       = get_field( 'company_address', 'option' );
$itoi_mgr1_name     = get_field( 'manager_1_name', 'option' );
$itoi_mgr1_email    = get_field( 'manager_1_email', 'option' );
$itoi_mgr1_phone    = get_field( 'manager_1_phone', 'option' );
$itoi_mgr2_name     = get_field( 'manager_2_name', 'option' );
$itoi_mgr2_email    = get_field( 'manager_2_email', 'option' );
$itoi_support_phone = get_field( 'support_phone', 'option' );
$itoi_support_email = get_field( 'support_email', 'option' );
$itoi_office_hours  = get_field( 'office_hours', 'option' );
$itoi_support_hours = get_field( 'support_hours', 'option' );

while ( have_posts() ) :
	the_post();
	?>

	<section class="border-b border-line bg-hero-bg px-8 pt-[168px] pb-16 min-[640px]:pt-[206px] min-[980px]:pb-[70px]">
		<div class="mx-auto max-w-[840px]">
			<div class="mb-2 text-[13.5px] font-bold uppercase tracking-wide text-teal-800">Contact</div>
			<h1 class="max-w-[20ch]"><?php the_title(); ?></h1>
		</div>
	</section>

	<section class="aurora-bg-light px-8 py-16 min-[980px]:py-[70px]">
		<div class="mx-auto grid max-w-[1280px] gap-12 min-[980px]:grid-cols-[1fr_1.1fr]">
			<div>
				<h2 class="mb-6 text-2xl">Get in touch</h2>
				<dl class="space-y-4 text-[14.5px]">
					<?php if ( $itoi_address ) : ?>
						<div class="glass-element-light rounded-2xl p-5">
							<dt class="mb-1 text-[12.5px] font-bold uppercase tracking-wide text-text-muted">Head office</dt>
							<dd><?php echo esc_html( $itoi_address ); ?></dd>
						</div>
					<?php endif; ?>
					<?php if ( $itoi_mgr1_name || $itoi_mgr2_name ) : ?>
						<div class="glass-element-light rounded-2xl p-5">
							<dt class="mb-1 text-[12.5px] font-bold uppercase tracking-wide text-text-muted">Management</dt>
							<dd>
								<?php if ( $itoi_mgr1_name ) : ?>
									<div><?php echo esc_html( $itoi_mgr1_name ); ?>
									<?php
									if ( $itoi_mgr1_email ) :
										?>
										&mdash; <a class="underline" href="mailto:<?php echo esc_attr( $itoi_mgr1_email ); ?>"><?php echo esc_html( $itoi_mgr1_email ); ?></a><?php endif; ?>
										<?php
										if ( $itoi_mgr1_phone ) :
											?>
										&mdash; <?php echo esc_html( $itoi_mgr1_phone ); ?><?php endif; ?></div>
								<?php endif; ?>
								<?php if ( $itoi_mgr2_name ) : ?>
									<div><?php echo esc_html( $itoi_mgr2_name ); ?>
									<?php
									if ( $itoi_mgr2_email ) :
										?>
										&mdash; <a class="underline" href="mailto:<?php echo esc_attr( $itoi_mgr2_email ); ?>"><?php echo esc_html( $itoi_mgr2_email ); ?></a><?php endif; ?></div>
								<?php endif; ?>
							</dd>
						</div>
					<?php endif; ?>
					<?php if ( $itoi_support_email || $itoi_support_phone ) : ?>
						<div class="glass-element-light rounded-2xl p-5">
							<dt class="mb-1 text-[12.5px] font-bold uppercase tracking-wide text-text-muted">Support</dt>
							<dd>
								<?php
								if ( $itoi_support_phone ) :
									?>
									<?php echo esc_html( $itoi_support_phone ); ?><br><?php endif; ?>
								<?php
								if ( $itoi_support_email ) :
									?>
									<a class="underline" href="mailto:<?php echo esc_attr( $itoi_support_email ); ?>"><?php echo esc_html( $itoi_support_email ); ?></a><?php endif; ?>
								<?php
								if ( $itoi_support_hours ) :
									?>
									<div class="mt-1 text-text-muted"><?php echo esc_html( $itoi_support_hours ); ?></div><?php endif; ?>
							</dd>
						</div>
					<?php endif; ?>
					<?php if ( $itoi_office_hours ) : ?>
						<div class="glass-element-light rounded-2xl p-5">
							<dt class="mb-1 text-[12.5px] font-bold uppercase tracking-wide text-text-muted">Office hours</dt>
							<dd><?php echo esc_html( $itoi_office_hours ); ?></dd>
						</div>
					<?php endif; ?>
				</dl>
			</div>

			<div>
				<h2 class="mb-6 text-2xl">Send a message</h2>
				<div class="itoi-contact-form glass-element-light rounded-2xl p-6 min-[640px]:p-8">
					<?php echo do_shortcode( '[contact-form-7 id="24" title="Contact form 1"]' ); ?>
				</div>
			</div>
		</div>
	</section>

	<style>
		.itoi-contact-form .row { display: flex; flex-wrap: wrap; gap: 1rem; margin: 0; }
		.itoi-contact-form .row > div { padding: 0; flex: 1 1 100%; }
		.itoi-contact-form .row > .col-md-6 { flex: 1 1 220px; }
		.itoi-contact-form label { display: block; margin-bottom: 0.375rem; font-size: 13px; font-weight: 700; }
		.itoi-contact-form .required { color: var(--signature-dim); margin-left: 2px; }
		/* Liquid glass wave 6: explicit solid background — Tailwind's preflight
			resets form elements to background-color:transparent, so these
			inputs only ever looked solid because the old parent card was
			opaque bg-white. The parent (.itoi-contact-form) is now
			.glass-element-light (translucent + blurred), so without this an
			input would show the blurred glass through it — the one thing
			this wave's own rule says must never happen to a real control. */
		.itoi-contact-form input.form-control,
		.itoi-contact-form textarea.form-control { width: 100%; background: #fff; border: 1px solid var(--line); border-radius: 12px; padding: 0.7rem 0.9rem; font-size: 14.5px; font-family: inherit; }
		.itoi-contact-form input.form-control:focus,
		.itoi-contact-form textarea.form-control:focus { outline: 2px solid var(--ink); outline-offset: 1px; }
		.itoi-contact-form .btn.btn-secondary { background: var(--cta); color: #fff; border: none; border-radius: 9999px; padding: 0.75rem 1.5rem; font-size: 14px; font-weight: 700; cursor: pointer; transition: background-color .2s; }
		.itoi-contact-form .btn.btn-secondary:hover { background: var(--cta-hover); }
		.itoi-contact-form .wpcf7-not-valid-tip { margin-top: 0.375rem; font-size: 12.5px; color: var(--signature-dim); }
		.itoi-contact-form .wpcf7-response-output { margin-top: 1.25rem; border-radius: 12px; border: 1px solid var(--line); padding: 0.75rem 1rem; font-size: 13.5px; }
	</style>

	<?php
endwhile;

get_footer();
