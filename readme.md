# BACKGROUND

## Barco Escape

Barco has developed a way of displaying content on 3 screens in a cinema. 
The main screen is filled with a scope (2.39) image, and two screens, left and right of the main screen,
have an HD (1.78) image. As a whole it can show one seamless 'ultra-scope' image over three screens 
(aspect ratio 5.95), show 3 different images on each screen, or some mix of these methods.

## Conversion

In order to prepare content for this system, I have developed a pipeline that 

* takes a video, 
* stretches it to full width, 
* crops to max height, 
* splits in 3 differents streams for L/C/R, and 
* generates C JPG/TIF frames to be used in the creation of a DCP (center screen) 
* generates L/R DPX frames for 7th Sense player

## Required toolset

* ffmpeg - for this development: `version N-67362-g0971154 - Nov  3 2014 22:12:21`
* imagemagick - for this development: `version 6.8.9-10 Q16 x64 2014-11-02`
* PHP - in CLI (command line interface) mode - for this development: `version 5.3.13`
* development was done on Windows. To run on Linux/MacosX some minor modifications would be necessary
 (path names mainly) 

# ULTRA-SCOPE 5.95

## Resolution calculation

### Native resolutions:

* center screen: 2048 x 858 (2K scope)
* left/right screen: 1920 x 1080 (HD 16:9)

### Video manipulation

* Ideally, the source is a 6418 x 1080 movie (in any codec/container that ffmpeg supports: MP4, X264, QT, MXF, ...)
* If this is not the case, source will be rescaled/stretched to 6418 pixels wide, and top and bottom cropped to arrive at 1080 pixels height
* the leftmost 1920 x 1080 is split off: left source
* the rightmost 1920 x 1080 is split off: right source
* the center image that remains is 2576 x 1080, which will be rescaled to 2048 x 858.
* for each intermediary step, x264 encoder is used with minimal compression (`ultrafast -q:v 1`)

### Center screen

* frames can be rendered in JPG (small, minimal lossy compression) or TIF format (losless)
* you will need a DCP encoder program (EasyDCP, Clipster, ...) to make the scope DCP

### Left/right screen

* frames are first rendered with ffmpeg to JPG or TIF
* then a second step is necessary with imagemagick/graphicsmagick to convert to DPX with the fine details
  `DPX 10 bit, RGB cineon, big endian - 4:4:4 1920x1080 10Bit Rec 709 (full) 2.2 gamma`