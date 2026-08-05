/**
 * ITOI homepage interactivity. Vanilla JS, no framework (PROJECT.md §2).
 * Mirrors preview-verkada-match.html's behavior exactly (ground truth per
 * PROJECT.md §3), split into named init functions per mechanic.
 *
 * prefers-reduced-motion: auto-advancing intervals (ticker, hero slideshow,
 * detection-box drift) are skipped entirely; manual interaction (clicking a
 * dot, dragging the traffic slider, tab clicks, carousel arrows) still works.
 */
(function () {
	'use strict';

	var reduceMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

	document.addEventListener( 'DOMContentLoaded', function () {
		initLazyMediaVideos();
		initTicker();
		initHero();
		initSolutionsCarousel();
		initProductsCarousel();
		initTrafficWidget();
		initWhyChooseTabs();
		initIndustriesCarousel();
		initFinder();
		initPlatformDemo();
		initClientFilter();
		initFlipCards();
		initRevealObserver();
		initTrustMetricsCounters();
		initMegaMenu();
		initFootTrafficFunnel();
		initHospitalityTimeline();
		initBankingSimulator();
		initGovernmentFundingChart();
		initLogisticsComparison();
		initStadiumDensity();
		initCasinoFloorMap();
		initLongformScrollspy();
		initLongformHeaderHide();
		initLongformMarquees();
		initItoiFilterLists();
		initGuideFilter();
		initUseCaseFilter();
		initIndustrySelector();
	} );

	// ---------------------------------------------------------------
	// 0. Lazy playback for server-rendered photo/video fields
	// ---------------------------------------------------------------
	// Every industry/solution/use-case media block that can hold an editor-
	// uploaded video (itoi_media_cover() in inc/media.php: industry hero,
	// solution hero/tile, use-case cards, Customers spotlight, team photos)
	// is server-rendered with no `autoplay` and `preload="none"` — a page
	// can carry several of these (a long-form industry page easily has 5-6),
	// and having every one of them start downloading its full video on page
	// load — one measured over 18MB — was real, reported lag ("the website
	// is laggy"), confirmed via Lighthouse (2026-08-04, see NOTES.md):
	// 34.6MB total page weight on a single industry page, largely from
	// videos nowhere near the viewport on initial load. Only actually plays
	// (which also triggers the deferred download, since preload="none")
	// once scrolled within `rootMargin` of the viewport, and pauses again
	// once scrolled back out — cuts both initial page weight and ongoing
	// decode/GPU cost from videos the visitor never sees at the same time.
	// Never played at all for a reduce-motion visitor — the poster frame is
	// the correct, final state for them, not a loading placeholder (same
	// convention as #heroBgVideo/#whyRight's own dedicated reduce-motion
	// handling — those aren't tagged .itoi-media-video and are handled by
	// their own init functions instead, since they get rebuilt by JS on
	// slide/tab change, not just observed once).
	// ---------------------------------------------------------------
	function initLazyMediaVideos() {
		var videos = document.querySelectorAll( '.itoi-media-video' );
		if ( ! videos.length || reduceMotion ) {
			return;
		}
		if ( ! ( 'IntersectionObserver' in window ) ) {
			// No IntersectionObserver support — fall back to playing
			// everything immediately rather than a video that never plays.
			videos.forEach( function ( video ) {
				video.play().catch( function () {} );
			} );
			return;
		}
		// Tracks each video's own in-flight play() promise. Calling pause()
		// while play() is still resolving (normal here, since preload="none"
		// means play() has to fetch data first) throws an AbortError and can
		// permanently strand the video at readyState 0 — never actually
		// loads — if scrolled past quickly enough that pause() fires before
		// play() settles. Confirmed via a real fast-scroll test (2026-08-04,
		// see NOTES.md): 4 of 5 videos on this page never loaded at all after
		// scrolling straight through it, all rejected with exactly that
		// AbortError. Fix is MDN's own documented pattern for this exact
		// race — wait for the pending play() to settle before pausing.
		var pendingPlay = new WeakMap();
		var observer = new IntersectionObserver(
			function ( entries ) {
				entries.forEach( function ( entry ) {
					var video = entry.target;
					if ( entry.isIntersecting ) {
						var playPromise = video.play();
						pendingPlay.set( video, playPromise );
						if ( playPromise ) {
							playPromise.catch( function () {} );
						}
					} else {
						var priorPlay = pendingPlay.get( video );
						if ( priorPlay ) {
							priorPlay.then( function () {
								video.pause();
							} ).catch( function () {} );
						} else {
							video.pause();
						}
					}
				} );
			},
			{ rootMargin: '200px 0px' }
		);
		videos.forEach( function ( video ) {
			observer.observe( video );
		} );
	}

	// ---------------------------------------------------------------
	// 1. Rotating ticker banner
	// ---------------------------------------------------------------
	function initTicker() {
		var lines = document.querySelectorAll( '.promo-line' );
		if ( ! lines.length || reduceMotion ) {
			return;
		}
		var idx = 0;
		setInterval( function () {
			idx = ( idx + 1 ) % lines.length;
			lines.forEach( function ( line ) {
				line.style.transform = 'translateY(' + ( -idx * 20 ) + 'px)';
			} );
		}, 4000 );
	}

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
	var heroSlidesFallback = [
		{
			headline: 'Enterprise security systems that eliminate operational blind spots.',
			subcopy: 'Integrated facial recognition, ANPR, access control, and intelligent surveillance for schools, healthcare facilities, logistics sites, and commercial property across Australia.',
		},
		{
			headline: 'Know what happened, the moment it happens',
			subcopy: 'Real-time detection across every zone, with alerts routed straight to the right person.',
		},
		{
			headline: 'One platform for cameras, access and analytics',
			subcopy: 'Retail analytics, facial recognition and smart security, unified in a single dashboard.',
		},
		{
			headline: 'Built for Australian retail, venues and government',
			subcopy: 'From single stores to council-wide deployments, backed by 24/7 local support.',
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
				// Same reduce-motion guard as initHeroDetectionBoxes() —
				// never autoplay a background video for that visitor.
				if ( reduceMotion ) {
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

			if ( ! reduceMotion ) {
				clearInterval( heroTimer );
				heroTimer = setInterval( function () {
					goToHeroSlide( ( heroIdx + 1 ) % heroSlides.length );
				}, 5000 );
			}
		}

		goToHeroSlide( 0 );
	}

	function initHeroDetectionBoxes() {
		var heroBg = document.getElementById( 'heroBg' );
		if ( ! heroBg ) {
			return;
		}

		// Optional hero background video (Site Settings, added 2026-07-23) —
		// never autoplays for a reduce-motion visitor. The <video> tag's own
		// `autoplay` attribute already started it by the time this runs, so
		// explicitly pause + reset to frame 0 rather than relying on the
		// attribute alone; the `poster` image (or the plain gradient, if no
		// poster/photo was set) shows in its place.
		var heroBgVideo = document.getElementById( 'heroBgVideo' );
		if ( heroBgVideo && reduceMotion ) {
			heroBgVideo.pause();
			heroBgVideo.currentTime = 0;
			heroBgVideo.removeAttribute( 'autoplay' );
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


	// "Explore our solutions" homepage carousel (front-page.php,
	// id="exploreSolutions") — replaced 2026-07-30 (see NOTES.md) a broken
	// .flip-card grid. Needs a responsive N-visible (1/2/3 at mobile/
	// tablet/desktop) track that shifts by exactly 1 slide — a real
	// translateX slide, not a 3-fixed-slot swap. Breakpoints/visible-count
	// live only in CSS (--sc-visible custom property, src/tailwind.css) —
	// this function measures rendered slide width via
	// getBoundingClientRect(), never hardcodes a pixel breakpoint, so the
	// two stay impossible to drift apart.
	function initSolutionsCarousel() {
		var wrap = document.getElementById( 'solutionsCarousel' );
		var viewport = document.getElementById( 'solutionsCarouselViewport' );
		var track = document.getElementById( 'solutionsCarouselTrack' );
		if ( ! wrap || ! viewport || ! track ) {
			return;
		}
		var slides = Array.prototype.slice.call( track.children );
		var total = slides.length;
		if ( ! total ) {
			return;
		}

		// prefers-reduced-motion: never add .is-active at all — the CSS
		// default state (no class) IS the static wrapped grid showing all
		// 8 cards, so returning here satisfies "show the static grid
		// instead" exactly, with no separate code path to maintain.
		if ( reduceMotion ) {
			return;
		}
		wrap.classList.add( 'is-active' );

		var dots = Array.prototype.slice.call( wrap.querySelectorAll( '.solutions-carousel-dot' ) );
		var prevBtn = document.getElementById( 'solCarouselPrev' );
		var nextBtn = document.getElementById( 'solCarouselNext' );
		var playPauseBtn = document.getElementById( 'solCarouselPlayPause' );
		var pauseIcon = document.getElementById( 'solCarouselPauseIcon' );
		var playIcon = document.getElementById( 'solCarouselPlayIcon' );

		var AUTO_MS = 3000;
		var index = 0;
		var timer = null;
		var userPaused = false;
		var hoverPaused = false;
		var visibleCount = 1;
		var maxIndex = 0;

		function measure() {
			visibleCount = parseInt( getComputedStyle( track ).getPropertyValue( '--sc-visible' ), 10 ) || 1;
			maxIndex = Math.max( 0, total - visibleCount );
			index = Math.min( index, maxIndex );
		}

		function render() {
			var first = slides[ 0 ].getBoundingClientRect();
			var second = total > 1 ? slides[ 1 ].getBoundingClientRect() : first;
			var step = second.left - first.left;
			track.style.transform = 'translateX(-' + ( index * step ) + 'px)';
			// Range check, not exact-index equality: with 8 dots and a
			// clamped maxIndex (fewer than 8 when more than 1 card is
			// visible), an exact match would leave the last few dots never
			// showing as current even though clicking them does bring
			// those cards into view. This makes every dot reachable as
			// "current" for some scroll position.
			dots.forEach( function ( dot, i ) {
				dot.classList.toggle( 'is-current', i >= index && i < index + visibleCount );
			} );
		}

		function stopTimer() {
			if ( timer ) {
				clearInterval( timer );
				timer = null;
			}
		}

		function startTimer() {
			stopTimer();
			if ( userPaused || hoverPaused ) {
				return;
			}
			timer = setInterval( function () {
				index = index >= maxIndex ? 0 : index + 1;
				render();
			}, AUTO_MS );
		}

		function goTo( i ) {
			measure();
			index = Math.min( Math.max( i, 0 ), maxIndex );
			render();
			startTimer();
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

		if ( playPauseBtn ) {
			playPauseBtn.addEventListener( 'click', function () {
				userPaused = ! userPaused;
				playPauseBtn.setAttribute( 'aria-pressed', String( userPaused ) );
				playPauseBtn.setAttribute( 'aria-label', userPaused ? 'Resume auto-advance' : 'Pause auto-advance' );
				pauseIcon.classList.toggle( 'hidden', userPaused );
				playIcon.classList.toggle( 'hidden', ! userPaused );
				if ( userPaused ) {
					stopTimer();
				} else {
					startTimer();
				}
			} );
		}

		// Hover pause / immediate resume on mouse-leave — no resume delay
		// needed here. Keyboard focus pauses/resumes the same way.
		wrap.addEventListener( 'mouseenter', function () {
			hoverPaused = true;
			stopTimer();
		} );
		wrap.addEventListener( 'mouseleave', function () {
			hoverPaused = false;
			startTimer();
		} );
		wrap.addEventListener( 'focusin', function () {
			hoverPaused = true;
			stopTimer();
		} );
		wrap.addEventListener( 'focusout', function ( e ) {
			if ( ! wrap.contains( e.relatedTarget ) ) {
				hoverPaused = false;
				startTimer();
			}
		} );

		var resizeTimer = null;
		window.addEventListener( 'resize', function () {
			clearTimeout( resizeTimer );
			resizeTimer = setTimeout( function () {
				measure();
				render();
			}, 150 );
		} );

		measure();
		render();
		startTimer();
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

		var AUTO_MS = 6000;
		var RESUME_MS = 4000;
		var index = 0;
		var timer = null;
		var resumeTimeout = null;
		var inView = false;

		function render() {
			cards.forEach( function ( card, i ) {
				card.classList.toggle( 'is-hidden', i !== index );
			} );
			dots.forEach( function ( dot, i ) {
				dot.classList.toggle( 'is-current', i === index );
			} );
		}

		function stopTimer() {
			if ( timer ) {
				clearInterval( timer );
				timer = null;
			}
		}

		function startTimer() {
			stopTimer();
			if ( reduceMotion || ! inView ) {
				return;
			}
			timer = setInterval( function () {
				goTo( index + 1, { silent: true } );
			}, AUTO_MS );
		}

		function goTo( i, opts ) {
			index = ( ( i % total ) + total ) % total;
			render();
			if ( ! ( opts && opts.silent ) ) {
				startTimer();
			}
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

		function scheduleResume() {
			clearTimeout( resumeTimeout );
			resumeTimeout = setTimeout( startTimer, RESUME_MS );
		}
		root.addEventListener( 'mouseenter', stopTimer );
		root.addEventListener( 'mouseleave', scheduleResume );
		root.addEventListener( 'touchstart', stopTimer, { passive: true } );
		root.addEventListener( 'touchend', scheduleResume, { passive: true } );

		if ( 'IntersectionObserver' in window ) {
			var io = new IntersectionObserver( function ( entries ) {
				entries.forEach( function ( entry ) {
					inView = entry.isIntersecting;
					if ( inView ) {
						startTimer();
					} else {
						stopTimer();
					}
				} );
			}, { threshold: 0.3 } );
			io.observe( root );
		} else {
			inView = true;
			startTimer();
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
	// Solution Builder popup (formerly "Find Your Fit") — modal open/close +
	// 7-step navigation/selection, ending in a handoff to /solution-builder/
	// rather than an inline result. Question/option copy is server-rendered
	// from the same PHP source the dedicated page uses (footer.php calls
	// inc/solution-builder.php's option-list functions directly, not ACF —
	// see NOTES.md, 2026-07-24).
	// ---------------------------------------------------------------
	function initFinder() {
		var overlay = document.getElementById( 'finderOverlay' );
		var trigger = document.getElementById( 'finderTrigger' );
		var closeBtn = document.getElementById( 'finderClose' );
		var steps = document.querySelectorAll( '.finder-step' );
		var progressWrap = document.getElementById( 'finderProgress' );
		var backBtn = document.getElementById( 'finderBack' );
		var nextBtn = document.getElementById( 'finderNext' );
		var nav = document.getElementById( 'finderNav' );
		var loadingEl = document.getElementById( 'finderLoading' );
		if ( ! overlay || ! trigger || ! steps.length ) {
			return;
		}

		// 2026-07-24: this popup now runs the Solution Builder's 7 questions
		// (see footer.php) and hands off to /solution-builder/ for results
		// instead of resolving a case-study/solution match inline — the old
		// itoiFinderData routing payload (caseStudyByIndustry/solutionByNeed)
		// no longer exists (inc/finder.php deleted). See NOTES.md.
		var answers = { challenges: [] };
		var current = 0;
		var totalQuestionSteps = steps.length;

		// --- Modal open/close ---
		function openModal() {
			overlay.classList.add( 'open' );
			trigger.classList.add( 'visible' );
			document.body.style.overflow = 'hidden';
		}
		function closeModal() {
			overlay.classList.remove( 'open' );
			document.body.style.overflow = '';
			trigger.classList.add( 'visible' ); // stays available to reopen
		}
		trigger.addEventListener( 'click', openModal );
		closeBtn.addEventListener( 'click', closeModal );
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

		// Auto-open once per session, then leave the floating trigger for later.
		// Unconditional on reduced-motion by design (matches the mockup exactly
		// and the explicit spec) — this is timed content appearing, not an
		// animation; the global prefers-reduced-motion rule already strips the
		// fade/scale transition so it just appears instantly instead.
		//
		// Guarded against the homepage's platform-demo modal (also z-index
		// 100, also fixed) being open at the 3.5s mark — without this, the
		// Finder would silently pop up on top of it and swallow the demo's
		// own clicks (found via testing: a visitor dwelling on the demo
		// past 3.5s could no longer switch its tabs). Skips outright rather
		// than retrying once the demo closes — an initial retry-on-close
		// attempt made the Finder pop up the instant the visitor dismissed
		// the demo, which is worse (immediate second modal) than just not
		// auto-showing this pageview; sessionStorage is deliberately left
		// unset so it still gets a fresh 3.5s chance on the next pageview
		// within the same session.
		if ( ! sessionStorage.getItem( 'finderAutoShown' ) ) {
			setTimeout( function () {
				var demo = document.getElementById( 'platformDemoOverlay' );
				if ( demo && demo.classList.contains( 'open' ) ) {
					return;
				}
				openModal();
				sessionStorage.setItem( 'finderAutoShown', '1' );
			}, 3500 );
		} else {
			trigger.classList.add( 'visible' );
		}

		// --- Step logic ---
		for ( var i = 0; i < totalQuestionSteps; i++ ) {
			var dot = document.createElement( 'div' );
			dot.className = 'finder-dot';
			progressWrap.appendChild( dot );
		}
		var dots = progressWrap.querySelectorAll( '.finder-dot' );

		function renderProgress() {
			dots.forEach( function ( d, di ) {
				d.classList.toggle( 'done', di < current );
				d.classList.toggle( 'current', di === current );
			} );
		}

		function showStep( i ) {
			steps.forEach( function ( s ) {
				s.classList.toggle( 'active', +s.dataset.step === i );
			} );
			current = i;
			renderProgress();
			backBtn.disabled = ( 0 === i );
			nav.style.display = ( i === totalQuestionSteps ) ? 'none' : 'flex';
			updateNextState();
		}

		function updateNextState() {
			var stepEl = document.querySelector( '.finder-step[data-step="' + current + '"]' );
			var group = stepEl.querySelector( '.finder-options' );
			if ( ! group ) {
				nextBtn.disabled = false;
				return;
			}
			if ( 'multi' === group.dataset.select ) {
				nextBtn.disabled = false; // "choose any that apply" — zero selections is valid
				return;
			}
			nextBtn.disabled = ! answers[ group.dataset.group ];
		}

		document.querySelectorAll( '.finder-opt' ).forEach( function ( opt ) {
			opt.addEventListener( 'click', function () {
				var group = opt.closest( '.finder-options' );
				var groupName = group.dataset.group;

				if ( 'multi' === group.dataset.select ) {
					opt.classList.toggle( 'selected' );
					var idx = answers.challenges.indexOf( opt.dataset.value );
					if ( opt.classList.contains( 'selected' ) && -1 === idx ) {
						answers.challenges.push( opt.dataset.value );
					} else if ( ! opt.classList.contains( 'selected' ) && idx > -1 ) {
						answers.challenges.splice( idx, 1 );
					}
				} else {
					group.querySelectorAll( '.finder-opt' ).forEach( function ( o ) {
						o.classList.remove( 'selected' );
					} );
					opt.classList.add( 'selected' );
					answers[ groupName ] = opt.dataset.value;
				}
				updateNextState();
			} );
		} );

		backBtn.addEventListener( 'click', function () {
			if ( current > 0 ) {
				showStep( current - 1 );
			}
		} );
		nextBtn.addEventListener( 'click', function () {
			if ( current < totalQuestionSteps - 1 ) {
				showStep( current + 1 );
			} else {
				handOffToSolutionBuilder();
			}
		} );

		// Hand off to the dedicated page instead of resolving/showing a
		// result inline (superseded 2026-07-24 — see NOTES.md). The 7
		// answers are stashed in sessionStorage under the same key
		// assets/js/solution-builder.js reads on load; that page runs the
		// real scoring/ROI/timeline calculation and shows results
		// immediately, without re-asking anything.
		function handOffToSolutionBuilder() {
			nav.style.display = 'none';
			if ( loadingEl ) {
				loadingEl.hidden = false;
			}
			try {
				sessionStorage.setItem( 'itoi_solution_builder_answers', JSON.stringify( answers ) );
			} catch ( e ) {
				// sessionStorage unavailable (private mode etc.) — the destination
				// page's own standalone form still works, just without the handoff.
			}
			window.location.href = ( window.itoiSolutionBuilderConfig && window.itoiSolutionBuilderConfig.url ) || '/solution-builder/';
		}
	}

	// ---------------------------------------------------------------
	// Customers page — category filter pills (page-customers.php).
	// No-op on every other page (querySelector returns null, early exit).
	// ---------------------------------------------------------------
	function initClientFilter() {
		var row = document.getElementById( 'clientFilterRow' );
		var grid = document.getElementById( 'clientGrid' );
		if ( ! row || ! grid ) {
			return;
		}

		var pills = row.querySelectorAll( '.client-filter-pill' );
		var cards = grid.querySelectorAll( '.client-card' );

		function setActive( filter ) {
			pills.forEach( function ( pill ) {
				var isActive = pill.dataset.filter === filter;
				pill.classList.toggle( 'active', isActive );
				pill.setAttribute( 'aria-pressed', isActive ? 'true' : 'false' );
				pill.classList.toggle( 'border-ink', isActive );
				pill.classList.toggle( 'bg-ink', isActive );
				pill.classList.toggle( 'text-white', isActive );
				pill.classList.toggle( 'border-line', ! isActive );
				pill.classList.toggle( 'text-ink', ! isActive );
			} );

			cards.forEach( function ( card ) {
				var show = filter === 'all' || card.dataset.category === filter;
				card.style.display = show ? '' : 'none';
			} );
		}

		pills.forEach( function ( pill ) {
			pill.addEventListener( 'click', function () {
				setActive( pill.dataset.filter );
			} );
		} );
	}

	// ---------------------------------------------------------------
	// Shared flip-card component — About page "Partners, not vendors",
	// homepage solutions grid, and the homepage Industries carousel all use
	// the same .flip-card markup/CSS (src/tailwind.css). Desktop flips via
	// pure CSS :hover/:focus-within; this handles touch devices, which have
	// no hover capability at all (`(hover: none)`) — tap toggles
	// `.is-flipped`, tap again flips back. A tap on a real link inside
	// .flip-card-back navigates instead of toggling, on every card.
	//
	// Industries-only extension, opt-in via `data-href` (the other two
	// usages never set it, so their behavior is byte-for-byte unchanged):
	// clicking/tapping the card itself — not just the explicit "Learn
	// more" link — also navigates, once the card is actually flipped.
	// Desktop: hover already flips it via CSS before any click can land,
	// so a click anywhere on the card (that isn't the inner link, handled
	// above) navigates immediately. Touch: there's no hover pre-flip, so
	// the first tap only flips (`.is-flipped` not yet set → click falls
	// through to the existing toggle() below); a second tap — card is now
	// `.is-flipped` — navigates. This is what stops an accidental single
	// tap from carrying a mobile visitor away before they've seen the
	// back face at all.
	// ---------------------------------------------------------------
	function initFlipCards() {
		var cards = document.querySelectorAll( '.flip-card' );
		if ( ! cards.length ) {
			return;
		}

		var isTouch = window.matchMedia( '(hover: none)' ).matches;

		cards.forEach( function ( card ) {
			// aria-pressed is only an ARIA-allowed attribute on elements with a
			// button role (axe-core's aria-allowed-attr flags it otherwise) —
			// only ever set role="button"/aria-pressed together, and only on
			// cards with no nested link. Cards with a real link in the back
			// face (the homepage solutions grid) skip both; tabindex +
			// :focus-within (CSS) + the keydown handler below still make them
			// keyboard-operable without a second, nested interactive role.
			var hasLink = !! card.querySelector( '.flip-card-back a' );
			if ( ! hasLink ) {
				card.setAttribute( 'role', 'button' );
				card.setAttribute( 'aria-pressed', 'false' );
			}
			card.setAttribute( 'tabindex', '0' );

			var clickThroughHref = card.dataset.href || null;

			function toggle() {
				var flipped = card.classList.toggle( 'is-flipped' );
				if ( ! hasLink ) {
					card.setAttribute( 'aria-pressed', flipped ? 'true' : 'false' );
				}
			}

			card.addEventListener( 'click', function ( e ) {
				if ( e.target.closest( 'a' ) ) {
					return;
				}
				if ( clickThroughHref && ( ! isTouch || card.classList.contains( 'is-flipped' ) ) ) {
					window.location.href = clickThroughHref;
					return;
				}
				if ( isTouch ) {
					toggle();
				}
			} );

			card.addEventListener( 'keydown', function ( e ) {
				if ( e.target.closest( 'a' ) ) {
					return;
				}
				if ( 'Enter' === e.key || ' ' === e.key || 'Spacebar' === e.key ) {
					e.preventDefault();
					// :focus-within has already flipped the card to reveal the
					// back face by the time focus reached it (tabindex="0" on
					// the card itself counts), so — unlike touch — there's no
					// "first press just flips" step needed here for keyboard.
					if ( clickThroughHref ) {
						window.location.href = clickThroughHref;
					} else {
						toggle();
					}
				}
			} );
		} );
	}

	// ---------------------------------------------------------------
	// Scroll-triggered "detection reveal" — signature-navy corner brackets +
	// confidence tag on major content blocks (signature layer, expanded
	// scope). IntersectionObserver, not a scroll listener, for perf.
	// reduce-motion: final state shown immediately, no brackets/tag at all
	// (handled in CSS too, but short-circuiting here skips the observer
	// entirely rather than relying on the CSS override alone).
	// ---------------------------------------------------------------
	function initRevealObserver() {
		// :not(.no-detect-reveal) is a defensive exclusion, not the primary
		// safeguard — the primary one is that this is opt-in (.itoi-reveal
		// must be added deliberately), so nothing gets the treatment by
		// default. Interactive per-industry components (the hero/funnel
		// card and its replacements) are marked .no-detect-reveal so they
		// stay excluded even if a future edit accidentally adds
		// .itoi-reveal to a parent that wraps one of them.
		var items = document.querySelectorAll( '.itoi-reveal:not(.no-detect-reveal)' );
		if ( ! items.length ) {
			return;
		}

		if ( reduceMotion ) {
			items.forEach( function ( el ) {
				el.classList.add( 'is-visible' );
			} );
			return;
		}

		var observer = new IntersectionObserver(
			function ( entries, obs ) {
				entries.forEach( function ( entry ) {
					if ( entry.isIntersecting ) {
						entry.target.classList.add( 'is-visible' );
						obs.unobserve( entry.target );
					}
				} );
			},
			{ threshold: 0.3 }
		);

		items.forEach( function ( el ) {
			observer.observe( el );
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
	function initTrustMetricsCounters() {
		var cards = document.querySelectorAll( '[data-trust-counter]' );
		if ( ! cards.length ) {
			return;
		}

		// Leading integer or decimal (e.g. "100", "24", "99.9", "2"),
		// captured separately from whatever text follows it (e.g. "+",
		// "/7", "%", " hour").
		var NUMBER_RE = /^(\d+(?:\.\d+)?)(.*)$/;

		function setFinal( el ) {
			el.textContent = el.getAttribute( 'data-target' ) || '';
		}

		if ( reduceMotion ) {
			cards.forEach( setFinal );
			return;
		}

		function animate( el ) {
			var target = el.getAttribute( 'data-target' ) || '';
			var match  = NUMBER_RE.exec( target );
			if ( ! match ) {
				// No leading number to count (shouldn't normally happen with
				// this section's own content) — just show the final value.
				setFinal( el );
				return;
			}

			var endValue  = parseFloat( match[ 1 ] );
			var suffix    = match[ 2 ];
			var decimals  = match[ 1 ].indexOf( '.' ) !== -1 ? match[ 1 ].split( '.' )[ 1 ].length : 0;
			var duration  = 1200;
			var startTime = null;

			function tick( now ) {
				if ( startTime === null ) {
					startTime = now;
				}
				var progress = Math.min( ( now - startTime ) / duration, 1 );
				// ease-out cubic — starts fast, settles gently, matching this
				// theme's other motion (0.2s ease-out CTA lifts, etc.) rather
				// than a flat linear tick.
				var eased   = 1 - Math.pow( 1 - progress, 3 );
				var current = endValue * eased;
				el.textContent = current.toFixed( decimals ) + suffix;
				if ( progress < 1 ) {
					requestAnimationFrame( tick );
				} else {
					el.textContent = target;
				}
			}

			requestAnimationFrame( tick );
		}

		var observer = new IntersectionObserver(
			function ( entries, obs ) {
				entries.forEach( function ( entry ) {
					if ( entry.isIntersecting ) {
						animate( entry.target );
						obs.unobserve( entry.target );
					}
				} );
			},
			{ threshold: 0.3 }
		);

		cards.forEach( function ( el ) {
			observer.observe( el );
		} );
	}

	// ---------------------------------------------------------------
	// Full-screen mega menu. Reuses the Find Your Fit modal's open/close
	// pattern (body scroll lock, overlay-click + Escape + X to close)
	// rather than new modal logic. Preview content is server-rendered
	// (data-eyebrow/-headline/-desc attributes from ACF) and swapped on
	// hover/focus — no content is authored in JS.
	// ---------------------------------------------------------------
	function initMegaMenu() {
		var trigger = document.getElementById( 'menuTrigger' );
		var overlay = document.getElementById( 'megaMenu' );
		var closeBtn = document.getElementById( 'megaMenuClose' );
		var navItems = document.querySelectorAll( '.mega-menu-item' );
		var previewEyebrow = document.getElementById( 'megaMenuEyebrow' );
		var previewHeadline = document.getElementById( 'megaMenuHeadline' );
		var previewDesc = document.getElementById( 'megaMenuDesc' );
		var previewPanel = document.getElementById( 'megaMenuPreview' );

		if ( ! trigger || ! overlay ) {
			return;
		}

		function openMenu() {
			overlay.classList.add( 'open' );
			trigger.classList.add( 'is-open' );
			trigger.setAttribute( 'aria-expanded', 'true' );
			document.body.style.overflow = 'hidden';
		}
		function closeMenu() {
			overlay.classList.remove( 'open' );
			trigger.classList.remove( 'is-open' );
			trigger.setAttribute( 'aria-expanded', 'false' );
			document.body.style.overflow = '';
		}

		trigger.addEventListener( 'click', function () {
			if ( overlay.classList.contains( 'open' ) ) {
				closeMenu();
			} else {
				openMenu();
			}
		} );
		if ( closeBtn ) {
			closeBtn.addEventListener( 'click', closeMenu );
		}
		document.addEventListener( 'keydown', function ( e ) {
			if ( 'Escape' === e.key && overlay.classList.contains( 'open' ) ) {
				closeMenu();
			}
		} );

		function swapPreview( item ) {
			if ( ! previewPanel ) {
				return;
			}
			navItems.forEach( function ( el ) {
				el.classList.toggle( 'is-active', el === item );
			} );
			previewPanel.classList.remove( 'is-visible' );
			window.setTimeout( function () {
				previewEyebrow.textContent = item.dataset.eyebrow || '';
				previewHeadline.textContent = item.dataset.headline || '';
				previewDesc.textContent = item.dataset.desc || '';
				previewPanel.classList.add( 'is-visible' );
			}, reduceMotion ? 0 : 120 );
		}

		navItems.forEach( function ( item ) {
			item.addEventListener( 'mouseenter', function () {
				swapPreview( item );
			} );
			item.addEventListener( 'focus', function () {
				swapPreview( item );
			} );
		} );

		if ( navItems.length ) {
			swapPreview( navItems[ 0 ] );
			if ( previewPanel ) {
				previewPanel.classList.add( 'is-visible' );
			}
		}
	}

	// ---------------------------------------------------------------
	// Foot-traffic funnel (Retail industry page) — slider drives a live
	// recompute of qualified leads/revenue using a clearly-labeled
	// illustrative assumption (conversion rate + value/lead, both ACF
	// fields, never a real ITOI performance claim — PROJECT.md §6). The
	// drifting/pulsing markers are decorative (aria-hidden in markup);
	// the real numbers live in the text stats, same pattern as the
	// hero's detection boxes and the traffic-demo widget's bars.
	// ---------------------------------------------------------------
	function initFootTrafficFunnel() {
		var section = document.getElementById( 'funnelSection' );
		var slider = document.getElementById( 'funnelTrafficSlider' );
		var numberInput = document.getElementById( 'funnelTrafficInput' );
		var trafficValueEl = document.getElementById( 'funnelTrafficValue' );
		var leadsValueEl = document.getElementById( 'funnelLeadsValue' );
		var revenueValueEl = document.getElementById( 'funnelRevenueValue' );
		var trafficMarkersWrap = document.getElementById( 'funnelMarkersTraffic' );
		var leadsMarkersWrap = document.getElementById( 'funnelMarkersLeads' );
		if ( ! section || ! slider || ! numberInput || ! trafficValueEl || ! leadsValueEl || ! revenueValueEl ) {
			return;
		}

		var conversionRate = parseFloat( section.dataset.conversionRate ) || 0;
		var valuePerLead = parseFloat( section.dataset.valuePerLead ) || 0;
		var lastLeads = 0;
		var lastRevenue = 0;

		function formatNumber( n ) {
			return Math.max( 0, Math.round( n ) ).toLocaleString( 'en-AU' );
		}
		function formatCurrency( n ) {
			return '$' + Math.max( 0, Math.round( n ) ).toLocaleString( 'en-AU' );
		}

		function animateCount( el, from, to, formatter ) {
			if ( reduceMotion ) {
				el.textContent = formatter( to );
				return;
			}
			var duration = 500;
			var start = null;
			function step( ts ) {
				if ( ! start ) {
					start = ts;
				}
				var progress = Math.min( ( ts - start ) / duration, 1 );
				el.textContent = formatter( from + ( to - from ) * progress );
				if ( progress < 1 ) {
					window.requestAnimationFrame( step );
				} else {
					el.textContent = formatter( to );
				}
			}
			window.requestAnimationFrame( step );
		}

		// Decorative marker clusters — illustrative counts, not literal
		// 1:1 with the real numbers (same convention as the hero's
		// detection boxes / traffic widget's bars).
		function buildMarkers( wrap, count ) {
			if ( ! wrap ) {
				return;
			}
			wrap.innerHTML = '';
			for ( var i = 0; i < count; i++ ) {
				var m = document.createElement( 'div' );
				m.className = 'funnel-marker';
				m.style.left = ( 6 + Math.random() * 78 ) + '%';
				m.style.top = ( 8 + Math.random() * 70 ) + '%';
				m.style.animationDelay = ( Math.random() * 2 ) + 's';
				wrap.appendChild( m );
			}
		}
		// Qualified markers use fixed, evenly-spread slots (not random) —
		// only 4 of them in a small area, so randomizing caused visual
		// overlap. A slot layout keeps each bounding box legible.
		function buildQualifiedMarkers( wrap ) {
			if ( ! wrap ) {
				return;
			}
			wrap.innerHTML = '';
			var slots = [
				{ left: '10%', top: '10%' },
				{ left: '52%', top: '18%' },
				{ left: '22%', top: '55%' },
				{ left: '60%', top: '58%' },
			];
			slots.forEach( function ( slot, i ) {
				var m = document.createElement( 'div' );
				m.className = 'funnel-marker funnel-marker--qualified';
				m.style.left = slot.left;
				m.style.top = slot.top;
				m.style.animationDelay = ( i * 0.3 ) + 's';
				wrap.appendChild( m );
			} );
		}
		buildMarkers( trafficMarkersWrap, 12 );
		buildQualifiedMarkers( leadsMarkersWrap );

		function render( traffic ) {
			traffic = Math.max( 0, traffic );
			var leads = traffic * ( conversionRate / 100 );
			var revenue = leads * valuePerLead;

			trafficValueEl.textContent = formatNumber( traffic );
			animateCount( leadsValueEl, lastLeads, leads, formatNumber );
			animateCount( revenueValueEl, lastRevenue, revenue, formatCurrency );
			lastLeads = leads;
			lastRevenue = revenue;
		}

		slider.addEventListener( 'input', function () {
			numberInput.value = slider.value;
			render( parseFloat( slider.value ) );
		} );
		numberInput.addEventListener( 'input', function () {
			var val = parseFloat( numberInput.value ) || 0;
			var clamped = Math.min( Math.max( val, parseFloat( slider.min ) ), parseFloat( slider.max ) );
			slider.value = clamped;
			render( val );
		} );

		render( parseFloat( slider.value ) );
	}

	// ---------------------------------------------------------------
	// Hospitality industry page — click-through guest-journey timeline.
	// Stages toggle independently (any order, any combination, re-click
	// to turn off); each active stage shows its own reveal card, and a
	// completeness bar/percentage tracks how many of the (data-driven)
	// stage count are currently active. All copy is server-rendered from
	// ACF — this only toggles classes/text already in the markup.
	// ---------------------------------------------------------------
	function initHospitalityTimeline() {
		var root = document.getElementById( 'hospitalityTimeline' );
		if ( ! root ) {
			return;
		}

		var stageCount = parseInt( root.dataset.stageCount, 10 ) || 0;
		var pills = root.querySelectorAll( '.hospitality-stage-pill' );
		var revealCards = root.querySelectorAll( '.hospitality-reveal-card' );
		var pctLabel = document.getElementById( 'hospitalityCompletenessPct' );
		var bar = document.getElementById( 'hospitalityCompletenessBar' );
		var resetBtn = document.getElementById( 'hospitalityReset' );
		var activeStages = {};

		function updateCompleteness() {
			var activeCount = Object.keys( activeStages ).filter( function ( k ) {
				return activeStages[ k ];
			} ).length;
			var pct = stageCount ? Math.round( ( activeCount / stageCount ) * 100 ) : 0;
			if ( pctLabel ) {
				pctLabel.textContent = pct + '%';
			}
			if ( bar ) {
				bar.style.width = pct + '%';
			}
		}

		function setStage( index, active ) {
			activeStages[ index ] = active;
			pills.forEach( function ( pill ) {
				if ( parseInt( pill.dataset.stageIndex, 10 ) !== index ) {
					return;
				}
				pill.classList.toggle( 'is-active', active );
				pill.setAttribute( 'aria-pressed', active ? 'true' : 'false' );
			} );
			revealCards.forEach( function ( card ) {
				if ( parseInt( card.dataset.stageIndex, 10 ) !== index ) {
					return;
				}
				card.classList.toggle( 'hidden', ! active );
			} );
			updateCompleteness();
		}

		pills.forEach( function ( pill ) {
			pill.addEventListener( 'click', function () {
				var index = parseInt( pill.dataset.stageIndex, 10 );
				setStage( index, ! activeStages[ index ] );
			} );
		} );

		if ( resetBtn ) {
			resetBtn.addEventListener( 'click', function () {
				pills.forEach( function ( pill ) {
					setStage( parseInt( pill.dataset.stageIndex, 10 ), false );
				} );
			} );
		}
	}

	// ---------------------------------------------------------------
	// Banking & Finance industry page — access scenario simulator.
	// Clicking a scenario card marks it active (signature navy) and replaces the
	// single result panel's content — never stacks multiple results.
	// The log line's timestamp is generated client-side from the real
	// current time; the log message text itself is server-rendered
	// ACF copy and stays illustrative (PROJECT.md §6).
	// ---------------------------------------------------------------
	function initBankingSimulator() {
		var root = document.getElementById( 'bankingSimulator' );
		if ( ! root ) {
			return;
		}

		var cards = root.querySelectorAll( '.banking-scenario-card' );
		var panel = document.getElementById( 'bankingResultPanel' );
		var iconEl = document.getElementById( 'bankingResultIcon' );
		var labelEl = document.getElementById( 'bankingResultLabel' );
		var logEl = document.getElementById( 'bankingResultLog' );

		function formatTime( date ) {
			var hours = date.getHours();
			var minutes = date.getMinutes();
			var seconds = date.getSeconds();
			var period = hours >= 12 ? 'PM' : 'AM';
			hours = hours % 12 || 12;
			return hours + ':' + ( minutes < 10 ? '0' : '' ) + minutes + ':' + ( seconds < 10 ? '0' : '' ) + seconds + ' ' + period;
		}

		function showResult( card ) {
			var isAlert = card.dataset.resultType === 'alert';
			var message = card.dataset.logMessage || '';

			cards.forEach( function ( c ) {
				c.classList.toggle( 'is-active', c === card );
			} );

			// Re-trigger the CSS entrance animations even when the same
			// scenario (or another one with the same result type) is
			// clicked twice in a row.
			panel.classList.remove( 'hidden' );
			iconEl.style.animation = 'none';
			void iconEl.offsetWidth;
			iconEl.style.animation = '';

			iconEl.textContent = isAlert ? '⚠' : '✓';
			iconEl.classList.toggle( 'banking-result-icon--alert', isAlert );

			labelEl.innerHTML = '';
			if ( isAlert ) {
				var badge = document.createElement( 'span' );
				badge.className = 'banking-flag-badge';
				badge.textContent = 'Flagged';
				labelEl.appendChild( badge );
				labelEl.appendChild( document.createElement( 'br' ) );
			}
			labelEl.appendChild( document.createTextNode( message ) );

			logEl.style.animation = 'none';
			void logEl.offsetWidth;
			logEl.style.animation = '';
			logEl.textContent = formatTime( new Date() ) + ' — ' + message;
		}

		cards.forEach( function ( card ) {
			card.addEventListener( 'click', function () {
				showResult( card );
			} );
		} );
	}

	// ---------------------------------------------------------------
	// Government & Councils industry page — live funding-allocation chart.
	// Checkboxes toggle independently (any combination, any order); each
	// category's bar height is its ACF baseline plus the sum of every
	// currently-checked box's impact on that category (parsed from the
	// box's "impact_map" ACF field, e.g. "0:20,1:15" — no impact mapping
	// is hardcoded in JS, it's entirely data-driven from ACF).
	// ---------------------------------------------------------------
	function initGovernmentFundingChart() {
		var root = document.getElementById( 'governmentFundingChart' );
		if ( ! root ) {
			return;
		}

		var MAX_BAR_HEIGHT = 160;
		var checkboxes = root.querySelectorAll( '.government-checkbox' );
		var barWraps = root.querySelectorAll( '.government-chart-bar-wrap' );

		function parseImpactMap( str ) {
			var map = {};
			( str || '' ).split( ',' ).forEach( function ( pair ) {
				var parts = pair.split( ':' );
				if ( parts.length !== 2 ) {
					return;
				}
				var catIndex = parseInt( parts[ 0 ], 10 );
				var amount = parseFloat( parts[ 1 ] );
				if ( ! isNaN( catIndex ) && ! isNaN( amount ) ) {
					map[ catIndex ] = amount;
				}
			} );
			return map;
		}

		var impactMaps = {};
		checkboxes.forEach( function ( cb ) {
			impactMaps[ cb.dataset.checkboxIndex ] = parseImpactMap( cb.dataset.impactMap );
		} );

		function render() {
			var totals = {};
			barWraps.forEach( function ( wrap ) {
				totals[ wrap.dataset.categoryIndex ] = parseFloat( wrap.dataset.baseline ) || 0;
			} );

			checkboxes.forEach( function ( cb ) {
				if ( ! cb.checked ) {
					return;
				}
				var map = impactMaps[ cb.dataset.checkboxIndex ] || {};
				Object.keys( map ).forEach( function ( catIndex ) {
					totals[ catIndex ] = ( totals[ catIndex ] || 0 ) + map[ catIndex ];
				} );
			} );

			barWraps.forEach( function ( wrap ) {
				var catIndex = wrap.dataset.categoryIndex;
				var baseline = parseFloat( wrap.dataset.baseline ) || 0;
				var total = Math.min( 100, totals[ catIndex ] || 0 );
				var bar = wrap.querySelector( '.government-chart-bar' );
				bar.style.height = Math.round( ( total / 100 ) * MAX_BAR_HEIGHT ) + 'px';
				bar.classList.toggle( 'is-boosted', total > baseline );
			} );
		}

		checkboxes.forEach( function ( cb ) {
			cb.addEventListener( 'change', function () {
				var label = cb.closest( '.government-checkbox-label' );
				if ( label ) {
					label.classList.toggle( 'is-active', cb.checked );
				}
				render();
			} );
		} );
	}

	// ---------------------------------------------------------------
	// Logistics & Warehousing industry page — drag-to-compare incident
	// timeline. Classic before/after image-comparison-slider mechanic
	// (clip-path/width reveal) built with two DOM timeline layers instead
	// of two images. The "with ITOI" layer is rendered at the stage's
	// full width (not the clipped wrapper's width) so its event markers
	// sit at the correct absolute position as more of it is revealed;
	// its wrapper's width is what actually performs the reveal/clip.
	// ---------------------------------------------------------------
	function initLogisticsComparison() {
		var stage = document.getElementById( 'logisticsCompareStage' );
		var handle = document.getElementById( 'logisticsHandle' );
		var clip = document.getElementById( 'logisticsWithClip' );
		var withLayer = document.getElementById( 'logisticsWithLayer' );
		if ( ! stage || ! handle || ! clip || ! withLayer ) {
			return;
		}

		function syncWithLayerWidth() {
			withLayer.style.width = stage.clientWidth + 'px';
		}
		syncWithLayerWidth();
		window.addEventListener( 'resize', syncWithLayerWidth );

		function setPosition( pct ) {
			pct = Math.max( 0, Math.min( 100, pct ) );
			clip.style.width = pct + '%';
			handle.style.left = pct + '%';
			handle.setAttribute( 'aria-valuenow', Math.round( pct ) );
		}

		function pctFromClientX( clientX ) {
			var rect = stage.getBoundingClientRect();
			return ( ( clientX - rect.left ) / rect.width ) * 100;
		}

		var dragging = false;

		function onPointerMove( e ) {
			if ( ! dragging ) {
				return;
			}
			setPosition( pctFromClientX( e.clientX ) );
		}

		function stopDragging() {
			dragging = false;
			document.removeEventListener( 'pointermove', onPointerMove );
			document.removeEventListener( 'pointerup', stopDragging );
		}

		handle.addEventListener( 'pointerdown', function ( e ) {
			dragging = true;
			e.preventDefault();
			document.addEventListener( 'pointermove', onPointerMove );
			document.addEventListener( 'pointerup', stopDragging );
		} );

		stage.addEventListener( 'pointerdown', function ( e ) {
			if ( e.target === handle || handle.contains( e.target ) ) {
				return;
			}
			setPosition( pctFromClientX( e.clientX ) );
		} );

		handle.addEventListener( 'keydown', function ( e ) {
			var current = parseFloat( handle.getAttribute( 'aria-valuenow' ) ) || 50;
			if ( 'ArrowLeft' === e.key ) {
				setPosition( current - 5 );
				e.preventDefault();
			} else if ( 'ArrowRight' === e.key ) {
				setPosition( current + 5 );
				e.preventDefault();
			} else if ( 'Home' === e.key ) {
				setPosition( 0 );
				e.preventDefault();
			} else if ( 'End' === e.key ) {
				setPosition( 100 );
				e.preventDefault();
			}
		} );
	}

	// ---------------------------------------------------------------
	// Stadiums & Events industry page — live density visualization.
	// Each zone gets a pool of markers pre-generated once (stable
	// left/top/size/opacity per marker, sized to that zone's share of
	// the ACF-configured marker cap); moving the slider only toggles how
	// many of that pool are visible, so markers never jump to a new
	// position as density changes — they just appear/disappear. No drift
	// animation at all (satisfies prefers-reduced-motion by construction,
	// not just via a media-query override).
	// ---------------------------------------------------------------
	function initStadiumDensity() {
		var root = document.getElementById( 'stadiumDensity' );
		if ( ! root ) {
			return;
		}

		var min = parseInt( root.dataset.min, 10 ) || 0;
		var max = parseInt( root.dataset.max, 10 ) || 100;
		var maxMarkersTotal = parseInt( root.dataset.maxMarkers, 10 ) || 50;
		var slider = document.getElementById( 'stadiumSlider' );
		var valueLabel = document.getElementById( 'stadiumSliderValue' );
		var zones = root.querySelectorAll( '.stadium-zone' );

		var totalWeight = 0;
		zones.forEach( function ( zone ) {
			totalWeight += parseFloat( zone.dataset.zoneWeight ) || 0;
		} );

		var zonePools = [];
		zones.forEach( function ( zone ) {
			var weight = parseFloat( zone.dataset.zoneWeight ) || 0;
			var zoneMax = totalWeight ? Math.round( ( weight / totalWeight ) * maxMarkersTotal ) : 0;
			var pool = [];
			for ( var i = 0; i < zoneMax; i++ ) {
				var marker = document.createElement( 'span' );
				marker.className = 'stadium-marker';
				var left = 8 + Math.random() * 82;
				var top = 28 + Math.random() * 62;
				var size = 6 + Math.random() * 4;
				var opacity = 0.55 + Math.random() * 0.45;
				marker.style.left = left + '%';
				marker.style.top = top + '%';
				marker.style.width = size + 'px';
				marker.style.height = size + 'px';
				marker.style.setProperty( '--marker-opacity', opacity.toFixed( 2 ) );
				zone.appendChild( marker );
				pool.push( marker );
			}
			zonePools.push( pool );
		} );

		function render( attendees ) {
			var fraction = max > min ? ( attendees - min ) / ( max - min ) : 0;
			fraction = Math.max( 0, Math.min( 1, fraction ) );
			zonePools.forEach( function ( pool ) {
				var visibleCount = Math.round( pool.length * fraction );
				pool.forEach( function ( marker, i ) {
					marker.classList.toggle( 'is-visible', i < visibleCount );
				} );
			} );
		}

		if ( slider ) {
			slider.addEventListener( 'input', function () {
				var value = parseInt( slider.value, 10 );
				if ( valueLabel ) {
					valueLabel.textContent = value.toLocaleString( 'en-AU' );
				}
				render( value );
			} );
			render( parseInt( slider.value, 10 ) );
		}
	}

	// ---------------------------------------------------------------
	// Casinos & Gaming industry page — floor-plan zone selector. Clicking
	// a zone marks it active (signature navy) and replaces the description panel's
	// content — never stacks, matching the same single-result pattern as
	// the Banking access simulator. The "related solution" link (Bar →
	// Liquor Management) only shows when that zone actually has one set.
	// ---------------------------------------------------------------
	function initCasinoFloorMap() {
		var root = document.getElementById( 'casinoFloorMap' );
		if ( ! root ) {
			return;
		}

		var zones = root.querySelectorAll( '.casino-zone' );
		var panel = document.getElementById( 'casinoDescriptionPanel' );
		var labelEl = document.getElementById( 'casinoDescriptionLabel' );
		var textEl = document.getElementById( 'casinoDescriptionText' );
		var linkEl = document.getElementById( 'casinoDescriptionSolutionLink' );

		zones.forEach( function ( zone ) {
			zone.addEventListener( 'click', function () {
				zones.forEach( function ( z ) {
					var active = z === zone;
					z.classList.toggle( 'is-active', active );
					z.setAttribute( 'aria-pressed', active ? 'true' : 'false' );
				} );

				labelEl.textContent = zone.dataset.label || '';
				textEl.textContent = zone.dataset.description || '';

				var solutionUrl = zone.dataset.solutionUrl;
				if ( solutionUrl ) {
					linkEl.href = solutionUrl;
					linkEl.textContent = 'Related solution: ' + zone.dataset.solutionLabel + ' →';
					linkEl.classList.remove( 'hidden' );
				} else {
					linkEl.classList.add( 'hidden' );
				}

				panel.classList.remove( 'hidden' );
			} );
		} );
	}

	// ---------------------------------------------------------------
	// Industry long-form page — sticky sub-nav scrollspy. Clicking a link
	// smooth-scrolls (instant if prefers-reduced-motion) to its section;
	// an IntersectionObserver watching a thin band roughly mid-viewport
	// toggles the active link as sections cross it — no scroll-position
	// math. Retail only for now (single-industry.php gates the whole
	// sub-nav + sections behind `longform_enabled`).
	// ---------------------------------------------------------------
	function initLongformScrollspy() {
		var nav = document.getElementById( 'longformSubnav' );
		if ( ! nav ) {
			return;
		}

		var links = nav.querySelectorAll( '.longform-subnav-link' );
		var sections = [];
		links.forEach( function ( link ) {
			var section = document.getElementById( link.dataset.target );
			if ( section ) {
				sections.push( section );
			}
		} );

		function setActive( id ) {
			links.forEach( function ( link ) {
				var isActive = link.dataset.target === id;
				link.classList.toggle( 'text-signature', isActive );
				link.classList.toggle( 'border-signature', isActive );
				link.classList.toggle( 'text-text-muted', ! isActive );
				link.classList.toggle( 'border-transparent', ! isActive );
				// Mobile: the nav row itself scrolls horizontally, so the newly
				// active link can end up clipped off-screen within it. `nearest`
				// on the block axis is a no-op here since the sticky nav is
				// already vertically in view — only the row's own horizontal
				// scroll moves.
				if ( isActive ) {
					link.scrollIntoView( { behavior: reduceMotion ? 'auto' : 'smooth', inline: 'center', block: 'nearest' } );
				}
			} );
		}

		links.forEach( function ( link ) {
			link.addEventListener( 'click', function ( e ) {
				e.preventDefault();
				var target = document.getElementById( link.dataset.target );
				if ( target ) {
					target.scrollIntoView( { behavior: reduceMotion ? 'auto' : 'smooth', block: 'start' } );
				}
			} );
		} );

		if ( ! sections.length ) {
			return;
		}

		var observer = new IntersectionObserver(
			function ( entries ) {
				entries.forEach( function ( entry ) {
					if ( entry.isIntersecting ) {
						setActive( entry.target.id );
					}
				} );
			},
			{ rootMargin: '-45% 0px -50% 0px', threshold: 0 }
		);

		sections.forEach( function ( section ) {
			observer.observe( section );
		} );
	}

	// ---------------------------------------------------------------
	// Industry long-form page — main nav auto-hide once .longform-subnav
	// goes sticky (2026-08-04, see NOTES.md). Watches a 1px sentinel
	// (#longformSubnavSentinel, single-industry.php) placed immediately
	// before the sub-nav in normal document flow — once that sentinel
	// scrolls above the sub-nav's own sticky offset, the sub-nav has just
	// engaged, so the fixed main nav (#siteHeaderFixed, header.php) slides
	// itself out of view (.header-hidden, src/tailwind.css) rather than
	// the two sitting stacked on top of each other. Reads the sub-nav's
	// own computed `top` for the observer's rootMargin instead of a second
	// hardcoded copy of that offset, so the two can never drift out of
	// sync with each other. No-ops entirely on any page without a
	// `.longform-subnav` — every page other than single-industry.php.
	//
	// Same-day follow-up: once the main nav is gone, the sub-nav no longer
	// needs to leave room for it, so it visually moves flush to the very
	// top instead of sitting at the original 130/134px offset with dead
	// space above it.
	//
	// Corrected same day, again: the first version of this follow-up
	// changed the sub-nav's `top` itself (`sticky top-[…]` → `0`) with a
	// `transition: top`, and caused real scroll jank — reported as "the
	// website is laggy." `top` is a layout-triggering property; animating
	// it forces the browser to recompute layout on every frame of the
	// transition (worse yet, while `position: sticky` is *already*
	// recalculating every scroll frame natively), instead of the
	// GPU-composited path a `transform` change gets. This project's own
	// motion rule (PROJECT.md §3) already says exactly this: animate only
	// transform/opacity, never top/left — this broke that rule, now fixed.
	// `top` itself is left completely alone, static, exactly as it always
	// was (`sticky top-[130px] min-[640px]:top-[134px]`, single-
	// industry.php) — sticky's own native engagement is untouched. The
	// visual "move to flush-top" is now a `transform: translateY(-Npx)`
	// instead, where N is that same static `top` offset already read into
	// `stickyTop` below for the observer's rootMargin — shifting the
	// already-correctly-stuck element up by exactly its own offset lands
	// it at 0, with zero effect while not yet stuck (transform reverts to
	// '', matching the sub-nav's normal in-flow position exactly).
	// ---------------------------------------------------------------
	function initLongformHeaderHide() {
		var subnav = document.querySelector( '.longform-subnav' );
		var sentinel = document.getElementById( 'longformSubnavSentinel' );
		var headerFixed = document.getElementById( 'siteHeaderFixed' );
		if ( ! subnav || ! sentinel || ! headerFixed ) {
			return;
		}

		var stickyTop = parseFloat( getComputedStyle( subnav ).top ) || 0;

		// A plain `isIntersecting` toggle can't tell "hasn't scrolled down to
		// the sentinel yet" apart from "scrolled past it" — both report
		// not-intersecting once the root's top edge is pushed down by
		// rootMargin. Disambiguating needs `boundingClientRect.top`, which
		// stays relative to the real (unshifted) viewport: still above the
		// sticky offset only once actually scrolled past it, still below
		// while not yet reached. threshold:[1] fires the instant the 1px
		// sentinel starts leaving either edge, which is all a 1px target
		// needs. Standard technique for detecting a `position: sticky`
		// engagement point via IntersectionObserver (no scroll-position
		// math / scroll listener).
		var observer = new IntersectionObserver(
			function ( entries ) {
				var entry = entries[ 0 ];
				var isPinned = entry.intersectionRatio < 1 && entry.boundingClientRect.top < stickyTop;
				headerFixed.classList.toggle( 'header-hidden', isPinned );
				subnav.style.transform = isPinned ? 'translateY(-' + stickyTop + 'px)' : '';
			},
			{ rootMargin: '-' + stickyTop + 'px 0px 0px 0px', threshold: [ 1 ] }
		);

		observer.observe( sentinel );
	}

	// ---------------------------------------------------------------
	// Industry long-form page — Customers section rolling marquees
	// (single-industry.php Section 5b). Each row is one track holding two
	// back-to-back copies of the same client-pill list (server-rendered,
	// the second copy `aria-hidden`); the CSS keyframe translates the
	// track by exactly -50% so the loop seams invisibly. Direction (left
	// vs right) is a template-driven class (.animate-itoi-marquee /
	// -reverse); this function only measures each row's real rendered
	// width and sets a matching animation-duration so rows with a
	// different number of names still scroll at the same px/s speed.
	// prefers-reduced-motion: CSS alone handles the static wrapped
	// fallback (src/tailwind.css), so this skips straight past — no
	// point measuring widths for an animation that won't run.
	// ---------------------------------------------------------------
	function initLongformMarquees() {
		if ( reduceMotion ) {
			return;
		}
		var tracks = document.querySelectorAll( '.longform-marquee-track' );
		if ( ! tracks.length ) {
			return;
		}

		var PIXELS_PER_SECOND = 45;

		tracks.forEach( function ( track ) {
			var primaryGroup = track.querySelector( '.longform-marquee-group[data-copy="primary"]' );
			if ( ! primaryGroup ) {
				return;
			}
			var width = primaryGroup.getBoundingClientRect().width;
			if ( width > 0 ) {
				track.style.animationDuration = ( width / PIXELS_PER_SECOND ) + 's';
			}
		} );
	}

	// ---------------------------------------------------------------
	// Guides index (archive-guide.php) — industry filter pills. Same
	// mechanic as initClientFilter above (page-customers.php), duplicated
	// rather than generalized since the two pages' pill/card markup and
	// active-state classes differ slightly and each is only used once.
	// ---------------------------------------------------------------
	function initGuideFilter() {
		var row = document.getElementById( 'guideFilterRow' );
		var grid = document.getElementById( 'guideGrid' );
		if ( ! row || ! grid ) {
			return;
		}

		var pills = row.querySelectorAll( '.guide-filter-pill' );
		var cards = grid.querySelectorAll( '.guide-card' );
		var emptyMsg = document.getElementById( 'guideFilterEmpty' );

		function setActive( filter ) {
			pills.forEach( function ( pill ) {
				var isActive = pill.dataset.filter === filter;
				pill.classList.toggle( 'active', isActive );
				pill.setAttribute( 'aria-pressed', isActive ? 'true' : 'false' );
				pill.classList.toggle( 'border-ink', isActive );
				pill.classList.toggle( 'bg-ink', isActive );
				pill.classList.toggle( 'text-white', isActive );
				pill.classList.toggle( 'border-line', ! isActive );
				pill.classList.toggle( 'text-ink', ! isActive );
			} );

			var visibleCount = 0;
			cards.forEach( function ( card ) {
				var show = filter === 'all' || card.dataset.category === filter;
				card.style.display = show ? '' : 'none';
				if ( show ) {
					visibleCount++;
				}
			} );
			if ( emptyMsg ) {
				emptyMsg.classList.toggle( 'hidden', visibleCount > 0 );
			}
		}

		pills.forEach( function ( pill ) {
			pill.addEventListener( 'click', function () {
				setActive( pill.dataset.filter );
			} );
		} );
	}

	// ---------------------------------------------------------------
	// /use-cases/ hub (archive-use_case.php) — industry filter pills. Same
	// mechanic as initClientFilter/initGuideFilter above, duplicated for the
	// same reason as initGuideFilter (page-specific markup/IDs, only used once).
	// ---------------------------------------------------------------
	function initUseCaseFilter() {
		var row = document.getElementById( 'useCaseFilterRow' );
		var grid = document.getElementById( 'useCaseGrid' );
		if ( ! row || ! grid ) {
			return;
		}

		var pills = row.querySelectorAll( '.use-case-filter-pill' );
		var cards = grid.querySelectorAll( '.use-case-card' );
		var emptyMsg = document.getElementById( 'useCaseFilterEmpty' );

		function setActive( filter ) {
			pills.forEach( function ( pill ) {
				var isActive = pill.dataset.filter === filter;
				pill.classList.toggle( 'active', isActive );
				pill.setAttribute( 'aria-pressed', isActive ? 'true' : 'false' );
				pill.classList.toggle( 'border-ink', isActive );
				pill.classList.toggle( 'bg-ink', isActive );
				pill.classList.toggle( 'text-white', isActive );
				pill.classList.toggle( 'border-line', ! isActive );
				pill.classList.toggle( 'text-ink', ! isActive );
			} );

			var visibleCount = 0;
			cards.forEach( function ( card ) {
				var show = filter === 'all' || card.dataset.category === filter;
				card.style.display = show ? '' : 'none';
				if ( show ) {
					visibleCount++;
				}
			} );
			if ( emptyMsg ) {
				emptyMsg.classList.toggle( 'hidden', visibleCount > 0 );
			}
		}

		pills.forEach( function ( pill ) {
			pill.addEventListener( 'click', function () {
				setActive( pill.dataset.filter );
			} );
		} );
	}

	// ---------------------------------------------------------------
	// Education Hub — shared filter-as-you-type pattern, built once and
	// applied to every [data-filter-root] on the page (the Glossary page,
	// the FAQ page, and the hub landing page's own quick-search each carry
	// one). Simple case-insensitive substring match against each item's
	// explicit data-filter-text (not its full rendered text — e.g. an FAQ
	// item's answer body is deliberately excluded so only the question
	// text is searched, per spec). Items fade/collapse out via Tailwind's
	// motion-reduce: variant already handling prefers-reduced-motion —
	// nothing extra needed here for that.
	// ---------------------------------------------------------------
	function initItoiFilterLists() {
		document.querySelectorAll( '[data-filter-root]' ).forEach( function ( root ) {
			var input = root.querySelector( '[data-filter-input]' );
			var items = root.querySelectorAll( '[data-filter-item]' );
			var groups = root.querySelectorAll( '[data-filter-group]' );
			var emptyMsg = root.querySelector( '[data-filter-empty]' );
			if ( ! input || ! items.length ) {
				return;
			}

			// Inline styles, not toggled Tailwind classes — a class-based
			// approach here previously collided with each item's own static
			// py-* padding (max-height:0 can't shrink a box below its own
			// padding, so "hidden" items still rendered ~40px tall). Inline
			// style always wins the cascade, so there's nothing to collide
			// with. Hidden items fade out, then get display:none after the
			// transition so they stop taking up space; reduced motion skips
			// straight to display:none.
			function setItemVisible( item, visible ) {
				window.clearTimeout( item._itoiFilterTimer );
				if ( visible ) {
					item.style.display = '';
					item.style.pointerEvents = '';
					window.requestAnimationFrame( function () {
						item.style.opacity = '1';
					} );
				} else {
					item.style.opacity = '0';
					item.style.pointerEvents = 'none';
					if ( reduceMotion ) {
						item.style.display = 'none';
					} else {
						item._itoiFilterTimer = window.setTimeout( function () {
							item.style.display = 'none';
						}, 200 );
					}
				}
			}

			function applyFilter() {
				var q = input.value.trim().toLowerCase();
				var anyVisible = false;

				// Match state is tracked on the item itself (dataset) rather
				// than read back from item.style.display for the group check
				// below — display:none only lands ~200ms later inside
				// setItemVisible's fade-out timeout, so reading it back in
				// the same tick would always see the pre-filter state.
				items.forEach( function ( item ) {
					var text = ( item.getAttribute( 'data-filter-text' ) || '' ).toLowerCase();
					var match = ! q || text.indexOf( q ) !== -1;
					setItemVisible( item, match );
					item.dataset.itoiMatch = match ? '1' : '0';
					if ( match ) {
						anyVisible = true;
					}
				} );

				groups.forEach( function ( group ) {
					var hasVisible = Array.prototype.some.call(
						group.querySelectorAll( '[data-filter-item]' ),
						function ( item ) {
							return '1' === item.dataset.itoiMatch;
						}
					);
					group.classList.toggle( 'hidden', ! hasVisible );
				} );

				if ( emptyMsg ) {
					emptyMsg.classList.toggle( 'hidden', anyVisible );
				}
			}

			input.addEventListener( 'input', applyFilter );
			applyFilter();
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

	// ---------------------------------------------------------------
	// Industry selector — homepage personalization (front-page.php,
	// "INDUSTRY SELECTOR" section + its two downstream sections).
	// Purely client-side: there's no visitor session on this WP install, so
	// every possible state (generic + all 7 personalized spotlight panels +
	// all 7 personalized use-case rows) is already server-rendered — this
	// function only reads localStorage and toggles which of those already-
	// real DOM nodes are visible/ordered, never builds markup or copy from
	// scratch.
	// ---------------------------------------------------------------
	var ITOI_INDUSTRY_STORAGE_KEY = 'itoi_industry_selection';

	// Exact mapping table from the Industry Selector spec, Part 2 — used
	// verbatim, not inferred. Each array is that industry's priority
	// solutions, in order; any of the grid's other slugs keep their
	// original relative order, appended after the priority set.
	var ITOI_INDUSTRY_SOLUTIONS_MAP = {
		retail: [ 'intelligence-analytics', 'cctv-video-loss-prevention', 'security-access-inventory', 'workforce-ops-robotics' ],
		hospitality: [ 'customer-engagement-signage', 'back-of-house-integration', 'cctv-video-loss-prevention', 'workforce-ops-robotics' ],
		'banking-finance': [ 'security-access-inventory', 'cctv-video-loss-prevention', 'intelligence-analytics' ],
		'government-councils': [ 'intelligence-analytics', 'cctv-video-loss-prevention', 'security-access-inventory' ],
		'logistics-warehousing': [ 'cctv-video-loss-prevention', 'workforce-ops-robotics', 'intelligence-analytics' ],
		'stadiums-events': [ 'intelligence-analytics', 'security-access-inventory', 'cctv-video-loss-prevention' ],
		'casinos-gaming': [ 'cctv-video-loss-prevention', 'security-access-inventory', 'back-of-house-integration' ],
	};

	function initIndustrySelector() {
		var section = document.getElementById( 'industrySelector' );
		if ( ! section ) {
			return;
		}

		var generic = document.getElementById( 'industrySelectorGeneric' );
		var personalized = document.getElementById( 'industrySelectorPersonalized' );
		var currentLabelEl = document.getElementById( 'industrySelectorCurrentLabel' );
		var changeBtn = document.getElementById( 'industrySelectorChange' );
		var buttons = section.querySelectorAll( '.industry-select-btn' );
		var spotlightPanels = section.querySelectorAll( '#industrySpotlightPanels [data-industry-slug]' );
		var solutionsGrid = document.getElementById( 'solutionsGrid' );
		var useCaseDefaultRow = document.getElementById( 'useCaseDefaultRow' );
		var useCaseIndustryRows = document.querySelectorAll( '.use-case-industry-row' );

		// Simple ~0.3s cross-fade between the generic button row and the
		// personalized panel — prefers-reduced-motion needs no branching
		// here beyond skipping the setTimeout delay: the sitewide
		// `transition: none !important` rule (src/tailwind.css) already
		// makes the opacity change instant for those users once the CSS
		// transition itself is a no-op, but the JS timeout below would
		// otherwise still impose a real ~300ms pause before the swap even
		// though nothing visibly animated — skipping the delay avoids that.
		function fadeSwap( hideEl, showEl ) {
			if ( ! hideEl || ! showEl ) {
				return;
			}
			function reveal() {
				hideEl.classList.add( 'hidden' );
				showEl.classList.remove( 'hidden' );
				showEl.style.transition = 'none';
				showEl.style.opacity = reduceMotion ? '1' : '0';
				if ( ! reduceMotion ) {
					void showEl.offsetWidth; // force layout so the fade-in below actually plays
					showEl.style.transition = 'opacity 0.3s';
					showEl.style.opacity = '1';
				}
			}
			if ( reduceMotion ) {
				reveal();
				return;
			}
			hideEl.style.transition = 'opacity 0.3s';
			hideEl.style.opacity = '0';
			setTimeout( reveal, 300 );
		}

		function showSpotlightPanel( slug ) {
			spotlightPanels.forEach( function ( panel ) {
				panel.classList.toggle( 'hidden', panel.getAttribute( 'data-industry-slug' ) !== slug );
			} );
		}

		function reorderSolutionsGrid( slug ) {
			var priority = ITOI_INDUSTRY_SOLUTIONS_MAP[ slug ];
			if ( ! solutionsGrid || ! priority ) {
				return;
			}
			var cards = Array.prototype.slice.call( solutionsGrid.children );
			cards.sort( function ( a, b ) {
				var ai = priority.indexOf( a.getAttribute( 'data-solution-slug' ) );
				var bi = priority.indexOf( b.getAttribute( 'data-solution-slug' ) );
				if ( -1 === ai ) {
					ai = priority.length + cards.indexOf( a );
				}
				if ( -1 === bi ) {
					bi = priority.length + cards.indexOf( b );
				}
				return ai - bi;
			} );
			cards.forEach( function ( card, i ) {
				solutionsGrid.appendChild( card );
				var badge = card.querySelector( '.solution-card-num' );
				if ( badge ) {
					badge.textContent = ( '0' + ( i + 1 ) ).slice( -2 );
				}
			} );
		}

		function showUseCaseRow( slug ) {
			if ( ! useCaseDefaultRow ) {
				return;
			}
			useCaseDefaultRow.classList.add( 'hidden' );
			useCaseIndustryRows.forEach( function ( row ) {
				var match = row.getAttribute( 'data-industry-slug' ) === slug;
				row.classList.toggle( 'hidden', ! match );
				row.classList.toggle( 'flex', match );
			} );
		}

		function applyIndustry( slug, label, animate ) {
			if ( currentLabelEl ) {
				currentLabelEl.textContent = label;
			}
			showSpotlightPanel( slug );
			reorderSolutionsGrid( slug );
			showUseCaseRow( slug );

			if ( animate ) {
				fadeSwap( generic, personalized );
			} else {
				generic.classList.add( 'hidden' );
				personalized.classList.remove( 'hidden' );
				personalized.style.opacity = '1';
			}
		}

		function storeSelection( slug, label ) {
			try {
				localStorage.setItem( ITOI_INDUSTRY_STORAGE_KEY, JSON.stringify( { slug: slug, label: label } ) );
			} catch ( e ) {
				// Private-browsing/storage-disabled — selection just won't
				// persist across reloads; the click-driven personalization
				// still works for the current page view.
			}
		}

		buttons.forEach( function ( btn ) {
			btn.addEventListener( 'click', function () {
				var slug = btn.getAttribute( 'data-industry-slug' );
				var label = btn.getAttribute( 'data-industry-label' );
				storeSelection( slug, label );
				applyIndustry( slug, label, true );
			} );
		} );

		if ( changeBtn ) {
			changeBtn.addEventListener( 'click', function () {
				fadeSwap( personalized, generic );
			} );
		}

		// Fallback-card CTA ("Explore our solutions →") — smooth-scrolls to
		// the (already reordered) grid instead of a plain anchor jump.
		section.addEventListener( 'click', function ( e ) {
			var cta = e.target.closest( '.industry-fallback-cta' );
			if ( ! cta ) {
				return;
			}
			var target = document.getElementById( 'exploreSolutions' );
			if ( target ) {
				e.preventDefault();
				target.scrollIntoView( { behavior: reduceMotion ? 'auto' : 'smooth', block: 'start' } );
			}
		} );

		// Re-check on every homepage load — if a selection already exists,
		// show the personalized view immediately, no re-prompt.
		var stored = null;
		try {
			stored = JSON.parse( localStorage.getItem( ITOI_INDUSTRY_STORAGE_KEY ) || 'null' );
		} catch ( e ) {
			stored = null;
		}
		if ( stored && stored.slug && stored.label && ITOI_INDUSTRY_SOLUTIONS_MAP[ stored.slug ] ) {
			applyIndustry( stored.slug, stored.label, false );
		}
	}
})();
