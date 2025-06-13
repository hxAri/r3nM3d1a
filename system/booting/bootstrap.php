<?php

call_user_func( function(): Void {
	
	$BASE_PATH = $_SERVER['DOCUMENT_ROOT'] ?? "";
	$BASE_PATH = $_SERVER['DOCUMENT_ROOT'] !== "" ? $BASE_PATH : __DIR__;
	
	$SUBSTR_PATH = "/system/booting";
	
	if( strpos( $BASE_PATH = str_replace( "\\", "/", $BASE_PATH ), $SUBSTR_PATH ) === False ) {
		$SUBSTR_PATH = strpos( $BASE_PATH, "/public" ) ? "/public" : "";
	}
	define( "BASE_PATH", str_replace( [ "/", "\\" ], DIRECTORY_SEPARATOR, $SUBSTR_PATH === "" ? $BASE_PATH : substr( $BASE_PATH, 0, - strlen( $SUBSTR_PATH ) ) ) );
});
