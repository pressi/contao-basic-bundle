<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\Helper;


class ContentHelper
{

    public static function generateImageHoverTags( $strContent, $objRow )
    {
        if( !preg_match('/image-hover-container/', $strContent) )
        {
            $hoverTags = '<div class="image-hover-container"><div class="image-hover-inside"></div></div>';

            if( $objRow->caption || preg_match('/figcaption/', $strContent) )
            {
                $strContent = preg_replace('/<\/a>([\s\n]{0,})<figcaption/' , $hoverTags . '</a>$1<figcaption', $strContent);
            }
            else
            {
                $strContent = preg_replace('/<\/a>([\s\n]{0,})<\/figure>/' , $hoverTags . '</a>$1</figure>', $strContent);
            }
        }

        return $strContent;
    }



    public static function renderText( $strText, $renderLines = false )
    {
        $strText = preg_replace(array('/&#40;/', '/&#41;/'), array('(', ')'), $strText);

        $strText = preg_replace('/;/', '<br>', $strText);
        $strText = preg_replace('/\|\|([^\|\|]+)\|\|/', '<span class="light">$1</span>', $strText);
        $strText = preg_replace('/\|([^\|]+)\|/', '<strong>$1</strong>', $strText);

        $strText = preg_replace('/\{\{sup\}\}/', '<sup>', $strText);
        $strText = preg_replace('/\{\{\/sup\}\}/', '</sup>', $strText);

        $strText = preg_replace('/\{\{sub\}\}/', '<sub>', $strText);
        $strText = preg_replace('/\{\{\/sub\}\}/', '</sub>', $strText);

        if( $renderLines )
        {
            $delimiter = '<br>';

            if( !preg_match('/' . $delimiter . '/', $strText) )
            {
                $delimiter = '{{br}}';

                if( !preg_match('/' . $delimiter . '/', $strText) )
                {
                    $delimiter = ';';
                }
            }

            $arrText = explode($delimiter, $strText);
            $strText = '<span class="text-line">' . implode('</span><br><span class="text-line">', $arrText) . '</span>';
        }

        return $strText;
    }



    public static function renderPosition( $objClass )
    {
        $strClass       = "";
        $strStyles      = "";

        if( $objClass->position )
        {
            $strClass = 'pos-abs pos-' . str_replace('_', '-', $objClass->position);
        }

        $arrPosMargin = deserialize($objClass->positionMargin, TRUE);

        if( $arrPosMargin['top'] || $arrPosMargin['right'] || $arrPosMargin['bottom'] || $arrPosMargin['left'] )
        {
            $unit = $arrPosMargin['unit']?:'px';

            if( $arrPosMargin['top'] )
            {
                $strStyles .= " margin-top:" . $arrPosMargin['top'] . $unit . ";";
            }

            if( $arrPosMargin['right'] )
            {
                $strStyles .= " margin-right:" . $arrPosMargin['right'] . $unit . ";";
            }

            if( $arrPosMargin['bottom'] )
            {
                $strStyles .= " margin-bottom:" . $arrPosMargin['bottom'] . $unit . ";";
            }

            if( $arrPosMargin['left'] )
            {
                $strStyles .= " margin-left:" . $arrPosMargin['left'] . $unit . ";";
            }
        }

        return array( $strClass, $strStyles);
    }



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

                $strContent = preg_replace('/\/\*#' . preg_quote($varName, '/') . '#\*\/' . $add . '/', $varValue, $strContent);
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

        $strContent     = preg_replace('/\/\*#' . preg_quote($varName, '/') . '_darker#\*\/' . $add . '/', $varValueDark, $strContent);
        $strContent     = preg_replace('/\/\*#' . preg_quote($varName, '/') . '_lighter#\*\/' . $add . '/', $varValueLight, $strContent);

        $rgb = ColorHelper::convertHexColor($varValue);

        if( count($rgb) )
        {
            $rgba = 'rgba(' . $rgb['red'] . ',' . $rgb['green'] . ',' . $rgb['blue'] . ',';

            $strContent = preg_replace('/\/\*#' . preg_quote($varName, '/') . '_trans95#\*\/' . $add . '/', $rgba . '0.95)', $strContent);
            $strContent = preg_replace('/\/\*#' . preg_quote($varName, '/') . '_trans90#\*\/' . $add . '/', $rgba . '0.9)', $strContent);
            $strContent = preg_replace('/\/\*#' . preg_quote($varName, '/') . '_trans85#\*\/' . $add . '/', $rgba . '0.85)', $strContent);
            $strContent = preg_replace('/\/\*#' . preg_quote($varName, '/') . '_trans80#\*\/' . $add . '/', $rgba . '0.8)', $strContent);
            $strContent = preg_replace('/\/\*#' . preg_quote($varName, '/') . '_trans75#\*\/' . $add . '/', $rgba . '0.75)', $strContent);
            $strContent = preg_replace('/\/\*#' . preg_quote($varName, '/') . '_trans70#\*\/' . $add . '/', $rgba . '0.7)', $strContent);
            $strContent = preg_replace('/\/\*#' . preg_quote($varName, '/') . '_trans65#\*\/' . $add . '/', $rgba . '0.65)', $strContent);
            $strContent = preg_replace('/\/\*#' . preg_quote($varName, '/') . '_trans60#\*\/' . $add . '/', $rgba . '0.6)', $strContent);
            $strContent = preg_replace('/\/\*#' . preg_quote($varName, '/') . '_trans55#\*\/' . $add . '/', $rgba . '0.55)', $strContent);
            $strContent = preg_replace('/\/\*#' . preg_quote($varName, '/') . '_trans50#\*\/' . $add . '/', $rgba . '0.5)', $strContent);
            $strContent = preg_replace('/\/\*#' . preg_quote($varName, '/') . '_trans45#\*\/' . $add . '/', $rgba . '0.45)', $strContent);
            $strContent = preg_replace('/\/\*#' . preg_quote($varName, '/') . '_trans40#\*\/' . $add . '/', $rgba . '0.4)', $strContent);
            $strContent = preg_replace('/\/\*#' . preg_quote($varName, '/') . '_trans35#\*\/' . $add . '/', $rgba . '0.35)', $strContent);
            $strContent = preg_replace('/\/\*#' . preg_quote($varName, '/') . '_trans30#\*\/' . $add . '/', $rgba . '0.3)', $strContent);
            $strContent = preg_replace('/\/\*#' . preg_quote($varName, '/') . '_trans25#\*\/' . $add . '/', $rgba . '0.25)', $strContent);
            $strContent = preg_replace('/\/\*#' . preg_quote($varName, '/') . '_trans20#\*\/' . $add . '/', $rgba . '0.2)', $strContent);
            $strContent = preg_replace('/\/\*#' . preg_quote($varName, '/') . '_trans15#\*\/' . $add . '/', $rgba . '0.15)', $strContent);
            $strContent = preg_replace('/\/\*#' . preg_quote($varName, '/') . '_trans10#\*\/' . $add . '/', $rgba . '0.1)', $strContent);
        }

        return $strContent;
    }



    public static function renderHeadStyles( $strStyles )
    {
        return self::renderStyleVars( $strStyles );
    }



    public function generateImageTag( $strImage, $arrImageSize = array() )
    {
        return ImageHelper::getImageTag( $strImage, $arrImageSize );
    }



    public static function getArticleLastSave( $articleID )
    {
        $objResult = \Database::getInstance()->prepare("SELECT * FROM tl_version WHERE fromTable=? AND pid=? ORDER BY tstamp DESC LIMIT 1")->execute("tl_article", $articleID);

        if( $objResult->numRows > 0 )
        {
            $objResult = $objResult->first();

            return $objResult->tstamp;
        }

        return 0;
    }



    public static function getArticleID( $articleID )
    {
        $objArticle = \ArticleModel::findByPk( $articleID );

        $cssID  = \StringUtil::deserialize($objArticle->cssID, TRUE);
        $strID  = trim($cssID[0]);

        if( !strlen($strID) )
        {
            $strID = 'article-' . $articleID;
        }

        return $strID;
    }

}