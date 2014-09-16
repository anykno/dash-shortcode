<?php 

$dush = $_GET['dush'];

if ( ini_get( 'zlib.output_compression' ) )
  ini_set( 'zlib.output_compression', 'Off' );

$file_extension = strtolower( substr( strrchr( $dush, "." ), 1 ) );

switch( $file_extension ) {
  case "pdf": $ctype="application/pdf"; break;
  case "mp4": $ctype="application/octet-stream"; break;
  case "mp3": $ctype="application/octet-stream"; break;
  case "gif": $ctype="image/gif"; break;
  case "png": $ctype="image/png"; break;
  case "jpeg":
  case "jpg": $ctype="image/jpg"; break;
  default: $ctype="application/force-download";
}

header( "Pragma: public" );
header( "Expires: 0" );
header( "Cache-Control: must-revalidate, post-check=0, pre-check=0" );
header( "Cache-Control: private", false ); 
header( "Content-Type: $ctype" );

header( "Content-Disposition: attachment; filename=\"" . basename($dush) . "\";" );
header( "Content-Transfer-Encoding: binary" );
header( "Content-Length: ". filesize($dush) );
readfile( "$dush" );
	exit();

?>