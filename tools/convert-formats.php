<?php
include dirname( dirname(__FILE__) ) . '/lib/class-ginger-mo-translation-file.php';

$opts = getopt( '', array( 'source:', 'destination:', 'overwrite::' ) );

if ( ! isset( $opts['source'] ) || ! isset( $opts['destination'] ) ) {
	echo "Usage: php $argv[0] --source file.mo --destination file.mo.php\n";
	echo "--source      Specifies the source file.\n";
	echo "--destination Specifies the output file.\n";
	echo "--overwrite   Include to overwrite any existing destination file.\n\n";

	echo "Any supported file format may be provided for both the source and destination.\n";
	echo "For example, you can convert from a .mo to .php, or .php to a .json file with ease.\n\n";
	exit(1);
}
if ( ! isset( $opts['overwrite'] ) && file_exists( $opts['destination'] ) ) {
	echo "Destination file exists, Specify --overwrite to overwrite. \n";
	exit(1);
}

echo "Converting " . basename( $opts['source'] ) . " to " . basename( $opts['destination'] ) . ".. ";

$source = Ginger_MO_Translation_File::create( $opts['source'], 'read' );
$destination = Ginger_MO_Translation_File::create( $opts['destination'], 'write' );

if ( ! $source || $source->error() ) {
	echo "Error: Source is unreadable\n";
	exit(1);
} elseif ( ! $destination || $destination->error() ) {
	echo "Error: Destination is unwritable\n";
	exit(1);
}

if ( ! $source->export( $destination ) ) {
	echo "Error Converting file: " . $source->error() . "\n";
	exit(1);
}
echo "DONE.\n";
