/**
 * ITOI single-industry.php interactivity: the 7 per-industry interactive
 * mechanics (foot-traffic funnel, hospitality timeline, banking simulator,
 * government funding chart, logistics comparison slider, stadium density,
 * casino floor map) plus the long-form sub-nav scrollspy/header-hide.
 * Each mechanic guards on its own root element's presence — a given
 * industry only ever renders one of the 7 simulators, so the other 6
 * safely no-op on that page; bundling all 7 together (rather than 7
 * separate enqueues) keeps inc/enqueue.php simple since they share one
 * condition (is_singular('industry')).
 *
 * Split out of the single assets/js/main.js 2026-08-06 (JS bundle split,
 * see NOTES.md) — same functions, same behavior. See assets/js/core.js's
 * header comment for the full split rationale.
 */
(function () {
	'use strict';

	var reduceMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

	document.addEventListener( 'DOMContentLoaded', function () {
		initFootTrafficFunnel();
		initHospitalityTimeline();
		initBankingSimulator();
		initGovernmentFundingChart();
		initLogisticsComparison();
		initStadiumDensity();
		initCasinoFloorMap();
		initLongformScrollspy();
		initLongformHeaderHide();
	} );

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

})();
