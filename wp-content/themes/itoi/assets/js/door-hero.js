/**
 * ITOI Door Reveal — the scroll-scrubbed homepage section
 * (template-parts/home/hero-door-reveal.php, .door-hero markup;
 * src/tailwind.css for the component styles). Enqueued only on the front
 * page, and only when that section is actually on the page
 * (inc/enqueue.php).
 *
 * Its own bundle rather than another function inside homepage.js: this is
 * the one thing on the site running work on every scroll frame, and
 * keeping it separate means the rest of the homepage's interactivity
 * doesn't have to be parsed before it can start (and it can be dropped
 * again by deleting one enqueue). Same "own bundle per heavy, isolated
 * feature" split as industry-simulators.js / solution-builder.js.
 *
 * How it works. Everything is a pure function of one normalised progress
 * value p (0 = section just reached the top of the viewport, 1 = its tall
 * scroll container is spent), so the whole animation scrubs backwards as
 * readily as forwards and tracks the user's real scroll speed — there is
 * no autoplay, no timed transition, and no trigger that can fire twice.
 *
 * Progress windows (from the section's spec):
 *   doors    0    -> 0.55   cubic ease-in-out
 *   lockup   0    -> 0.18   linear fade out
 *   hint     0    -> 0.10   linear fade out
 *   heading  0.34 -> 0.56   cubic ease-out
 *   cards    0.44 + 0.05 per card, 0.16 wide each, cubic ease-out
 *
 * Performance. The scroll listener is passive and does nothing but flag a
 * pending frame; all reads and writes happen inside one requestAnimationFrame
 * callback, so several scroll events between paints collapse into a single
 * update. The only per-frame read is window.scrollY (no forced layout —
 * the section's offset and scroll range are measured once and re-measured
 * on resize, never in the scroll path), and the only per-frame writes are
 * transform, opacity and one custom property, all compositor-friendly.
 */
(function () {
	'use strict';

	var reduceMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

	// Progress windows, named so the render function reads as the spec above.
	var DOORS_END = 0.55;
	var LOCKUP_END = 0.18;
	var HINT_END = 0.1;
	var HEAD_START = 0.34;
	var HEAD_END = 0.56;
	var CARD_START = 0.44;
	var CARD_STAGGER = 0.05;
	var CARD_WINDOW = 0.16;

	document.addEventListener( 'DOMContentLoaded', initDoorHero );

	function initDoorHero() {
		var hero = document.getElementById( 'doorHero' );
		if ( ! hero ) {
			return;
		}

		var pin = hero.querySelector( '.door-hero-pin' );
		var behind = hero.querySelector( '.door-hero-behind' );
		var head = document.getElementById( 'doorHeroHead' );
		var lockup = document.getElementById( 'doorHeroLockup' );
		var hint = document.getElementById( 'doorHeroHint' );
		var cards = [].slice.call( hero.querySelectorAll( '.door-hero-card' ) );
		// All five are part of the same template part, so a missing one means
		// the markup changed underneath this script — bail rather than half-
		// animate a section whose CSS default is already a perfectly good
		// finished state.
		if ( ! pin || ! behind || ! head || ! lockup || ! hint ) {
			return;
		}

		// Reduced motion: leave the CSS default alone. That default is
		// already the finished open state, and without .door-hero--scrub the
		// section is one screen tall with no sticky pin — so there's no
		// animation AND no three-viewports of dead scrolling to get past.
		// No listener is bound at all.
		if ( reduceMotion ) {
			return;
		}

		// Only now does the section become tall/pinned and the doors shut.
		// Adding the class and painting the first frame happen in the same
		// synchronous block, so there's no flash of the open state first.
		hero.classList.add( 'door-hero--scrub' );

		var scrollRange = 0;
		var heroTop = 0;
		var frameQueued = false;
		// Set by a keyboard user tabbing into the content behind the doors:
		// from then on the section renders its open state regardless of
		// scroll, so nobody has to scroll an animation to read what they
		// just focused. Released when focus leaves the section again.
		var forcedOpen = false;

		measure();
		render( progress() );

		window.addEventListener( 'scroll', onScroll, { passive: true } );
		window.addEventListener( 'resize', onResize );
		// Late-loading images/webfonts above this section can shift its
		// document offset after DOMContentLoaded, which would put the pin and
		// the progress maths out of step until the first resize.
		window.addEventListener( 'load', onResize );

		// Anything focusable inside the revealed content — the cards are
		// plain text today, but a link added to one later must not be
		// reachable-yet-invisible, so this is bound to the container rather
		// than to today's specific elements.
		hero.addEventListener( 'focusin', function ( e ) {
			if ( behind.contains( e.target ) ) {
				forcedOpen = true;
				render( 1 );
			}
		} );
		hero.addEventListener( 'focusout', function ( e ) {
			// relatedTarget is where focus is heading; null when it leaves
			// the document entirely, which shouldn't drop the forced state.
			if ( e.relatedTarget && ! hero.contains( e.relatedTarget ) ) {
				forcedOpen = false;
				render( progress() );
			}
		} );

		/**
		 * Cached geometry, so the scroll path never touches the layout.
		 * offsetTop is walked up the offsetParent chain because the section
		 * sits inside <main>, not at the document root.
		 */
		function measure() {
			var top = 0;
			var node = hero;
			while ( node ) {
				top += node.offsetTop;
				node = node.offsetParent;
			}
			heroTop = top;
			// How far the page scrolls while the pin is stuck: the section's
			// full height less the one viewport the pin itself occupies.
			scrollRange = Math.max( hero.offsetHeight - window.innerHeight, 1 );
		}

		function progress() {
			return clamp( ( window.pageYOffset - heroTop ) / scrollRange );
		}

		function onScroll() {
			if ( frameQueued || forcedOpen ) {
				return;
			}
			frameQueued = true;
			window.requestAnimationFrame( function () {
				frameQueued = false;
				render( progress() );
			} );
		}

		function onResize() {
			measure();
			render( forcedOpen ? 1 : progress() );
		}

		function render( p ) {
			// Doors: a single 0-1 custom property; the CSS turns it into a
			// translate of each door's own width, so this needs no pixel
			// maths and stays correct through a resize with no recalculation.
			hero.style.setProperty( '--door-open', easeInOut( seg( p, 0, DOORS_END ) ).toFixed( 4 ) );

			var shut = seg( p, 0, LOCKUP_END );
			lockup.style.opacity = 1 - shut;
			lockup.style.transform = 'translate(-50%, -50%) scale(' + ( 1 - 0.06 * shut ) + ')';
			hint.style.opacity = 1 - seg( p, 0, HINT_END );

			var h = easeOut( seg( p, HEAD_START, HEAD_END ) );
			head.style.opacity = h;
			head.style.transform = 'translate3d(0,' + ( 1 - h ) * 22 + 'px,0)';

			for ( var i = 0; i < cards.length; i++ ) {
				var start = CARD_START + i * CARD_STAGGER;
				var c = easeOut( seg( p, start, start + CARD_WINDOW ) );
				cards[ i ].style.opacity = c;
				cards[ i ].style.transform = 'translate3d(0,' + ( 1 - c ) * 26 + 'px,0)';
			}
		}
	}

	function clamp( v ) {
		return v < 0 ? 0 : v > 1 ? 1 : v;
	}

	/** Progress p renormalised to 0-1 across the window a-b. */
	function seg( p, a, b ) {
		return clamp( ( p - a ) / ( b - a ) );
	}

	function easeOut( t ) {
		return 1 - Math.pow( 1 - t, 3 );
	}

	function easeInOut( t ) {
		return t < 0.5 ? 4 * t * t * t : 1 - Math.pow( -2 * t + 2, 3 ) / 2;
	}
} )();
