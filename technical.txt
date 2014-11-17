Front Screen:
·         2K: 2048 x 858
·         24 frames per second (cannot be higher than 24fps)
·         Color Space: Normal DC
·         Format: DCP
 
Side Screens
·         HD: 1920 x 1080
·         24 frames per second
·         Format: DPX 10 bit, RGB cineon big endian
·         Color space: P3 transform LUT, which is the Digital Cinema color space to make sure they are the closest possible match to the existing center screen
·         (HD: 1920x1080) - 1.78:1 extraction with no feathering and render out as 4:4:4 1920x1080 10Bit Rec 709 (full) 2.2 gamma files
 
ffmpeg pix_fmt formats for 10 bit 4:4:4

IO... yuv444p10be            3            30


http://www.graphicsmagick.org/motion-picture.html
-colorspace {CineonLog|RGB|Gray|Rec601Luma|Rec709Luma|Rec601YCbCr|Rec709YCbCr}
  Specifies the colorspace to be used when saving the DPX file. CineonLog selects log encoding according to Kodak Cineon specifications. RGB selects linear RGB encoding. Gray selects linear gray encoding similar to RGB, but with a single channel. Rec601Luma requests that RGB is converted to a gray image using Rec601 Luma. Rec709Luma requests that RGB is converted to a gray image using Rec709Luma. Rec601YCbCr requests that the image is saved as YCbCr according to Rec601 (SDTV) specifications. Rec709CbCr requests that the image is saved as YCbCr according to Rec709 (HDTV) specifications.
-endian {lsb|msb}
  Specifies the endian order to use when writing the DPX file. GraphicsMagick writes big-endian DPX files by default since they are the most portable. Other implementations may use the native order of the host CPU (e.g. little-endian when using an Intel 'x86 CPU).
-depth <value>
  Specifies the number of bits to preserve in a color sample. By default the output file is written with the same number of bits as the input file. For example, if the input file is 16 bits, it may be reduced to 10 bits via '-depth 10'.
-define dpx:bits-per-sample=<value>
  If the dpx:bits-per-sample key is defined, GraphicsMagick will write DPX images with the specified bits per sample, overriding any existing depth value. If this option is not specified, then the value is based on the existing image depth value from the original image file. The DPX standard supports bits per sample values of 1, 8, 10, 12, and 16. Many DPX readers demand a sample size of 10 bits with type A padding (see below).
-define dpx:colorspace={rgb|cineonlog}
  Use the dpx:colorspace option when reading a DPX file to specify the colorspace the DPX file uses. This overrides the colorspace type implied by the DPX header (if any). Currently files with the transfer characteristic Printing Density are assumed to be log encoded density while files marked as Linear are assumed to be linear. Hint: use -define dpx:colorspace=rgb in order to avoid the log to linear transformation for DPX files which use Printing Density.
-define dpx:packing-method={packed|a|b|lsbpad|msbpad}
  DPX samples may be output within 32-bit words. They may be tightly packed end-to-end within the words ("packed"), padded with null bits to the right of the sample ("a" or "lsbpad"), or padded with null bits to the left of the sample ("b" or "msbpad"). This option only has an effect for sample sizes of 10 or 12 bits. If samples are not packed, the DPX standard recommends type A padding. Many DPX readers demand a sample size of 10 bits with type A padding.
-define dpx:pixel-endian={lsb|msb}
  DPX pixels should use the endian order that the DPX header specifies. Sometimes there is a mis-match and the pixels use a different endian order than the file header specifies. For example, the file header may specify little endian, but the pixels are in big-endian order. To work around that use -define dpx-pixel-endian=msb when reading the file. Likewise, this option may be used to intentionally write the pixels using a different order than the header.
-define dpx:swap-samples={true|false}
  GraphicsMagick strives to adhere to the DPX standard but certain aspects of the standard can be quite confusing. As a result, some 10-bit DPX files have Red and Blue interchanged, or Cb and Cr interchanged due to an different interpretation of the standard, or getting the wires crossed. The swap-samples option may be supplied when reading or writing in order to read or write using the necessary sample order.
-interlace plane
  By default, samples are stored contiguously in a single element when possible. Specifying '-interlace plane' causes each sample type (e.g. 'red') to be stored in its own image element. Planar storage is fully supported for grayscale (with alpha) and RGB. For YCbCr, chroma must be 4:2:2 subsampled in order to use planar storage. While planar storage offers a number of benefits, it seems that very few DPX-supporting applications support it.
-sampling-factor 4:2:2
  Select 4:2:2subsampling when saving an image in YCbCr format. Subsampling is handled via a general-purpose image resize algorithm (lanczos) rather than a dedicated filter so subsampling is slow (but good).
-set reference-white <value>
  Set the 90% white card level (default 685) for Cineon Log.
-set reference-black <value>
  Set the 1% black card level (default 95) for Cineon Log.
-set display-gamma <value>
  Set the display gamma (default 1.7) for Cineon Log.
-set film-gamma <value>
  Set the film gamma (default 0.6) for Cineon Log.
-set soft-clip-offset <value>
  Set the soft clip offset (default 0) when converting to computer RGB from Cineon Log.
-------------
  
  Image: ruin_left_barcotiming_grd02.01961.dpx
  Format: DPX (SMPTE 268M-2003 (DPX 2.0))
  Class: DirectClass
  Geometry: 1920x1080+0+0
  Units: Undefined
  Type: TrueColor
  Base type: TrueColor
  Endianess: MSB
  Colorspace: Log
  Depth: 10-bit
  Channel depth:
    red: 10-bit
    green: 10-bit
    blue: 10-bit
  Channel statistics:
    Red:
      min: 107 (0.104601)
      max: 740 (0.722393)
      mean: 254.897 (0.248926)
      standard deviation: 18.3808 (0.0179503)
      kurtosis: 28.8088
      skewness: 2.443
    Green:
      min: 130 (0.127077)
      max: 731 (0.713588)
      mean: 260.797 (0.254689)
      standard deviation: 17.8884 (0.0174694)
      kurtosis: 21.639
      skewness: 1.6458
    Blue:
      min: 0 (0)
      max: 539 (0.525902)
      mean: 102.854 (0.100445)
      standard deviation: 20.4913 (0.0200114)
      kurtosis: 11.9106
      skewness: 0.626629
  Image statistics:
    Overall:
      min: 0 (0)
      max: 740 (0.722393)
      mean: 206.183 (0.201354)
      standard deviation: 18.9538 (0.0185099)
      kurtosis: 455.512
      skewness: -40.2932
  Rendering intent: Perceptual
  Gamma: 0.454545
  Chromaticity:
    red primary: (0.64,0.33)
    green primary: (0.3,0.6)
    blue primary: (0.15,0.06)
    white point: (0.3127,0.329)
  Background color: log(255,255,255)
  Border color: log(223,223,223)
  Matte color: log(189,189,189)
  Transparent color: log(0,0,0)
  Interlace: None
  Intensity: Undefined
  Compose: Over
  Page geometry: 1920x1080+0+0
  Dispose: Undefined
  Iterations: 0
  Compression: Undefined
  Orientation: TopLeft
  Properties:
    date:create: 2014-11-05T12:26:28+01:00
    date:modify: 2014-11-05T12:22:18+01:00
    document: /san19/temp/032014_mr_187502/FROM_EFILM_032014_LEFT_1920x1080/ruin_left_barcotiming_grd02/1920x1080
    dpx:file.creator: Shake
    dpx:file.ditto.key: 1
    dpx:file.filename: /san19/temp/032014_mr_187502/FROM_EFILM_032014_LEFT_1920x1080/ruin_left_barcotiming_grd02/1920x1080
    dpx:file.timestamp: 2014:03:20:17:48:17:PDT
    dpx:file.version: V1.0
    dpx:film.frame_position: 0
    dpx:film.held_count: 0
    dpx:film.sequence_extent: 0
    dpx:image.element[0].transfer-characteristic: PrintingDensity
    dpx:image.element[1].transfer-characteristic: Reserved
    dpx:image.element[2].transfer-characteristic: Reserved
    dpx:image.element[3].transfer-characteristic: Reserved
    dpx:image.element[4].transfer-characteristic: Reserved
    dpx:image.element[5].transfer-characteristic: Reserved
    dpx:image.element[6].transfer-characteristic: Reserved
    dpx:image.element[7].transfer-characteristic: Reserved
    dpx:image.orientation: 0
    dpx:orientation.aspect_ratio: 1000x1000
    dpx:orientation.border: 0x0+0+0
    dpx:orientation.x_center: 1.34525e-042
    dpx:orientation.x_offset: 0
    dpx:orientation.x_size: 1920
    dpx:orientation.y_center: 7.56701e-043
    dpx:orientation.y_offset: 0
    dpx:orientation.y_size: 1080
    dpx:television.frame_rate: 23.976
    dpx:television.time.code: 00:00:00:00
    dpx:television.user.bits: 00:00:00:00
    signature: 84eec396f1d5c9e815ef3c5f8a3fef9599383b67b99828ea398126d244f7e573
    software: Shake
  Profiles:
    Profile-dpx: 6112 bytes
  Artifacts:
    filename: ruin_left_barcotiming_grd02.01961.dpx
    verbose: true
  Tainted: False
  Filesize: 8.303MB
  Number pixels: 2.074M
  Pixels per second: 16.59MB
  User time: 0.250u
  Elapsed time: 0:01.125
  Version: ImageMagick 6.8.8-0 Q16 x64 2013-12-21 http://www.imagemagick.org


