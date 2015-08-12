<?php
/** Image Functions.

See: {@link http://www.php.net/manual/en/book.image.php}
@package gd
*/

# Required for E_WARNING:
/*. require_module 'standard'; .*/


# FIXME: all these values are dummy:
define('GD_VERSION', "?");
define('GD_MAJOR_VERSION', 2);
define('GD_MINOR_VERSION', 3);
define('GD_RELEASE_VERSION', 4);
define('GD_EXTRA_VERSION', "?");
define("GD_BUNDLED", 6);
define("IMG_GIF", 7);
define("IMG_JPG", 8);
define("IMG_JPEG", 9);
define("IMG_PNG", 10);
define("IMG_WBMP", 11);
define("IMG_XPM", 12);
define("IMG_COLOR_TILED", 13);
define("IMG_COLOR_STYLED", 14);
define("IMG_COLOR_BRUSHED", 15);
define("IMG_COLOR_STYLEDBRUSHED", 16);
define("IMG_COLOR_TRANSPARENT", 17);
define("IMG_ARC_ROUNDED", 18);
define("IMG_ARC_PIE", 19);
define("IMG_ARC_CHORD", 20);
define("IMG_ARC_NOFILL", 21);
define("IMG_ARC_EDGED", 22);
define("IMG_GD2_RAW", 23);
define("IMG_GD2_COMPRESSED", 24);
define("IMG_EFFECT_REPLACE", 25);
define("IMG_EFFECT_ALPHABLEND", 26);
define("IMG_EFFECT_NORMAL", 27);
define("IMG_EFFECT_OVERLAY", 28);
define("IMG_FILTER_NEGATE", 29);
define("IMG_FILTER_GRAYSCALE", 30);
define("IMG_FILTER_BRIGHTNESS", 31);
define("IMG_FILTER_CONTRAST", 32);
define("IMG_FILTER_COLORIZE", 33);
define("IMG_FILTER_EDGEDETECT", 34);
define("IMG_FILTER_GAUSSIAN_BLUR", 35);
define("IMG_FILTER_SELECTIVE_BLUR", 36);
define("IMG_FILTER_EMBOSS", 37);
define("IMG_FILTER_MEAN_REMOVAL", 38);
define("IMG_FILTER_SMOOTH", 39);
define('IMAGETYPE_UNKNOWN', 0);
define('IMAGETYPE_GIF', 1);
define('IMAGETYPE_JPEG', 2);
define('IMAGETYPE_PNG', 3);
define('IMAGETYPE_SWF', 4);
define('IMAGETYPE_PSD', 5);
define('IMAGETYPE_BMP', 6);
define('IMAGETYPE_WBMP', 15);
define('IMAGETYPE_XBM', 16);
define('IMAGETYPE_TIFF_II', 7);
define('IMAGETYPE_TIFF_MM', 8);
define('IMAGETYPE_JPEG2000', 9);
define('IMAGETYPE_IFF', 14);
define('IMAGETYPE_JB2', 12);
define('IMAGETYPE_JPC', 9);
define('IMAGETYPE_JP2', 10);
define('IMAGETYPE_JPX', 11);
define('IMAGETYPE_SWC', 13);
define('IMAGETYPE_ICO', 17);
define('IMAGETYPE_COUNT', 18);
define('PNG_NO_FILTER', 57);
define('PNG_FILTER_NONE', 58);
define('PNG_FILTER_SUB', 59);
define('PNG_FILTER_UP', 60);
define('PNG_FILTER_AVG', 61);
define('PNG_FILTER_PAETH', 62);
define('PNG_ALL_FILTERS', 63);

/*. array .*/ function gd_info(){}
/*. array[]mixed .*/ function getimagesize(/*. string .*/ $fn /*., args .*/)
/*. triggers E_WARNING .*/{}
/*. int   .*/ function imageloadfont(/*. string .*/ $filename)/*. triggers E_WARNING .*/{}
/*. bool  .*/ function imagesetstyle(/*. resource .*/ $im, /*. array .*/ $styles){}
/*. resource .*/ function imagecreatetruecolor(/*. int .*/ $x_size, /*. int .*/ $y_size){}
/*. bool  .*/ function imageistruecolor(/*. resource .*/ $im){}
/*. void .*/ function imagetruecolortopalette(/*. resource .*/ $im, /*. bool .*/ $ditherFlag, /*. int .*/ $colorsWanted){}
/*. bool  .*/ function imagecolormatch(/*. resource .*/ $im1, /*. resource .*/ $im2){}
/*. bool  .*/ function imagesetthickness(/*. resource .*/ $im, /*. int .*/ $thickness){}
/*. bool  .*/ function imagefilledellipse(/*. resource .*/ $im, /*. int .*/ $cx, /*. int .*/ $cy, /*. int .*/ $w, /*. int .*/ $h, /*. int .*/ $color){}
/*. bool  .*/ function imagefilledarc(/*. resource .*/ $im, /*. int .*/ $cx, /*. int .*/ $cy, /*. int .*/ $w, /*. int .*/ $h, /*. int .*/ $s, /*. int .*/ $e, /*. int .*/ $col, /*. int .*/ $style){}
/*. bool  .*/ function imagealphablending(/*. resource .*/ $im, /*. bool .*/ $on){}
/*. bool  .*/ function imagesavealpha(/*. resource .*/ $im, /*. bool .*/ $on){}
/*. bool  .*/ function imagelayereffect(/*. resource .*/ $im, /*. int .*/ $effect){}
/*. int   .*/ function imagecolorallocatealpha(/*. resource .*/ $im, /*. int .*/ $red, /*. int .*/ $green, /*. int .*/ $blue, /*. int .*/ $alpha){}
/*. int   .*/ function imagecolorresolvealpha(/*. resource .*/ $im, /*. int .*/ $red, /*. int .*/ $green, /*. int .*/ $blue, /*. int .*/ $alpha){}
/*. int   .*/ function imagecolorclosestalpha(/*. resource .*/ $im, /*. int .*/ $red, /*. int .*/ $green, /*. int .*/ $blue, /*. int .*/ $alpha){}
/*. int   .*/ function imagecolorexactalpha(/*. resource .*/ $im, /*. int .*/ $red, /*. int .*/ $green, /*. int .*/ $blue, /*. int .*/ $alpha){}
/*. bool  .*/ function imagecopyresampled(/*. resource .*/ $dst_im, /*. resource .*/ $src_im, /*. int .*/ $dst_x, /*. int .*/ $dst_y, /*. int .*/ $src_x, /*. int .*/ $src_y, /*. int .*/ $dst_w, /*. int .*/ $dst_h, /*. int .*/ $src_w, /*. int .*/ $src_h){}
/*. resource .*/ function imagerotate(/*. resource .*/ $src_im, /*. float .*/ $angle, /*. int .*/ $bgdcolor){}
/*. bool  .*/ function imagesettile(/*. resource .*/ $image, /*. resource .*/ $tile){}
/*. bool  .*/ function imagesetbrush(/*. resource .*/ $image, /*. resource .*/ $brush){}
/*. resource .*/ function imagecreate(/*. int .*/ $x_size, /*. int .*/ $y_size){}
/*. int   .*/ function imagetypes(){}
/*. resource .*/ function imagecreatefromstring(/*. string .*/ $image)/*. triggers E_WARNING .*/{}
/*. resource .*/ function imagecreatefromgif(/*. string .*/ $filename)/*. triggers E_WARNING .*/{}
/*. resource .*/ function imagecreatefromjpeg(/*. string .*/ $filename)/*. triggers E_WARNING .*/{}
/*. resource .*/ function imagecreatefrompng(/*. string .*/ $filename)/*. triggers E_WARNING .*/{}
/*. resource .*/ function imagecreatefromxbm(/*. string .*/ $filename)/*. triggers E_WARNING .*/{}
/*. resource .*/ function imagecreatefromxpm(/*. string .*/ $filename)/*. triggers E_WARNING .*/{}
/*. resource .*/ function imagecreatefromwbmp(/*. string .*/ $filename)/*. triggers E_WARNING .*/{}
/*. resource .*/ function imagecreatefromgd(/*. string .*/ $filename)/*. triggers E_WARNING .*/{}
/*. resource .*/ function imagecreatefromgd2(/*. string .*/ $filename)/*. triggers E_WARNING .*/{}
/*. resource .*/ function imagecreatefromgd2part(/*. string .*/ $filename, /*. int .*/ $srcX, /*. int .*/ $srcY, /*. int .*/ $width, /*. int .*/ $height)/*. triggers E_WARNING .*/{}
/*. int   .*/ function imagexbm(/*. resource .*/ $im, /*. string .*/ $filename, $foreground = 0)/*. triggers E_WARNING .*/{}
/*. bool  .*/ function imagegif(/*. resource .*/ $im, /*. string .*/ $filename = NULL)/*. triggers E_WARNING .*/{}
/*. bool  .*/ function imagepng(/*. resource .*/ $im, /*. string .*/ $filename = NULL, $quality = 0, $filters = 0)/*. triggers E_WARNING .*/{}
/*. bool  .*/ function imagejpeg(/*. resource .*/ $im, /*. string .*/ $filename = NULL, $quality = 0)/*. triggers E_WARNING .*/{}
/*. bool  .*/ function imagewbmp(/*. resource .*/ $im, /*. string .*/ $filename, $foreground = 0)/*. triggers E_WARNING .*/{}
/*. bool  .*/ function imagegd(/*. resource .*/ $im, /*. string .*/ $filename = NULL)/*. triggers E_WARNING .*/{}
/*. bool  .*/ function imagegd2(/*. resource .*/ $im, /*. string .*/ $filename = NULL, $chunk_size = 0, $type = IMG_GD2_RAW)/*. triggers E_WARNING .*/{}
/*. bool  .*/ function imagedestroy(/*. resource .*/ $im){}
/*. int   .*/ function imagecolorallocate(/*. resource .*/ $im, /*. int .*/ $red, /*. int .*/ $green, /*. int .*/ $blue){}
/*. void .*/ function imagepalettecopy(/*. resource .*/ $dst, /*. resource .*/ $src){}
/*. int   .*/ function imagecolorat(/*. resource .*/ $im, /*. int .*/ $x, /*. int .*/ $y){}
/*. int   .*/ function imagecolorclosest(/*. resource .*/ $im, /*. int .*/ $red, /*. int .*/ $green, /*. int .*/ $blue){}
/*. int   .*/ function imagecolorclosesthwb(/*. resource .*/ $im, /*. int .*/ $red, /*. int .*/ $green, /*. int .*/ $blue){}
/*. bool  .*/ function imagecolordeallocate(/*. resource .*/ $im, /*. int .*/ $index){}
/*. int   .*/ function imagecolorresolve(/*. resource .*/ $im, /*. int .*/ $red, /*. int .*/ $green, /*. int .*/ $blue){}
/*. int   .*/ function imagecolorexact(/*. resource .*/ $im, /*. int .*/ $red, /*. int .*/ $green, /*. int .*/ $blue){}
/*. void .*/ function imagecolorset(/*. resource .*/ $im, /*. int .*/ $col, /*. int .*/ $red, /*. int .*/ $green, /*. int .*/ $blue){}
/*. array .*/ function imagecolorsforindex(/*. resource .*/ $im, /*. int .*/ $col){}
/*. bool  .*/ function imagegammacorrect(/*. resource .*/ $im, /*. float .*/ $inputgamma, /*. float .*/ $outputgamma){}
/*. bool  .*/ function imagesetpixel(/*. resource .*/ $im, /*. int .*/ $x, /*. int .*/ $y, /*. int .*/ $col){}
/*. bool  .*/ function imageline(/*. resource .*/ $im, /*. int .*/ $x1, /*. int .*/ $y1, /*. int .*/ $x2, /*. int .*/ $y2, /*. int .*/ $col){}

/**
@deprecated Use combination of {@link imagesetstyle()} and {@link imageline()} instead. */
/*. bool  .*/ function imagedashedline(/*. resource .*/ $im, /*. int .*/ $x1, /*. int .*/ $y1, /*. int .*/ $x2, /*. int .*/ $y2, /*. int .*/ $col)
{}

/*. bool  .*/ function imagerectangle(/*. resource .*/ $im, /*. int .*/ $x1, /*. int .*/ $y1, /*. int .*/ $x2, /*. int .*/ $y2, /*. int .*/ $col){}
/*. bool  .*/ function imagefilledrectangle(/*. resource .*/ $im, /*. int .*/ $x1, /*. int .*/ $y1, /*. int .*/ $x2, /*. int .*/ $y2, /*. int .*/ $col){}
/*. bool  .*/ function imagearc(/*. resource .*/ $im, /*. int .*/ $cx, /*. int .*/ $cy, /*. int .*/ $w, /*. int .*/ $h, /*. int .*/ $s, /*. int .*/ $e, /*. int .*/ $col){}
/*. bool  .*/ function imageellipse(/*. resource .*/ $im, /*. int .*/ $cx, /*. int .*/ $cy, /*. int .*/ $w, /*. int .*/ $h, /*. int .*/ $color){}
/*. bool  .*/ function imagefilltoborder(/*. resource .*/ $im, /*. int .*/ $x, /*. int .*/ $y, /*. int .*/ $border, /*. int .*/ $col){}
/*. bool  .*/ function imagefill(/*. resource .*/ $im, /*. int .*/ $x, /*. int .*/ $y, /*. int .*/ $col){}
/*. int   .*/ function imagecolorstotal(/*. resource .*/ $im){}
/*. int   .*/ function imagecolortransparent(/*. resource .*/ $im /*., args .*/){}
/*. int   .*/ function imageinterlace(/*. resource .*/ $im /*., args .*/){}
/*. bool  .*/ function imagepolygon(/*. resource .*/ $im, /*. array .*/ $point, /*. int .*/ $num_points, /*. int .*/ $col){}
/*. bool  .*/ function imagefilledpolygon(/*. resource .*/ $im, /*. array .*/ $point, /*. int .*/ $num_points, /*. int .*/ $col){}
/*. int   .*/ function imagefontwidth(/*. int .*/ $font){}
/*. int   .*/ function imagefontheight(/*. int .*/ $font){}
/*. bool  .*/ function imagechar(/*. resource .*/ $im, /*. int .*/ $font, /*. int .*/ $x, /*. int .*/ $y, /*. string .*/ $c, /*. int .*/ $col){}
/*. bool  .*/ function imagecharup(/*. resource .*/ $im, /*. int .*/ $font, /*. int .*/ $x, /*. int .*/ $y, /*. string .*/ $c, /*. int .*/ $col){}
/*. bool  .*/ function imagestring(/*. resource .*/ $im, /*. int .*/ $font, /*. int .*/ $x, /*. int .*/ $y, /*. string .*/ $str, /*. int .*/ $col){}
/*. bool  .*/ function imagestringup(/*. resource .*/ $im, /*. int .*/ $font, /*. int .*/ $x, /*. int .*/ $y, /*. string .*/ $str, /*. int .*/ $col){}
/*. bool  .*/ function imagecopy(/*. resource .*/ $dst_im, /*. resource .*/ $src_im, /*. int .*/ $dst_x, /*. int .*/ $dst_y, /*. int .*/ $src_x, /*. int .*/ $src_y, /*. int .*/ $src_w, /*. int .*/ $src_h){}
/*. bool  .*/ function imagecopymerge(/*. resource .*/ $src_im, /*. resource .*/ $dst_im, /*. int .*/ $dst_x, /*. int .*/ $dst_y, /*. int .*/ $src_x, /*. int .*/ $src_y, /*. int .*/ $src_w, /*. int .*/ $src_h, /*. int .*/ $pct){}
/*. bool  .*/ function imagecopymergegray(/*. resource .*/ $src_im, /*. resource .*/ $dst_im, /*. int .*/ $dst_x, /*. int .*/ $dst_y, /*. int .*/ $src_x, /*. int .*/ $src_y, /*. int .*/ $src_w, /*. int .*/ $src_h, /*. int .*/ $pct){}
/*. bool  .*/ function imagecopyresized(/*. resource .*/ $dst_im, /*. resource .*/ $src_im, /*. int .*/ $dst_x, /*. int .*/ $dst_y, /*. int .*/ $src_x, /*. int .*/ $src_y, /*. int .*/ $dst_w, /*. int .*/ $dst_h, /*. int .*/ $src_w, /*. int .*/ $src_h){}
/*. int   .*/ function imagesx(/*. resource .*/ $im){}
/*. int   .*/ function imagesy(/*. resource .*/ $im){}
/*. array .*/ function imageftbbox(/*. float .*/ $size, /*. float .*/ $angle, /*. string .*/ $font_file, /*. string .*/ $text /*., args .*/)/*. triggers E_WARNING .*/{}
/*. array .*/ function imagefttext(/*. resource .*/ $im, /*. float .*/ $size, /*. float .*/ $angle, /*. int .*/ $x, /*. int .*/ $y, /*. int .*/ $col, /*. string .*/ $font_file, /*. string .*/ $text /*., args .*/)/*. triggers E_WARNING .*/{}
/*. array .*/ function imagettfbbox(/*. float .*/ $size, /*. float .*/ $angle, /*. string .*/ $font_file, /*. string .*/ $text)/*. triggers E_WARNING .*/{}
/*. array .*/ function imagettftext(/*. resource .*/ $im, /*. float .*/ $size, /*. float .*/ $angle, /*. int .*/ $x, /*. int .*/ $y, /*. int .*/ $col, /*. string .*/ $font_file, /*. string .*/ $text)/*. triggers E_WARNING .*/{}
/*. resource .*/ function imagepsloadfont(/*. string .*/ $pathname){}
/*. int   .*/ function imagepscopyfont(/*. int .*/ $font_index){}
/*. bool  .*/ function imagepsfreefont(/*. resource .*/ $font_index){}
/*. bool  .*/ function imagepsencodefont(/*. resource .*/ $font_index, /*. string .*/ $filename)/*. triggers E_WARNING .*/{}
/*. bool  .*/ function imagepsextendfont(/*. resource .*/ $font_index, /*. float .*/ $extend){}
/*. bool  .*/ function imagepsslantfont(/*. resource .*/ $font_index, /*. float .*/ $slant){}
/*. array .*/ function imagepstext(/*. resource .*/ $image, /*. string .*/ $text, /*. resource .*/ $font, /*. int .*/ $size, /*. int .*/ $xcoord, /*. int .*/ $ycoord /*., args .*/){}
/*. array .*/ function imagepsbbox(/*. string .*/ $text, /*. resource .*/ $font, /*. int .*/ $size /*., args .*/){}
/*. bool  .*/ function image2wbmp(/*. resource .*/ $im /*., args .*/){}
/*. bool  .*/ function jpeg2wbmp(/*. string .*/ $f_org, /*. string .*/ $f_dest, /*. int .*/ $d_height, /*. int .*/ $d_width, /*. int .*/ $threshold){}
/*. bool  .*/ function png2wbmp(/*. string .*/ $f_org, /*. string .*/ $f_dest, /*. int .*/ $d_height, /*. int .*/ $d_width, /*. int .*/ $threshold){}
/*. bool  .*/ function imagefilter(/*. resource .*/ $src_im, /*. int .*/ $filtertype /*., args .*/){}
/*. bool  .*/ function imageantialias(/*. resource .*/ $im, /*. bool .*/ $on){}
/*. array .*/ function iptcparse(/*. string .*/ $iptcblock){}
/*. mixed .*/ function iptcembed(/*. string .*/ $iptcdata, /*. string .*/ $jpeg_file_name /*., args .*/)/*. triggers E_WARNING .*/{}
