<?php
include_once("pfor_tools.inc");
$prog=basename($argv[0],".php");
$version="0.1";
$attrib="Peter Forret <p.forret@brightfish.be>";
$moddate=date("Y-m-d",filemtime($argv[0]));

// only works for Windows now
$ffmpeg='C:\tools\ffmpeg64\ffmpeg.exe';
$magick='C:\Program Files\graphicsmagick-1.3.20-q16\gm.exe';
$identify='C:\tools\identify.exe';
$testsec=10;

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

$prefix=substr(pathinfo($input,PATHINFO_FILENAME),0,12);		// make +- sure name refers to input file - easier to distinguish
$prefix=preg_replace("([^\w])","",$prefix);
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

$ff_src=ff_details($input);

/*
    [DURATION] => 00:01:06.92, start: 0.000000, bitrate: 97228 kb/s
    [DUR_FRAMES] => 1606
    [DUR_LENGTH] => 00:01:06.92
    [DUR_SECS] => 66.92
    [FOUND_AUDIO] => 0
    [FOUND_VIDEO] => 1
    [PROGRAM] => C:\tools\ffmpeg64\ffmpeg.exe
    [SIZE_DIMENSIONS] => 6416x1080
    [SIZE_HEIGHT] => 1080
    [SIZE_WIDTH] => 6416
    [VIDEO] => h264 (high 4:4:4 predictive) (avc1 / 0x31637661), yuv444p, 6416x1080, 97227 kb/s, 24 fps, 24 tbr, 12288 tbn, 48 tbc (default)
    [VID_CODEC] =>  h264 (High 4:4:4 Predictive) (avc1 / 0x31637661)
    [VID_FPS] => 24
    [VID_KBPS] => 97227
*/
$orig_w=$ff_src["SIZE_WIDTH"];
$orig_h=$ff_src["SIZE_HEIGHT"];
trace("ESCAPE HEIGTH: $escp_h");
trace("LEFT   SCREEN : $cutl_w * $escp_h");
trace("CENTER SCREEN : $cutc_w * $escp_h");
trace("RIGHT  SCREEN : $cutr_w * $escp_h");
trace("ESCAPE WIDTH  : $escp_w");

/// ----------------------------------
/// ------------------ RESCALE TO ESCAPE SIZE
/// ----------------------------------
// based on https://trac.ffmpeg.org/wiki/Encode/H.264
if($escp_h == $orig_h AND $escp_w == $orig_w){
	trace("ULTRASCOPE 5.95: already in right format","INFO");
	$ffparam[]="-c:v copy"; 			// almost lossless compression
	$ffparam[]="-c:a copy";
} else {
	trace("ULTRASCOPE 5.95: width $orig_w => $escp_w - crop to height $escp_h","INFO");
	$ffparam[]="-vf \"scale=$escp_w:-1,crop=$escp_w:$escp_h\""; 	// rescale to full escape width / crop center
	$ffparam[]="-c:v libx264 -preset ultrafast -crf 1"; 			// almost lossless compression
	$ffparam[]="-c:a copy";
}

$ftemp="$dtemp\\$prefix.sscope.mp4";
if(do_if_necessary($input,$ftemp)){
	run_ffmpeg($input,$ftemp,$ffparam);
}

$out_l="$dtemp/$prefix.hd_l.mp4";
$out_r="$dtemp/$prefix.hd_r.mp4";
$out_c="$dtemp/$prefix.scope_c.mp4";

/// ----------------------------------
/// ------------------ CUT IN 3 MOVIES
/// ----------------------------------

$ffcut="-an -c:v libx264 -preset ultrafast -crf 1";

if(do_if_necessary($ftemp,$out_l)){
	trace("CUT LEFT SCREEN $out_l","INFO");
	//run_ffmpeg($ftemp,$out_l,"$ffcut -vf \"crop=$cutl_w:$escp_h:0:0,scale=1920:1080\"");
	run_ffmpeg($ftemp,$out_l,"$ffcut -vf \"crop=$cutl_w:$escp_h:0:0\"");
}

if(do_if_necessary($ftemp,$out_r)){
	trace("CUT RIGHT SCREEN $out_r","INFO");
//	run_ffmpeg($ftemp,$out_r,"$ffcut -vf \"crop=$cutl_w:$escp_h:in_w-$cutr_w:0,scale=1920:1080\"");
	run_ffmpeg($ftemp,$out_r,"$ffcut -vf \"crop=$cutl_w:$escp_h:in_w-$cutr_w:0\"");
}

if(do_if_necessary($ftemp,$out_c)){
	trace("CUT CENTER SCREEN $out_c","INFO");
	run_ffmpeg($ftemp,$out_c,"$ffcut -vf \"crop=$cutc_w:$escp_h:$cutl_w:0,scale=2048:858\"");
}

/// ----------------------------------
/// ------------------ RENDER FRAMES
/// ----------------------------------

$d_L1="$dout\\$prefix.L";
$d_L2="$dout\\$prefix.L.dpx";
$d_R1="$dout\\$prefix.R";
$d_R2="$dout\\$prefix.R.dpx";
$d_C="$dout\\$prefix.C";

render_frames($out_l,$d_L1,"jpg");
render_frames($out_c,$d_C,"jpg");
render_frames($out_r,$d_R1,"jpg");

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
		// Input #0, dpx_pipe, from 'DD_TRAILER_SCREEN_23_98_LEFT_grd001.00200.dpx':
		//   Stream #0:0: Video: dpx, gbrp10le, 1920x1080 [SAR 1:1 DAR 16:9], 25 tbr, 25 tbn, 25 tbc
		// just the gamma still has to be added
		$ffparam="-pix_fmt gbrp10le -q:v 1";
		$ffimg="$bname.%06d.dpx";
		break;;
	default:
		$ffparam="-q:v 1";
		$ffimg="$bname.%06d.png";
	}

	$first="$folder\\".sprintf($ffimg,1);
	if(do_if_necessary($mov,$first)){
		trace("RENDER FRAMES FOR $mov [$type]","INFO");
		$t1=microtime(true);
		cmdline("\"$ffmpeg\" -i \"$mov\" $ffparam -y \"$folder\\$ffimg\" 2>> \"$flog\"");
		$t2=microtime(true);
	}
}

function run_ffmpeg($input,$output,$ffparam){
	global $ffmpeg;
	
	$binput=basename($input);
	$sinput=filesize($input);
	$minput=round($sinput/1000000,1)."MB";
	$uniq=substr(md5("$input$ffparam$output"),0,8);
	if(is_array($ffparam)){
		$ffparam=implode(" ",$ffparam);
	}
	$flog="$binput.$uniq.log";
	trace("RUN ffmpeg on $binput - $minput","INFO");
	trace("    $ffparam");
	trace("    log to $flog");
	$t1=microtime(true);
	exec("\"$ffmpeg\" -i \"$input\" $ffparam -y \"$output\" 2>> \"log\\$flog\"");
	$t2=microtime(true);
	if(contains($output,'%')){
		// render to frame files
	} else {
		// render to single file
		
	}
}

function convert_dpx($folderin,$folderout){
	$flog="log\\cnvdpx." . basename($folderin). ".log";
	global $ffmpeg;
	global $magick;
	global $identify;
	
	$dpxsettings="-colorspace RGB -endian msb -depth 10 -define dpx:film.frame_rate=24 ";

	if(!file_exists("$folderout\\.")){
		trace("Create folder [$folderout]");
		mkdir($folderout);
	}

	$filesin=listfiles($folderin);
	trace("");
		trace("CONVERT 2 DPX FOR $mov [" . basename($folderin) . "]","INFO");
	$imgno=0;
	foreach($filesin as $srcfile){
		$imgno++;
		$dstfile=basename($srcfile);
		$dstfile="$folderout/".str_replace(Array(".jpg",".tif",".dpx"),"",$dstfile).".dpx";
		if(do_if_necessary($srcfile,$dstfile)){
			// based on http://www.graphicsmagick.org/motion-picture.html
			exec("\"$magick\" convert \"$srcfile\" $dpxsettings \"$dstfile\" ");			
		}
		if($imgno==1){
			trace("Settings: $dpxsettings");			
			$infolines=cmdline("\"$identify\" -verbose \"$dstfile\" ");
			foreach($infolines as $infoline){
				$lower=strtolower($infoline);
				switch(true){
				case(contains($lower,"#qnan")):
					// nothing
					break;;
				case(contains($lower,"gamma")):
				case(contains($lower,"geometry")):
				case(contains($lower,"colorspace")):
				case(contains($lower,"filesize")):
				case(contains($lower,"frame_rate")):
					printf("   * %s \r\n",trim($infoline));
				}
			}
		}
	}

}

function ff_details($file){
	global $ffmpeg;
	
	$program=$ffmpeg;
	$output=cmdline("\"$ffmpeg\" -i \"$file\" 2>&1");
	//print_r($output);
	$result=Array();
	$result["FOUND_VIDEO"]=0;
	$result["FOUND_AUDIO"]=0;
	foreach($output as $line){
		if(contains($line,"Stream")){
			if(contains($line,"Video:")){
				$result["FOUND_VIDEO"]=1;
				$data=quickmatch($line,"|Video:(.*)|");
				$result["VIDEO"]=trim(txt_removespecialchars($data));
				$result["SIZE_DIMENSIONS"]=quickmatch($data,"|(\d\d+x\d\d+)|");
				$result["VID_FPS"]=quickmatch($data,"|([\d\.]+) fps|");
				$result["VID_KBPS"]=quickmatch($data,"|(\d+) kb/s|");
				list($codec,$rest)=explode(",",$data);
				$result["VID_CODEC"]=$codec;
			}
			if(contains($line,"Audio:")){
				$result["FOUND_AUDIO"]=1;
				$data=quickmatch($line,"|Audio:(.*)|");
				$result["AUDIO"]=trim(txt_removespecialchars($data));
				$result["CHANNELS"]=quickmatch($data,"|(\d) channels|");
				$result["AUD_KBPS"]=quickmatch($data,"|(\d+) kb/s|");
				$result["HZ"]=quickmatch($data,"|(\d\d\d\d\d)|");
			}

		}
		if(contains($line,"Duration:")){
			$data=quickmatch($line,"|Duration:(.*)|");
			$result["DURATION"]=trim(txt_removespecialchars($data));
			$result["DUR_LENGTH"]=quickmatch($data,"|(\d\d:\d\d:\d\d\.\d\d)|");
		}	
	}
	$result["PROGRAM"]=$program;
	if($result["DUR_LENGTH"] AND $result["VID_FPS"]){
		$lenparts=explode(":",$result["DUR_LENGTH"]);
		$secs=((int)$lenparts[0]*3600) + ((int)$lenparts[1]*60) + (double)$lenparts[2];
		$frames=round($secs*(double)$result["VID_ FPS"]);
		$result["DUR_SECS"]=number_format($secs,2);
		$result["DUR_FRAMES"]=$frames;
	}
	if($result["SIZE_DIMENSIONS"]){
		list($result["SIZE_WIDTH"],$result["SIZE_HEIGHT"])=explode("x",$result["SIZE_DIMENSIONS"],2);
	}
	ksort($result);
	return $result;
}

function quickmatch($text,$search){

	$nb=preg_match($search , $text, $matches);
	if($nb){
		$value=$matches[1];
		return $value;
	}
	return false;
}


function txt_removespecialchars($input){
	$return=utf8_decode($input);
	$return=strtolower($input);
	$return=str_replace(	Array("â","à","ä","ã"),	"a",$return);
	$return=str_replace(	Array("ç"),				"c",$return);
	$return=str_replace(	Array("é","è","ë","ê"),	"e",$return);
	$return=str_replace(	Array("î","ï","í"),		"i",$return);
	$return=str_replace(	Array("ñ"),				"n",$return);
	$return=str_replace(	Array("ô","ö","ò","ó"),	"o",$return);
	$return=str_replace(	Array("œ"),				"oe",$return);
	$return=str_replace(	Array("ü"),				"u",$return);

	//trace("txt_removespecialchars: output = " . txt_shortentext($return,100));
	return $return;
}

function txt_makecanonical($input,$aremove=false,$maxlen=255){

	if(!$aremove){
		$aremove=Array("the","to","for","and","in");
	}
	$areplace=Array();
	foreach($aremove as $keyword){
		$areplace[]=txt_removespecialchars(" $keyword ");
	}

	$return=txt_removespecialchars($input);
	$return=str_replace($areplace," "," " . $return . " ");
	$return=str_replace(Array("-","_","'","/",".",",",";",":","!","?","(",")","°","~"),' ',$return);
	$return2=str_replace(' ','',$return);
	if(strlen($return2)>$maxlen){
		$wordend=strpos($return,' ',$maxlen);
		$return3=substr($return,0,$wordend);
		if(!$return3){
			$return3=substr($return,0,$maxlen);
		}

		$return2=str_replace(' ','',$return3);
	}
	trace("txt_makecanonical: output = [$return2] ( " . strlen($return2) . " chars)");
	return($return2);
}

function txt_shortentext($text,$max,$etc="…"){
	if(strlen($text)<=$max) return $text;
	$text=substr($text,0,$max-strlen($etc)).$etc;
	trace("txt_shortentext: result= [$text]");
	return $text;
}

function txt_removehtml($html,$br="\n"){
	$result=str_replace("/>","/> ",$html);
	$result=str_replace(Array("</div>","</p>","<br>","<br />","<br/>"),$br,$html);
	$result=preg_replace("#<[^>]*>#","",$result);
	trace("txt_removehtml: output = " . txt_shortentext($result,100));
	return $result;
}

?>