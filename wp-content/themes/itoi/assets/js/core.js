/**
 * ITOI theme — sitewide interactivity: nav (mega menu, desktop dropdowns),
 * the sitewide "Build your solution" finder popup, flip-cards, scroll-
 * reveal, lazy media playback, and the two small always-on effects
 * (ticker rest-state, off-screen aurora pause). Loaded on every page.
 *
 * Split out of the single assets/js/main.js 2026-08-06 (JS bundle split,
 * see NOTES.md) — same functions, same behavior, just grouped by where
 * they actually run instead of one file shipping everything everywhere.
 * homepage.js / industry-simulators.js / listing-filters.js are the other
 * 3 bundles; inc/enqueue.php enqueues only the ones a given page needs.
 *
 * prefers-reduced-motion: auto-advancing/animated effects are skipped
 * entirely; manual interaction still works.
 */
(function () {
	'use strict';

	var reduceMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

	// ---------------------------------------------------------------
	// Shared focus-trap utility for the 2 full-screen dialogs (#megaMenu,
	// #finderOverlay) — see initMegaMenu()/initFinder() below.
	// ---------------------------------------------------------------
	function itoiCreateFocusTrap( container ) {
		var FOCUSABLE_SELECTOR = 'a[href], button:not([disabled]), textarea:not([disabled]), input:not([disabled]), select:not([disabled]), [tabindex]:not([tabindex="-1"])';
		var previousFocus = null;
		var mainEl = document.querySelector( 'main' );

		function getFocusable() {
			return Array.prototype.slice.call( container.querySelectorAll( FOCUSABLE_SELECTOR ) ).filter( function ( el ) {
				return !! ( el.offsetWidth || el.offsetHeight || el.getClientRects().length );
			} );
		}

		function handleKeydown( e ) {
			if ( 'Tab' !== e.key ) {
				return;
			}
			var focusable = getFocusable();
			if ( ! focusable.length ) {
				return;
			}
			var first = focusable[ 0 ];
			var last = focusable[ focusable.length - 1 ];
			if ( e.shiftKey && document.activeElement === first ) {
				e.preventDefault();
				last.focus();
			} else if ( ! e.shiftKey && document.activeElement === last ) {
				e.preventDefault();
				first.focus();
			}
		}

		return {
			activate: function ( focusTargetEl ) {
				previousFocus = document.activeElement;
				if ( mainEl ) {
					mainEl.setAttribute( 'inert', '' );
				}
				document.addEventListener( 'keydown', handleKeydown );

				function doFocus() {
					var focusable = getFocusable();
					( focusTargetEl || focusable[ 0 ] || container ).focus();
				}

				// Real bug caught via Puppeteer before shipping, and it
				// took real digging to pin down: both dialogs open by
				// adding an `.open` class that transitions `visibility`
				// hidden -> visible. Calling .focus() on a descendant in
				// the same tick — or even one requestAnimationFrame later
				// — still silently failed. Root cause, confirmed by direct
				// testing: while a `visibility` transition is active, the
				// browser holds the element at its pre-transition,
				// non-focusable state until the transition genuinely
				// completes — this held even against an inline
				// `!important` override forcing visibility: visible
				// directly on the element, which rules out every ordinary
				// CSS specificity/cascade/layer explanation; it's the
				// transition's own "current value" outranking everything
				// else for that property, for its whole duration. The
				// correct fix is waiting for the real `transitionend`
				// event, not a longer guessed delay. A safety-net timeout
				// covers transitionend never firing at all (interrupted
				// transition, unsupported browser, or prefers-reduced-
				// motion's sitewide near-zero transition-duration override
				// possibly not firing a real event for some engines).
				var focused = false;
				function onTransitionEnd( e ) {
					if ( e.target !== container || 'visibility' !== e.propertyName || focused ) {
						return;
					}
					focused = true;
					container.removeEventListener( 'transitionend', onTransitionEnd );
					doFocus();
				}
				container.addEventListener( 'transitionend', onTransitionEnd );
				window.setTimeout( function () {
					if ( ! focused ) {
						focused = true;
						container.removeEventListener( 'transitionend', onTransitionEnd );
						doFocus();
					}
				}, 400 );
			},
			deactivate: function () {
				document.removeEventListener( 'keydown', handleKeydown );
				if ( mainEl ) {
					mainEl.removeAttribute( 'inert' );
				}
				if ( previousFocus && 'function' === typeof previousFocus.focus ) {
					previousFocus.focus();
				}
				previousFocus = null;
			},
		};
	}

	document.addEventListener( 'DOMContentLoaded', function () {
		initLazyMediaVideos();
		initAuroraPause();
		initTicker();
		initFinder();
		initFlipCards();
		initRevealObserver();
		initMegaMenu();
		initDesktopDropdowns();
		initLongformMarquees();
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
	// 0b. Off-screen aurora pause (2026-08-05 performance pass, see
	// NOTES.md) — single-industry.php stacks 9 concurrent .aurora-bg/
	// .aurora-bg-light instances (each animates a blurred gradient on its
	// own ::before via itoi-aurora-drift, src/tailwind.css); the 2026-08-03
	// audit measured ~84ms/frame with all 9 animating vs ~17ms with none.
	// Only the ones actually near the viewport need to keep animating —
	// same IntersectionObserver + rootMargin pattern as
	// initLazyMediaVideos() above, toggling .aurora-paused (src/
	// tailwind.css) rather than touching the animation directly.
	//
	// Reduced-motion is left entirely to the existing
	// @media (prefers-reduced-motion: reduce) rule already on
	// .aurora-bg::before/.aurora-bg-light::before, which already fully
	// stops the animation for that visitor — this still runs (and still
	// toggles the class) for a reduced-motion visitor too rather than
	// special-casing it, per the ask ("don't add a second path"); toggling
	// aurora-paused on an element whose animation is already `none` is
	// harmless, just redundant.
	// ---------------------------------------------------------------
	function initAuroraPause() {
		var auroraEls = document.querySelectorAll( '.aurora-bg, .aurora-bg-light' );
		if ( ! auroraEls.length ) {
			return;
		}
		var observer = new IntersectionObserver(
			function ( entries ) {
				entries.forEach( function ( entry ) {
					entry.target.classList.toggle( 'aurora-paused', ! entry.isIntersecting );
				} );
			},
			{ rootMargin: '200px 0px' }
		);
		auroraEls.forEach( function ( el ) {
			observer.observe( el );
		} );
	}

	// ---------------------------------------------------------------
	// 1. Rotating ticker banner
	// ---------------------------------------------------------------
	// 2026-08-05: stopped auto-rotating (external improvement plan Phase
	// 3.4/5.6 — an unprompted, continuously-moving ticker was one of
	// several homepage effects competing for attention; user explicitly
	// authorized overriding CLAUDE.md's "don't cut corners" mechanics rule
	// for this specific reduction, see NOTES.md). The first message is
	// already the server-rendered rest state (header.php), so this is now
	// a no-op left in place only so the markup/CSS transition rule stay
	// meaningful if rotation is ever reinstated — not deleted outright,
	// since the ticker itself (and its 3 real messages) still exist and
	// still render, just without the auto-advance.
	function initTicker() {}

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

		// 2026-08-06: the popup only ever opens on a click of this floating
		// trigger now (no auto-open timer — see NOTES.md), and the
		// homepage's duplicate "Build your solution" CTA that used to share
		// bottom-right screen space with it (front-page.php's Final CTA
		// band) has been removed in favor of a nav CTA instead, so there's
		// no longer a same-wording CTA for this to visually stack with.
		// Simply visible from page load.
		trigger.classList.add( 'visible' );

		// --- Modal open/close ---
		var focusTrap = itoiCreateFocusTrap( overlay );
		function openModal() {
			overlay.classList.add( 'open' );
			overlay.setAttribute( 'aria-hidden', 'false' );
			document.body.style.overflow = 'hidden';
			focusTrap.activate( closeBtn );
		}
		function closeModal() {
			overlay.classList.remove( 'open' );
			overlay.setAttribute( 'aria-hidden', 'true' );
			document.body.style.overflow = '';
			focusTrap.deactivate();
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

		// 2026-08-05 (external improvement plan Phase 5.8) — these are real
		// <button> elements (keyboard-focusable/operable by default), but
		// selection state was only ever a `.selected` CSS class with no
		// ARIA equivalent — a screen reader user tabbing through a step's
		// options had no way to tell which one (if any) was currently
		// picked. aria-pressed is the correct pattern for toggle buttons
		// like these (vs. converting to role="radio", which would also
		// need roving-tabindex management this simpler pattern doesn't).
		document.querySelectorAll( '.finder-opt' ).forEach( function ( opt ) {
			opt.setAttribute( 'aria-pressed', 'false' );
			opt.addEventListener( 'click', function () {
				var group = opt.closest( '.finder-options' );
				var groupName = group.dataset.group;

				if ( 'multi' === group.dataset.select ) {
					opt.classList.toggle( 'selected' );
					opt.setAttribute( 'aria-pressed', opt.classList.contains( 'selected' ) ? 'true' : 'false' );
					var idx = answers.challenges.indexOf( opt.dataset.value );
					if ( opt.classList.contains( 'selected' ) && -1 === idx ) {
						answers.challenges.push( opt.dataset.value );
					} else if ( ! opt.classList.contains( 'selected' ) && idx > -1 ) {
						answers.challenges.splice( idx, 1 );
					}
				} else {
					group.querySelectorAll( '.finder-opt' ).forEach( function ( o ) {
						o.classList.remove( 'selected' );
						o.setAttribute( 'aria-pressed', 'false' );
					} );
					opt.classList.add( 'selected' );
					opt.setAttribute( 'aria-pressed', 'true' );
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
	// Flat desktop nav dropdowns (header.php, .nav-dropdown, >=1180px).
	// Previously CSS-only (group-hover/group-focus-within) — worked for
	// plain mouse hover and Tab, but had no close delay (a diagonal mouse
	// move across the gap could drop the panel mid-crossing), no Escape,
	// and nothing for a touchscreen with no hover at all. Layered on top
	// here rather than replacing the CSS, so hover/keyboard-focus still
	// work even if this script fails to load — inline styles used to force
	// the panel open/closed since they always win over the group-hover/
	// group-focus-within utility classes without fighting Tailwind's layer
	// order (same technique as initLongformHeaderHide's sub-nav offset).
	// ---------------------------------------------------------------
	function initDesktopDropdowns() {
		var CLOSE_DELAY = 250;
		var groups = document.querySelectorAll( 'nav[aria-label="Primary"] > .nav-dropdown' );
		if ( ! groups.length ) {
			return;
		}
		var controllers = [];

		groups.forEach( function ( group ) {
			// 2026-08-05 ("10/10" pass): link and disclosure button are now
			// two separate elements (header.php) — `trigger` only ever
			// navigates; `toggleBtn` owns aria-expanded and is the thing
			// Enter/Space/click/tap toggles, for every input type equally
			// (previously the link itself had a touch-only click-intercept
			// hack; the dedicated button makes that unnecessary — a tap on
			// the link now just navigates, same as a click always did).
			var trigger = group.querySelector( ':scope > a' );
			var toggleBtn = group.querySelector( ':scope > button' );
			var panel = group.querySelector( ':scope > div' );
			if ( ! trigger || ! toggleBtn || ! panel ) {
				return;
			}
			var closeTimer = null;

			function open() {
				window.clearTimeout( closeTimer );
				panel.style.visibility = 'visible';
				panel.style.opacity = '1';
				panel.style.transform = 'translateY(0.5rem)';
				toggleBtn.setAttribute( 'aria-expanded', 'true' );
			}
			function close() {
				window.clearTimeout( closeTimer );
				panel.style.visibility = '';
				panel.style.opacity = '';
				panel.style.transform = '';
				toggleBtn.setAttribute( 'aria-expanded', 'false' );
			}
			function scheduleClose() {
				window.clearTimeout( closeTimer );
				closeTimer = window.setTimeout( close, CLOSE_DELAY );
			}

			group.addEventListener( 'mouseenter', open );
			group.addEventListener( 'mouseleave', scheduleClose );
			group.addEventListener( 'focusin', open );
			group.addEventListener( 'focusout', function ( e ) {
				if ( ! group.contains( e.relatedTarget ) ) {
					scheduleClose();
				}
			} );
			group.addEventListener( 'keydown', function ( e ) {
				if ( 'Escape' === e.key ) {
					close();
					toggleBtn.focus();
				}
			} );
			toggleBtn.addEventListener( 'click', function () {
				if ( 'true' === toggleBtn.getAttribute( 'aria-expanded' ) ) {
					close();
				} else {
					controllers.forEach( function ( c ) {
						if ( c.group !== group ) {
							c.close();
						}
					} );
					open();
				}
			} );

			controllers.push( { group: group, close: close } );
		} );

		// Closes any explicitly-toggled-open dropdown on an outside
		// click/tap — hover/focus-based opens already close themselves via
		// mouseleave/focusout above, so this only matters for the
		// click-to-open path.
		document.addEventListener( 'click', function ( e ) {
			controllers.forEach( function ( c ) {
				if ( ! c.group.contains( e.target ) ) {
					c.close();
				}
			} );
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

		var focusTrap = itoiCreateFocusTrap( overlay );

		function openMenu() {
			overlay.classList.add( 'open' );
			overlay.setAttribute( 'aria-hidden', 'false' );
			trigger.classList.add( 'is-open' );
			trigger.setAttribute( 'aria-expanded', 'true' );
			document.body.style.overflow = 'hidden';
			focusTrap.activate( closeBtn );
		}
		function closeMenu() {
			overlay.classList.remove( 'open' );
			overlay.setAttribute( 'aria-hidden', 'true' );
			trigger.classList.remove( 'is-open' );
			trigger.setAttribute( 'aria-expanded', 'false' );
			document.body.style.overflow = '';
			focusTrap.deactivate();
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


	// initLongformMarquees is needed both here (core) and would otherwise
	// seem industry-only — it's also used by the homepage's Trust &
	// Credibility client-logo marquee (itoi_render_client_logo_row(),
	// inc/customers-section.php, >5 clients) as well as single-industry.php's
	// Customers section. Kept in core rather than duplicated into both
	// homepage.js and industry-simulators.js — it's tiny and self-guards
	// via its own `if (!tracks.length) return;`.
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

})();
