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

                if( $pageColor != "transparent" )
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

                        if( $pageColor != "transparent" )
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

                                if( $pageColor != "transparent" )
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



    public static function replaceDefaultVars( $strContent )
    {
//        global $objPage;

        preg_match_all('/\/\*#[^*]+#\*\//', $strContent, $arrChunks);

        foreach ($arrChunks[0] as $strChunk)
        {
            $strKey = strtolower(substr($strChunk, 3, -3));

            switch( $strKey )
            {
                case "page_content_width":
                    $strContent = str_replace($strChunk . '1px', '100%', $strContent);
                    break;

//                case "page_color":
//                    $pageColor  = ColorHelper::getPageColor( $objPage );
//                    $strContent = str_replace($strChunk . '#fff', $pageColor, $strContent);
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
        }

        return $strContent;
    }



    public static function renderHeadStyles( $strStyles )
    {
        return self::renderStyleVars( $strStyles );
    }



    public static function getGlobalElementStyles( $mode, $objData )
    {
        $arrStyles = array();

        if( $mode === "header" || $mode === "footer" )
        {
            $arrStyles = array
            (
                'selector'      => $mode,
            );

            if( $objData->isFixed )
            {
                $arrPosition    = array();
                $articleWidth   = \StringUtil::deserialize($objData->articleWidth, true);
                $articleHeight  = \StringUtil::deserialize($objData->articleHeight, true);

                $arrStyles = array
                (
                    'selector'      => $mode . '.is-fixed',

                    'positioning'   => true,
                    'position'      => 'fixed'
                );

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

                if( $objData->position === "top" )
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
                        $arrPosition['botom']   = '0';
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
                        $arrPosition['botom']   = '0';
                    }
                }

                $arrPosition['unit'] = 'px';

                $arrStyles['trbl']    = serialize($arrPosition);
            }

            $arrStyles = array_merge($arrStyles, StylesheetHelper::getBackgroundStyles($objData));

            if( $objData->isFixed )
            {
                if( !preg_match('/z-index/', $arrStyles['own']) )
                {
                    $arrStyles['own']     = $arrStyles['own'] . 'z-index:900;';
                }
            }
        }

        return $arrStyles;
    }



    public static function getBackgroundStyles($objArticle, $onlyOwnStyles = false, $returnAsArray = true, $writeInFile = false, $selector = '')
    {
        $arrOwnStyles       = array();
        $arrBackgroundSize  = deserialize($objArticle->bgSize, true);

        if( is_array($arrBackgroundSize) && strlen($arrBackgroundSize[2]) && $arrBackgroundSize[2] != '-' )
        {
            $bgSize = $arrBackgroundSize[2];

            if( $arrBackgroundSize[2] == 'own' )
            {
                unset($arrBackgroundSize[2]);
                $bgSize = implode(" ", $arrBackgroundSize);
            }

            $arrOwnStyles[] = '-webkit-background-size:' . $bgSize . ';-moz-background-size:' . $bgSize . ';-o-background-size:' . $bgSize . ';background-size:' . $bgSize . ';';
        }

        if( $objArticle->bgAttachment )
        {
            $arrOwnStyles[] = 'background-attachment:' . $objArticle->bgAttachment . ';';
        }

        if( $onlyOwnStyles )
        {
            return $returnAsArray ? $onlyOwnStyles : implode("", $arrOwnStyles);
        }

        $rootDir    = dirname(\System::getContainer()->getParameter('kernel.root_dir'));

        $strImage   = '';
        $objImage   = \FilesModel::findByUuid( $objArticle->bgImage );

        if( $objImage && file_exists($rootDir . '/' . $objImage->path) )
        {
            $strImage = $objImage->path;
        }

        $arrStyles = array
        (
            'background'        => TRUE,
            'bgcolor'           => $objArticle->bgColor,
            'bgimage'           => $strImage,
            'bgrepeat'          => $objArticle->bgRepeat,
            'bgposition'        => $objArticle->bgPosition,
            'gradientAngle'     => $objArticle->gradientAngle,
            'gradientColors'    => $objArticle->gradientColors
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



    public static function getStylesheetSource( $scriptName, $public = false )
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

        return BundleConfig::getBundlePath( $public ) . ($public ? self::$stylesheetPathPublic : self::$stylesheetPath) . $scriptName . '/' . $folderVersion . '/' . $fileName;
    }



    public static function getStylesheetMode()
    {
        global $objPage;

        $objLayout = BasicHelper::getPageLayout( $objPage );

        return ($objLayout->combineScripts ? self::$stylesheetModeCombine : '');
    }

}