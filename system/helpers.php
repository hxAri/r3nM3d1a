<?php

/**
 * Return generated random string.
 * 
 * @param int $max
 * 
 * @return string
 */
function bytes( Int $max ): String {

	// Generate random bytes.
	$byte32x = random_bytes( 32 );
	$byte64x = random_bytes( 64 );
	
	// Encrypt byte string.
	$encrypt = crypt( $byte32x, bin2hex( $byte64x ) );
	$encrypt = str_replace( "\x2f", "\x2e", $encrypt );
	
	if( strlen( $encrypt ) > $max ) {
		return substr( $encrypt, 0, $max );
	}
	return $encrypt;
}

/**
 * Parse exception class into string.
 *
 * @param Throwable $e
 *
 * @return never
 */
function e( Throwable $e ): Never {
	$format = static function( Throwable $thrown ): Mixed {
		$values = [
			"class" => $thrown::class,
			"message" => $thrown->getMessage(),
			"file" => $thrown->getFile(),
			"line" => $thrown->getLine(),
			"code" => $thrown->getCode(),
			"trace" => json_encode( $thrown->getTrace(), JSON_PRETTY_PRINT )
		];
		return join(
			array: [
				" {$values['class']}: {$values['message']}",
				" {$values['class']}: File: {$values['file']}",
				" {$values['class']}: Line: {$values['line']}",
				" {$values['class']}: Code: {$values['code']}",
				" {$values['class']}: {$values['trace']}",
				" "
			],
			separator: "\x0a"
		);
	};
	$stack = [
		$format( $error = $e )
	];
	while( $error = $error->getPrevious() ) {
		$stack[] = $format( $error );
	}
	echo join( "", array_reverse( $stack ) );
	exit( 1 );
}

/**
 * Scaning directory.
 * 
 * @param string $dir
 * 
 * @return array|false
 */
function scanner( String $dir ): Array|False {
	if( is_dir( $dir ) ) {
		return array_diff( scandir( $dir ), [ ".", ".." ] );
	}
	return False;
}

/**
 * Create tree structure.
 * 
 * @param array $array
 * @param string $space
 * 
 * @return string
 */
function tree( Array $array = [], String $space = "" ): String {
	$it = 0;
	$re = "";
	if( count( $array ) !== 0 ) {
		foreach( $array As $key => $val ) {
			$it++;
			if( count( $array ) === $it ) {
				$lK = TREE_LAST;
				$lA = TREE_SPACE;
			}
			else {
				$lK = TREE_MIDDLE;
				$lA = TREE_STRAIGHT;
			}
			$re .= $space;
			$re .= $lK;
			if( is_int( $key ) ) {
				if( is_array( $val ) ) {
					$re .= "\e[1;33m[]\e[0m\n";
					$re .= tree( $val, $space . $lA );
				}
				else {
					$re .= "\e[1;37m$val\e[0m\n";
				}
			}
			else if( is_array( $val ) ) {
				$re .= "\e[1;35m$key\e[0m\n";
				$re .= tree( $val, $space . $lA );
			}
			else {
				$re .= "\e[1;30m$key\e[0m\n";
				$re .= $space;
				$re .= $lA;
				$re .= TREE_LAST;
				$re .= "\e[1;37m$val\e[0m\n";
			}
		}
	}
	else {
		$re .= $space;
		$re .= TREE_LAST;
		$re .= "\e[1;34m[]\e[0m\n";
	}
	return $re;
}
