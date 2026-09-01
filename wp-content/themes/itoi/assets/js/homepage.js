/**
 * ITOI homepage-only interactivity: mega-hero slideshow, products carousel,
 * traffic-demo widget, Why Choose ITOI tabs, Industries carousel,
 * trust-metrics display, and the platform-demo modal.
 * Enqueued only on the front page (inc/enqueue.php, is_front_page()).
 *
 * Split out of the single assets/js/main.js 2026-08-06 (JS bundle split,
 * see NOTES.md) — same functions, same behavior. See assets/js/core.js's
 * header comment for the full split rationale.
 */
(function () {
	'use strict';

	var reduceMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

	// 2026-08-05 (external improvement plan Phase 4.3/5.5): mega-hero
	// background video prefers a static image — either for a reduce-motion
	// visitor (existing behavior) or on a narrow viewport, where a looping
	// autoplay video is a real, avoidable data/battery cost for a visitor
	// who's more likely on a metered mobile connection. Checked once at
	// load, same convention as reduceMotion above (not re-evaluated on
	// resize/orientation-change) — this is a decision made for the
	// pageview, not something that should flip mid-visit.
	var heroPrefersStaticMedia = reduceMotion || window.matchMedia( '(max-width: 639px)' ).matches;

	// Step interval for the hero's page-load stagger reveal — see
	// initHeroPageLoadReveal() below.
	var HERO_PAGELOAD_STEP = 140;

	document.addEventListener( 'DOMContentLoaded', function () {
		initHero();
		initUseCasesCarousel();
		initTrafficWidget();
		initWhyChooseTabs();
		initIndustriesCarousel();
		initTrustSectionReveal();
		initTrustLogoRotation();
		initTrustMetricsRotation();
		initPlatformDemo();
		initVideoShowcase();
		initPartnersCarousel();
	} );

	// ---------------------------------------------------------------
	// 2. Mega-hero: headline/sub slideshow + dot-nav
	// 2026-07-23 correction: a prior session incorrectly removed the
	// slideshow/dot-nav entirely (kept only the Live Detection boxes, since
	// removed 2026-08-26) while only a height/padding reduction had
	// actually been requested. Restored here — see NOTES.md for both the
	// original (incorrect) removal entry and this correction. Slide
	// content now comes from the ACF `hero_slides` repeater (localized as
	// `itoiHeroSlides`, see inc/enqueue.php) instead of being hardcoded in
	// this file — the old pre-removal version had this same array
	// hardcoded here and the field existed but was never actually read by
	// any template; that gap is closed properly this time, not
	// reintroduced.
	// ---------------------------------------------------------------
	function initHero() {
		initHeroSlideshow();
		initHeroStaticMediaGuard();
		initHeroPageLoadReveal();
	}

	// ---------------------------------------------------------------
	// Hero page-load stagger reveal, 2026-08-21 — NOT scroll-triggered
	// (the hero is already in view at page load); fires immediately.
	// Steps 1-4 (nav bar, headline, subcopy, CTA row) share one ordered
	// .itoi-stagger-item group via the generic itoiStaggerReveal()
	// primitive (assets/js/core.js). A 5th step (the Live Detection boxes)
	// used to fade in separately here — removed 2026-08-26 along with the
	// boxes themselves, so this is just the 4-step sequence now.
	// #siteHeaderFixed (step 1) only carries .itoi-stagger-item on the
	// front page (header.php, is_front_page() check) — safe to always
	// include it here since this whole file only ever loads on the front
	// page too.
	// ---------------------------------------------------------------
	function initHeroPageLoadReveal() {
		var items = [
			document.getElementById( 'siteHeaderFixed' ),
			document.getElementById( 'heroHeadline' ),
			document.getElementById( 'heroSub' ),
			document.getElementById( 'heroCtaRow' ),
		];
		window.itoiStaggerReveal( items, { trigger: 'load', step: HERO_PAGELOAD_STEP } );
	}

	// Hardcoded fallback only — used if the localized itoiHeroSlides array
	// is empty (e.g. the options page has never been saved), same
	// defensive pattern as other localized data elsewhere in this file.
	// 2026-08-05 ("10/10" pass, see NOTES.md): trimmed from 4 entries to 2,
	// kept in sync with acf-json's default_value — a single definitive
	// positioning slide plus the RetailNext partnership slide, not a
	// rotating set of unrelated messages.
	var heroSlidesFallback = [
		{
			headline: 'Turn what happens across your sites into decisions you can act on.',
			subcopy: 'Connect cameras, sensors and operational systems in one intelligent platform for security, analytics and automation.',
		},
		{
			headline: 'RetailNext × iTOi Solutions',
			subcopy: "Powered by RetailNext's proven retail analytics platform, deployed and supported by ITOI.",
			isPartnership: true,
			partnerName: 'RetailNext',
		},
	];

	function initHeroSlideshow() {
		var dotNav = document.getElementById( 'dotNav' );
		var heroHeadline = document.getElementById( 'heroHeadline' );
		var heroSub = document.getElementById( 'heroSub' );
		if ( ! dotNav || ! heroHeadline || ! heroSub ) {
			return;
		}

		var heroSlides = ( window.itoiHeroSlides && window.itoiHeroSlides.length ) ? window.itoiHeroSlides : heroSlidesFallback;
		if ( ! heroSlides.length ) {
			return;
		}

		// 2026-08-21 — active slide renders as a small numbered circular
		// badge in place of the plain dot (spec: "small circular badge
		// with the slide number, replacing the dot at that position").
		// Both .dot-mark (plain dot) and .dot-number (numbered badge) are
		// always in the DOM per button, one hidden — goToHeroSlide() below
		// just toggles which is hidden, rather than rebuilding innerHTML on
		// every slide change. The progress ring is unchanged (still keyed
		// off the .active class via the itoi-ringfill CSS keyframe,
		// src/tailwind.css) and now visually wraps the numbered badge
		// instead of the plain dot.
		heroSlides.forEach( function ( slide, i ) {
			var btn = document.createElement( 'button' );
			btn.className = 'dot-btn relative flex h-[30px] w-[30px] items-center justify-center' + ( 0 === i ? ' active' : '' );
			btn.setAttribute( 'aria-label', 'Hero slide ' + ( i + 1 ) + ' of ' + heroSlides.length );
			btn.innerHTML =
				'<span class="dot-mark h-1.5 w-1.5 rounded-full transition-all duration-300 ' + ( 0 === i ? 'hidden' : 'bg-white/40' ) + '"></span>' +
				'<span class="dot-number flex h-5 w-5 items-center justify-center rounded-full border border-white/80 text-[10px] font-bold text-white ' + ( 0 === i ? '' : 'hidden' ) + '">' + ( i + 1 ) + '</span>' +
				'<svg class="pointer-events-none absolute inset-0" viewBox="0 0 30 30" width="30" height="30">' +
				'<circle class="ring" cx="15" cy="15" r="15" fill="none" stroke="#fff" stroke-width="2" stroke-opacity="0.9" stroke-dasharray="94.2" stroke-dashoffset="94.2" style="transform:rotate(-90deg);transform-origin:50% 50%;"></circle>' +
				'</svg>';
			btn.addEventListener( 'click', function () {
				goToHeroSlide( i );
			} );
			dotNav.appendChild( btn );
		} );

		var dotBtns = dotNav.querySelectorAll( '.dot-btn' );
		var heroIdx = 0;
		var heroTimer;

		// ---------------------------------------------------------------
		// 2026-08-05 (external improvement plan Phase 4.5) — pause controls
		// for the auto-advancing dot-nav. Previously the only stop
		// condition was prefers-reduced-motion (checked once, whole
		// mechanic skipped). Added: pause while the browser tab is hidden,
		// pause while the hero has scrolled out of view, pause on
		// hover/keyboard-focus of the dot-nav itself, and an accessible
		// pause/play toggle button next to the dots — all independent
		// flags ORed into one shouldAutoAdvance() check, restored the
		// instant every blocking condition clears (e.g. switching back to
		// this tab resumes advancing immediately, not just eligible to).
		// No live-region announcement of slide changes — deliberately
		// omitted, per the plan's own "avoid live-region announcements
		// that become distracting" guidance; the dots' aria-labels already
		// let an AT user query current position on demand.
		// ---------------------------------------------------------------
		var heroUserPaused = false;
		var heroTabHidden = document.hidden;
		var heroOffscreen = false;
		var heroHovering = false;
		var pauseBtn = null;
		var renderPauseBtn = function () {}; // reassigned below only if the button is actually built

		function shouldAutoAdvance() {
			return ! reduceMotion && heroSlides.length > 1 && ! heroUserPaused && ! heroTabHidden && ! heroOffscreen && ! heroHovering;
		}

		function refreshHeroTimer() {
			clearInterval( heroTimer );
			if ( shouldAutoAdvance() ) {
				heroTimer = setInterval( function () {
					goToHeroSlide( ( heroIdx + 1 ) % heroSlides.length );
				}, 5000 );
			}
			renderPauseBtn();
		}

		if ( ! reduceMotion && heroSlides.length > 1 ) {
			pauseBtn = document.createElement( 'button' );
			pauseBtn.type = 'button';
			pauseBtn.className = 'hero-pause-btn flex h-[26px] w-[26px] items-center justify-center rounded-full text-white/80 hover:text-white';
			// 2026-08-05, real bug caught before shipping (not just a
			// Puppeteer artifact — this would have broken real mouse
			// clicks too): both icons are built once, up front, as static
			// children toggled with a class — NOT via repeated
			// `pauseBtn.innerHTML = ...` on every refreshHeroTimer() call.
			// The dotNav 'mouseenter' listener below fires refreshHeroTimer
			// the instant the cursor enters the dot-nav area, which is
			// *before* a click on this button actually completes — if that
			// call had replaced innerHTML (the original approach here),
			// it destroyed the very <svg> the browser's pending
			// mousedown/mouseup click gesture was tracking, silently
			// swallowing the click. Confirmed via Puppeteer
			// (page.mouse.click() at the exact verified coordinates still
			// left aria-pressed unchanged) before landing on this fix.
			pauseBtn.innerHTML =
				'<svg class="hero-pause-icon" width="14" height="14" viewBox="0 0 14 14" fill="none" aria-hidden="true"><rect x="3" y="1.5" width="3" height="11" fill="currentColor"/><rect x="8" y="1.5" width="3" height="11" fill="currentColor"/></svg>' +
				'<svg class="hero-play-icon hidden" width="14" height="14" viewBox="0 0 14 14" fill="none" aria-hidden="true"><path d="M3 1.5 12 7 3 12.5V1.5Z" fill="currentColor"/></svg>';
			var pauseIconEl = pauseBtn.querySelector( '.hero-pause-icon' );
			var playIconEl = pauseBtn.querySelector( '.hero-play-icon' );
			renderPauseBtn = function () {
				var playing = shouldAutoAdvance();
				pauseBtn.setAttribute( 'aria-pressed', heroUserPaused ? 'true' : 'false' );
				pauseBtn.setAttribute( 'aria-label', heroUserPaused ? 'Play hero slideshow' : 'Pause hero slideshow' );
				pauseIconEl.classList.toggle( 'hidden', heroUserPaused );
				playIconEl.classList.toggle( 'hidden', ! heroUserPaused );
				// Reflects whether it's actually advancing right now (could
				// be non-playing from tab-hidden/offscreen/hover without
				// heroUserPaused itself being true) without changing the
				// button's own pressed/label semantics, which track only
				// the visitor's own explicit choice.
				pauseBtn.classList.toggle( 'is-actually-paused', ! playing );
			};
			pauseBtn.addEventListener( 'click', function () {
				heroUserPaused = ! heroUserPaused;
				refreshHeroTimer();
			} );
			// Inside #dotNav itself (not a sibling) so it shares the same
			// .dotnav-glass pill as the dots — first child, ahead of the
			// row/column of dot buttons already appended above.
			dotNav.insertBefore( pauseBtn, dotNav.firstChild );
			renderPauseBtn();

			document.addEventListener( 'visibilitychange', function () {
				heroTabHidden = document.hidden;
				refreshHeroTimer();
			} );

			var heroSection = document.getElementById( 'megaHero' );
			if ( heroSection && 'IntersectionObserver' in window ) {
				new IntersectionObserver( function ( entries ) {
					entries.forEach( function ( entry ) {
						heroOffscreen = ! entry.isIntersecting;
						refreshHeroTimer();
					} );
				}, { threshold: 0.1 } ).observe( heroSection );
			}

			dotNav.addEventListener( 'mouseenter', function () {
				heroHovering = true;
				refreshHeroTimer();
			} );
			dotNav.addEventListener( 'mouseleave', function () {
				heroHovering = false;
				refreshHeroTimer();
			} );
			dotNav.addEventListener( 'focusin', function () {
				heroHovering = true;
				refreshHeroTimer();
			} );
			dotNav.addEventListener( 'focusout', function ( e ) {
				if ( ! dotNav.contains( e.relatedTarget ) ) {
					heroHovering = false;
					refreshHeroTimer();
				}
			} );
		}

		// Content-aware headline sizing — mirrors itoi_hero_headline_size_class()
		// (front-page.php) exactly, same 4 tiers/thresholds, so a slide
		// changed client-side sizes identically to how slide 0 was server-
		// rendered. See src/tailwind.css's "Hero headline content-aware
		// sizing" comment for the full rationale. Keep both in sync if these
		// thresholds ever change.
		var HERO_HEADLINE_SIZE_CLASSES = [ 'hero-headline-size-1', 'hero-headline-size-2', 'hero-headline-size-3', 'hero-headline-size-4' ];
		function heroHeadlineSizeClass( text ) {
			var length = ( text || '' ).length;
			if ( length <= 60 ) {
				return 'hero-headline-size-1';
			} else if ( length <= 85 ) {
				return 'hero-headline-size-2';
			} else if ( length <= 110 ) {
				return 'hero-headline-size-3';
			}
			return 'hero-headline-size-4';
		}

		// Partnership slides (e.g. RetailNext) render a co-branding lockup
		// — "Partner × ITOI", partner logo if uploaded else styled text —
		// instead of a plain headline sentence. Isolated in its own
		// function since it builds markup, not just text.
		function renderHeroHeadline( slide ) {
			heroHeadline.classList.remove.apply( heroHeadline.classList, HERO_HEADLINE_SIZE_CLASSES );
			if ( ! slide.isPartnership ) {
				heroHeadline.classList.add( heroHeadlineSizeClass( slide.headline ) );
				heroHeadline.textContent = slide.headline;
				return;
			}
			// The partnership lockup's own children are em-sized against
			// this H1's font-size (see src/tailwind.css) — always tier 1
			// (the original, largest size) so the lockup renders at its
			// intended size regardless of what tier a previous text slide
			// left behind.
			heroHeadline.classList.add( 'hero-headline-size-1' );
			var partnerMark = slide.partnerLogo
				? '<img src="' + slide.partnerLogo + '" alt="' + ( slide.partnerName || 'Partner' ) + '" class="hero-partner-logo">'
				: '<span class="hero-partner-name">' + ( slide.partnerName || 'Partner' ) + '</span>';
			heroHeadline.innerHTML =
				'<span class="hero-partner-lockup">' +
				partnerMark +
				'<span class="hero-partner-x" aria-hidden="true">&times;</span>' +
				'<span class="hero-partner-itoi"><span class="h-[9px] w-[9px] rounded-full bg-signature-bright" aria-hidden="true"></span>ITOI</span>' +
				'</span>';
		}

		// Per-slide background photo/video (Site Settings, added 2026-07-23).
		// #heroBgVideo/#heroBgPhoto are stable elements (server-rendered
		// once with slide 0's media, see front-page.php) that every slide
		// change just re-points/toggles — never rebuilt from scratch.
		// Falls back to the plain gradient (#heroBg's own two alternate
		// background-color classes) whenever the active slide has neither
		// a photo nor a video.
		function updateHeroBackground( slide ) {
			var video = document.getElementById( 'heroBgVideo' );
			var photo = document.getElementById( 'heroBgPhoto' );
			var heroBg = document.getElementById( 'heroBg' );
			if ( ! video || ! photo || ! heroBg ) {
				return;
			}
			var videoUrl = slide.video || '';
			var photoUrl = slide.photo || '';

			// 2026-08-05 (Phase 4.3/5.5): a static-media visitor
			// (heroPrefersStaticMedia — reduce-motion or a narrow
			// viewport) never fetches this slide's video at all when a
			// photo exists for it — not just "loads it but keeps it
			// paused" as before. Only falls through to the video path
			// below if this particular slide has no photo of its own.
			if ( heroPrefersStaticMedia && photoUrl ) {
				videoUrl = '';
			}

			if ( videoUrl ) {
				var source = video.querySelector( 'source' );
				if ( ! source ) {
					source = document.createElement( 'source' );
					video.appendChild( source );
				}
				if ( source.getAttribute( 'src' ) !== videoUrl ) {
					source.setAttribute( 'src', videoUrl );
					video.load();
				}
				if ( photoUrl ) {
					video.setAttribute( 'poster', photoUrl );
				}
				video.classList.remove( 'hidden' );
				photo.classList.add( 'hidden' );
				// Same static-media guard as initHeroStaticMediaGuard() —
				// never autoplay a background video for that visitor (this
				// only still runs when the slide has no photo to fall back
				// to — see above).
				if ( heroPrefersStaticMedia ) {
					video.pause();
				} else {
					video.play().catch( function () {} );
				}
			} else if ( photoUrl ) {
				photo.src = photoUrl;
				photo.alt = slide.photoAlt || '';
				photo.classList.remove( 'hidden' );
				video.classList.add( 'hidden' );
				video.pause();
			} else {
				photo.classList.add( 'hidden' );
				video.classList.add( 'hidden' );
				video.pause();
			}

			var hasMedia = !! ( videoUrl || photoUrl );
			heroBg.classList.toggle( 'bg-[#0a1720]', hasMedia );
			heroBg.classList.toggle( 'bg-[linear-gradient(160deg,#0a1720,#122b38_55%,#1b3a48)]', ! hasMedia );
		}

		function goToHeroSlide( i ) {
			heroIdx = i;
			dotBtns.forEach( function ( btn, bi ) {
				var active = bi === i;
				btn.classList.toggle( 'active', active );
				var mark = btn.querySelector( '.dot-mark' );
				mark.classList.toggle( 'hidden', active );
				mark.classList.toggle( 'bg-white/40', ! active );
				var numberBadge = btn.querySelector( '.dot-number' );
				numberBadge.classList.toggle( 'hidden', ! active );
			} );
			renderHeroHeadline( heroSlides[ i ] );
			heroSub.textContent = heroSlides[ i ].subcopy;
			updateHeroBackground( heroSlides[ i ] );
			refreshHeroTimer();
		}

		goToHeroSlide( 0 );
	}

	// 2026-08-26 — was initHeroDetectionBoxes(): that function drew the
	// "Live Detection" bounding-box visualization (5 drifting boxes with
	// fake PERSON/confidence labels) over the hero background. Removed —
	// explicit instruction. This guard is the one piece of that function
	// that wasn't actually about the boxes — it stops the background
	// video for a reduce-motion/narrow-viewport visitor
	// (heroPrefersStaticMedia, above) — so it stays, just under its own
	// name now instead of being bundled inside the box-drawing function.
	function initHeroStaticMediaGuard() {
		var heroBgVideo = document.getElementById( 'heroBgVideo' );
		var heroBgPhotoEl = document.getElementById( 'heroBgPhoto' );
		if ( ! heroBgVideo || ! heroPrefersStaticMedia ) {
			return;
		}
		// Optional hero background video (Site Settings, added 2026-07-23) —
		// never autoplays for a reduce-motion visitor or on a narrow
		// viewport. The <video> tag's own `autoplay` attribute already
		// started it by the time this runs, so explicitly pause + cancel
		// its buffering rather than relying on the attribute alone.
		// 2026-08-05: also swaps to the actual #heroBgPhoto <img> when one
		// exists, rather than leaving the paused video's current frame on
		// screen — a real static image, not a video element that merely
		// isn't moving. Falls back to the paused video frame only if this
		// slide genuinely has no separate photo uploaded (video-only slide).
		heroBgVideo.pause();
		heroBgVideo.removeAttribute( 'autoplay' );
		var heroBgSourceEl = heroBgVideo.querySelector( 'source' );
		if ( heroBgSourceEl ) {
			heroBgSourceEl.removeAttribute( 'src' );
			heroBgVideo.load(); // cancels any further buffering of the now-sourceless video
		}
		if ( heroBgPhotoEl && heroBgPhotoEl.getAttribute( 'src' ) ) {
			heroBgVideo.classList.add( 'hidden' );
			heroBgPhotoEl.classList.remove( 'hidden' );
		}
	}


	// ---------------------------------------------------------------
	// Real Use Cases carousel (front-page.php's "Meet our products" section
	// replaced 2026-08-21 — see template-parts/home/use-cases.php's own
	// docblock for the full rationale). The flip itself needs no JS here —
	// initFlipCards() (core.js, sitewide) already handles every .flip-card
	// on the page, including these. This function owns only the carousel
	// shell around the cards: prev/next arrow scroll (same cardWidth() +
	// scrollBy() pattern as initIndustriesCarousel() below — one more
	// reason not to invent a third variant) plus a scroll-position progress
	// bar, which the industries carousel doesn't have.
	// ---------------------------------------------------------------
	function initUseCasesCarousel() {
		var carousel = document.getElementById( 'ucCarousel' );
		var progressFill = document.getElementById( 'ucProgressFill' );
		var prevBtn = document.getElementById( 'ucPrev' );
		var nextBtn = document.getElementById( 'ucNext' );
		if ( ! carousel ) {
			return;
		}

		function cardWidth() {
			var first = carousel.firstElementChild;
			return first ? first.offsetWidth + 24 : 680; // 24 = the track's own gap-6; 656 card + 24 gap
		}

		if ( nextBtn ) {
			nextBtn.addEventListener( 'click', function () {
				carousel.scrollBy( { left: cardWidth(), behavior: reduceMotion ? 'auto' : 'smooth' } );
			} );
		}
		if ( prevBtn ) {
			prevBtn.addEventListener( 'click', function () {
				carousel.scrollBy( { left: -cardWidth(), behavior: reduceMotion ? 'auto' : 'smooth' } );
			} );
		}

		if ( ! progressFill ) {
			return;
		}

		// Fill width = the visible fraction of the track (proportional to
		// how many cards fit on screen at once out of the total — a wider
		// viewport showing more cards gets a wider fill, same idea as a
		// native scrollbar thumb); fill position = how far scrolled through
		// the remaining (unfilled) track. Recomputed on every scroll event
		// rather than once, so it also stays correct across a resize (a
		// scroll event isn't guaranteed on resize alone, but the visible
		// card count changing is what actually needs recalculating, and
		// this is a decorative indicator, not a case worth a separate
		// ResizeObserver for).
		function updateProgress() {
			var trackWidth = carousel.scrollWidth;
			var visibleWidth = carousel.clientWidth;
			if ( trackWidth <= visibleWidth ) {
				progressFill.style.width = '100%';
				progressFill.style.left = '0';
				return;
			}
			var fillPct = Math.max( ( visibleWidth / trackWidth ) * 100, 8 );
			var maxScroll = trackWidth - visibleWidth;
			var scrollPct = ( carousel.scrollLeft / maxScroll ) * ( 100 - fillPct );
			progressFill.style.width = fillPct + '%';
			progressFill.style.left = scrollPct + '%';
		}

		carousel.addEventListener( 'scroll', updateProgress, { passive: true } );
		window.addEventListener( 'resize', updateProgress );
		updateProgress();
	}

	// ---------------------------------------------------------------
	// 5. Traffic-demo widget — illustrative hourly foot-traffic
	// ---------------------------------------------------------------
	function initTrafficWidget() {
		var slider = document.getElementById( 'trafficSlider' );
		var bars = document.getElementById( 'trafficBars' );
		var timeLabel = document.getElementById( 'trafficTimeLabel' );
		var densityLabel = document.getElementById( 'trafficDensityLabel' );
		if ( ! slider || ! bars || ! timeLabel || ! densityLabel ) {
			return;
		}

		// Illustrative dataset only — not sourced from any real ITOI site.
		var hours = [
			{ label: '6 AM', value: 8 }, { label: '7 AM', value: 18 }, { label: '8 AM', value: 35 },
			{ label: '9 AM', value: 42 }, { label: '10 AM', value: 38 }, { label: '11 AM', value: 45 },
			{ label: '12 PM', value: 62 }, { label: '1 PM', value: 68 }, { label: '2 PM', value: 55 },
			{ label: '3 PM', value: 48 }, { label: '4 PM', value: 52 }, { label: '5 PM', value: 70 },
			{ label: '6 PM', value: 82 }, { label: '7 PM', value: 88 }, { label: '8 PM', value: 75 },
			{ label: '9 PM', value: 58 }, { label: '10 PM', value: 34 }, { label: '11 PM', value: 15 },
		];

		hours.forEach( function () {
			var bar = document.createElement( 'div' );
			bar.className = 'flex-1 rounded-t-sm bg-[rgba(15,74,87,0.25)] transition-all duration-300';
			bars.appendChild( bar );
		} );
		var barEls = bars.querySelectorAll( 'div' );

		function densityFor( value ) {
			if ( value < 30 ) {
				return 'Low';
			}
			if ( value < 60 ) {
				return 'Moderate';
			}
			return 'High';
		}

		function render( i ) {
			var hour = hours[ i ];
			timeLabel.textContent = hour.label.replace( ' ', ':00 ' );
			densityLabel.textContent = densityFor( hour.value );
			barEls.forEach( function ( bar, bi ) {
				bar.style.height = hours[ bi ].value + '%';
				bar.classList.toggle( 'bg-signature', bi === i );
				bar.classList.toggle( 'bg-[rgba(15,74,87,0.25)]', bi !== i );
			} );
		}

		slider.addEventListener( 'input', function () {
			render( parseInt( slider.value, 10 ) );
		} );

		render( parseInt( slider.value, 10 ) );
	}

	// ---------------------------------------------------------------
	// 6. Why-choose pill tabs
	// ---------------------------------------------------------------
	function initWhyChooseTabs() {
		var tabsWrap = document.getElementById( 'pillTabs' );
		var whyLeft = document.getElementById( 'whyLeft' );
		var whyRight = document.getElementById( 'whyRight' );
		if ( ! tabsWrap || ! whyLeft || ! whyRight ) {
			return;
		}
		var tabs = tabsWrap.querySelectorAll( '.pill-tab' );

		// The server renders tab 0's video (if any) with the autoplay
		// attribute already set (front-page.php) since PHP has no reliable
		// reduce-motion signal — pause it here on load for that visitor,
		// same as every subsequent tab-click swap does below.
		if ( reduceMotion ) {
			var whyInitialVideo = whyRight.querySelector( 'video' );
			if ( whyInitialVideo ) {
				whyInitialVideo.pause();
			}
		}

		tabs.forEach( function ( tab ) {
			tab.addEventListener( 'click', function () {
				tabs.forEach( function ( t ) {
					t.classList.remove( 'active', 'bg-white', 'border-white', 'text-teal-900' );
					t.classList.add( 'border-white/25', 'text-white/70' );
				} );
				tab.classList.add( 'active', 'bg-white', 'border-white', 'text-teal-900' );
				tab.classList.remove( 'border-white/25', 'text-white/70' );

				// itoiWhyTabs is localized from the why_choose_photos ACF repeater
				// (inc/enqueue.php) — one {tabLabel, title, description, bullets,
				// ctaLabel, ctaUrl, photo, video} object per tab, in pill order.
				// Fully replaced the old hardcoded whyData array 2026-08-04 (see
				// NOTES.md) — this is now the only source for both panels.
				var tabIndex = parseInt( tab.dataset.tab, 10 );
				var d = ( typeof itoiWhyTabs !== 'undefined' && itoiWhyTabs[ tabIndex ] ) ? itoiWhyTabs[ tabIndex ] : {};
				var bullets = d.bullets || [];
				var ctaLabel = d.ctaLabel || 'Learn more';
				var ctaUrl = d.ctaUrl || '#';
				whyLeft.innerHTML =
					'<h3 class="text-[clamp(22px,2.4vw,28px)] text-white">' + ( d.title || '' ) + '</h3>' +
					'<p class="text-white/80">' + ( d.description || '' ) + '</p>' +
					( bullets.length ?
						'<ul class="my-4 grid list-none gap-2.5 p-0">' +
						bullets.map( function ( b ) {
							return '<li class="flex items-start gap-2.5 text-sm text-white/90"><span class="flex h-5 w-5 flex-none items-center justify-center rounded-full bg-white/15 text-[11px]">&#10003;</span>' + b + '</li>';
						} ).join( '' ) +
						'</ul>' : '' ) +
					'<a href="' + ctaUrl + '" class="w-fit rounded-full border-[1.5px] border-white bg-white px-5 py-2.5 text-sm font-bold text-teal-900">' + ctaLabel + '</a>';

				// Video takes priority over photo (same convention as the hero
				// slideshow's updateHeroBackground()); falls back to a generic
				// placeholder caption if neither is set for that tab (empty state).
				var photoUrl = d.photo || '';
				var videoUrl = d.video || '';
				if ( videoUrl ) {
					var posterAttr = photoUrl ? ' poster="' + photoUrl + '"' : '';
					whyRight.innerHTML = '<video id="whyRightImg" class="absolute inset-0 h-full w-full object-cover" autoplay muted loop playsinline' + posterAttr + '><source src="' + videoUrl + '"></video>';
					var whyVideoEl = whyRight.querySelector( 'video' );
					// Same reduce-motion guard as the hero background video —
					// never autoplay for that visitor; the poster (or a black
					// frame if no photo is set) shows as a static image instead.
					if ( reduceMotion ) {
						whyVideoEl.pause();
					} else {
						whyVideoEl.play().catch( function () {} );
					}
				} else if ( photoUrl ) {
					whyRight.innerHTML = '<img src="' + photoUrl + '" alt="' + ( d.title || '' ) + '" id="whyRightImg" class="absolute inset-0 h-full w-full object-cover">';
				} else {
					whyRight.innerHTML = '<span id="whyRightImg">Photo — ' + ( d.tabLabel || '' ) + '</span>';
				}
			} );
		} );
	}

	// ---------------------------------------------------------------
	// 9. Industries arrow-nav carousel
	// ---------------------------------------------------------------
	function initIndustriesCarousel() {
		var carousel = document.getElementById( 'indCarousel' );
		var prevBtn = document.getElementById( 'indPrev' );
		var nextBtn = document.getElementById( 'indNext' );
		if ( ! carousel || ! prevBtn || ! nextBtn ) {
			return;
		}

		function cardWidth() {
			var first = carousel.firstElementChild;
			return first ? first.offsetWidth + 16 : 340;
		}

		nextBtn.addEventListener( 'click', function () {
			carousel.scrollBy( { left: cardWidth(), behavior: 'smooth' } );
		} );
		prevBtn.addEventListener( 'click', function () {
			carousel.scrollBy( { left: -cardWidth(), behavior: 'smooth' } );
		} );
	}

	// ---------------------------------------------------------------
	// Trust & credibility section — scroll-triggered reveal, 2026-08-21.
	// Re-introduces real count-up (removed 2026-08-05, see NOTES.md — that
	// removal is superseded by this explicit ask, not silently reversed).
	// Sequence on first scroll-into-view of #trustCredibility: the 6
	// featured-client logos stagger in ~80ms apart left to right, then the
	// 4 stat cards stagger in ~100ms apart once the logos have genuinely
	// finished (see cardsBaseDelay below), each card's number counting up
	// the instant that card starts revealing.
	//
	// Per-item logo stagger only works here because the row is a curated,
	// fixed 6 (trust-credibility.php) — a DIFFERENT component from
	// itoi_render_client_logo_row()'s full 114-client auto-scrolling
	// marquee used elsewhere (single-industry.php's Customers section);
	// staggering that many individual items would take ~10s to finish and
	// fight the marquee's own already-looping motion, which is why that
	// component still isn't used on this section.
	// ---------------------------------------------------------------
	function initTrustSectionReveal() {
		var section = document.getElementById( 'trustCredibility' );
		var logos = document.querySelectorAll( '#trustLogoRow .itoi-stagger-item' );
		var cards = document.querySelectorAll( '#trustMetricsGrid .itoi-stagger-item' );
		if ( ! section || ( ! logos.length && ! cards.length ) ) {
			return;
		}

		var LOGO_STEP = 80;
		var CARD_STEP = 100;
		var ITEM_TRANSITION_MS = 550; // .itoi-stagger-item's own transition duration, src/tailwind.css
		// Cards start once the logo row has genuinely finished — last
		// logo's own delay plus its transition time — not a guessed
		// constant, so this stays correct if the featured-client count
		// (currently 6) ever changes.
		var cardsBaseDelay = logos.length ? ( logos.length - 1 ) * LOGO_STEP + ITEM_TRANSITION_MS : 0;

		function reveal() {
			if ( logos.length ) {
				window.itoiStaggerReveal( logos, { trigger: 'load', step: LOGO_STEP } );
			}
			if ( cards.length ) {
				window.itoiStaggerReveal( cards, { trigger: 'load', step: CARD_STEP, baseDelay: cardsBaseDelay } );
				cards.forEach( function ( card, i ) {
					scheduleCounter( card, reduceMotion ? 0 : cardsBaseDelay + i * CARD_STEP );
				} );
			}
		}

		if ( reduceMotion || ! ( 'IntersectionObserver' in window ) ) {
			reveal();
			return;
		}
		new IntersectionObserver(
			function ( entries, obs ) {
				entries.forEach( function ( entry ) {
					if ( entry.isIntersecting ) {
						reveal();
						obs.disconnect();
					}
				} );
			},
			{ threshold: 0.2 }
		).observe( section );
	}

	// ---------------------------------------------------------------
	// Trust & Stats logo row rotation — cycles the 6 visible client
	// names/logos through every real published `client` post (itoiTrustClients,
	// localized in inc/enqueue.php), 6 at a time, cross-fading between
	// chunks. First chunk here is identical to the server-rendered one
	// (same `orderby title ASC` on both sides), so there's no visual jump
	// before the first rotation. Same 5000ms interval as the mega-hero
	// slideshow (initHeroSlideshow(), above) — one consistent auto-advance
	// cadence on this page, not two different-feeling timers.
	// prefers-reduced-motion / 6-or-fewer real clients: row just stays
	// static on its first (server-rendered) chunk, no JS needed.
	// ---------------------------------------------------------------
	function initTrustLogoRotation() {
		var row = document.getElementById( 'trustLogoRow' );
		var clients = window.itoiTrustClients || [];
		if ( ! row || reduceMotion || clients.length <= 6 ) {
			return;
		}

		var CHUNK_SIZE = 6;
		var chunks = [];
		for ( var i = 0; i < clients.length; i += CHUNK_SIZE ) {
			chunks.push( clients.slice( i, i + CHUNK_SIZE ) );
		}
		if ( chunks.length < 2 ) {
			return;
		}

		var current = 0;
		var tabHidden = false;
		document.addEventListener( 'visibilitychange', function () {
			tabHidden = document.hidden;
		} );

		function itemHtml( client ) {
			// Mirrors itoi_trust_logo_mark() (template-parts/home/
			// trust-credibility.php) exactly, real-logo-image branch vs
			// text-wordmark-fallback branch — values arrive pre-escaped
			// from PHP (esc_html/esc_url, inc/enqueue.php), safe for
			// innerHTML the same way itoiHeroSlides' data already is.
			var mark = client.logo
				? '<img src="' + client.logo + '" alt="' + client.name + '" class="h-11 w-auto max-w-[240px] object-contain mix-blend-multiply min-[640px]:h-12">'
				: '<span class="flex h-11 items-center whitespace-nowrap text-[18px] font-extrabold text-ink min-[640px]:h-12 min-[640px]:text-[20px]">' + client.name + '</span>';
			return (
				'<div class="relative flex flex-none items-center justify-center pb-2 pt-2 min-[640px]:pb-6 min-[640px]:pt-6">' +
					'<span class="absolute left-0 top-0 h-1 w-1 rounded-full bg-teal-500" aria-hidden="true"></span>' +
					'<span class="absolute bottom-0 left-0 h-1 w-1 rounded-full bg-teal-500" aria-hidden="true"></span>' +
					mark +
				'</div>'
			);
		}

		setInterval( function () {
			if ( tabHidden ) {
				return;
			}
			row.style.opacity = '0';
			setTimeout( function () {
				current = ( current + 1 ) % chunks.length;
				row.innerHTML = chunks[ current ].map( itemHtml ).join( '' );
				row.style.opacity = '1';
			}, 700 ); // matches the row's own transition-opacity duration-700
		}, 5000 );
	}

	// ---------------------------------------------------------------
	// Trust & Stats stat-card grid rotation — same mechanic as
	// initTrustLogoRotation() just above, applied to the "Trust metrics"
	// repeater (itoiTrustMetrics, localized in inc/enqueue.php from
	// itoi_get_trust_metrics(), inc/trust.php) instead of client logos:
	// cycles the 4 visible stat cards through every configured metric, 4 at
	// a time, cross-fading between chunks. First chunk here is identical to
	// the server-rendered one (both take the same first-4 slice), so
	// there's no visual jump before the first rotation. Same 5000ms
	// interval / 700ms fade as the logo row and the mega-hero slideshow —
	// one consistent cadence, not a third different-feeling timer.
	// prefers-reduced-motion / 4-or-fewer real metrics: grid just stays
	// static on its first (server-rendered) chunk, no JS needed. Rotated-in
	// cards render their final value directly rather than re-running
	// animateCounter() — that count-up is a one-time "first paint" flourish
	// (initTrustSectionReveal() above), the cross-fade itself is this
	// grid's "reveal".
	// ---------------------------------------------------------------
	function initTrustMetricsRotation() {
		var grid = document.getElementById( 'trustMetricsGrid' );
		var metrics = window.itoiTrustMetrics || [];
		if ( ! grid || reduceMotion || metrics.length <= 4 ) {
			return;
		}

		var CHUNK_SIZE = 4;
		var chunks = [];
		for ( var i = 0; i < metrics.length; i += CHUNK_SIZE ) {
			chunks.push( metrics.slice( i, i + CHUNK_SIZE ) );
		}
		if ( chunks.length < 2 ) {
			return;
		}

		var current = 0;
		var tabHidden = false;
		document.addEventListener( 'visibilitychange', function () {
			tabHidden = document.hidden;
		} );

		function cardHtml( metric ) {
			// Mirrors the server-rendered stat card markup exactly
			// (trust-credibility.php) — values arrive pre-escaped from PHP
			// (esc_html, inc/enqueue.php), safe for innerHTML the same way
			// itoiTrustClients' data already is.
			return (
				'<div class="flex min-h-[150px] flex-col justify-between rounded-[14px] bg-[#F4F3F6] p-7">' +
					'<div class="text-[32px] font-extrabold leading-none tracking-[-0.01em] text-[#111]">' + metric.value + '</div>' +
					'<div class="line-clamp-2 text-[14px] text-[#6B7280]">' + metric.label + '</div>' +
				'</div>'
			);
		}

		setInterval( function () {
			if ( tabHidden ) {
				return;
			}
			grid.style.opacity = '0';
			setTimeout( function () {
				current = ( current + 1 ) % chunks.length;
				grid.innerHTML = chunks[ current ].map( cardHtml ).join( '' );
				grid.style.opacity = '1';
			}, 700 ); // matches the grid's own transition-opacity duration-700
		}, 5000 );
	}

	// data-target's numeric part counts up from 0 (or, for a non-1 decimal
	// count, "0.00") to its real value over ~1.2s ease-out, preserving
	// whatever prefix/suffix surrounds it ("<100ms" -> "<" + 100 + "ms",
	// "99.87%" -> "" + 99.87 + "%") — generic regex parse, not per-item
	// logic, so any future stat_value format keeps working without a JS
	// change. A value with no digits at all (e.g. "Multi-site") has
	// nothing to animate — set to its final text immediately, same as the
	// reduceMotion path.
	function scheduleCounter( card, delay ) {
		var el = card.querySelector( '[data-trust-counter]' );
		if ( ! el ) {
			return;
		}
		if ( reduceMotion ) {
			el.textContent = el.getAttribute( 'data-target' ) || '';
			return;
		}
		setTimeout( function () {
			animateCounter( el );
		}, delay );
	}

	function animateCounter( el ) {
		var target = el.getAttribute( 'data-target' ) || '';
		var match = target.match( /^([^\d]*)([\d,]*\.?\d+)(.*)$/ );
		if ( ! match ) {
			el.textContent = target;
			return;
		}
		var prefix = match[ 1 ];
		var suffix = match[ 3 ];
		var hasComma = match[ 2 ].indexOf( ',' ) !== -1;
		var decimals = ( match[ 2 ].split( '.' )[ 1 ] || '' ).length;
		var endValue = parseFloat( match[ 2 ].replace( /,/g, '' ) );
		if ( isNaN( endValue ) ) {
			el.textContent = target;
			return;
		}

		function format( n ) {
			var fixed = n.toFixed( decimals );
			if ( hasComma ) {
				var parts = fixed.split( '.' );
				parts[ 0 ] = parts[ 0 ].replace( /\B(?=(\d{3})+(?!\d))/g, ',' );
				fixed = parts.join( '.' );
			}
			return fixed;
		}

		var duration = 1200;
		var startTime = null;
		function tick( ts ) {
			if ( null === startTime ) {
				startTime = ts;
			}
			var progress = Math.min( ( ts - startTime ) / duration, 1 );
			var eased = 1 - Math.pow( 1 - progress, 3 ); // ease-out cubic
			el.textContent = prefix + format( endValue * eased ) + suffix;
			if ( progress < 1 ) {
				requestAnimationFrame( tick );
			} else {
				el.textContent = target; // exact final string, no float rounding drift
			}
		}
		requestAnimationFrame( tick );
	}

	// ---------------------------------------------------------------
	// Homepage platform-demo modal — teaser (play button / "Learn more")
	// opens a 6-tab illustrative dashboard. Modal open/close reuses the
	// exact pattern already established for Find Your Fit (initFinder
	// above): .open class toggle + document.body.style.overflow lock,
	// same open/close function shape, rather than new modal logic.
	// ---------------------------------------------------------------
	function initPlatformDemo() {
		var overlay = document.getElementById( 'platformDemoOverlay' );
		if ( ! overlay ) {
			return;
		}
		var playBtn = document.getElementById( 'platformDemoPlayBtn' );
		var learnMoreBtn = document.getElementById( 'platformDemoLearnMoreBtn' );
		var closeBtn = document.getElementById( 'platformDemoClose' );

		function openModal() {
			overlay.classList.add( 'open' );
			document.body.style.overflow = 'hidden';
		}
		function closeModal() {
			overlay.classList.remove( 'open' );
			document.body.style.overflow = '';
		}
		[ playBtn, learnMoreBtn ].forEach( function ( trigger ) {
			if ( trigger ) {
				trigger.addEventListener( 'click', openModal );
			}
		} );
		if ( closeBtn ) {
			closeBtn.addEventListener( 'click', closeModal );
		}
		overlay.addEventListener( 'click', function ( e ) {
			if ( e.target === overlay ) {
				closeModal();
			}
		} );
		document.addEventListener( 'keydown', function ( e ) {
			if ( 'Escape' === e.key && overlay.classList.contains( 'open' ) ) {
				closeModal();
			}
		} );

		// --- Tab switching ---
		var tabs = overlay.querySelectorAll( '.pd-tab' );
		var panels = overlay.querySelectorAll( '.pd-panel' );
		tabs.forEach( function ( tab ) {
			tab.addEventListener( 'click', function () {
				tabs.forEach( function ( t ) {
					t.classList.remove( 'active' );
					t.setAttribute( 'aria-selected', 'false' );
				} );
				panels.forEach( function ( p ) {
					p.classList.remove( 'active' );
				} );
				tab.classList.add( 'active' );
				tab.setAttribute( 'aria-selected', 'true' );
				var panel = overlay.querySelector( '.pd-panel[data-panel="' + tab.dataset.tab + '"]' );
				if ( panel ) {
					panel.classList.add( 'active' );
				}
			} );
		} );

		// --- Staff Planning calculator (Optimization tab) — live
		// recalculation on every input change, not a one-shot read.
		// "(Illustrative calculation)" is styled in the site's
		// TODO/illustrative-note amber-warning tone (#9A3412), matching
		// the convention used elsewhere for explicitly-not-real content.
		var laborInput = document.getElementById( 'platformDemoLaborHours' );
		var staffOutput = document.getElementById( 'platformDemoStaffOutput' );
		if ( laborInput && staffOutput ) {
			laborInput.addEventListener( 'input', function () {
				var hours = parseFloat( laborInput.value );
				if ( isNaN( hours ) || hours < 0 ) {
					staffOutput.textContent = 'Enter available labor hours to see recommended staffing by day.';
					return;
				}
				var perDay = Math.round( hours / 7 );
				staffOutput.innerHTML = 'Recommended: <strong>' + perDay + ' labor hours/day</strong> across 7 days, weighted toward Fri&ndash;Sun peak traffic. <span style="color:#9A3412">(Illustrative calculation)</span>';
			} );
		}
	}

	// ---------------------------------------------------------------
	// Video Showcase "Play video" lightbox (template-parts/home/
	// video-showcase.php) — same open/close overlay.classList.toggle('open')
	// + body-scroll-lock shape as initPlatformDemo() above, not new modal
	// logic. The one addition that modal doesn't need: actually
	// playing/pausing the lightbox's own <video> on open/close, since this
	// one IS a video player, not a data dashboard.
	//
	// Deliberately does NOT touch the section's own looping BACKGROUND
	// video (if any) — that's owned entirely by initLazyMediaVideos()
	// (core.js, sitewide), which plays/pauses it via its own
	// IntersectionObserver + pending-play-promise tracking (see that
	// function's comment for the real AbortError race it's guarding
	// against). Calling .pause()/.play() on it from here too would fight
	// that tracking outside its own state machine; it's muted already
	// (itoi_media_cover()'s `muted` attribute) so there's no real audio
	// conflict with the lightbox player to justify the risk.
	// ---------------------------------------------------------------
	function initVideoShowcase() {
		var overlay = document.getElementById( 'videoShowcaseOverlay' );
		if ( ! overlay ) {
			return;
		}
		var playBtn = document.getElementById( 'videoShowcasePlayBtn' );
		var closeBtn = document.getElementById( 'videoShowcaseClose' );
		var player = document.getElementById( 'videoShowcasePlayer' );

		function openModal() {
			overlay.classList.add( 'open' );
			overlay.setAttribute( 'aria-hidden', 'false' );
			document.body.style.overflow = 'hidden';
			if ( player ) {
				player.play();
			}
		}
		function closeModal() {
			overlay.classList.remove( 'open' );
			overlay.setAttribute( 'aria-hidden', 'true' );
			document.body.style.overflow = '';
			if ( player ) {
				player.pause();
				player.currentTime = 0;
			}
		}
		if ( playBtn ) {
			playBtn.addEventListener( 'click', openModal );
		}
		if ( closeBtn ) {
			closeBtn.addEventListener( 'click', closeModal );
		}
		overlay.addEventListener( 'click', function ( e ) {
			if ( e.target === overlay ) {
				closeModal();
			}
		} );
		document.addEventListener( 'keydown', function ( e ) {
			if ( 'Escape' === e.key && overlay.classList.contains( 'open' ) ) {
				closeModal();
			}
		} );
	}

	// ---------------------------------------------------------------
	// Technology Partners carousel (template-parts/partners.php).
	// 2026-08-26: moved here from assets/js/core.js — the section itself
	// moved from footer.php (sitewide) to front-page.php (homepage-only,
	// see that file's header comment), so its carousel JS belongs in this
	// homepage-only bundle now too, not the sitewide one. Same shell/math
	// as the Real Use Cases carousel above (initUseCasesCarousel()) —
	// arrow-nav scrolls by one card+gap, a progress bar tracks scroll
	// position — kept as its own function rather than factored into a
	// shared helper, same reasoning as before the move: not worth it for
	// one call site.
	// ---------------------------------------------------------------
	function initPartnersCarousel() {
		var carousel = document.getElementById( 'partnersCarousel' );
		var progressFill = document.getElementById( 'partnersProgressFill' );
		var prevBtn = document.getElementById( 'partnersPrev' );
		var nextBtn = document.getElementById( 'partnersNext' );
		if ( ! carousel ) {
			return;
		}

		function cardWidth() {
			var first = carousel.firstElementChild;
			return first ? first.offsetWidth + 24 : 364; // 24 = the track's own gap-6; 340 card + 24 gap
		}

		if ( nextBtn ) {
			nextBtn.addEventListener( 'click', function () {
				carousel.scrollBy( { left: cardWidth(), behavior: reduceMotion ? 'auto' : 'smooth' } );
			} );
		}
		if ( prevBtn ) {
			prevBtn.addEventListener( 'click', function () {
				carousel.scrollBy( { left: -cardWidth(), behavior: reduceMotion ? 'auto' : 'smooth' } );
			} );
		}

		if ( ! progressFill ) {
			return;
		}

		function updateProgress() {
			var trackWidth = carousel.scrollWidth;
			var visibleWidth = carousel.clientWidth;
			if ( trackWidth <= visibleWidth ) {
				progressFill.style.width = '100%';
				progressFill.style.left = '0';
				return;
			}
			var fillPct = Math.max( ( visibleWidth / trackWidth ) * 100, 8 );
			var maxScroll = trackWidth - visibleWidth;
			var scrollPct = ( carousel.scrollLeft / maxScroll ) * ( 100 - fillPct );
			progressFill.style.width = fillPct + '%';
			progressFill.style.left = scrollPct + '%';
		}

		carousel.addEventListener( 'scroll', updateProgress, { passive: true } );
		window.addEventListener( 'resize', updateProgress );
		updateProgress();
	}

})();
