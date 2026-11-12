<?php
/**
 * Standalone tests for the document validator.
 */
define( 'ABSPATH', __DIR__ );
function wp_parse_url( $url, $component = -1 ) { return parse_url( $url, $component ); }
require_once __DIR__ . '/../../packages/plugin/nirmaan-builder/includes/class-document-validator.php';

use NirmaanBuilder\Document_Validator;

function check( $condition, $message ) {
	if ( ! $condition ) {
		fwrite( STDERR, "FAIL: {$message}\n" );
		exit( 1 );
	}
}

$valid = array(
	'schema_version' => 1,
	'elements' => array(
		array( 'id' => 'heading-1', 'type' => 'heading', 'settings' => array( 'text' => 'Hello', 'level' => 'h2', 'align' => 'left' ) ),
		array( 'id' => 'button-1', 'type' => 'button', 'settings' => array( 'text' => 'Go', 'url' => '/contact', 'align' => 'center' ) ),
	),
);
check( Document_Validator::is_valid( $valid ), 'valid document should pass' );

$invalid = $valid;
$invalid['elements'][0]['settings']['onclick'] = 'alert(1)';
check( ! Document_Validator::is_valid( $invalid ), 'unknown module settings should fail' );

$invalid = $valid;
$invalid['elements'][1]['settings']['url'] = 'javascript:alert(1)';
check( ! Document_Validator::is_valid( $invalid ), 'unsafe URL scheme should fail' );

$invalid = $valid;
$invalid['elements'][1]['id'] = $invalid['elements'][0]['id'];
check( ! Document_Validator::is_valid( $invalid ), 'duplicate element IDs should fail' );

$invalid = $valid;
$invalid['elements'][0]['settings']['level'] = 'script';
check( ! Document_Validator::is_valid( $invalid ), 'invalid heading level should fail' );

$invalid = $valid;
$invalid['elements'][0]['settings']['text'] = str_repeat( 'x', 10001 );
check( ! Document_Validator::is_valid( $invalid ), 'oversized text should fail' );

$invalid = $valid;
$invalid['elements'] = array_fill( 0, 501, $valid['elements'][0] );
check( ! Document_Validator::is_valid( $invalid ), 'too many modules should fail' );

echo "Document validator tests passed.\n";
