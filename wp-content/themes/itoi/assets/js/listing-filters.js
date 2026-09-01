/**
 * ITOI listing-page filter pills: page-portfolio.php, archive-guide.php,
 * page-use-cases.php, and the 3 Education Hub pages (Glossary/FAQ/hub
 * landing, which share the [data-filter-root] pattern). Each function
 * guards on its own page's specific element IDs, so bundling all 4 is
 * safe — a given page only ever matches one of them.
 *
 * Split out of the single assets/js/main.js 2026-08-06 (JS bundle split,
 * see NOTES.md) — same functions, same behavior. See assets/js/core.js's
 * header comment for the full split rationale.
 */
(function () {
	'use strict';

	var reduceMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

	document.addEventListener( 'DOMContentLoaded', function () {
		initClientFilter();
		initGuideFilter();
		initUseCaseFilter();
		initItoiFilterLists();
	} );

	// ---------------------------------------------------------------
	// Portfolio page — category filter pills (page-portfolio.php).
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
	// Guides index (archive-guide.php) — industry filter pills. Same
	// mechanic as initClientFilter above (page-portfolio.php), duplicated
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

})();
