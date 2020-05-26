<?php
/*******************************************************************
 * (c) 2020 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\Helper;


use Contao\Model\Collection;


/**
 * Class Helper
 * @package IIDO\BasicBundle
 */
class FilterHelper extends \Frontend
{

    public static function renderFilter( $strName )
    {
//        $GLOBALS['TL_JAVASCRIPT']['isotope'] = '';

        $strName = strtolower( preg_replace(array('/ & /', '/ /'), array('_and_', '_'), $strName) );

        return $strName;
    }



    public static function renderFilterName( $filterName )
    {
        $filterName = strtolower( preg_replace(array('/ß/', '/ü/', '/ö/', '/ä/', '/ &amp; /', '/ & /', '/&#40;/', '/&#41;/', '/\(/', '/\)/', '/, /', '/\s/', '/Ä/', '/Ü/', '/Ö/', '/ /', '/\./'), array('ss', 'ue', 'oe', 'ae', '_and_', '_and_', '', '', '', '', ',', '_', 'Ae', 'Ue', 'Oe', '_', ''), trim($filterName)) );

        return trim($filterName);
    }

    public static function getProjectFilters( $pid, $itemsInNextArticle = false )
    {
        return self::getElementFilters( $pid, 'rsce_project', $itemsInNextArticle);
    }

    public static function getTypeFilters( $pid, $itemType, $itemsInNextArticle = false )
    {
        return self::getElementFilters( $pid, $itemType, $itemsInNextArticle);
    }



    public static function getElementFilters( $pid, $type = 'rsce_project', $itemsInNextArticle = false )
    {
        $subfilters     = array();
        $mainfilters    = array();

        $objElements    = false;

        if( !$itemsInNextArticle )
        {
            $objElements    = \ContentModel::findBy(array('pid=?', 'type=?'), array($pid, $type));
        }
        else
        {
            global $objPage;

            $arrElements    = array();
            $objArticles    = \ArticleModel::findPublishedByPidAndColumn( $objPage->id, 'main' );

            if( $objArticles )
            {
                $loadArticle = false;

                while( $objArticles->next() )
                {
                    if( $objArticles->id === $pid )
                    {
                        $loadArticle = true;
                        continue;
                    }

                    if( $loadArticle )
                    {
                        $objArticleElements = \ContentModel::findBy(array('pid=?', 'type=?'), array($objArticles->id, $type));

                        if( $objArticleElements )
                        {
                            while( $objArticleElements->next() )
                            {
                                if( !$objArticleElements->invisible )
                                {
                                    $arrElements[] = $objArticleElements->current();
                                }
                            }
                        }
                    }
                }
            }

            if( count($arrElements) )
            {
                $objElements = new Collection( $arrElements, \ContentModel::getTable() );
            }
        }

        if( $objElements )
        {
            while( $objElements->next() )
            {
                $arrData = array();

                if ($objElements->rsce_data && substr($objElements->rsce_data, 0, 1) === '{')
                {
                    $arrData = json_decode($objElements->rsce_data);
                }

                $arrData = BasicHelper::deserializeDataRecursive($arrData);

                $mainFilter     = trim(self::renderFilterName( $arrData->mainFilter ));
                $arrSubFilter   = explode(",", $arrData->subFilter);

                if( $mainFilter )
                {
                    $mainfilters[ $mainFilter ] = $arrData->mainFilter;
                }

                foreach($arrSubFilter as $strSubFilter)
                {
                    $subFilter  = trim(self::renderFilterName( trim($strSubFilter) ));

                    if( $subFilter )
                    {
                        if( in_array($subFilter, $subfilters) )
                        {
                            if( !in_array($mainFilter, $subfilters[ $subFilter ]['mainFilter']) )
                            {
                                $subfilters[ $subFilter ]['mainFilter'][] = $mainFilter;
                            }
                        }
                        else
                        {
                            $subfilters[ $subFilter ] = array
                            (
                                'label'         => $strSubFilter,
                                'mainFilter'    => array($mainFilter)
                            );
                        }
                    }
                }
            }
        }

        return array($mainfilters, $subfilters);
    }



    public static function getFilters( $pid, $type, $fieldName = 'mainFilter', $hasSubfilter = false, $subfilterName = 'subFilter' )
    {
        \Controller::loadDataContainer("tl_content");
        $subfilters     = array();
        $mainfilters    = array();

        $objElements    = \ContentModel::findBy(array('pid=?', 'type=?'), array($pid, $type));
        $arrConfig      = glob(TL_ROOT . '/templates/*/' . $type . '_config.php');

        $arrFileConfig  = include  $arrConfig[0];
        $arrFieldConfig = $arrFileConfig['fields'][ $fieldName ];

        if( $objElements )
        {
            while( $objElements->next() )
            {
                $arrData = array();

                if ($objElements->rsce_data && substr($objElements->rsce_data, 0, 1) === '{')
                {
                    $arrData = json_decode($objElements->rsce_data);
                }

                $arrData        = BasicHelper::deserializeDataRecursive($arrData);

                if( $arrFieldConfig['eval']['multipleTags'] )
                {
                    $arrMainFiltter = explode(",", $arrData->$fieldName);

                    if( count($arrMainFiltter) )
                    {
                        foreach($arrMainFiltter as $strMainFilter)
                        {
                            $mainFilter     = self::renderFilterName( trim($strMainFilter) );

                            if( $mainFilter )
                            {
                                $mainfilters[ $mainFilter ] = $strMainFilter;
                            }
                        }
                    }
                }
                else
                {
                    $mainFilter     = trim(self::renderFilterName( $arrData->$fieldName ));

                    if( $mainFilter )
                    {
                        $mainfilters[ $mainFilter ] = $arrData->$fieldName;
                    }
                }


                if( $hasSubfilter )
                {
                    $arrSubFilter   = explode(",", $arrData->$subfilterName);

                    foreach($arrSubFilter as $strSubFilter)
                    {
                        $subFilter  = trim(self::renderFilterName( trim($strSubFilter) ));

                        if( $subFilter )
                        {
                            if( in_array($subFilter, $subfilters) )
                            {
                                if( !in_array($mainFilter, $subfilters[ $subFilter ][ $fieldName ]) )
                                {
                                    $subfilters[ $subFilter ][ $fieldName ][] = $mainFilter;
                                }
                            }
                            else
                            {
                                $subfilters[ $subFilter ]['label']      = $strSubFilter;
                                $subfilters[ $subFilter ][$fieldName]   = array($mainFilter);
                            }
                        }
                    }
                }
            }
        }

        return $hasSubfilter ? array($mainfilters, $subfilters) : $mainfilters;
    }



//    public static function getElementFilters( $id, $type, $fieldName = 'mainFilter', $hasSubfilter = false, $subfilterName = 'subFilter' )
//    {
//
//    }

}
