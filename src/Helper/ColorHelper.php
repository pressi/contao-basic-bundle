<?php
/***************************************************************************
 * (c) 2018 Stephan Preßl, www.stephanpressl.at <mail@stephanpressl.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by Stephan Preßl
 ***************************************************************************/
namespace IIDO\BasicBundle\Helper;

//use IIDO\WebsiteBundle\Util\ColorUtil as Color;

use IIDO\BasicBundle\Config\BundleConfig;


class ColorHelper
{

    /**
     * @param object $objRow
     *
     * @return bool|object
     */
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



    /**
     * @param string $htmlCode
     *
     * @return number
     */
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



    /**
     * @param $RGB
     *
     * @return object
     */
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
     *
     * @param mixed
     * @param boolean
     * @param array
     *
     * @return string
     */
    public static function compileColor($color, $blnWriteToFile=false, $vars=array())
    {
        if( preg_match('/^#/', $color) )
        {
            return $color;
        }

        $checkColor = \StringUtil::deserialize($color);

        if( is_array($checkColor) && is_string($color) )
        {
            $color = $checkColor;
        }

        if( !is_array($color) )
        {
            return ((strlen($color)) ? '#' . self::shortenHexColor($color) : 'transparent');
        }
        elseif( !isset($color[1]) || empty($color[1]) )
        {
            if($color[0] == "")
            {
                if( $color[2] )
                {
                    $strColor = $color[2];

                    if( !preg_match('/^#/', $strColor) )
                    {
                        $strColor = '#' . $strColor;
                    }

                    return $strColor;
                }

                return "transparent";
            }

            return '#' . self::shortenHexColor($color[0]);
        }
        else
        {
            if( $color[2] && !$color[0] )
            {
                $color[0] = $color[2];
            }

            return 'rgba(' . implode(',', self::convertHexColor($color[0], $blnWriteToFile, $vars)) . ','. ($color[1] / 100) .')';
        }
    }



    /**
     * Try to shorten a hex color
     *
     * @param string
     *
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
     *
     * @param string
     * @param boolean
     * @param array
     *
     * @return array
     * @see http://de3.php.net/manual/de/function.hexdec.php#99478
     */
    public static function convertHexColor($color, $blnWriteToFile=false, $vars=array())
    {
        $color = preg_replace('/^\#/', '', $color);

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



    /**
     * @param $color
     *
     * @return string
     */
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



    /**
     * @param null|object $objCurrentPage
     * @param boolean $returnPageObject
     *
     * @return array|string
     */
    public static function getPageColor( $objCurrentPage = NULL, $returnPageObject = false )
    {
        global $objPage;

        if( $objCurrentPage === NULL )
        {
            $objCurrentPage = $objPage;
        }

        $pageColor = self::getCurrentPageColor( $objCurrentPage );

        if( $pageColor === "transparent" && $objCurrentPage->pid > 0 )
        {
            if( $returnPageObject )
            {
                list($pageColor, $objCurrentPage) = self::getPageColor( \PageModel::findByPk( $objCurrentPage->pid ), $returnPageObject );
            }
            else
            {
                $pageColor = self::getPageColor( \PageModel::findByPk( $objCurrentPage->pid ) );
            }

            return ($returnPageObject) ? array($pageColor, $objCurrentPage) : $pageColor;
        }

        return ($returnPageObject) ? array($pageColor, $objCurrentPage) : $pageColor;
    }



    public static function getCurrentPageColor( $objCurrentPage = NULL )
    {
        global $objPage;

        if( $objCurrentPage === NULL )
        {
            $objCurrentPage = $objPage;
        }

        return self::compileColor( \StringUtil::deserialize($objCurrentPage->pageColor, TRUE) );
    }



    public static function getPageColorClass( $objCurrentPage = NULL )
    {
        list($pageColor, $objColorPage) = self::getPageColor($objCurrentPage, TRUE);

        if( $pageColor !== "transparent" )
        {
            return 'pc-' . preg_replace('/\//', '_', $objColorPage->alias);
        }

        return false;
    }



    public static function mixColors($basecolor, $mixcolor, $ratio, $addHash = true)
    {
        $baseComponentOffset    = strlen($basecolor) == 7 ? 1 : 0;
        $baseComponentRed       = hexdec(substr($basecolor, $baseComponentOffset, 2));
        $baseComponentGreen     = hexdec(substr($basecolor, $baseComponentOffset+2, 2));
        $baseComponentBlue      = hexdec(substr($basecolor, $baseComponentOffset+4, 2));

        $mixComponentOffset     = strlen($mixcolor) == 7 ? 1 : 0;
        $mixComponentRed        = hexdec(substr($mixcolor, $mixComponentOffset, 2));
        $mixComponentGreen      = hexdec(substr($mixcolor, $mixComponentOffset+2, 2));
        $mixComponentBlue       = hexdec(substr($mixcolor, $mixComponentOffset+4, 2));

        $Rsum = $baseComponentRed+$mixComponentRed;
        $Gsum = $baseComponentGreen+$mixComponentGreen;
        $Bsum = $baseComponentBlue+$mixComponentBlue;

        $R = ($baseComponentRed*(100-$ratio) + $mixComponentRed*$ratio) / 100;
        $G = ($baseComponentGreen*(100-$ratio) + $mixComponentGreen*$ratio) / 100;
        $B = ($baseComponentBlue*(100-$ratio) + $mixComponentBlue*$ratio) / 100;

        $redPercentage      = max($R, $G, $B) > 255 ? $R/max($Rsum, $Gsum, $Bsum) : $R/255;
        $greenPercentage    = max($R, $G, $B) > 255 ? $G/max($Rsum, $Gsum, $Bsum) : $G/255;
        $bluePercentage     = max($R, $G, $B) > 255 ? $B/max($Rsum, $Gsum, $Bsum) : $B/255;

        $redRGB     = floor(255*$redPercentage);
        $greenRGB   = floor(255*$greenPercentage);
        $blueRGB    = floor(255*$bluePercentage);

        $color = sprintf("%02X%02X%02X", $redRGB, $greenRGB, $blueRGB);
        return $addHash ? '#'.$color : $color;
    }



    public static function getThemeColors()
    {
        $arrOptions = array();

        $objThemes = \ThemeModel::findAll();

        if( $objThemes )
        {
            while( $objThemes->next() )
            {
                $arrVars = \StringUtil::deserialize($objThemes->vars, true);

                foreach($arrVars as $arrVar)
                {
                    if( preg_match('/color/', $arrVar['key']) )
                    {
                        $strValue = preg_replace('/color_/', '', $arrVar['key']);

                        $arrOptions[ $objThemes->name ][ $arrVar['value'] ] = self::renderLangValue($strValue);
                    }
                }
            }
        }

        return $arrOptions;
    }



    public static function getCurrentWebsiteColors()
    {
//        global $objPage;
//        $objRootPage = \PageModel::findByPk( $objPage->rootId );

        $parentLabel    = 'Standardfarben';
        $arrColors      = array();
        $fieldPrefix    = BundleConfig::getTableFieldPrefix();


        $primary = self::compileColor( \Config::get( $fieldPrefix . 'colorPrimary' ) );

        if( $primary && $primary !== "transparent" )
        {
            $arrColors[ $parentLabel ][ $primary ] = 'Primary - Hauptfarbe';
        }


        $secondary = self::compileColor( \Config::get( $fieldPrefix . 'colorSecondary' ) );

        if( $secondary && $secondary !== "transparent" )
        {
            $arrColors[ $parentLabel ][ $secondary ] = 'Secondary - Zweitfarbe';
        }



        $colors = \StringUtil::deserialize( \Config::get( $fieldPrefix . 'colors' ), TRUE );

        if( count($colors) && $colors[0]['color'] )
        {
            foreach($colors as $arrColor)
            {
                $arrColors[ $parentLabel ][ '#' . $arrColor['color'] ] = $arrColor['name']; //TODO: check root page
            }
        }

        return $arrColors;
    }



    public static function renderLangValue( $strText )
    {
        $arrText = explode("_", $strText);

        switch( $arrText[0] )
        {
            case "orange":
                $strText = 'Orange';
                break;

            case "yellow":
                $strText = 'Gelb';
                break;

            case "pink":
                $strText = 'Pink / Rosa';
                break;

            case "blue":
                $strText = 'Blau';
                break;

            case "darkblue":
                $strText = 'Dunkelblau';
                break;

            case "green":
                $strText = 'Grün';
                break;

            case "darkgreen":
                $strText = 'Dunkelgrün';
                break;

            case "red":
                $strText = 'Rot';
                break;

            case "darkred":
                $strText = 'Dunkelrot';
                break;

            case "black":
                $strText = 'Schwarz';
                break;

            case "white":
                $strText = 'Weiss';
                break;

            case "purple":
                $strText = 'Lila';
                break;

            case "brown":
                $strText = 'Braun';
        }

        if( $arrText[1] )
        {
            $strText .= ' (' . ucfirst($arrText[1]) . ')';
        }

        return $strText;
    }



    public static function renderColorConfig( $arrColor )
    {
        if( !is_array($arrColor) )
        {
            $arrColor = \StringUtil::deserialize( $arrColor, TRUE );
        }

        $strColor = self::compileColor( $arrColor );

        if( $strColor !== "transparent" )
        {
            if( $arrColor[0] === "" && $arrColor[2] )
            {
                $arrColor[0] = preg_replace(array('/^#/'), '', $arrColor[2]);
            }
        }

        unset($arrColor[2]);

        return serialize($arrColor);
    }
}