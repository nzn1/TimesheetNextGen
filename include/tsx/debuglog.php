<?php
class LogFile{
	public static function write($the_string ) {
		if( $fh = @fopen("/tmp/php-debug.log", "a+" ) ) {
			fputs( $fh, $the_string, strlen($the_string) );
			fclose( $fh );
			return( true );
		} else {
			return( false );
		}
	}
	public static function print_r($the_array ) {
		if( $fh = @fopen("/tmp/php-debug.log", "a+" ) ) {
			foreach ($the_array as $key => $value) {
			fputs( $fh, "Key: $key; Value: $value\n" );
			}
			fclose( $fh );
			return( true );
		} else {
			return( false );
		}
	}
}

// vim:ai:ts=4:sw=4
?>
