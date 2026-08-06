/**
 * ITOI homepage-only interactivity: mega-hero slideshow + Live Detection
 * boxes, products carousel, traffic-demo widget, Why Choose ITOI tabs,
 * Industries carousel, trust-metrics display, and the platform-demo modal.
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

	document.addEventListener( 'DOMContentLoaded', function () {
		initHero();
		initProductsCarousel();
		initTrafficWidget();
		initWhyChooseTabs();
		initIndustriesCarousel();
		initTrustMetricsCounters();
		initPlatformDemo();
	} );

	// ---------------------------------------------------------------
	// 2. Mega-hero: headline/sub slideshow + dot-nav + Live Detection boxes
	// 2026-07-23 correction: a prior session incorrectly removed the
	// slideshow/dot-nav entirely (kept only the Live Detection boxes) while
	// only a height/padding reduction had actually been requested. Restored
	// here — see NOTES.md for both the original (incorrect) removal entry
	// and this correction. Slide content now comes from the ACF
	// `hero_slides` repeater (localized as `itoiHeroSlides`, see
	// inc/enqueue.php) instead of being hardcoded in this file — the old
	// pre-removal version had this same array hardcoded here and the field
	// existed but was never actually read by any template; that gap is
	// closed properly this time, not reintroduced.
	// ---------------------------------------------------------------
	function initHero() {
		initHeroSlideshow();
		initHeroDetectionBoxes();
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

		heroSlides.forEach( function ( slide, i ) {
			var btn = document.createElement( 'button' );
			btn.className = 'dot-btn relative flex h-[30px] w-[30px] items-center justify-center' + ( 0 === i ? ' active' : '' );
			btn.setAttribute( 'aria-label', 'Hero slide ' + ( i + 1 ) + ' of ' + heroSlides.length );
			btn.innerHTML =
				'<span class="dot-mark h-1.5 w-1.5 rounded-full transition-all duration-300 ' + ( 0 === i ? 'scale-[1.3] bg-white' : 'bg-white/40' ) + '"></span>' +
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
		// change just re-points/toggles — never rebuilt from scratch, so
		// the Live Detection markers appended into #heroBg by
		// initHeroDetectionBoxes() are completely undisturbed by a slide
		// change. Falls back to the plain gradient (#heroBg's own two
		// alternate background-color classes) whenever the active slide
		// has neither a photo nor a video.
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
				// Same static-media guard as initHeroDetectionBoxes() —
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
				mark.classList.toggle( 'scale-[1.3]', active );
				mark.classList.toggle( 'bg-white', active );
				mark.classList.toggle( 'bg-white/40', ! active );
			} );
			renderHeroHeadline( heroSlides[ i ] );
			heroSub.textContent = heroSlides[ i ].subcopy;
			updateHeroBackground( heroSlides[ i ] );
			refreshHeroTimer();
		}

		goToHeroSlide( 0 );
	}

	function initHeroDetectionBoxes() {
		var heroBg = document.getElementById( 'heroBg' );
		if ( ! heroBg ) {
			return;
		}

		// Optional hero background video (Site Settings, added 2026-07-23) —
		// never autoplays for a reduce-motion visitor or on a narrow
		// viewport (heroPrefersStaticMedia, above). The <video> tag's own
		// `autoplay` attribute already started it by the time this runs, so
		// explicitly pause + cancel its buffering rather than relying on
		// the attribute alone. 2026-08-05: now also swaps to the actual
		// #heroBgPhoto <img> when one exists, rather than leaving the
		// paused video's current frame on screen — a real static image,
		// not a video element that merely isn't moving. Falls back to the
		// paused video frame only if this slide genuinely has no separate
		// photo uploaded (video-only slide).
		var heroBgVideo = document.getElementById( 'heroBgVideo' );
		var heroBgPhotoEl = document.getElementById( 'heroBgPhoto' );
		if ( heroBgVideo && heroPrefersStaticMedia ) {
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

		var boxes = [];
		for ( var i = 0; i < 5; i++ ) {
			var box = document.createElement( 'div' );
			box.className = 'absolute rounded-[3px] border-[1.5px] border-signature-bright transition-all duration-[2000ms] ease-[cubic-bezier(0.4,0,0.2,1)]';
			var w = 60 + Math.random() * 60;
			var h = 90 + Math.random() * 70;
			box.style.width = w + 'px';
			box.style.height = h + 'px';
			var pct = ( 90 + Math.random() * 9 ).toFixed( 1 );
			box.innerHTML = '<span class="absolute -left-px -top-[18px] whitespace-nowrap rounded-[2px] bg-signature-bright px-1.5 py-0.5 text-[9.5px] font-extrabold text-[#122b38]">PERSON ' + pct + '%</span>';
			heroBg.appendChild( box );
			boxes.push( box );
		}

		function placeBox( box ) {
			var vw = heroBg.clientWidth;
			var vh = heroBg.clientHeight;
			box.style.left = ( 40 + Math.random() * Math.max( vw - 160, 40 ) ) + 'px';
			box.style.top = ( 60 + Math.random() * Math.max( vh - 220, 40 ) ) + 'px';
		}

		boxes.forEach( placeBox );

		if ( ! reduceMotion ) {
			setInterval( function () {
				boxes.forEach( function ( box ) {
					if ( Math.random() > 0.45 ) {
						placeBox( box );
					}
				} );
			}, 2200 );
		}
	}


	// "Meet our products" homepage section (front-page.php) — rebuilt
	// 2026-07-31, same day, as a compact single-card-visible carousel:
	// a peek-carousel (prev/center/next cards partly visible) took up too
	// much homepage space (see NOTES.md). Cards ARE real <a> links here
	// since there's no side-peek card to accidentally double-click-and-
	// navigate — only the explicit prev/next/dot controls change which
	// card is visible.
	// No-op (and no controls rendered — see front-page.php) when there's
	// only one product, which is the current state and exactly matches
	// the original single-card teaser this replaced.
	function initProductsCarousel() {
		var root = document.getElementById( 'productsCompactCarousel' );
		if ( ! root ) {
			return;
		}
		var cards = Array.prototype.slice.call( root.querySelectorAll( '.products-compact-card' ) );
		var dots = Array.prototype.slice.call( root.querySelectorAll( '.products-compact-dot' ) );
		var prevBtn = root.querySelector( '.products-compact-prev' );
		var nextBtn = root.querySelector( '.products-compact-next' );
		var total = cards.length;
		if ( total < 2 ) {
			return; // Single product: no controls rendered, nothing to wire up.
		}

		// 2026-08-05: auto-advance removed (external improvement plan Phase
		// 3.4 — see NOTES.md; user explicitly authorized overriding
		// CLAUDE.md's mechanics rule for this one reduction). Was a 6s
		// setInterval, paused on hover/touch/scrolled-out-of-view — all of
		// that scaffolding (IntersectionObserver, hover/touch listeners,
		// resume timeout) is gone with it since there's no longer a timer
		// for it to pause/resume. Dots and prev/next arrows are unchanged
		// and still fully functional — this is manual-only now, not removed.
		var index = 0;

		function render() {
			cards.forEach( function ( card, i ) {
				card.classList.toggle( 'is-hidden', i !== index );
			} );
			dots.forEach( function ( dot, i ) {
				dot.classList.toggle( 'is-current', i === index );
			} );
		}

		function goTo( i ) {
			index = ( ( i % total ) + total ) % total;
			render();
		}

		dots.forEach( function ( dot, i ) {
			dot.addEventListener( 'click', function () {
				goTo( i );
			} );
		} );
		if ( prevBtn ) {
			prevBtn.addEventListener( 'click', function () {
				goTo( index - 1 );
			} );
		}
		if ( nextBtn ) {
			nextBtn.addEventListener( 'click', function () {
				goTo( index + 1 );
			} );
		}

		render();
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
	// Trust & credibility metric-card counters (front-page.php,
	// #trustMetricsGrid) — each card's big number counts up from 0 to its
	// stat_value's leading numeric portion on scroll-in, then appends
	// whatever non-numeric text followed it (+, %, /7, " hour"). Generic
	// regex parse, not per-item logic, so any future stat_value format
	// (a 5th metric, a different suffix) keeps working without a JS
	// change. Same defensive/observer pattern as initRevealObserver()
	// above: reduceMotion skips straight to the final value, each card
	// fires once via IntersectionObserver, no interval/timer left running
	// after it finishes.
	// ---------------------------------------------------------------
	// 2026-08-05: count-up-on-scroll animation removed (external
	// improvement plan Phase 3.4 — "counters" named explicitly as one of
	// several homepage effects competing for attention; user explicitly
	// authorized overriding CLAUDE.md's mechanics rule for this one
	// reduction, see NOTES.md). Cards now render their real final value
	// immediately, same as the reduced-motion path already did — the
	// scroll-triggered easing/tick machinery is gone, not just skipped.
	function initTrustMetricsCounters() {
		var cards = document.querySelectorAll( '[data-trust-counter]' );
		cards.forEach( function ( el ) {
			el.textContent = el.getAttribute( 'data-target' ) || '';
		} );
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

})();
