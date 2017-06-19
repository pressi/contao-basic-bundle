<?php
/*******************************************************************
 * (c) 2017 Stephan Preßl, www.prestep.at <development@prestep.at>
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
        $hoverTags = '<div class="image-hover-container"></div>';

        if( $objRow->caption || preg_match('/figcaption/', $strContent) )
        {
            $strContent = preg_replace('/<\/a>([\s\n]{0,})<figcaption/' , $hoverTags . '</a>$1<figcaption', $strContent);
        }
        else
        {
            $strContent = preg_replace('/<\/a>([\s\n]{0,})<\/figure>/' , $hoverTags . '</a>$1</figure>', $strContent);
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

        $arrPosMargin = deserialize($objClass->position_margin, TRUE);

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

}