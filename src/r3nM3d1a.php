<?php

namespace hxAri\r3nM3d1a;

use Iterator;
use Throwable;


final Readonly class r3nM3d1a {
	
	/**
	 * Allowed file extensions.
	 *
	 * @access Private Readonly
	 *
	 * @var array<string>
	 */
	private Array $extensions;
	
	/**
	 * Directory Pathname.
	 *
	 * @access Private Readonly
	 *
	 * @var string
	 */
	private String $pathname;
	
	/**
	 * Replacements for unallowed characters.
	 *
	 * @access Private Readonly
	 *
	 * @var array<array<string,string>>
	 */
	private Array $replacements;
	
	public function __construct( ? String $pathname ) {
		$pathname??= "{$_SERVER['HOME']}/Pictures";
		if( str_ends_with( $pathname, "/" ) ) {
			$pathname = substr( $pathname, 0, -1 );
		}
		$this->extensions = [
			"3gp",
			"heic",
			"jpg",
			"jpeg",
			"mov",
			"mp4",
			"png",
			"webp"
		];
		$this->pathname = $pathname;
		$this->replacements = [
			[ "/", "A" ],
			[ "-", "C" ]
		];
	}
	
	/**
	 * File iterator handle.
	 * 
	 * @param string $pathname
	 * @param mixed $parent
	 * 
	 * @return Iterator[array|string]
	 */
	private function iterator( String $pathname, ? String $parent = Null ): Iterator {
		$target = $parent === Null ? $pathname : "$parent/$pathname";
		if( is_dir( $target ) ) {
			$scandir = array_diff( scandir( $target ), [ ".", ".." ] );
			foreach( $scandir as $content ) {
				$filename = "$target/$content";
				if( is_dir( $filename ) ) {
					yield $content;
					yield from $this->iterator( $filename );
				}
				else {
					$pathinfo = pathinfo( $filename );
					if( isset( $pathinfo['extension'] ) ) {
						$extension = strtolower( $pathinfo['extension'] ?: "" );
						if( array_search( $extension, $this->extensions ) !== False ) {
							$filestat = stat( $filename );
							$filectime = $filestat['ctime'];
							$filemtime = $filestat['mtime'];
							$timestamp = $filectime <= $filemtime ? $filectime : $filemtime;
							$checksum = [
								"md5" => md5_file( $filename ),
								"sha" => sha1_file( $filename )
							];
							$filenamed = crypt( ...array_values( $checksum ) );
							foreach( $this->replacements As $replacement ) {
								$filenamed = str_replace( ...[ ...$replacement, $filenamed ] );
							}
							$renamed = "$target/$timestamp;$filenamed.$extension";
							if( $renamed !== $filename ) {
								$rename = Null;
								if( file_exists( $renamed ) ) {
									$renamed = "$target/$timestamp;$filenamed-duplicate.$extension";
									$iterator = 1;
									while( file_exists( $renamed ) ) {
										$renamed = "$target/$timestamp;$filenamed-duplicate-$iterator.$extension";
										$iterator++;
									}
									$rename = rename( $filename, $renamed );
								}
								else {
									$rename = rename( $filename, $renamed );
								}
								yield [
									"rename" => $rename === True ? "True" : ( $rename === Null ? "None" : "False" ),
									"sign" => $checksum,
									"from" => "{$pathname}/{$content}",
									"to" => $renamed
								];
							}
							else {
								yield $content;
							}
							continue;
						}
					}
				}
			}
		}
		else {
			yield $target;
		}
	}
	
	/** Starting replace all file contents in current directory */
	public function replace(): Void {
		$indent = "\x20\x20\x20\x20\x20";
		echo "\x20\x5b";
		echo PHP_EOL;
		foreach( $this->iterator( $this->pathname ) as $value ) {
			if( is_array( $value ) ) {
				echo $indent;
				echo str_replace( "\x0a", "\x0a{$indent}", json_encode( $value, JSON_PRETTY_PRINT ) );
				echo "\x2c";
				echo PHP_EOL;
				continue;
			}
			echo $indent;
			echo $value;
			echo "\x2c";
			echo PHP_EOL;
		}
		echo "\x20\x5d";
		echo PHP_EOL;
	}
	
}


function main(): Void {
	if( defined( "BASE_PATH" )  == false ) {
		include "vendor/autoload.php";
	}
	$fopen = fopen( BASE_PATH . "/banner.hx", "r" );
	$fread = fread( $fopen, 3168 );
	fclose( $fopen );
	echo hex2bin( $fread );
	if( count( $_SERVER['argv'] ) <= 1 ) {
		echo " Usage: src/Main.php [PATHNAME]";
		echo "\x0a";
		exit( 1 );
	}
	try {
		$r3nM3d1a = new r3nM3d1a( $_SERVER['argv'][1] );
		$r3nM3d1a->replace();
	}
	catch( Throwable $e ) {
		e( $e );
	}
}

main();
