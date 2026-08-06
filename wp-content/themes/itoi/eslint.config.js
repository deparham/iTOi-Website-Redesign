// ESLint flat config (ESLint 9+). This theme's JS (assets/js/*.js) is
// plain, unbundled, browser-global vanilla JS — no module system, no
// build step for JS (see PROJECT.md §2: "Vanilla JS for interactivity...
// don't fight WordPress core's jQuery, but don't build on it either").
// Kept intentionally light: catches real bugs (unused vars, undefined
// globals, accidental globals) without fighting this codebase's existing
// style (tabs, single quotes already used consistently — not re-litigated
// here as lint rules, to avoid a mass reformat with no functional value).
module.exports = [
	{
		files: [ 'assets/js/**/*.js' ],
		ignores: [ 'node_modules/**', 'vendor/**' ],
		languageOptions: {
			ecmaVersion: 2019,
			sourceType: 'script',
			globals: {
				window: 'readonly',
				document: 'readonly',
				navigator: 'readonly',
				location: 'readonly',
				sessionStorage: 'readonly',
				localStorage: 'readonly',
				console: 'readonly',
				fetch: 'readonly',
				FormData: 'readonly',
				IntersectionObserver: 'readonly',
				MutationObserver: 'readonly',
				requestAnimationFrame: 'readonly',
				cancelAnimationFrame: 'readonly',
				setTimeout: 'readonly',
				clearTimeout: 'readonly',
				setInterval: 'readonly',
				clearInterval: 'readonly',
				Node: 'readonly',
				getComputedStyle: 'readonly',
				Image: 'readonly',
				URL: 'readonly',
				Event: 'readonly',
				CustomEvent: 'readonly',
				URLSearchParams: 'readonly',
				// wp-admin-only scripts (e.g. admin-use-case-bulk-edit.js)
				// run in a context where WP core's own admin JS has already
				// defined this.
				wp: 'readonly',
				// Data localized server-side via wp_localize_script()
				// (inc/enqueue.php) — real globals by design, not typos.
				itoiHeroSlides: 'readonly',
				itoiWhyTabs: 'readonly',
				itoiSolutionBuilderConfig: 'readonly',
				itoiSolutionBuilder: 'readonly',
			},
		},
		rules: {
			'no-unused-vars': [ 'warn', { args: 'none' } ],
			'no-undef': 'error',
			'no-redeclare': 'error',
			'no-var': 'off', // this codebase deliberately uses var, not let/const (ES5-leaning style)
			eqeqeq: [ 'warn', 'smart' ],
			'no-implicit-globals': 'error',
		},
	},
];
