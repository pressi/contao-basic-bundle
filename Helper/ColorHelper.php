<?php
/***************************************************************************
 * (c) 2017 Stephan Preßl, www.stephanpressl.at <mail@stephanpressl.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by Stephan Preßl
 ***************************************************************************/
namespace IIDO\BasicBundle\Helper;

//use IIDO\WebsiteBundle\Util\ColorUtil as Color;

class ColorHelper
{

    public static function getBackgroundColor( $objRow )
    {
        $bgColor = deserialize($objRow->bgColor, TRUE);
//        $bgColor = self::compileColor(deserialize($objRow->bgColor, TRUE));

//        if( !strlen($bgColor) || $bgColor == 'transparent' )
//        {
//        }
//        else
//        {
//            if( substr($bgColor, 0, 3) == 'rgb' )
//            {
//                if( !empty($bgColor[1]) || !isset($bgColor[1]) )
//                {
//                }
//                else
//                {
//
//                }
//            }
//            else
//            {
//                $rgb = self::HTMLToRGB( $bgColor );
//                $hsl = self::RGBToHSL( $rgb );
//
//                return $hsl;
//            }
//        }

        if( !empty($bgColor[0]) )
        {
            $bgColor = $bgColor[0];
            $bgTrans = $bgColor[1];

            $rgb = self::HTMLToRGB( $bgColor );
            $hsl = self::RGBToHSL( $rgb );

            return $hsl;
        }
        else
        {
            $bgGradient = deserialize($objRow->gradientColors, TRUE);

            if( !empty($bgGradient[0]) )
            {
                $arrColors = array();

                $r = 0;
                $g = 0;
                $b = 0;

                foreach($bgGradient as $gradientColor)
                {
                    if( strlen($gradientColor) )
                    {
                        $convertColor = self::convertHexColor( $gradientColor );

                        $arrColors[] = $convertColor;

                        $r += $convertColor['red'];
                        $g += $convertColor['green'];
                        $b += $convertColor['blue'];
                    }
                }

                $cc = count($arrColors);
                $hex = self::compileRGBtoHex(array(($r / $cc) , ($g / $cc) , ($b / $cc)));
                $rgb = self::HTMLToRGB( $hex );
                $hsl = self::RGBToHSL( $rgb );

                return $hsl;
            }
        }

        return false;
    }

    public static function HTMLToRGB($htmlCode)
    {
        if($htmlCode[0] == '#')
        {
            $htmlCode = substr($htmlCode, 1);
        }

        if (strlen($htmlCode) == 3)
        {
            $htmlCode = $htmlCode[0] . $htmlCode[0] . $htmlCode[1] . $htmlCode[1] . $htmlCode[2] . $htmlCode[2];
        }

        $r = hexdec($htmlCode[0] . $htmlCode[1]);
        $g = hexdec($htmlCode[2] . $htmlCode[3]);
        $b = hexdec($htmlCode[4] . $htmlCode[5]);

        return $b + ($g << 0x8) + ($r << 0x10);
    }



    public static function RGBToHSL($RGB)
    {
        $r = 0xFF & ($RGB >> 0x10);
        $g = 0xFF & ($RGB >> 0x8);
        $b = 0xFF & $RGB;

        $r = ((float)$r) / 255.0;
        $g = ((float)$g) / 255.0;
        $b = ((float)$b) / 255.0;

        $maxC = max($r, $g, $b);
        $minC = min($r, $g, $b);

        $l = ($maxC + $minC) / 2.0;

        if($maxC == $minC)
        {
            $s = 0;
            $h = 0;
        }
        else
        {
            if($l < .5)
            {
                $s = ($maxC - $minC) / ($maxC + $minC);
            }
            else
            {
                $s = ($maxC - $minC) / (2.0 - $maxC - $minC);
            }
            if($r == $maxC)
                $h = ($g - $b) / ($maxC - $minC);
            if($g == $maxC)
                $h = 2.0 + ($b - $r) / ($maxC - $minC);
            if($b == $maxC)
                $h = 4.0 + ($r - $g) / ($maxC - $minC);

            $h = $h / 6.0;
        }

        $h = (int)round(255.0 * $h);
        $s = (int)round(255.0 * $s);
        $l = (int)round(255.0 * $l);

        return (object) Array('hue' => $h, 'saturation' => $s, 'lightness' => $l);
    }



    /**
     * Compile a color value and return a hex or rgba color
     * @param mixed
     * @param boolean
     * @param array
     * @return string
     */
    public static function compileColor($color, $blnWriteToFile=false, $vars=array())
    {
        if (!is_array($color))
        {
            return ((strlen($color)) ? '#' . self::shortenHexColor($color) : 'transparent');
        }
        elseif (!isset($color[1]) || empty($color[1]))
        {
            if($color[0] == "")
            {
                return "transparent";
            }

            return '#' . self::shortenHexColor($color[0]);
        }
        else
        {
            return 'rgba(' . implode(',', self::convertHexColor($color[0], $blnWriteToFile, $vars)) . ','. ($color[1] / 100) .')';
        }
    }



    /**
     * Try to shorten a hex color
     * @param string
     * @return string
     */
    public static function shortenHexColor($color)
    {
        if ($color[0] == $color[1] && $color[2] == $color[3] && $color[4] == $color[5])
        {
            return $color[0] . $color[2] . $color[4];
        }

        return $color;
    }


    /**
     * Convert hex colors to rgb
     * @param string
     * @param boolean
     * @param array
     * @return array
     * @see http://de3.php.net/manual/de/function.hexdec.php#99478
     */
    public static function convertHexColor($color, $blnWriteToFile=false, $vars=array())
    {
        // Support global variables
        if (strncmp($color, '$', 1) === 0)
        {
            if (!$blnWriteToFile)
            {
                return array($color);
            }
            else
            {
                $color = str_replace(array_keys($vars), array_values($vars), $color);
            }
        }

        $rgb = array();

        // Try to convert using bitwise operation
        if (strlen($color) == 6)
        {
            $dec = hexdec($color);
            $rgb['red']     = 0xFF & ($dec >> 0x10);
            $rgb['green']   = 0xFF & ($dec >> 0x8);
            $rgb['blue']    = 0xFF & $dec;
        }

        // Shorthand notation
        elseif (strlen($color) == 3)
        {
            $rgb['red']     = hexdec(str_repeat(substr($color, 0, 1), 2));
            $rgb['green']   = hexdec(str_repeat(substr($color, 1, 1), 2));
            $rgb['blue']    = hexdec(str_repeat(substr($color, 2, 1), 2));
        }

        return $rgb;
    }



    public static function compileRGBtoHex($color)
    {
        $hex    = "";
        $red    = $color[0];
        $green  = $color[1];
        $blue   = $color[2];

        $hex    .= str_pad(dechex($red), 2, "0", STR_PAD_LEFT);
        $hex    .= str_pad(dechex($green), 2, "0", STR_PAD_LEFT);
        $hex    .= str_pad(dechex($blue), 2, "0", STR_PAD_LEFT);

        return self::compileColor($hex);
    }
}