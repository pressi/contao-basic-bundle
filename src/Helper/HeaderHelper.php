<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\Helper;


/**
 * Description
 *
 */
class HeaderHelper
{
    static $headerAlias = 'header';
    static $headerTopBarAlias = 'topheader';



    public static function renderHeader( $strContent )
    {
        $arrData = self::getHeaderData();

        $replaceTag = $replacedTag = '<header';

        $strClasses     = $arrData['class'];
        $strAttributes  = $arrData['attributes'];

        $objTopHeader   = self::headerTopBarExists();

        if( !$objTopHeader )
        {
            $strClasses = ' class="' . $strClasses . '"';

            $strContent = preg_replace('/' . $replaceTag . '/', $replacedTag . $strClasses . $strAttributes, $strContent);
        }
        else
        {
//            $replaceTag     = '<div([A-Za-z0-9\s\-=",;.:_\(\)]{0,})class="header-bar';
//            $replacedTag    = '<div$1class="header-bar ';

//            $arrTopHeaderData = self::getHeaderData( false, self::$headerTopBarAlias );

//            $strTopClasses      = $arrTopHeaderData['class'];
//            $strTopAttributes   = $arrTopHeaderData['attributes'];

//            $strContent = preg_replace('/<div([A-Za-z0-9\s\-=",;.:_\(\)]{0,})class="header-top-bar/', '<div$1class="header-top-bar ' . $strTopClasses . $strTopAttributes, $strContent);
        }

//        $strContent = preg_replace('/' . $replaceTag . '/', $replacedTag . $strClasses . $strAttributes, $strContent);

        return $strContent;
    }



    public static function getHeaderData( $returnAsArray = false, $alias = 'header' )
    {
        $objHeaderArticle   = \ArticleModel::findByAlias('ge_' . $alias . '_' . BasicHelper::getRootPageAlias());

        if( $objHeaderArticle )
        {
            $headerClasses      = \StringUtil::deserialize($objHeaderArticle->cssID, true)[1];
            $arrClasses         = explode(" ", $headerClasses);
            $arrAttributes      = array();

            if( $objHeaderArticle->isFixed )
            {
                if( !$objHeaderArticle->isAbsolute )
                {
                    $headerClasses = trim($headerClasses . ' is-fixed');
                    $arrClasses[] = 'is-fixed';
                }
                else
                {
                    $headerClasses = trim($headerClasses . ' pos-abs');
                    $arrClasses[] = 'pos-abs';
                }

                if( $objHeaderArticle->enableSticky )
                {
                    $headerClasses = trim($headerClasses . ' is-sticky-element');
                    $arrClasses[] = 'is-sticky-element';
                }

                $strPosition    = $objHeaderArticle->position;

                if( $strPosition === "top" )
                {
                    $headerClasses = trim($headerClasses . ' pos-top');
                    $arrClasses[] = 'pos-top';
                }
                elseif( $strPosition === "right" )
                {
                    $headerClasses = trim($headerClasses . ' pos-right');
                    $arrClasses[] = 'pos-right';
                }
                elseif( $strPosition === "bottom" )
                {
                    $headerClasses = trim($headerClasses . ' pos-bottom');
                    $arrClasses[] = 'pos-bottom';
                }
                elseif( $strPosition === "left" )
                {
                    $headerClasses = trim($headerClasses . ' pos-left');
                    $arrClasses[] = 'pos-left';
                }

                $arrPositionStyles = BasicHelper::renderPosition( $objHeaderArticle, 'positionMargin', true , true );

                if( count($arrPositionStyles) )
                {
                    $positionStyles = '';
                    $dataOffset     = 0;

                    foreach( $arrPositionStyles as $posKey => $posValue )
                    {
                        if( $posKey === "margin-top" && $objHeaderArticle->enableSticky )
                        {
                            $dataOffset = $dataOffset + (int) preg_replace('/(px)$/', '', $posValue);
                        }

//                        if( $posKey === "margin-bottom" && $objHeaderArticle->enableSticky )
//                        {
//                            $dataOffset = $dataOffset + (int) preg_replace('/(px)$/', '', $posValue);
//                        }

                        $positionStyles .= $posKey . ':' . $posValue . ';';
                    }

                    if( $dataOffset > 0 )
                    {
//                        $arrAttributes['data-offset']   = $dataOffset;
                        $arrAttributes['data-wrapper']  = "no";
                    }

                    $arrAttributes['style'] = trim($arrAttributes['style'] . ' ' . $positionStyles);
                }

                $arrHeaderWidth = \StringUtil::deserialize($objHeaderArticle->articleWidth, TRUE);

                if( $arrHeaderWidth['value'] )
                {
                    $arrAttributes['style'] = trim($arrAttributes['style'] . ' width:' . $arrHeaderWidth['value'] . ($arrHeaderWidth['unit'] . ';' ? : 'px;'));
                }

                $arrHeaderHeight = \StringUtil::deserialize($objHeaderArticle->articleHeight, TRUE);

                if( $arrHeaderHeight['value'] )
                {
                    $arrAttributes['style'] = trim($arrAttributes['style'] . ' height:' . $arrHeaderHeight['value'] . ($arrHeaderHeight['unit'] . ';' ? : 'px;'));
                }
            }

            if( count($arrAttributes) || count($arrClasses) )
            {
                $strAttributes = '';

                if( count($arrAttributes) && !$returnAsArray )
                {
                    if( count($arrAttributes) )
                    {
                        foreach($arrAttributes as $key => $value)
                        {
                            $strAttributes .= ' ' . $key . '="' . $value . '"';
                        }
                    }
                }

//            if( !$objTopHeaderArticle )
//            {
                return array
                (
                    'class'         => $returnAsArray ? $arrClasses : $headerClasses,
                    'attributes'    => $returnAsArray ? $arrAttributes : $strAttributes
                );
//            }
            }
        }

        return false;
    }



    public static function getTopHeaderData( $returnAsArray = false )
    {
        return self::getHeaderData( $returnAsArray , self::$headerTopBarAlias);
    }



    public static function headerTopBarExists()
    {
        return \ArticleModel::findByAlias("ge_" . self::$headerTopBarAlias . "_" . BasicHelper::getRootPageAlias());
    }



    public static function isHeaderIsSticky()
    {
        return self::isStickyArticle( self::$headerAlias );
    }



    public static function isTopHeaderIsSticky()
    {
        return self::isStickyArticle( self::$headerTopBarAlias );
    }



    public static function isStickyArticle( $alias )
    {
        $objArticle = \ArticleModel::findByAlias("ge_" . $alias . "_" . BasicHelper::getRootPageAlias());

        if( $objArticle && $objArticle->enableSticky )
        {
            return true;
        }

        return false;
    }
}
