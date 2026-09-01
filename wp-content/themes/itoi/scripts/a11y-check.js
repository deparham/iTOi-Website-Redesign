/**
 * Accessibility check — axe-core, driven via this project's own Puppeteer
 * dependency (no extra package to install/verify).
 *
 * Why axe-core directly, not pa11y-ci: pa11y-ci's default runner
 * (HTML_CodeSniffer) walks the DOM ancestor chain looking for a
 * background-color to compute contrast against. That false-positives on
 * this theme's hero/funnel-style sections, where the dark background is a
 * separate, absolutely-positioned sibling <div> (#heroBg etc.) — a
 * deliberate, working layering pattern used throughout this codebase, not
 * a bug. axe-core's contrast rule correctly accounts for the actual
 * rendered/stacked background instead of just walking ancestors. Confirmed
 * directly: pa11y-ci (default runner) reported 3 contrast "violations" on
 * hero-style sections that axe-core (this script) has consistently, and
 * independently, reported as zero violations on the same pages throughout
 * development (see NOTES.md, 2026-08-05 entries). Switching pa11y-ci to
 * axe as its runner needs the separate `pa11y-runner-axe` package, which
 * wasn't available to verify in this environment — rather than document a
 * fix that was never actually confirmed working, this script uses the
 * already-proven approach directly.
 *
 * Usage: node scripts/a11y-check.js [baseUrl]
 * Exit code 1 if any page has violations (for CI), 0 otherwise.
 */
const puppeteer = require( 'puppeteer' );
const fs = require( 'fs' );
const axeSrc = fs.readFileSync( require.resolve( 'axe-core/axe.min.js' ), 'utf8' );

const baseUrl = ( process.argv[ 2 ] || process.env.A11Y_BASE_URL || 'http://192.168.22.80' ).replace( /\/$/, '' );

const paths = [
	'/',
	'/contact/',
	'/solutions/intelligence-analytics/',
	'/industries/retail/',
	'/case-studies/drakes-supermarkets/',
	'/products/aurora/',
	'/solution-builder/',
	'/about/',
	'/insights/',
];

( async () => {
	const browser = await puppeteer.launch( { headless: 'new', args: [ '--no-sandbox' ] } );
	let anyViolations = false;

	for ( const path of paths ) {
		const url = baseUrl + path;
		const page = await browser.newPage();
		await page.setViewport( { width: 1440, height: 900 } );
		try {
			await page.goto( url, { waitUntil: 'networkidle0', timeout: 30000 } );
			await page.evaluate( axeSrc );
			const results = await page.evaluate( async () => await axe.run( document, { resultTypes: [ 'violations' ] } ) );
			if ( results.violations.length ) {
				anyViolations = true;
				console.log( `\n✘ ${ url } — ${ results.violations.length } violation(s)` );
				results.violations.forEach( ( v ) => {
					console.log( `  [${ v.impact }] ${ v.id }: ${ v.help } (${ v.nodes.length } node(s))` );
					v.nodes.forEach( ( n ) => console.log( '     - ' + n.target.join( ' ' ) ) );
				} );
			} else {
				console.log( `✓ ${ url } — no violations` );
			}
		} catch ( err ) {
			anyViolations = true;
			console.log( `✘ ${ url } — failed to load: ${ err.message }` );
		}
		await page.close();
	}

	await browser.close();
	process.exit( anyViolations ? 1 : 0 );
} )();
