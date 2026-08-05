/**
 * Solution Builder (/solution-builder/) — multi-step form, AJAX scoring,
 * lead capture, and a client-side print proposal. All scoring/ROI/timeline
 * arithmetic lives server-side (inc/solution-builder.php); this file only
 * collects answers, renders whatever the server returns, and builds the
 * printable proposal from that same response — no rules are duplicated
 * here. Vanilla JS, no build step, no new dependency (PROJECT.md §2).
 */
( function () {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', function () {
		var config = window.itoiSolutionBuilder || {};
		var form = document.getElementById( 'sbForm' );
		if ( ! form || ! config.ajaxUrl ) {
			return;
		}

		var steps        = Array.prototype.slice.call( document.querySelectorAll( '.sb-step' ) );
		var progressWrap = document.getElementById( 'sbProgress' );
		var backBtn      = document.getElementById( 'sbBack' );
		var nextBtn      = document.getElementById( 'sbNext' );
		var loadingEl    = document.getElementById( 'sbLoading' );
		var resultsEl    = document.getElementById( 'sbResults' );
		var totalSteps   = steps.length;
		var current      = 0;
		var answers      = { challenges: [] };

		// Icon paths mirror inc/solution-builder.php's itoi_solution_builder_icon()
		// exactly — presentation only, not scoring logic, so this duplication
		// is cosmetic (kept in sync by hand; both are small and static).
		var ICONS = {
			'intelligence-analytics':      'M4 20V10M10 20V4M16 20v-7M22 20V8',
			'customer-engagement-signage': 'M8 20h8M12 16v4',
			'sensory-intelligence':        'M12 2v3M4.2 6.2l2 2M2 13h3M19 13h3M17.8 8.2l2-2',
			'workforce-ops-robotics':      'M12 8V5M9 5h6',
			'cctv-video-loss-prevention':  'M3 8.5 13 6v12L3 15.5v-7Z M13 9.5 21 7v10l-8-2.5',
			'security-access-inventory':   'M12 2.5 20 5.5V11c0 5-3.5 9-8 10.5-4.5-1.5-8-5.5-8-10.5V5.5L12 2.5Z',
			'back-of-house-integration':   'M12 3 21 8 12 13 3 8 12 3Z M3 16 12 21 21 16',
			'it-network-infrastructure':   'M12 6v5M12 11 6 17M12 11l6 6',
			'dashboard':                   'M3 9h18M8 14h3',
			'existing-systems':            'M9 20h6M12 17v3'
		};

		function iconSvg( slug ) {
			var d = ICONS[ slug ] || ICONS.dashboard;
			return '<svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="' + d + '"/></svg>';
		}

		// --- Build a value -> label map straight from the rendered option
		// buttons, so the print proposal never needs a second copy of copy. ---
		var optionLabels = {};
		document.querySelectorAll( '.sb-options' ).forEach( function ( group ) {
			var groupName = group.dataset.group;
			optionLabels[ groupName ] = {};
			group.querySelectorAll( '.sb-opt' ).forEach( function ( opt ) {
				optionLabels[ groupName ][ opt.dataset.value ] = opt.textContent.trim();
			} );
		} );

		// --- Progress dots ---
		for ( var i = 0; i < totalSteps; i++ ) {
			var dot = document.createElement( 'div' );
			dot.className = 'sb-dot';
			progressWrap.appendChild( dot );
		}
		var dots = progressWrap.querySelectorAll( '.sb-dot' );

		function renderProgress() {
			dots.forEach( function ( d, di ) {
				d.classList.toggle( 'done', di < current );
				d.classList.toggle( 'current', di === current );
			} );
			progressWrap.setAttribute( 'aria-valuenow', String( current + 1 ) );
		}

		function currentGroupName() {
			return steps[ current ].querySelector( '.sb-options' ).dataset.group;
		}

		function updateNextState() {
			var group    = steps[ current ].querySelector( '.sb-options' );
			var name     = group.dataset.group;
			var isMulti  = 'multi' === group.dataset.select;
			if ( isMulti ) {
				nextBtn.disabled = false; // "choose any" — zero selections is valid
			} else {
				nextBtn.disabled = ! answers[ name ];
			}
			nextBtn.textContent = ( current === totalSteps - 1 ) ? 'See my recommendations' : 'Next';
		}

		function showStep( i ) {
			steps.forEach( function ( s ) {
				s.classList.toggle( 'active', +s.dataset.step === i );
			} );
			current = i;
			renderProgress();
			backBtn.disabled = ( 0 === i );
			updateNextState();
		}

		// --- Option selection ---
		document.querySelectorAll( '.sb-opt' ).forEach( function ( opt ) {
			opt.addEventListener( 'click', function () {
				var group   = opt.closest( '.sb-options' );
				var name    = group.dataset.group;
				var isMulti = 'multi' === group.dataset.select;

				if ( isMulti ) {
					opt.classList.toggle( 'selected' );
					var idx = answers.challenges.indexOf( opt.dataset.value );
					if ( opt.classList.contains( 'selected' ) && -1 === idx ) {
						answers.challenges.push( opt.dataset.value );
					} else if ( ! opt.classList.contains( 'selected' ) && idx > -1 ) {
						answers.challenges.splice( idx, 1 );
					}
				} else {
					group.querySelectorAll( '.sb-opt' ).forEach( function ( o ) {
						o.classList.remove( 'selected' );
					} );
					opt.classList.add( 'selected' );
					answers[ name ] = opt.dataset.value;
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
			if ( current < totalSteps - 1 ) {
				showStep( current + 1 );
				return;
			}
			runCalculate();
		} );

		// --- AJAX: calculate ---
		function postForm( action, extraFields ) {
			var body = new URLSearchParams();
			body.set( 'action', action );
			body.set( 'nonce', config.nonce );
			body.set( 'business_type', answers.business_type || '' );
			body.set( 'employees', answers.employees || '' );
			body.set( 'sites', answers.sites || '' );
			body.set( 'existing_cctv', answers.existing_cctv || '' );
			body.set( 'existing_pos', answers.existing_pos || '' );
			body.set( 'cloud_based', answers.cloud_based || '' );
			answers.challenges.forEach( function ( c ) {
				body.append( 'challenges[]', c );
			} );
			if ( extraFields ) {
				Object.keys( extraFields ).forEach( function ( k ) {
					body.set( k, extraFields[ k ] );
				} );
			}
			return fetch( config.ajaxUrl, {
				method: 'POST',
				headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
				body: body.toString()
			} ).then( function ( r ) {
				return r.json();
			} );
		}

		var lastResult = null;

		function runCalculate() {
			loadingEl.hidden = false;
			nextBtn.disabled = true;
			postForm( 'itoi_solution_builder_calculate' ).then( function ( res ) {
				loadingEl.hidden = true;
				if ( ! res.success ) {
					nextBtn.disabled = false;
					return;
				}
				lastResult = res.data;
				renderResults( res.data );
				form.hidden = true;
				resultsEl.hidden = false;
				resultsEl.scrollIntoView( { behavior: 'smooth', block: 'start' } );
			} ).catch( function () {
				loadingEl.hidden = true;
				nextBtn.disabled = false;
			} );
		}

		function formatCurrency( n ) {
			return '$' + Math.round( n ).toLocaleString( 'en-AU' ) + ' AUD';
		}

		function renderResults( data ) {
			var cards = document.getElementById( 'sbRecommendedCards' );
			cards.innerHTML = '';
			data.recommended.forEach( function ( sol ) {
				var card = document.createElement( 'div' );
				// Liquid glass, wave 5: no glass here (recommended-solution
				// items explicitly stay on their current solid styling, per
				// the brief) — bg-white added explicitly since #sbResults'
				// section background is no longer implicitly white (see
				// page-solution-builder.php).
				card.className = 'rounded-2xl border border-line bg-white p-6';
				card.innerHTML =
					'<div class="mb-3 flex h-11 w-11 items-center justify-center rounded-full bg-hero-bg text-ink">' + iconSvg( sol.slug ) + '</div>' +
					'<h4 class="mb-1.5 text-[17px] font-bold"><a href="' + sol.url + '" class="hover:underline">' + sol.title + '</a></h4>' +
					'<p class="text-[14px] text-text-muted">' + sol.desc + '</p>';
				cards.appendChild( card );
			} );

			renderArchitecture( document.getElementById( 'sbArchitecture' ), data );

			document.getElementById( 'sbRoiTotal' ).textContent = formatCurrency( data.roi.total );
			document.getElementById( 'sbRoiDisclaimer' ).textContent = data.roi.disclaimer;
			document.getElementById( 'sbTimelineRange' ).textContent = data.timeline.range;
			document.getElementById( 'sbTimelineCaption' ).textContent = data.timeline.caption;
		}

		function renderArchitecture( container, data ) {
			container.innerHTML = '';
			var row = document.createElement( 'div' );
			row.className = 'sb-flow';

			function node( slug, title ) {
				var n = document.createElement( 'div' );
				n.className = 'sb-flow-node';
				n.innerHTML = '<span class="sb-flow-icon">' + iconSvg( slug ) + '</span><span class="sb-flow-label">' + title + '</span>';
				return n;
			}
			function arrow() {
				var a = document.createElement( 'span' );
				a.className = 'sb-flow-arrow';
				a.setAttribute( 'aria-hidden', 'true' );
				a.textContent = '→';
				return a;
			}

			var stack = document.createElement( 'div' );
			stack.className = 'sb-flow-stack';
			data.architecture.nodes.forEach( function ( n ) {
				stack.appendChild( node( n.slug, n.title ) );
			} );
			row.appendChild( stack );
			row.appendChild( arrow() );
			row.appendChild( node( 'dashboard', 'ITOI Dashboard' ) );
			if ( data.architecture.show_existing_systems ) {
				row.appendChild( arrow() );
				row.appendChild( node( 'existing-systems', 'Your existing systems' ) );
			}
			container.appendChild( row );
		}

		// --- Lead capture ---
		var leadForm    = document.getElementById( 'sbLeadForm' );
		var leadError   = document.getElementById( 'sbLeadError' );
		var leadSuccess = document.getElementById( 'sbLeadSuccess' );
		var downloadBtn = document.getElementById( 'sbDownloadBtn' );
		var submitBtn   = document.getElementById( 'sbLeadSubmit' );

		leadForm.addEventListener( 'submit', function ( e ) {
			e.preventDefault();
			leadError.classList.add( 'hidden' );

			var name    = document.getElementById( 'sbName' ).value.trim();
			var email   = document.getElementById( 'sbEmail' ).value.trim();
			var company = document.getElementById( 'sbCompany' ).value.trim();
			var phone   = document.getElementById( 'sbPhone' ).value.trim();
			var emailOk = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test( email );

			if ( ! name || ! company || ! emailOk ) {
				leadError.textContent = 'Please enter your name, a valid email, and your company.';
				leadError.classList.remove( 'hidden' );
				return;
			}

			submitBtn.disabled = true;
			submitBtn.textContent = 'Sending…';

			postForm( 'itoi_solution_builder_submit_lead', { name: name, email: email, company: company, phone: phone } ).then( function ( res ) {
				submitBtn.disabled = false;
				submitBtn.textContent = 'Get my proposal';
				if ( ! res.success ) {
					leadError.textContent = ( res.data && res.data.message ) ? res.data.message : 'Something went wrong — please try again.';
					leadError.classList.remove( 'hidden' );
					return;
				}
				leadSuccess.classList.remove( 'hidden' );
				downloadBtn.classList.remove( 'hidden' );
				buildPrintProposal( res.data, { name: name, email: email, company: company, phone: phone } );
			} ).catch( function () {
				submitBtn.disabled = false;
				submitBtn.textContent = 'Get my proposal';
				leadError.textContent = 'Something went wrong — please try again.';
				leadError.classList.remove( 'hidden' );
			} );
		} );

		downloadBtn.addEventListener( 'click', function () {
			window.print();
		} );

		function label( group, value ) {
			return ( optionLabels[ group ] && optionLabels[ group ][ value ] ) ? optionLabels[ group ][ value ] : value;
		}

		function buildPrintProposal( data, lead ) {
			var challengeLabels = answers.challenges.map( function ( c ) {
				return label( 'challenges', c );
			} );

			var solutionsHtml = data.recommended.map( function ( sol ) {
				return '<div class="sb-print-solution"><h4>' + sol.title + '</h4><p>' + sol.desc + '</p></div>';
			} ).join( '' );

			var flowNames = data.architecture.nodes.map( function ( n ) {
				return n.title;
			} );
			flowNames.push( 'ITOI Dashboard' );
			if ( data.architecture.show_existing_systems ) {
				flowNames.push( 'Your existing systems' );
			}

			var contact = data.contact || {};
			var contactLine = contact.name || 'ITOI Solutions';
			if ( contact.role ) {
				contactLine += ', ' + contact.role;
			}

			var today = new Date().toLocaleDateString( 'en-AU', { year: 'numeric', month: 'long', day: 'numeric' } );

			document.getElementById( 'sbProposalPrint' ).innerHTML =
				'<div class="sb-print-page">' +
					'<div class="sb-print-header"><div class="sb-print-logo">ITOI Solutions</div><div>' + today + '</div></div>' +
					'<h1>Solution Builder Proposal</h1>' +
					'<p>Prepared for <strong>' + lead.name + '</strong>, ' + lead.company + '</p>' +

					'<h2>Your inputs</h2>' +
					'<ul class="sb-print-list">' +
						'<li>Business type: ' + label( 'business_type', answers.business_type ) + '</li>' +
						'<li>Employees: ' + label( 'employees', answers.employees ) + '</li>' +
						'<li>Sites: ' + label( 'sites', answers.sites ) + '</li>' +
						'<li>Existing CCTV: ' + label( 'existing_cctv', answers.existing_cctv ) + '</li>' +
						'<li>Existing POS: ' + label( 'existing_pos', answers.existing_pos ) + '</li>' +
						'<li>Cloud-based operations: ' + label( 'cloud_based', answers.cloud_based ) + '</li>' +
						'<li>Challenges: ' + ( challengeLabels.length ? challengeLabels.join( ', ' ) : 'None selected' ) + '</li>' +
					'</ul>' +

					'<h2>Recommended solutions</h2>' +
					solutionsHtml +

					'<h2>How it fits together</h2>' +
					'<p>' + flowNames.join( ' → ' ) + '</p>' +

					'<h2>Estimated Annual Value</h2>' +
					'<p class="sb-print-roi">' + formatCurrency( data.roi.total ) + '</p>' +
					'<p class="sb-print-disclaimer">' + data.roi.disclaimer + '</p>' +

					'<h2>Implementation timeline</h2>' +
					'<p>' + data.timeline.range + '</p>' +
					'<p class="sb-print-disclaimer">' + data.timeline.caption + '</p>' +

					'<h2>Contact</h2>' +
					'<p>' + contactLine + '<br>' + contact.email + '</p>' +
				'</div>';
		}

		// --- Popup handoff (footer.php's floating quiz) ---
		// A visitor who just finished the 7 questions in the sitewide popup
		// arrives here with their answers already in sessionStorage instead
		// of clicking through this page's own form a second time. Consumed
		// (read + removed) once, immediately, so a later plain reload of
		// this page always falls back to the standalone form below rather
		// than replaying stale results.
		var handoffAnswers = readHandoffAnswers();
		if ( handoffAnswers ) {
			answers = handoffAnswers;
			form.hidden = true;
			loadingEl.hidden = false;
			runCalculate();
		} else {
			showStep( 0 );
		}

		function readHandoffAnswers() {
			var raw;
			try {
				raw = sessionStorage.getItem( 'itoi_solution_builder_answers' );
				if ( raw ) {
					sessionStorage.removeItem( 'itoi_solution_builder_answers' );
				}
			} catch ( e ) {
				return null; // sessionStorage unavailable (private mode etc.) — standalone form still works
			}
			if ( ! raw ) {
				return null;
			}
			var parsed;
			try {
				parsed = JSON.parse( raw );
			} catch ( e ) {
				return null;
			}
			if ( ! parsed || 'object' !== typeof parsed || ! parsed.business_type ) {
				return null;
			}
			parsed.challenges = Array.isArray( parsed.challenges ) ? parsed.challenges : [];
			return parsed;
		}
	} );
} )();
