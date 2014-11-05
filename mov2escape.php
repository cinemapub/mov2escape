<?php
include_once("pfor_tools.inc");
$prog=basename($argv[0],".php");
$version="0.1";
$attrib="Peter Forret <p.forret@brighfish.be>";
$moddate=date("Y-m-d",filemtime($argv[0]));

if(!isset($argv[1])){
	// usage
	trace("$prog $version -- $attrib","INFO");
	trace("Update: $moddate","INFO");
	trace("Usage : $prog [INPUT FILE] [OUTPUT FOLDER]","INFO");
	trace("        INPUT FILE   : MOV/MP4/M4V file","INFO");
	trace("        OUTPUT FOLDER: where the 3 output files (L/C/R) should be created","INFO");
}


?>