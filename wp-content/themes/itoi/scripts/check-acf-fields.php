<?php
/**
 * Static ACF field audit — no WordPress bootstrap required, pure file
 * parsing. Cross-references every get_field()/the_field() call in the
 * theme's PHP against every field `name` defined across acf-json/*.json
 * (recursing into repeater/flexible-content/group sub_fields), and reports:
 *
 *   1. Field names referenced in templates with no matching acf-json field
 *      (typo, abandoned field, or a field that only exists via wp-admin UI
 *      registration rather than local JSON — worth checking by hand).
 *   2. Which options page / post type each acf-json group attaches to (used
 *      to generate docs/acf-fields.md — see that file for the human-readable
 *      version of this same data).
 *
 * Usage: php scripts/check-acf-fields.php
 * Exit code: 0 if no unmatched fields found, 1 otherwise (CI-friendly).
 *
 * Known non-issues this script will still flag (read before "fixing"):
 *   - `the_field`/`get_field` calls whose first argument is a variable, not
 *     a string literal (e.g. get_field( $itoi_key )) — can't be resolved
 *     statically, and are printed separately as "dynamic calls, not checked"
 *     rather than false-flagged as unmatched.
 *   - ACF's own built-in pseudo-fields some templates read via get_field()
 *     that aren't declared in any group (e.g. none currently in this theme,
 *     but WooCommerce/other plugins sometimes add these) would show up as
 *     unmatched; there are none of that kind here as of this writing.
 */

$theme_dir = dirname( __DIR__ );
$json_dir  = $theme_dir . '/acf-json';

// ---------------------------------------------------------------------
// 1. Parse every acf-json group, collect field names (recursively) and
// the location rules (post_type / options_page) it attaches to.
// ---------------------------------------------------------------------
$all_field_names = array(); // name => [group_title, ...]
$groups_summary  = array(); // for docs/acf-fields.md generation

function itoi_collect_field_names( array $fields, array &$all_field_names, string $group_title ) {
	foreach ( $fields as $field ) {
		if ( ! empty( $field['name'] ) ) {
			$all_field_names[ $field['name'] ][] = $group_title;
		}
		// Repeater / group / flexible content: recurse into sub_fields.
		if ( ! empty( $field['sub_fields'] ) && is_array( $field['sub_fields'] ) ) {
			itoi_collect_field_names( $field['sub_fields'], $all_field_names, $group_title );
		}
		// Flexible content: layouts each carry their own sub_fields.
		if ( ! empty( $field['layouts'] ) && is_array( $field['layouts'] ) ) {
			foreach ( $field['layouts'] as $layout ) {
				if ( ! empty( $layout['sub_fields'] ) && is_array( $layout['sub_fields'] ) ) {
					itoi_collect_field_names( $layout['sub_fields'], $all_field_names, $group_title );
				}
			}
		}
	}
}

function itoi_describe_location( array $location_groups ) {
	$parts = array();
	foreach ( $location_groups as $or_group ) {
		$clauses = array();
		foreach ( $or_group as $rule ) {
			$clauses[] = sprintf( '%s %s %s', $rule['param'] ?? '?', $rule['operator'] ?? '==', $rule['value'] ?? '?' );
		}
		$parts[] = implode( ' AND ', $clauses );
	}
	return implode( ' OR ', $parts );
}

$json_files = glob( $json_dir . '/*.json' );
sort( $json_files );

foreach ( $json_files as $file ) {
	$raw  = file_get_contents( $file );
	$data = json_decode( $raw, true );
	if ( null === $data && JSON_ERROR_NONE !== json_last_error() ) {
		fwrite( STDERR, "INVALID JSON: $file — " . json_last_error_msg() . "\n" );
		continue;
	}
	$title    = $data['title'] ?? basename( $file );
	$location = isset( $data['location'] ) ? itoi_describe_location( $data['location'] ) : '(none)';

	$field_count_before = count( $all_field_names );
	itoi_collect_field_names( $data['fields'] ?? array(), $all_field_names, $title );

	$groups_summary[] = array(
		'file'     => basename( $file ),
		'title'    => $title,
		'location' => $location,
		'fields'   => $data['fields'] ?? array(),
	);
}

// ---------------------------------------------------------------------
// 2. Grep all PHP files for get_field( 'name' ... ) / the_field( 'name' ... )
// and get_sub_field( 'name' ... ) — string-literal first argument only.
// ---------------------------------------------------------------------
$php_files = array();
$rii       = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $theme_dir, FilesystemIterator::SKIP_DOTS ) );
foreach ( $rii as $f ) {
	if ( 'php' === $f->getExtension()
		&& false === strpos( $f->getPathname(), '/vendor/' )
		&& false === strpos( $f->getPathname(), '/node_modules/' )
		&& basename( $f->getPathname() ) !== basename( __FILE__ ) ) {
		$php_files[] = $f->getPathname();
	}
}

$used_fields  = array(); // name => [ "file.php:12", ... ]
$dynamic_uses = array(); // "file.php:12: get_field( $var )" — can't resolve statically

foreach ( $php_files as $file ) {
	$lines = file( $file );
	foreach ( $lines as $i => $line ) {
		// Skip comment-only lines (// ..., * ..., /** ...) so prose that
		// merely mentions get_field()/the_field() isn't treated as a call.
		if ( preg_match( '#^\s*(//|\*|/\*)#', $line ) ) {
			continue;
		}
		if ( ! preg_match( '/\b(get_field|the_field|get_sub_field)\s*\(\s*(.+?)\s*[,)]/', $line, $m ) ) {
			continue;
		}
		$arg = $m[2];
		if ( preg_match( '/^[\'"]([a-zA-Z0-9_]+)[\'"]$/', $arg, $sm ) ) {
			$name                   = $sm[1];
			$used_fields[ $name ][] = str_replace( $theme_dir . '/', '', $file ) . ':' . ( $i + 1 );
		} else {
			$dynamic_uses[] = str_replace( $theme_dir . '/', '', $file ) . ':' . ( $i + 1 ) . ': ' . trim( $line );
		}
	}
}

// ---------------------------------------------------------------------
// 3. Report.
// ---------------------------------------------------------------------
echo '=== ACF field groups found: ' . count( $groups_summary ) . " ===\n";
foreach ( $groups_summary as $g ) {
	printf( "  %-22s %-40s -> %s\n", $g['file'], $g['title'], $g['location'] );
}

echo "\n=== Fields referenced in templates but NOT in any acf-json group ===\n";
$unmatched = array();
foreach ( $used_fields as $name => $refs ) {
	if ( ! isset( $all_field_names[ $name ] ) ) {
		$unmatched[ $name ] = $refs;
	}
}
if ( empty( $unmatched ) ) {
	echo "  (none)\n";
} else {
	foreach ( $unmatched as $name => $refs ) {
		echo "  '$name' used at:\n";
		foreach ( $refs as $ref ) {
			echo "    - $ref\n";
		}
	}
}

echo "\n=== Dynamic get_field()/the_field() calls (first arg not a string literal — not checked) ===\n";
if ( empty( $dynamic_uses ) ) {
	echo "  (none)\n";
} else {
	foreach ( $dynamic_uses as $d ) {
		echo "  $d\n";
	}
}

echo "\n";
if ( ! empty( $unmatched ) ) {
	echo 'RESULT: ' . count( $unmatched ) . " unmatched field name(s) found.\n";
	exit( 1 );
}
echo "RESULT: every string-literal get_field()/the_field() call resolves to a real acf-json field.\n";
exit( 0 );
