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
class DownloadFilterElement extends \ContentElement
{

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'ce_iido_downloadFilter';



    /**
     * Generate the content element
     */
    protected function compile()
    {
        global $objPage;

        $objArticles    = \ArticleModel::findPublishedByPidAndColumn( $objPage->id, 'main');
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
                        if( $objElements->type === 'downloads' )
                        {
//                            $arrDownloads = FilesHelper::getMultipleFiles($objElements->multiSRC, $objElements->orderSRC)
                            $arrDownloads = \StringUtil::deserialize($objElements->orderSRC, TRUE);

                            if( count($arrDownloads) )
                            {
                                foreach($arrDownloads as $downloadID )
                                {
                                    $objFile    = \FilesModel::findByPk( $downloadID );
                                    $filterName = FilterHelper::renderFilter( $objFile->extension );

                                    $arrFilters['extension'][ $filterName ] = $objFile->extension;

//                                    $arrCurrentFilters = explode(',', $arrImage['meta']['categories']);

//                                    if( count($arrCurrentFilters) )
//                                    {
//                                        foreach($arrCurrentFilters as $strFilter)
//                                        {
//                                            if( strlen($strFilter) )
//                                            {
//                                                $arrFilters[] = $strFilter;
//                                            }
//                                        }
//                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

//        if( count($arrFilters) )
//        {
//            $arrListFilters = array();
//
//            foreach($arrFilters as $filterName => $arrLFilters)
//            {
//                $arrLFilters = array_unique($arrLFilters);
//                $arrLFilters = array_values($arrLFilters);
//
//                foreach($arrLFilters as $strFilter)
//                {
//                    $filterName = FilterHelper::renderFilter( $strFilter );
//
//                    $arrListFilters[ $filterName ] = $strFilter;
//                }
//            }
//        }

//        $this->Template->filters = $arrListFilters;
        $this->Template->filters = $arrFilters;
    }
}
