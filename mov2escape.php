<?php
include_once("pfor_tools.inc");
$prog=basename($argv[0],".php");
$version="0.1";
$attrib="Peter Forret <p.forret@brightfish.be>";
$moddate=date("Y-m-d",filemtime($argv[0]));

// only works for Windows now
$ffmpeg='c:\tools\ffmpeg64\ffmpeg.exe';
$magick='c:\program files\graphicsmagick-1.3.20-q16\gm.exe';
$testsec=20;

$dtemp="temp";
$dout="output";

if(!isset($argv[1])){
	// usage
	trace("$prog $version -- $attrib","INFO");
	trace("Update: $moddate","INFO");
	trace("Usage : $prog [-v] [-t] --input [INPUT FILE]","INFO");
	trace("        INPUT FILE   : MOV/MP4/M4V file","INFO");
	trace("        -v: verbose","INFO");
	trace("        -t: test (only $testsec sec of video)","INFO");
	exit(0);
}

$opts=getopt("tvV",Array("input:","height:"));
//print_r($opts);
// ORIG CONTENT = ORIG prefix
// COMPLETE ESCAPE 5.95:1 = ESCP prefix
// ESCAPE LEFT  = CUTL
// ESCAPE RIGHT = CUTR
// ESCAPE CENTER = CUTC

if(isset($opts["v"])){
	$debug=true; // more debugging info
}

if(isset($opts["V"])){
	trace("$prog $version -- $attrib","INFO");
}

$ffparam=Array();
$ffparam[]="-r 24";
$maxlen="";

$arc=2048/858;
$arl=1920/1080;
$arr=1920/1080;

$ar=$arc+$arl+$arr;

$escp_h=1080;

$cutl_w=round($escp_h * $arl/4)*4;
$cutr_w=round($escp_h * $arr/4)*4;
$cutc_w=round($escp_h * $arc/4)*4;

$escp_w=$cutc_w+$cutl_w+$cutr_w;

if(!isset($opts["input"])) {
	trace("NO INPUT FILE GIVEN","INFO");
	exit(0);
}
$input=$opts["input"];

$prefix=substr(pathinfo($input,PATHINFO_FILENAME),0,8);		// make +- sure name refers to input file - easier to distinguish
$prefix.="." . substr(md5($input),0,4); 					// make +- sure it's unique based on input file

if(isset($opts["t"])){
	trace("test mode - only $testsec seconds","INFO");
	$ffparam[]="-ss 30 -t $testsec";  // 1 sec for testing
	$prefix="test.${testsec}s.$prefix";
}
$prefix.=".$escp_h";

if(!file_exists($input)){
	trace("input file [$input] not found","INFO");
	exit(1);
}

trace("ESCAPE HEIGTH: $escp_h");
trace("LEFT   SCREEN : $cutl_w * $escp_h");
trace("CENTER SCREEN : $cutc_w * $escp_h");
trace("RIGHT  SCREEN : $cutr_w * $escp_h");
trace("ESCAPE WIDTH  : $escp_w");

/// ----------------------------------
/// ------------------ RESCALE TO ESCAPE SIZE
/// ----------------------------------
$ffparam[]="-vf \"scale=$escp_w:-1,crop=$escp_w:$escp_h\""; 	// rescale to full escape width / crop center
$ffparam[]="-c:v libx264 -preset ultrafast -crf 18"; 			// lossless compression
$ffparam[]="-c:a copy";
$ffparams=implode(" ",$ffparam);


$ftemp="$dtemp\\$prefix.sscope.mp4";
$flog="log/" . basename($ftemp) . ".log";
if(do_if_necessary($input,$ftemp)){
	trace("SUPERSCOPE:    $ftemp","INFO");
	trace("FFMPEG: $ffparams");
	cmdline("\"$ffmpeg\" -i \"$input\" $ffparams -y \"$ftemp\" 2> \"$flog\"");
}

$out_l="$dtemp/$prefix.out_l.mp4";
$out_r="$dtemp/$prefix.out_r.mp4";
$out_c="$dtemp/$prefix.out_c.mp4";

/// ----------------------------------
/// ------------------ CUT IN 3 MOVIES
/// ----------------------------------

$ffcut="-acodec copy -c:v libx264 -preset ultrafast -qp 0";

if(do_if_necessary($ftemp,$out_l)){
	trace("CUT LEFT SCREEN $out_l","INFO");
	cmdline("\"$ffmpeg\" -i \"$ftemp\" $ffcut -vf \"crop=$cutl_w:$escp_h:0:0,scale=1920:1080\" -y \"$out_l\" 2> \"$flog\"");
}

if(do_if_necessary($ftemp,$out_r)){
	trace("CUT RIGHT SCREEN $out_r","INFO");
	cmdline("\"$ffmpeg\" -i \"$ftemp\" $ffcut -vf \"crop=$cutl_w:$escp_h:in_w-$cutr_w:0,scale=1920:1080\" -y \"$out_r\" 2> \"$flog\"");
}

if(do_if_necessary($ftemp,$out_c)){
	trace("CUT CENTER SCREEN $out_c","INFO");
	cmdline("\"$ffmpeg\" -i \"$ftemp\" $ffcut -vf \"crop=$cutl_w:$escp_h:$cutl_w:0,scale=2048:858\" -y \"$out_c\" 2> \"$flog\"");
}

/// ----------------------------------
/// ------------------ RENDER FRAMES
/// ----------------------------------

$d_L1="$dout\\$prefix.L";
$d_L2="$dout\\$prefix.L.dpx";
$d_R1="$dout\\$prefix.R";
$d_R2="$dout\\$prefix.R.dpx";
$d_C="$dout\\$prefix.C";

render_frames($out_l,$d_L1,"tif");
render_frames($out_c,$d_C,"tif");
render_frames($out_r,$d_R1,"tif");

convert_dpx($d_L1,$d_L2);
convert_dpx($d_R1,$d_R2);


function render_frames($mov,$folder,$type="dpx"){
	$flog="log\\frames." . basename($mov). ".log";
	global $ffmpeg;
	$bname=basename($folder);
	if(!file_exists("$folder\\.")){
		trace("Create folder [$folder]");
		mkdir($folder);
	}
	switch($type){
	case "png":
		$ffparam="-q:v 1";
		$ffimg="$bname.%06d.png";
		break;;
	case "tif":
		$ffparam="-q:v 1";
		$ffimg="$bname.%06d.tif";
		break;;
	case "jpg":
		$ffparam="-q:v 1";
		$ffimg="$bname.%06d.jpg";
		break;;
	case "dpx":
		$ffparam="-pix_fmt yuv444p10be -q:v 1";
		$ffimg="$bname.%06d.dpx";
		break;;
	default:
		$ffparam="-q:v 1";
		$ffimg="$bname.%06d.png";
	}

	$first="$folder\\".sprintf($ffimg,1);
	if(do_if_necessary($mov,$first)){
		trace("RENDER FRAMES FOR $mov [$type]","INFO");
		cmdline("\"$ffmpeg\" -i \"$mov\" $ffparam -y \"$folder\\$ffimg\" 2>> \"$flog\"");	
	}
}

function convert_dpx($folderin,$folderout){
	$flog="log\\cnvdpx." . basename($folderin). ".log";
	global $ffmpeg;
	global $magick;

	if(!file_exists("$folderout\\.")){
		trace("Create folder [$folderout]");
		mkdir($folderout);
	}

	$filesin=listfiles($folderin);
	trace("");
		trace("CONVERT 2 DPX FOR $mov [" . basename($folderin) . "]","INFO");
	foreach($filesin as $srcfile){
		$dstfile=basename($srcfile);
		$dstfile="$folderout/".str_replace(Array(".jpg",".tif",".dpx"),"",$dstfile).".dpx";
		if(do_if_necessary($srcfile,$dstfile)){
			cmdline("\"$magick\" convert \"$srcfile\" -colorspace CineonLog -endian msb -set display-gamma 2.2 -depth 10 \"$dstfile\" ");			
		}
	}

}

?>