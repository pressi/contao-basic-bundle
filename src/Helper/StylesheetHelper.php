<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\Helper;


//TODO: StylesheetHelper && StylesHelper zusammenführen?? Stylesheet helper nur für das händling von css dateien (wie script helper)
use IIDO\BasicBundle\Config\BundleConfig;
use IIDO\BasicBundle\Helper\GlobalElementsHelper;


class StylesheetHelper
{
    protected static $stylesheetPath        = '/Resources/public/css/';


    protected static $stylesheetPathPublic  = '/css/';


    protected static $stylesheetModeCombine = '||static';



    public static function renderStyleVars( $strContent )
    {
        global $objPage;

        $objLayout  = BasicHelper::getPageLayout( $objPage );
        $objTheme   = \ThemeModel::findByPk( $objLayout->pid );

        $themeVars  = deserialize($objTheme->vars, TRUE);
        $themeVars  = self::combineVars($themeVars, self::getDefaultsCSS());

        if( count($themeVars) )
        {
            foreach($themeVars as $arrValue)
            {
                $varName    = $arrValue['key'];
                $varValue   = $arrValue['value'];
//                        $objCurPage = $arrValue['page'];
                $add        = '';

                if( !preg_match('/shrink/', $varName) )
                {
                    $add = '1px';
                }

                if( preg_match('/color/', $varName) )
                {
                    $add = '#fff';

//                            if( preg_match('/^page_color/', $varName) )
//                            {
//                                $varName .= '_' . $objCurPage->alias;
//                            }
                    $varValue       = preg_replace(array('/&#35;/', '/&#40;/', '/&#41;/'), array('#', '(', ')'), $varValue);

                    $strContent     = self::replaceColorVariants($varName, $varValue, $strContent, $add);
                }

                $strContent = preg_replace('/\/\*#' . $varName . '#\*\/' . $add . '/', $varValue, $strContent);
            }
        }

        $strContent = self::replaceStylesEditorColors( $strContent );

        return self::replaceDefaultVars( $strContent );
    }



    public static function getDefaultsCSS()
    {
        global $objPage;

//        $objRootPage    = \PageModel::findByPk( $objPage->rootId );
        $arrVariables   = array();
        $pageColor      = ColorHelper::getPageColor( NULL );

        if( $pageColor !== "transparent" )
        {
            $arrVariables[] = array
            (
                'key'   => 'page_color',
                'value' => $pageColor,
//            'page'  => $objColorPage
            );

            $rgb = ColorHelper::convertHexColor($pageColor);

            if( count($rgb) )
            {
                $rgba = 'rgba(' . $rgb[ 'red' ] . ',' . $rgb[ 'green' ] . ',' . $rgb[ 'blue' ] . ',';

                for($i = 5; $i <= 100; $i+=5)
                {
                    $trans = $i;

                    if( strlen($i) === 1 )
                    {
                        $trans = '0' . $trans;
                    }

                    $arrVariables[] = array
                    (
                        'key'   => 'page_color_trans' . $trans,
                        'value' => $rgba . $i . ')',
                    );
                }
            }
        }

        $objPages = \PageModel::findPublishedByPid( $objPage->rootId );

        if( $objPages )
        {
            while( $objPages->next() )
            {
                $pageColor = ColorHelper::getCurrentPageColor( $objPages );

                if( $pageColor !== 'transparent' )
                {
                    $arrVariables[] = array
                    (
                        'key'   => 'page_color_' . $objPages->alias,
                        'value' => $pageColor
                    );

                    $rgb = ColorHelper::convertHexColor($pageColor);

                    if( count($rgb) )
                    {
                        $rgba = 'rgba(' . $rgb[ 'red' ] . ',' . $rgb[ 'green' ] . ',' . $rgb[ 'blue' ] . ',';

                        for($i = 5; $i <= 100; $i+=5)
                        {
                            $trans = $i;

                            if( strlen($i) === 1 )
                            {
                                $trans = '0' . $trans;
                            }

                            $arrVariables[] = array
                            (
                                'key'   => 'page_color_' . $objPages->alias . '_trans' . $trans,
                                'value' => $rgba . $i . ')',
                            );
                        }
                    }
                }

                $objSubPages = \PageModel::findPublishedByPid( $objPages->id );

                if( $objSubPages && $objSubPages->count() )
                {
                    while( $objSubPages->next() )
                    {
                        $pageColor = ColorHelper::getCurrentPageColor( $objSubPages->current() );

                        if( $pageColor !== 'transparent' )
                        {
                            $arrVariables[] = array
                            (
                                'key'   => 'page_color_' . $objSubPages->alias,
                                'value' => $pageColor
                            );

                            $rgb = ColorHelper::convertHexColor($pageColor);

                            if( count($rgb) )
                            {
                                $rgba = 'rgba(' . $rgb[ 'red' ] . ',' . $rgb[ 'green' ] . ',' . $rgb[ 'blue' ] . ',';

                                for($i = 5; $i <= 100; $i+=5)
                                {
                                    $trans = $i;

                                    if( strlen($i) === 1 )
                                    {
                                        $trans = '0' . $trans;
                                    }

                                    $arrVariables[] = array
                                    (
                                        'key'   => 'page_color_' . $objSubPages->alias . '_trans' . $trans,
                                        'value' => $rgba . $i . ')',
                                    );
                                }
                            }
                        }

                        $objSubSubPages = \PageModel::findPublishedByPid( $objSubPages->id );

                        if( $objSubSubPages && $objSubSubPages->count() )
                        {
                            while( $objSubSubPages->next() )
                            {
                                $pageColor = ColorHelper::getCurrentPageColor( $objSubSubPages->current() );

                                if( $pageColor !== 'transparent' )
                                {
                                    $arrVariables[] = array
                                    (
                                        'key'   => 'page_color_' . $objSubSubPages->alias,
                                        'value' => $pageColor
                                    );

                                    $rgb = ColorHelper::convertHexColor($pageColor);

                                    if( count($rgb) )
                                    {
                                        $rgba = 'rgba(' . $rgb[ 'red' ] . ',' . $rgb[ 'green' ] . ',' . $rgb[ 'blue' ] . ',';

                                        for($i = 5; $i <= 100; $i+=5)
                                        {
                                            $trans = $i;

                                            if( strlen($i) === 1 )
                                            {
                                                $trans = '0' . $trans;
                                            }

                                            $arrVariables[] = array
                                            (
                                                'key'   => 'page_color_' . $objSubSubPages->alias . '_trans' . $trans,
                                                'value' => $rgba . $i . ')',
                                            );
                                        }
                                    }
                                }

                            }
                        }

                    }
                }

            }
        }

        return $arrVariables;
    }



    public static function combineVars($arrPrimary, $arrSecondary)
    {
        $arrKeys    = array();
        $arrResult  = array();

        foreach( $arrPrimary as $key => $value )
        {
            $vKey       = $value['key'];
            $arrItem    = $value;

            foreach($arrSecondary as $skey => $svalue )
            {
                if( $svalue['key'] === $vKey )
                {
                    if( $vKey === "page_color" && $svalue === "transparent" )
                    {
                        continue;
                    }

                    $arrItem    = $svalue;
                    break;
                }
            }

            $arrKeys[]      = $arrItem['key'];
            $arrResult[]    = $arrItem;
        }

        foreach( $arrSecondary as $sKey => $sValue )
        {
            if( !in_array($sValue, $arrKeys) )
            {
                $arrResult[] = $sValue;
            }
        }

        return $arrResult;
    }



    public static function replaceDefaultVars( $strContent, $useDefaultMatcher = false )
    {
//        global $objPage;

//        if( $useDefaultMatcher )
//        {
//            preg_match_all('/#[^=#]+#/', $strContent, $arrChunks);
//        }
//        else
//        {
            preg_match_all('/\/\*#[^=#]+#\*\//', $strContent, $arrChunks);
//        }

        $fieldPrefix = BundleConfig::getTableFieldPrefix();

        foreach ($arrChunks[0] as $strChunk)
        {
//            if( $useDefaultMatcher )
//            {
//                $addPx      = '';
//                $addColor   = '';
//
//                $strKey     = strtolower(substr($strChunk, 1, -1));
//            }
//            else
//            {
                $addPx      = '1px';
                $addColor   = '#fff';

                $strKey     = strtolower(substr($strChunk, 3, -3));
//            }

            switch( $strKey )
            {
                case "page_width":
                    $pageWidth      = self::renderUnits( \Config::get($fieldPrefix . 'pageWidth') ?:'100%' );
                    $strContent     = str_replace($strChunk . $addPx, $pageWidth, $strContent);
                    break;

                case "page_content_width":
                    $contentWidth   = self::renderUnits( \Config::get($fieldPrefix . 'pageContentWidth') ?: \Config::get($fieldPrefix . 'pageWidth')?:'100%');
                    $strContent     = str_replace($strChunk . $addPx, $contentWidth, $strContent);
                    break;

//                case "page_color":
//                    $pageColor  = ColorHelper::getPageColor( $objPage );
//                    $strContent = str_replace($strChunk . $addColor, $pageColor, $strContent);
//                    break;

                case (0 === strpos( $strKey, 'calc_' )):
                    $arrCalc    = explode('__', $strKey);
//                    $calc       = preg_replace('/px/', '', self::replaceDefaultVars( self::replaceThemeVars( preg_replace('/^calc_/', '', $arrCalc[0]) ), true ));
                    $calc       = self::replaceThemeVars( preg_replace('/^calc_/', '', $arrCalc[0]) );
                    $result     = 0; //MathHelper::calculate( $calc );

                    eval( '$result = ' . $calc . ';');

                    $op = '';

                    if( $arrCalc[1] && $arrCalc[1] === 'neg' )
                    {
                        $op = '-';
                    }

                    $strContent = str_replace($strChunk . $addPx, $op . $result . 'px', $strContent);
                    break;

                case (preg_match("/^color_(primary|secondary|tertiary|quaternary)$/", $strKey, $matches) ? true : false):
                    $color      = ColorHelper::compileColor( \Config::get($fieldPrefix . 'color' . ucfirst($matches[1])) ?:'#000' );
                    $strContent = str_replace($strChunk . $addColor, $color, $strContent);
                    $strContent = self::replaceColorVariants($strKey, $color, $strContent, '#fff');
                    break;

//                case "color_secondary":
//                    $color      = ColorHelper::compileColor( \Config::get($fieldPrefix . 'colorSecondary') ?:'#fff' );
//                    $strContent = str_replace($strChunk . $addColor, $color, $strContent);
//                    $strContent = self::replaceColorVariants($strKey, $color, $strContent, '#fff');
//                    break;

//                case "btn_color_primary":
                case (preg_match("/^btn_color_([a-zA-Z]{0,})$/", $strKey, $matches) ? true : false):
                    $color      = ColorHelper::compileColor( \Config::get($fieldPrefix . 'buttonColor' . ucfirst($matches[1])) ?: \Config::get($fieldPrefix . 'color' . ucfirst($matches[1])) ?:'#000' );
                    $strContent = str_replace($strChunk . $addColor, $color, $strContent);
                    $strContent = self::replaceColorVariants($strKey, $color, $strContent, '#fff');
                    break;

//                case "btn_font-color_primary":
                case (preg_match("/^btn_font-color_([a-zA-Z]{0,})$/", $strKey, $matches) ? true : false):
                    $color      = ColorHelper::compileColor( \Config::get($fieldPrefix . 'buttonFontColor' . ucfirst($matches[1])) ?:'#000' );
                    $strContent = str_replace($strChunk . $addColor, $color, $strContent);
                    $strContent = self::replaceColorVariants($strKey, $color, $strContent, '#fff');
                    break;

//                case "btn_hover_color_primary":
                case (preg_match("/^btn_hover_color_([a-zA-Z]{0,})$/", $strKey, $matches) ? true : false):
                    $arrColor   = \StringUtil::deserialize(\Config::get($fieldPrefix . 'buttonHoverColor' . ucfirst($matches[1])), TRUE);
                    $strColor   = ColorHelper::compileColor( $arrColor );

                    if( $strColor === "transparent" && $arrColor[0] === "" && $arrColor[1] === "" )
                    {
                        $arrColor   = \StringUtil::deserialize(\Config::get($fieldPrefix . 'buttonColor' . ucfirst($matches[1])), TRUE);
                        $strColor   = self::getDarkerColor( ColorHelper::compileColor($arrColor) );

                        if( $strColor === "transparent" && $arrColor[0] === "" && $arrColor[1] === "" )
                        {
                            $strColor = '#333';
                        }
                    }

                    $color      = $strColor;
                    $strContent = str_replace($strChunk . $addColor, $color, $strContent);
                    $strContent = self::replaceColorVariants($strKey, $color, $strContent, '#fff');
                    break;

                case (preg_match("/^btn_hover_font-color_([a-zA-Z]{0,})$/", $strKey, $matches) ? true : false):
                    $arrColor   = \StringUtil::deserialize(\Config::get($fieldPrefix . 'buttonHoverFontColor' . ucfirst($matches[1])), TRUE);
                    $strColor   = ColorHelper::compileColor( $arrColor );

                    if( $strColor === "transparent" && $arrColor[0] === "" && $arrColor[1] === "" )
                    {
                        $arrColor   = \StringUtil::deserialize(\Config::get($fieldPrefix . 'buttonFontColor' . ucfirst($matches[1])), TRUE);
                        $strColor   = self::getLighterColor( ColorHelper::compileColor($arrColor) );

                        if( $strColor === "transparent" && $arrColor[0] === "" && $arrColor[1] === "" )
                        {
                            $strColor = '#ff';
                        }
                    }

                    $color      = $strColor;
                    $strContent = str_replace($strChunk . $addColor, $color, $strContent);
                    $strContent = self::replaceColorVariants($strKey, $color, $strContent, '#fff');
                    break;


//                case "btn_color_secondary":
//                    $color      = ColorHelper::compileColor( \Config::get($fieldPrefix . 'buttonColorSecondary') ?:\Config::get($fieldPrefix . 'colorSecondary') ?:'#fff' );
//                    $strContent = str_replace($strChunk . $addColor, $color, $strContent);
//                    $strContent = self::replaceColorVariants($strKey, $color, $strContent, '#fff');
//                    break;

//                case "btn_hover_color_secondary":
//                    $color      = ColorHelper::compileColor( \Config::get($fieldPrefix . 'buttonHoverColorSecondary') ?: self::getDarkerColor( \Config::get($fieldPrefix . 'buttonColorSecondary') ) ?:'#333' );
//                    $strContent = str_replace($strChunk . $addColor, $color, $strContent);
//                    $strContent = self::replaceColorVariants($strKey, $color, $strContent, '#fff');
//                    break;

//                case "btn_font-color_secondary":
//                    $color      = ColorHelper::compileColor( \Config::get($fieldPrefix . 'buttonFontColorSecondary') ?:'#fff' );
//                    $strContent = str_replace($strChunk . $addColor, $color, $strContent);
//                    $strContent = self::replaceColorVariants($strKey, $color, $strContent, '#fff');
//                    break;
            }
        }

        return $strContent;
    }



    public static function replaceColorVariants( $varName, $varValue, $strContent, $add )
    {
        $varValueDark   = ColorHelper::mixColors($varValue, '#000000', 20.0);
        $varValueLight  = ColorHelper::mixColors($varValue, '#ffffff', 90.0);

        $strContent     = preg_replace('/\/\*#' . $varName . '_darker#\*\/' . $add . '/', $varValueDark, $strContent);
        $strContent     = preg_replace('/\/\*#' . $varName . '_lighter#\*\/' . $add . '/', $varValueLight, $strContent);

        $rgb = ColorHelper::convertHexColor($varValue);

        if( count($rgb) )
        {
            $rgba = 'rgba(' . $rgb['red'] . ',' . $rgb['green'] . ',' . $rgb['blue'] . ',';

            $strContent = preg_replace('/\/\*#' . $varName . '_trans95#\*\/' . $add . '/', $rgba . '0.95)', $strContent);
            $strContent = preg_replace('/\/\*#' . $varName . '_trans90#\*\/' . $add . '/', $rgba . '0.9)', $strContent);
            $strContent = preg_replace('/\/\*#' . $varName . '_trans85#\*\/' . $add . '/', $rgba . '0.85)', $strContent);
            $strContent = preg_replace('/\/\*#' . $varName . '_trans80#\*\/' . $add . '/', $rgba . '0.8)', $strContent);
            $strContent = preg_replace('/\/\*#' . $varName . '_trans75#\*\/' . $add . '/', $rgba . '0.75)', $strContent);
            $strContent = preg_replace('/\/\*#' . $varName . '_trans70#\*\/' . $add . '/', $rgba . '0.7)', $strContent);
            $strContent = preg_replace('/\/\*#' . $varName . '_trans65#\*\/' . $add . '/', $rgba . '0.65)', $strContent);
            $strContent = preg_replace('/\/\*#' . $varName . '_trans60#\*\/' . $add . '/', $rgba . '0.6)', $strContent);
            $strContent = preg_replace('/\/\*#' . $varName . '_trans55#\*\/' . $add . '/', $rgba . '0.55)', $strContent);
            $strContent = preg_replace('/\/\*#' . $varName . '_trans50#\*\/' . $add . '/', $rgba . '0.5)', $strContent);
            $strContent = preg_replace('/\/\*#' . $varName . '_trans45#\*\/' . $add . '/', $rgba . '0.45)', $strContent);
            $strContent = preg_replace('/\/\*#' . $varName . '_trans40#\*\/' . $add . '/', $rgba . '0.4)', $strContent);
            $strContent = preg_replace('/\/\*#' . $varName . '_trans35#\*\/' . $add . '/', $rgba . '0.35)', $strContent);
            $strContent = preg_replace('/\/\*#' . $varName . '_trans30#\*\/' . $add . '/', $rgba . '0.3)', $strContent);
            $strContent = preg_replace('/\/\*#' . $varName . '_trans25#\*\/' . $add . '/', $rgba . '0.25)', $strContent);
            $strContent = preg_replace('/\/\*#' . $varName . '_trans20#\*\/' . $add . '/', $rgba . '0.2)', $strContent);
            $strContent = preg_replace('/\/\*#' . $varName . '_trans15#\*\/' . $add . '/', $rgba . '0.15)', $strContent);
            $strContent = preg_replace('/\/\*#' . $varName . '_trans10#\*\/' . $add . '/', $rgba . '0.1)', $strContent);
            $strContent = preg_replace('/\/\*#' . $varName . '_trans05#\*\/' . $add . '/', $rgba . '0.05)', $strContent);
            $strContent = preg_replace('/\/\*#' . $varName . '_trans5#\*\/' . $add . '/', $rgba . '0.05)', $strContent);
        }

        return $strContent;
    }



    public static function getDarkerColor( $color )
    {
        return ColorHelper::mixColors($color, '#000000', 20.0);
    }



    public static function getLighterColor( $color )
    {
        return ColorHelper::mixColors($color, '#ffffff', 90.0);
    }



    public static function replaceThemeVars( $string )
    {
        global $objPage;

        $objLayout  = BasicHelper::getPageLayout( $objPage );
        $objTheme   = \ThemeModel::findByPk( $objLayout->pid );

        $themeVars  = \StringUtil::deserialize($objTheme->vars, TRUE);
        $themeVars  = self::combineVars($themeVars, self::getDefaultsCSS());

        \Controller::loadDataContainer( "tl_iido_basic_website_styles" );

        $arrFields      = $GLOBALS['TL_DCA']['tl_iido_basic_website_styles']['fields'];
        $fieldPrefix    = BundleConfig::getTableFieldPrefix();

        foreach( $themeVars as $arrVar )
        {
            $string = preg_replace('/;' . $arrVar['key'] . ';/', preg_replace('/px$/', '', $arrVar['value']), $string);
        }

        foreach($arrFields as $strField => $arrField)
        {
            $strField       = preg_replace('/^' . $fieldPrefix . '/', '', $strField);
            $strNewField    = '';
            $arrFieldParts  = $pieces = preg_split('/(?=[A-Z])/', $strField);

            $i = 0;
            foreach($arrFieldParts as $fieldPart)
            {
                $partName = strtolower( $fieldPart );

                if( $partName === "button" )
                {
                    $partName = 'btn';
                }

                $strNewField .= (($i > 0) ? '_' : '') . $partName;

                $i++;
            }

            $string = preg_replace('/;' . $strNewField . ';/', preg_replace('/px$/', '', self::replaceDefaultVars('/*#' . $strNewField . '#*/1px')), $string);
        }

        return $string;
    }



    public static function replaceStylesEditorColors( $strContent )
    {
        $fieldPrefix    = BundleConfig::getTableFieldPrefix();
        $arrColors      = \StringUtil::deserialize( \Config::get( $fieldPrefix . 'colors'), TRUE);

        $add            = '#fff';

        foreach( $arrColors as $arrColor)
        {
            if( count($arrColor) )
            {
                $varName    = $arrColor['variable'];

                $strContent = preg_replace('/\/\*#' . $varName . '#\*\/' . $add . '/', '#' . $arrColor['color'], $strContent);
                $strContent = self::replaceColorVariants($varName, $arrColor['color'], $strContent, $add);
            }
        }

        return $strContent;
    }



    public static function renderHeadStyles( $strStyles )
    {
        return self::renderStyleVars( $strStyles );
    }


    //TODO: funktion in global elements helper??
    public static function getGlobalElementStyles( $mode, $objData )
    {
        $arrStyles      = array();
        $objTopHeader   = false;

        if( $mode === "topheader" || $mode === "header" || $mode === "footer" )
        {
            $selector = $mode;

            if( $mode === 'header' )
            {
                $objTopHeader = HeaderHelper::headerTopBarExists();

                if( $objTopHeader )
                {
                    $selector = '#header .header-bar';
                }
            }
            elseif( $mode === 'topheader' )
            {
                $selector = '#header .header-top-bar';
            }

            if( $mode === 'footer' )
            {
                $selector = '#' . $mode;
            }

            $arrStyles = array
            (
                'selector'      => $selector,
            );

            if( $objData->isFixed )
            {
                $arrPosition    = array('top'=>'','right'=>'','bottom'=>'','left'=>'','unit'=>'px');
                $articleWidth   = \StringUtil::deserialize($objData->articleWidth, true);
                $articleHeight  = \StringUtil::deserialize($objData->articleHeight, true);

                if( $mode === 'header' )
                {
                    if( $objTopHeader )
                    {
                        $arrStyles = array
                        (
                            'selector'      => $selector,

                            'positioning'   => true,
                            'position'      => $objTopHeader->isAbsolute ? 'absolute' : 'fixed'
                        );
                    }
                    else
                    {
                        if( !$objData->isAbsolute )
                        {
                            $selector .= '.is-fixed';
                        }

                        $arrStyles = array
                        (
                            'selector'      => $selector,

                            'positioning'   => true,
                            'position'      => $objData->isAbsolute ? 'absolute' : 'fixed'
                        );
                    }
                }
                elseif( $mode === 'topheader' )
                {
                    $arrStyles = array
                    (
                        'selector'      => $selector,

                        'positioning'   => true,
                        'position'      => $objData->isAbsolute ? 'absolute' : 'fixed'
                    );
                }
                else
                {
                    $selector .= '.is-fixed';

                    $arrStyles = array
                    (
                        'selector'      => $selector,

                        'positioning'   => true,
                        'position'      => 'fixed'
                    );
                }

                if( $articleWidth['value'] || $articleHeight['value'] )
                {
                    $arrStyles['size'] = true;

                    if( $articleWidth['value'] )
                    {
                        $arrStyles['width'] = $objData->articleWidth;
                    }

                    if( $articleHeight['value'] )
                    {
                        $arrStyles['height'] = $objData->articleHeight;
                    }
                }

                if( $objData->position === 'top' )
                {
                    $arrPosition['top'] = '0';

                    if( !$articleWidth['value'] || $articleWidth['value'] === 100 || $articleWidth['value'] === "100" )
                    {
                        $arrPosition['right']   = '0';
                        $arrPosition['left']    = '0';
                    }
                }
                elseif( $objData->position === "right" )
                {
                    $arrPosition['right'] = '0';

                    if( !$articleHeight['value'] || $articleHeight['value'] === 100 || $articleHeight['value'] === "100" )
                    {
                        $arrPosition['top']     = '0';
                        $arrPosition['bottom']   = '0';
                    }
                }
                elseif( $objData->position === "bottom" )
                {
                    $arrPosition['bottom'] = '0';

                    if( !$articleWidth['value'] || $articleWidth['value'] === 100 || $articleWidth['value'] === "100" )
                    {
                        $arrPosition['right']   = '0';
                        $arrPosition['left']    = '0';
                    }
                }
                elseif( $objData->position === "left" )
                {
                    $arrPosition['left'] = '0';

                    if( !$articleHeight['value'] || $articleHeight['value'] === 100 || $articleHeight['value'] === "100" )
                    {
                        $arrPosition['top']     = '0';
                        $arrPosition['bottom']   = '0';
                    }
                }

                $arrPosition['unit'] = 'px';

                $arrStyles['trbl']    = serialize($arrPosition);
            }

            $arrStyles = array_merge($arrStyles, StylesheetHelper::getBackgroundStyles($objData));

            if( $objData->isFixed )
            {
                if( FALSE === strpos( $arrStyles['own'], 'z-index' ) )
                {
                    $arrStyles['own']     .= 'z-index:900;';
                }
            }

            if( $mode === 'header' || $mode === 'footer' )
            {
                $arrPadding = \StringUtil::deserialize( $objData->padding, TRUE);

                if( $arrPadding['top'] || $arrPadding['right'] || $arrPadding['bottom'] || $arrPadding['left'] )
                {
                    $headerStyles = $arrStyles;

                    $arrStyles = array();

                    $arrStyles[] = $headerStyles;
                    $arrStyles[] = array
                    (
                        'selector'  => $mode . ' .inside',
                        'alignment' => true,
                        'padding'   => $objData->padding
                    );
                }
            }
        }

        return $arrStyles;
    }



    public static function getBackgroundStyles($objArticle, $onlyOwnStyles = false, $returnAsArray = true, $writeInFile = false, $selector = '')
    {
        $addBackgroundImage = $objArticle->addBackgroundImage;
        $arrOwnStyles       = array();
        $arrBackgroundSize  = \StringUtil::deserialize($objArticle->bgSize, true);

        if( $addBackgroundImage && is_array($arrBackgroundSize) && strlen($arrBackgroundSize[2]) && $arrBackgroundSize[2] != '-' )
        {
            $bgSize = $arrBackgroundSize[2];

            if( $arrBackgroundSize[2] == 'own' )
            {
                unset($arrBackgroundSize[2]);
                $bgSize = implode(" ", $arrBackgroundSize);
            }

            $arrOwnStyles[] = '-webkit-background-size:' . $bgSize . ';-moz-background-size:' . $bgSize . ';-o-background-size:' . $bgSize . ';background-size:' . $bgSize . ';';
        }

        if( $addBackgroundImage && $objArticle->bgAttachment )
        {
            $arrOwnStyles[] = 'background-attachment:' . $objArticle->bgAttachment . ';';
        }

        if( $onlyOwnStyles )
        {
            return $returnAsArray ? $onlyOwnStyles : implode("", $arrOwnStyles);
        }

        $rootDir    = dirname(\System::getContainer()->getParameter('kernel.root_dir'));

        $strImage   = '';

        if( $addBackgroundImage )
        {
            $objImage   = \FilesModel::findByUuid( $objArticle->bgImage );

            if( $objImage && file_exists($rootDir . '/' . $objImage->path) )
            {
                $strImage = $objImage->path;
            }
        }

        $arrStyles = array
        (
            'background'        => TRUE,
            'bgcolor'           => $objArticle->bgColor,
            'bgimage'           => $strImage,
            'bgrepeat'          => $addBackgroundImage ? $objArticle->bgRepeat          : '',
            'bgposition'        => $addBackgroundImage ? $objArticle->bgPosition        : '',
            'gradientAngle'     => $addBackgroundImage ? $objArticle->gradientAngle     : '',
            'gradientColors'    => $addBackgroundImage ? $objArticle->gradientColors    : ''
        );

        if( count($arrOwnStyles) )
        {
            $arrStyles['own'] = implode("", $arrOwnStyles);
        }

        if( strlen($selector) )
        {
            $arrStyles['selector'] = $selector;
        }

        if( !$returnAsArray )
        {
            $objStyleSheets     = new \StyleSheets();
            $arrStyles          = $objStyleSheets->compileDefinition($arrStyles, $writeInFile);
        }

        return $arrStyles;
    }










    public static function addStylesheet( $stylesheetName )
    {
        if( !is_array($stylesheetName) )
        {
            $stylesheetName = array( $stylesheetName );
        }

        foreach($stylesheetName as $fileKey => $fileName)
        {
            if( is_numeric($fileKey) )
            {
                $fileKey = $fileName;
            }

            $filePath       = self::getStylesheetSource( $fileName, true );
            $filePathIntern = self::getStylesheetSource( $fileName );

            if( file_exists(BasicHelper::getRootDir( true ) . $filePathIntern) )
            {
                $GLOBALS['TL_CSS'][ $fileKey ] = $filePath . self::getStylesheetMode();
            }
        }
    }



    public static function addMasterStylesheet( $stylesheetName )
    {
        $GLOBALS['TL_USER_CSS'][ $stylesheetName ] = 'files/master/css/' . $stylesheetName . '.css' . self::getStylesheetMode();
    }



    public static function addThemeStyle( $stylesheetName, $stylesheets)
    {
        $arrStyleSheets = $stylesheets;

        if( !is_array($arrStyleSheets) )
        {
            $arrStyleSheets = explode(",", $stylesheets);
        }

        if( !is_array($stylesheetName) )
        {
            $stylesheetName = array( $stylesheetName );
        }

        foreach($stylesheetName as $fileKey => $fileName)
        {
            if( is_numeric( $fileKey ) )
            {
                $fileKey = $fileName;
            }

            $filePath       = self::getStylesheetSource( $fileName, true, true );
            $filePathIntern = self::getStylesheetSource( $fileName, false, true );

            foreach($arrStyleSheets as $styleSheet)
            {
                $styleSheetKey = preg_replace('/.css$/', '', $styleSheet);

                if( !preg_match('/.css$/', $styleSheet) )
                {
                    $styleSheet = $styleSheet . '.css';
                }

                if( file_exists( BasicHelper::getRootDir( true ) . $filePathIntern . '/theme/' . $styleSheet ) )
                {
                    $GLOBALS['TL_CSS'][ $fileKey . '-' . $styleSheetKey ] = $filePath . '/theme/' . $styleSheet . self::getStylesheetMode();
                }
            }
        }
    }



    public static function getStylesheetSource( $scriptName, $public = false, $withoutFile = false )
    {
        $strPath        = BundleConfig::getBundlePath() . self::$stylesheetPath;
        $folderVersion  = ScriptHelper::getScriptVersion( $scriptName );

        $arrFiles = scan( BasicHelper::getRootDir( true ) . $strPath . '/' . $scriptName . '/' . $folderVersion );
        $fileName = '';

        foreach($arrFiles as $strFile)
        {
            if( preg_match('/.min.css$/', $strFile) )
            {
                $fileName = $strFile;
                break;
            }
        }

        return BundleConfig::getBundlePath( $public ) . ($public ? self::$stylesheetPathPublic : self::$stylesheetPath) . $scriptName . '/' . $folderVersion . ($withoutFile ? '' : '/' . $fileName);
    }



    public static function getStylesheetMode()
    {
        global $objPage;

        $objLayout = BasicHelper::getPageLayout( $objPage );

        return ($objLayout->combineScripts ? self::$stylesheetModeCombine : '');
    }



    public static function addDefaultStylesheets()
    {
        $rootAlias = BasicHelper::getRootPageAlias();

        $cssPath        = BundleConfig::getBundlePath( true ) . '/css/';
        $cssPathStd     = $cssPath . 'frontend/iido/';
        $cssPathMaster  = 'files/master/css/';
        $cssPathCustom  = 'files/' . $rootAlias . '/css/';
        $scssPathCustom = 'files/' . $rootAlias . '/scss/';

        $arrFilesFirst = array
        (
            'reset.css'
        );

        $arrFiles       = array
        (
//            'reset.css',
            'animate.css',
//            'grid16.css',
            'styles.css',
            'standards.css',
            'page.css'
        );

        $arrMasterFiles = array
        (
//            'reset.css',
            'animate.css',
            'hamburgers.css',
            'hamburgers.min.css',
            'icons.css',
            'core.css',
            'buttons.css',
            'form.css',
            'forms.css',
            'layout.css',
            'navigation.css',
            'bulma/columns.css',
            'bulma/tile.css',
            'bulma/form.css',
            'bulma/checkradio.css',
            'content.css',
            'responsive.css'
        );

        $arrCustomFiles = array
        (
            'fonts.css',
            'icons.css',
            'animate.css',
            'core.css',
            'buttons.css',
            'form.css',
            'forms.css',
            'layout.css',
            'hamburgers.css',
            'hamburgers.min.css',
            'navigation.css',
            'content.css',
            'style.css',
            'styles.css',
            'page.css',
            'responsive.css'
        );

        $rootDir = BasicHelper::getRootDir();

        foreach($arrFilesFirst as $strFile)
        {
            if( file_exists( $rootDir . '/' . $cssPathMaster . $strFile) )
            {
                $GLOBALS['TL_CSS'][ 'master_' . $strFile ] =  $cssPathMaster . $strFile . '||static';
            }

            if( file_exists( $rootDir . '/' . $cssPathCustom . $strFile) )
            {
                $GLOBALS['TL_CSS'][ 'custom_' . $strFile ] =  $cssPathCustom . $strFile . '||static';
            }
        }

        foreach($arrFiles as $strFile)
        {
            if( file_exists( $rootDir . '/' . $cssPathStd . $strFile) )
            {
                $GLOBALS['TL_USER_CSS'][ 'std_' . $strFile ] =  $cssPathStd . $strFile . '||static';
            }
        }

        foreach($arrMasterFiles as $strFile)
        {
            if( file_exists($rootDir . '/' . $cssPathMaster  . $strFile) )
            {
                $GLOBALS['TL_USER_CSS'][ 'master_' . $strFile ] =  $cssPathMaster . $strFile . '||static';
            }
        }

        foreach($arrCustomFiles as $strFile)
        {
            if( file_exists($rootDir . '/' . $cssPathCustom  . $strFile) )
            {
                $GLOBALS['TL_USER_CSS'][ 'custom_' . $strFile ] =  $cssPathCustom . $strFile . '||static';
            }
        }

        $arrScssFiles = array();

        foreach($arrCustomFiles as $strFile)
        {
            $strFile = preg_replace('/.css$/', '.scss', $strFile);


            if( file_exists($rootDir . '/' . $scssPathCustom  . $strFile) )
            {
                $arrScssFiles[ $strFile ] = $scssPathCustom . $strFile . '||static';
            }
        }

        if( count($arrScssFiles) )
        {
            $GLOBALS['TL_USER_CSS'][ 'custom_scss_functions'] =  $scssPathCustom . 'config/functions.scss||static';

            foreach($arrScssFiles as $strFileName => $strFilePath)
            {
                $GLOBALS['TL_USER_CSS'][ 'custom_scss_' . $strFileName] =  $strFilePath;
            }
        }

        if( file_exists($rootDir . '/' . $cssPathCustom  . '/page-styles.css') )
        {
            $strFile = $rootDir. '/' . $cssPathCustom  . '/page-styles.css';

            $GLOBALS['TL_HEAD']['custom_page_styles'] = '<style>' . self::renderHeadStyles( file_get_contents($strFile) ) . '</style>';
        }
    }



    public static function createDefaultStylesheet( $arrBodyClasses )
    {
        global $objPage; // TODO: CSS-Datei je Seite / Seitenbaum / Domain bzw. je einzelne Seite (wenn mehrere Seiten in einer installation)

        $objRootPage        = \PageModel::findByPk( $objPage->rootId );

        $arrPageStyles      = array();
        $objAllPages        = \PageModel::findAll(); //\PageModel::findPublishedByPid( $objPage->rootId, array("order"=>"sorting") );
        $createTime         = 0;
        $createFile         = FALSE;
        $objFile            = new \File('assets/css/page-styles.css');

        if( $objFile->exists() )
        {
            $createTime = $objFile->mtime;
        }

//        if( $objAllPages )
//        {
//            while( $objAllPages->next() )
//            {
        $objArticles = \ArticleModel::findAll();
//                $objArticles = \ArticleModel::findPublishedByPidAndColumn( $objAllPages->id, "main");

        if( $objArticles )
        {
            $count      = $objArticles->count();
            $zIndex     = 100 + (10 * $count);

            while( $objArticles->next() )
            {
                if( !$objArticles->published )
                {
                    continue;
                }

                if( $objArticles->articleType === "header" || $objArticles->articleType === "footer" || $objArticles->articleType === "ge" )
                {
                    continue;
                }

                if( $objArticles->tstamp > $createTime || ContentHelper::getArticleLastSave( $objArticles->id ) > $createTime )
                {
                    $createFile     = TRUE;
                }

                if( $objArticles->fullWidth )
                {
                    if( !preg_match('/content-width/', $objAllPages->cssClass) && !in_array('content-width', $arrBodyClasses))
                    {
                        $arrBodyClasses[] = 'content-width';
                    }
                }

                $cssID      = deserialize($objArticles->cssID, TRUE);
//                        $strImage   = '';
//                        $objImage   = \FilesModel::findByUuid( $objArticles->bgImage );
//
//                        if( $objImage && file_exists($this->rootDir . '/' . $objImage->path) )
//                        {
//                            $strImage = $objImage->path;
//                        }

                $addContainer   = '';
                $articleID      = (empty($cssID[0])? 'article-' . $objArticles->id : $cssID[0]);
                $artBgName      = $objArticles->id . '_background';

                if( preg_match('/bg-in-container/', $cssID[1]) )
                {
                    $addContainer = ' .background-container';
                }

                if( $objArticles->addDivider )
                {
                    $addContainer = ' .article-inside';
                }

                $arrPageStyles[ $artBgName ] = array
                (
                    'selector'          => '#container .mod_article#' . $articleID . $addContainer
                );

                $arrPageStyles[ $artBgName ] = array_merge($arrPageStyles[ $artBgName ], StylesheetHelper::getBackgroundStyles($objArticles->current()));
//                echo "<pre>"; print_r( $arrPageStyles ); echo "</pre>";
//                exit;

                if( $objArticles->addDivider )
                {
                    $arrPageStyles[ $artBgName . '_article' ] = array
                    (
                        'selector'  => '#container .mod_article#' . $articleID,
                        'own'       => $arrPageStyles[ $artBgName . '_article' ]['own'] . 'z-index:' . $zIndex . ';'
                    );

                    $bgColor = ColorHelper::compileColor( \StringUtil::deserialize($objArticles->bgColor, TRUE) );

                    if( $bgColor === 'transparent' )
                    {
                        $bgColor = '#fff';
                    }

                    switch( $objArticles->dividerStyle )
                    {
                        case "style1":
                            $arrPageStyles[ $objArticles->id . '_arrow-left' ]  = array
                            (
                                'selector'  => '.mod_article.has-article-divider#' . $articleID . ':before',
                                'own'       => 'background:linear-gradient(to left bottom, ' . $bgColor . ' 50%, transparent 50%);'
                            );

                            $arrPageStyles[ $objArticles->id . '_arrow-right' ] = array
                            (
                                'selector'  => '.mod_article.has-article-divider#' . $articleID . ':after',
                                'own'       => 'background:linear-gradient(to right bottom, ' . $bgColor . ' 50%, transparent 50%);'
                            );
                            break;

                        case "style2":
                            $objNextArticle = \ArticleModel::findOneBy(array('published=?', 'pid=?', 'inColumn=?', 'sorting>?'), array('1', $objArticles->pid, $objArticles->inColumn, $objArticles->sorting));
                            $nextBgColor    = $objNextArticle->bgColor;

                            $arrNextBgColor = \StringUtil::deserialize($nextBgColor, TRUE);

                            if( $arrNextBgColor[0] === '' && $arrNextBgColor[1] === '' )
                            {
                                $arrNextBgColor[0] = 'fff';

                                $nextBgColor = serialize( $arrNextBgColor );
                            }

                            $arrPageStyles[ $objArticles->id . '_bow-bottom_background' ] = array
                            (
                                'selector'      => '.mod_article.has-article-divider#' . $articleID . ':before',
                                'background'    => '1',
                                'bgcolor'       => $objArticles->bgColor
                            );

                            $arrPageStyles[ $objArticles->id . '_bow-bottom' ] = array
                            (
                                'selector'      => '.mod_article.has-article-divider#' . $articleID . ':after',
                                'background'    => '1',
                                'bgcolor'       => $nextBgColor
                            );
                            break;

                        case "style3":
                            $objNextArticle = \ArticleModel::findOneBy(array('published=?', 'pid=?', 'inColumn=?', 'sorting>?'), array('1', $objArticles->pid, $objArticles->inColumn, $objArticles->sorting));
                            $nextBgColor    = $objNextArticle->bgColor;

                            $arrNextBgColor = \StringUtil::deserialize($nextBgColor, TRUE);

                            if( $arrNextBgColor[0] === '' && $arrNextBgColor[1] === '' )
                            {
                                $arrNextBgColor[0] = 'fff';

                                $nextBgColor = serialize( $arrNextBgColor );
                            }

                            $arrPageStyles[ $objArticles->id . '_bow-bottom-top_background' ] = array
                            (
                                'selector'      => '.mod_article.has-article-divider#' . $articleID . ':before',
                                'background'    => '1',
                                'bgcolor'       => $nextBgColor
                            );

                            $arrPageStyles[ $objArticles->id . '_bow-bottom-top' ] = array
                            (
                                'selector'      => '.mod_article.has-article-divider#' . $articleID . ':after',
                                'background'    => '1',
                                'bgcolor'       => $objArticles->bgColor
                            );
                            break;

                        case "style4":
                        case "style5":
                            $arrPageStyles[ $objArticles->id . '_arrow-bottom' ] = array
                            (
                                'selector'  => '.mod_article.has-article-divider#' . $articleID . ':after',
                                'own'       => 'border-top-color:' . $bgColor . ';'
                            );
                            break;

                        case "style6":
                            $arrPageStyles[ $objArticles->id . '_bows-bottom' ] = array
                            (
                                'selector'  => '.mod_article.has-article-divider#' . $articleID . ':before,.mod_article.has-article-divider#' . $articleID . ':after',
                                'own'       => 'border-color:' . $bgColor . ';'
                            );
                            break;

                        case "style7":
                            $arrPageStyles[ $objArticles->id . '_clouds_background' ] = array
                            (
                                'selector'      => '.mod_article.has-article-divider#' . $articleID . ':before',
                                'background'    => '1',
                                'bgcolor'       => $objArticles->bgColor
                            );
                            break;
                    }
//                            echo "<pre>"; print_r( $arrPageStyles ); exit;
                }

                if( $objArticles->toNextArrow )
                {
                    $strArrowColor      = ColorHelper::compileColor( \StringUtil::deserialize($objArticles->toNextArrowColor, TRUE) );

                    if( $strArrowColor !== "transparent" )
                    {
                        $arrPageStyles[ $objArticles->id . '_next-arrow' ] = array
                        (
                            'selector'      => '.mod_article#' . $articleID . ' .arrow .arrow-inside-container:before,.mod_article#' . $articleID . ' .arrow .arrow-inside-container:after',
                            'background'    => '1',
                            'bgcolor'       => ColorHelper::renderColorConfig( $objArticles->toNextArrowColor )
                        );
                    }

                    $strArrowHoverColor     = ColorHelper::compileColor( \StringUtil::deserialize($objArticles->toNextArrowHoverColor, TRUE) );

                    if( $strArrowHoverColor !== "transparent" )
                    {
                        $arrPageStyles[ $objArticles->id . '_next-arrow-hover' ] = array
                        (
                            'selector'      => '.mod_article#' . $articleID . ' .arrow:hover .arrow-inside-container:before,.mod_article#' . $articleID . ' .arrow:hover .arrow-inside-container:after',
                            'background'    => '1',
                            'bgcolor'       => ColorHelper::renderColorConfig( $objArticles->toNextArrowHoverColor )
                        );
                    }
                }

//                        $bgColor        = deserialize($objArticles->bgColor, TRUE);
//                        $arrOwnStyles   = array();

//                if( !empty($bgColor[0]) )
//                {
//                    $rgb = ColorHelper::HTMLToRGB( $bgColor[0] );
//                    $hsl = ColorHelper::RGBToHSL( $rgb );
//
//                    if( $hsl->lightness < 200 )
//                    {
//                        $arrPageStyles[ $objArticles->id ]['font']      = TRUE;
//                        $arrPageStyles[ $objArticles->id ]['fontcolor'] = serialize(array('fff', ''));
//                    }
//                }

                $zIndex = ($zIndex - 10);
            }
        }
//            }
//        }
//exit;
//        $objHeader = \ArticleModel::findByAlias('ge_header_' . $objRootPage->alias);
        $objHeader = GlobalElementsHelper::getObject('header', $objRootPage->alias);

        if( $objHeader )
        {
            $objTopHeader = HeaderHelper::headerTopBarExists();

            if( $objTopHeader )
            {
                $arrTopHeaderStyles = StylesheetHelper::getGlobalElementStyles('topheader', $objTopHeader);

                if( ($objTopHeader->tstamp > $createTime || ContentHelper::getArticleLastSave( $objTopHeader->id ) > $createTime) && count($arrTopHeaderStyles) )
                {
                    $createFile     = TRUE;
                }

                $arrPageStyles[ 'topheader_' . $objTopHeader->id ] = $arrTopHeaderStyles;
            }

            $arrHeaderStyles = StylesheetHelper::getGlobalElementStyles('header', $objHeader);

            if( ($objHeader->tstamp > $createTime || ContentHelper::getArticleLastSave( $objHeader->id ) > $createTime) && count($arrHeaderStyles) )
            {
                $createFile     = TRUE;
            }

            if( is_array($arrHeaderStyles) && !isset($arrHeaderStyles['selector']) )
            {
                foreach($arrHeaderStyles as $num => $arrHeadStyle )
                {
                    $arrPageStyles[ 'header_' . $objHeader->id  . '_' . $num ] = $arrHeadStyle;
                }
            }
        }


//        $objFooter = \ArticleModel::findByAlias('ge_footer_' . $objRootPage->alias);
        $objFooter = GlobalElementsHelper::getObject('footer', $objRootPage->alias);

        if( $objFooter )
        {
            $arrFooterStyles = StylesheetHelper::getGlobalElementStyles('footer', $objFooter);

            if( ($objFooter->tstamp > $createTime || ContentHelper::getArticleLastSave( $objFooter->id ) > $createTime) && count($arrFooterStyles) )
            {
                $createFile     = TRUE;
            }

            $arrPageStyles[ 'footer_' . $objFooter->id ] = $arrFooterStyles;
        }

        if( count($arrPageStyles) && $createFile )
        {
            if( $objFile->exists() )
            {
                $objFile->delete();
            }

            $objStyleSheets     = new \StyleSheets();
            $arrStyles          = array();

            foreach($arrPageStyles as $arrPageStyle)
            {
                $bgColor = \StringUtil::deserialize($arrPageStyle['bgcolor'], TRUE);

                if( $bgColor[0] == "" && $bgColor[2] )
                {
                    $bgColor[0] = $bgColor[2];

                    $arrPageStyle['bgcolor'] = serialize($bgColor);
                }

                $arrStyles[] = $objStyleSheets->compileDefinition($arrPageStyle, true);
            }

            if( count($arrStyles) )
            {
                $writeToFile = FALSE;

                $objFile = new \File('assets/css/page-styles.css');
                $objFile->write("/* Auto generated File - by IIDO */\n");

                foreach($arrStyles as $strStyle)
                {
                    $strOnlyStyles = preg_replace('/#container .mod_article#([A-Za-z0-9\-_]{0,})\{([A-Za-z0-9\s\-\(\)\"\'\\,;.:\/_@]{0,})\}/', '$2', $strStyle);
                    $strOnlyStyles = preg_replace('/(#|)header\{\}/', '', $strOnlyStyles);
                    $strOnlyStyles = preg_replace('/(#|)footer\{\}/', '', $strOnlyStyles);

                    if( strlen(trim($strOnlyStyles)) )
                    {
                        $writeToFile = TRUE;
                        $objFile->append($strStyle, '');
                    }
                }

                $objFile->close();

                if( !$writeToFile )
                {
                    $objFile->delete();
                }
            }
        }

        $rootDir = BasicHelper::getRootDir();

        if( file_exists($rootDir . '/assets/css/page-styles.css') )
        {
            $GLOBALS['TL_CSS']['custom_page-styles'] = 'assets/css/page-styles.css||static';
        }

        if( file_exists($rootDir . '/files/' . $objRootPage->alias . '/css/theme.css') )
        {
            $GLOBALS['TL_CSS']['custom_theme'] = 'files/' . $objRootPage->alias . '/css/theme.css||static';
        }

        return $arrBodyClasses;
    }



    public static function renderUnits( $varValue )
    {
        $arrValue = \StringUtil::deserialize( $varValue );

        if( is_array($arrValue) )
        {
            $strUnit    = $arrValue['unit']?:'px';
            $varValue   = $arrValue['value'] . $strUnit;
        }

        return $varValue;
    }

}