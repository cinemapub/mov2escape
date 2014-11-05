set SRCVID=Library\gopro4-4k.mp4
set SRCAUD=Library\blackjoy.mp3

:: cinema scherm is 2.39 - de 2 zij-schermen zijn 1.78
:: Dus we moeten een beeld maken van 6420 x 1080 pixels (aspect ratio 5.95)
:: links: 1920 x 1080
:: midden: 2580 x 1080 en rescale naar 2048 x 848 (DCP)
:: rechts: 1920 x 1080

:: explode to 6K, crop to superscope.

 :: Duration: 00:02:18.52, start: 0.000000, bitrate: 33457 kb/s
 ::   Stream #0:0(eng): Video: h264 (High) (avc1 / 0x31637661), yuv420p, 2560x1440 [SAR 1:1 DAR 16:9], 33145 kb/s, 23.98 fps, 23.98 tbr, 23976 tbn, 47.95 tbc

set HEIGHT=688

set /A WIDTH=(%HEIGHT% * 6420 / 1080)/4 * 4

::set TEST=-t 40
set INTERMED=_full%HEIGHT%.mkv
if not exist %INTERMED% c:\tools\ffmpeg64\ffmpeg -ss 10 -i %SRCVID% -i %SRCAUD% -r 24 -vf "scale=%WIDTH%:-1,crop=%WIDTH%:%HEIGHT%" -c:v libx264 -preset ultrafast -qp 0 -b:a 256K %TEST% -y %INTERMED%

set /a HDW=(%HEIGHT% * 4 / 9) * 4

set /a DCPW=%WIDTH% - ( 2 * %HDW )
set OUTL=_output%HEIGHT%_L.mp4
set OUTR=_output%HEIGHT%_R.mp4
set OUTM=_output%HEIGHT%_M.mp4

set TXTFMT=fontfile=OpenSans-Semibold.ttf:fontsize=50:fontcolor=white:x=(main_w/2-text_w/2):y=main_h-60
if not exist %OUTL% c:\tools\ffmpeg64\ffmpeg -i %INTERMED% -acodec copy -b:v 100M -vf "crop=%HDW%:%HEIGHT%:0:0,drawtext=%TXTFMT%:text='GoPro_4K'" -y %OUTL%
if not exist %OUTM% c:\tools\ffmpeg64\ffmpeg -i %INTERMED% -acodec copy -b:v 100M -vf "crop=%DCPW%:%HEIGHT%:%HDW%:0,scale=2048:858,drawtext=%TXTFMT%:text='BARCO-ESCAPE'" -y %OUTM%
if not exist %OUTR% c:\tools\ffmpeg64\ffmpeg -i %INTERMED% -acodec copy -b:v 100M -vf "crop=%HDW%:%HEIGHT%:in_w-%HDW%:0,drawtext=%TXTFMT%:text='Brightfish_Test'" -y %OUTR%