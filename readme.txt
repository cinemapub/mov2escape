# BACKGROUND

## Barco Escape

Barco has developed a way of displaying content on 3 screens in a cinema. 
The main screen is filled with a scope (2.39) image, and two screens, left and right of the main screen,
have an HD (1.78) image. As a whole it can show one seamless 'ultra-scope' image over three screens 
(aspect ratio 5.95), show 3 different image on each screen, or some mic of these methods.

## Conversion

In order to prepare content for this system, I have developed a pipeline that takes a video, 
stretched it to full width, crops to max height, splits in 3 differents streams for L/C/R,
and finally generates frames to be used in the creation of a DCP (center screen) or DPX sequences (L/R)

## Required toolset

* ffmpeg - the latest version (for this development: ffmpeg version N-67362-g0971154 - Nov  3 2014 22:12:21)

* imagemagick - the latest version  (for this development: ImageMagick 6.8.9-10 Q16 x64 2014-11-02 )

* PHP -we're using PHP in CLI (command line interface) mode. (for this development: PHP 5.3.13)

* current development was done on Windows. To run on Linux/MacosX some minor modifications would be necessary
 (path names mainly) 

# Ultra-scope 5.95:1 image

The full image will consist of a center screen in scope: 2048 x 858 pixels. 
The side screens will each be 1920 x 1080 pixels.
Input images will be stretched to 6418 x 1080 pixels.
Left and right 1920 x 1080 will be cut off. 
The center image that remains is 2576 x 1080, which will be rescaled to 2048 x 858px.
