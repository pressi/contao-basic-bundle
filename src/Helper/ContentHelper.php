<?php
declare(strict_types=1);

/*******************************************************************
 * (c) 2020 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\Helper;


use Contao\Config;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\System;
use IIDO\BasicBundle\Config\BundleConfig;


class ContentHelper
{
    public static function getElementClass( $objRow )
    {
        $elementType    = $objRow->type;
        $elementClass   = $objRow->typePrefix . $elementType;

        if( $elementType === "module" )
        {
            $objModule = \ModuleModel::findByPk( $objRow->module );

            if( $objModule )
            {
                $elementClass = 'mod_' . $objModule->type;
            }
        }

        if( FALSE !== strpos( $elementType, 'rocksolid' ) )
        {
            $elementClass = 'mod_' . $elementType;
        }

        if( $elementClass === 'ce_alias' )
        {
            $objAliasElement    = \ContentModel::findByPk ( $objRow->cteAlias );
            $elementClass       = 'ce_' . $objAliasElement->type;
        }

        if( 0 === strpos( $elementClass, 'rsce_' ) )
        {
            $elementClass = 'ce_' . $elementClass;
        }

//        if( $elementClass === "ce_iido_navigation" )
//        {
//            $elementClass = 'mod_navigation';
//        }

        if( $elementType === 'newslist' || $elementType === 'eventlist' || $elementType === 'newscategories' )
        {
            $elementClass = preg_replace('/^ce_/', 'mod_', $elementClass);
        }

        if( 0 !== strpos($elementClass, 'ce_') && 0 !== strpos($elementClass, 'mod_') )
        {
            $elementClass = 'ce_' . $elementClass;
        }

        return $elementClass;
    }



    public static function addClassToElement( $strContent, $objRow, array $arrClasses ): string
    {
        if( count($arrClasses) )
        {
            $elementClass       = self::getElementClass( $objRow );

            $elementClassNew    = preg_replace('/(\s{2,})/', ' ', $elementClass);
            $newElementClasses  = preg_replace('/(\s{2,})/', ' ', ' ' . implode(' ', $arrClasses));

            $strContent     = preg_replace('/class="' . $elementClass . '/', 'class="' . trim($elementClassNew) . $newElementClasses, $strContent);
        }

        return $strContent;
    }



    public static  function renderText( $strText, $renderLines = false, $dontUseBr = false, $addWrapper = false )
    {
        $strText = str_replace(['&lt;', '&#62;', '&#61;'], ['<', '>', '='], $strText );

//        $strText = preg_replace(array('/&#40;/', '/&#41;/'), array('(', ')'), $strText);
//
//        $strText = preg_replace('/;/', '<br>', $strText);
//        $strText = preg_replace('/\|\|([^\|\|]+)\|\|/', '<span class="light">$1</span>', $strText);
        $strText = preg_replace('/\|([^\|]+)\|/', '<strong>$1</strong>', $strText);
        $strText = preg_replace('/:([^\|]+):/', '<span class="underline">$1</span>', $strText);
//
//        $strText = preg_replace('/\{\{sup\}\}/', '<sup>', $strText);
//        $strText = preg_replace('/\{\{\/sup\}\}/', '</sup>', $strText);
//
//        $strText = preg_replace('/\{\{sub\}\}/', '<sub>', $strText);
//        $strText = preg_replace('/\{\{\/sub\}\}/', '</sub>', $strText);

        $strText = StringUtil::toHtml5( $strText );

        if( $staticUrl = System::getContainer()->get('contao.assets.files_context')->getStaticUrl() )
        {
            $path       = Config::get('uploadPath') . '/';
            $strText    = str_replace(' src="' . $path, ' src="' . $staticUrl . $path, $strText);
        }

        $strText = StringUtil::encodeEmail( $strText );

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
            $tagName = 'span';

            if( $dontUseBr )
            {
                $tagName = 'div';
            }

            if( count($arrText) === 1 )
            {
                $strText = $arrText[0];
            }
            else
            {
                $strText = '<' . $tagName . ' class="text-line">' . implode('</' . $tagName . '>' . ($dontUseBr ? '' : '<br>') . '<' . $tagName . ' class="text-line">', $arrText) . '</' . $tagName . '>';
            }
        }
        elseif( $addWrapper )
        {
            $strText = '<span class="text-line">' . $strText . '</span>';
        }

        return $strText;
    }



    public static function addAttributesToContentElement( $strContent, $objRow, array $arrAttributes)
    {
        $strAttributes = '';

        foreach($arrAttributes as $attributeName => $attributeValue)
        {
            if( !is_array($attributeValue) )
            {
                $attributeValue = array($attributeValue);
            }

            $strAttributes .= $attributeName . '="' . implode("", $attributeValue) . '"';
        }

        $elementClass   = self::getElementClass( $objRow );
        $strContent     = preg_replace('/class="' . $elementClass . '/', $strAttributes . ' class="' . $elementClass, $strContent);

        return $strContent;
    }
}