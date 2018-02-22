<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\ContentElement;


use IIDO\BasicBundle\Helper\FilterHelper;
use IIDO\BasicBundle\Helper\ImageHelper;


/**
 * Front end content element "files filter".
 *
 * @package IIDO\BasicBundle
 * @author Stephan Preßl <development@prestep.at>
 */
class FilesFilterElement extends \ContentElement
{

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'ce_iido_filesFilter';



    /**
     * Generate the content element
     */
    protected function compile()
    {
        global $objPage;

        $objArticles    = \ArticleModel::findPublishedByPidAndColumn( $objPage->id, "main");
        $arrFilters     = array();
        $arrListFilters = array();

        if( $objArticles )
        {
            while( $objArticles->next() )
            {
                $objElements = \ContentModel::findPublishedByPidAndTable( $objArticles->id, "tl_article");

                if( $objElements )
                {
                    while( $objElements->next() )
                    {
                        if( $objElements->type === "gallery" && $objElements->galleryTpl === "gallery_filter" )
                        {
                            $arrImages = ImageHelper::getMultipleImages($objElements->multiSRC, $objElements->orderSRC);

                            if( count($arrImages) )
                            {
                                foreach($arrImages as $arrImage )
                                {
                                    $arrCurrentFilters = explode(',', $arrImage['meta']['categories']);

                                    if( count($arrCurrentFilters) )
                                    {
                                        foreach($arrCurrentFilters as $strFilter)
                                        {
                                            if( strlen($strFilter) )
                                            {
                                                $arrFilters[] = $strFilter;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        if( count($arrFilters) )
        {
            $arrFilters = array_unique($arrFilters);
            $arrFilters = array_values($arrFilters);

            $arrListFilters = array();

            foreach($arrFilters as $strFilter)
            {
                $filterName = FilterHelper::renderFilter( $strFilter );

                $arrListFilters[ $filterName ] = $strFilter;
            }
        }

        $this->Template->filters = $arrListFilters;
    }
}
