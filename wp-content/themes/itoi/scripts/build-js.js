/**
 * Minifies each front-end JS bundle in assets/js/ to a sibling .min.js file
 * via terser (--compress --mangle). Run via `npm run build:js` (also part
 * of the top-level `npm run build`).
 *
 * Explicit file list, not a glob over assets/js/*.js — deliberately skips
 * assets/js/admin-use-case-bulk-edit.js (admin-only, never enqueued on the
 * front end, so there's nothing to minify it for) and any future non-bundle
 * script added to the directory without also being added here.
 */
const fs = require( 'fs' );
const path = require( 'path' );
const { minify } = require( 'terser' );

const JS_DIR = path.join( __dirname, '..', 'assets', 'js' );
const BUNDLES = [
	'core.js',
	'door-hero.js',
	'homepage.js',
	'industry-simulators.js',
	'listing-filters.js',
	'solution-builder.js',
];

( async () => {
	for ( const file of BUNDLES ) {
		const srcPath = path.join( JS_DIR, file );
		if ( ! fs.existsSync( srcPath ) ) {
			console.warn( `skip (not found): ${ file }` );
			continue;
		}
		const code = fs.readFileSync( srcPath, 'utf8' );
		const result = await minify( code, { compress: true, mangle: true } );
		if ( result.error ) {
			throw result.error;
		}
		const outPath = path.join( JS_DIR, file.replace( /\.js$/, '.min.js' ) );
		fs.writeFileSync( outPath, result.code );
		const before = Buffer.byteLength( code, 'utf8' );
		const after = Buffer.byteLength( result.code, 'utf8' );
		console.log( `${ file } -> ${ path.basename( outPath ) } (${ before }B -> ${ after }B)` );
	}
} )().catch( ( err ) => {
	console.error( err );
	process.exit( 1 );
} );
