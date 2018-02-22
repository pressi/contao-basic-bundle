<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\ContentElement;


use IIDO\BasicBundle\Helper\ImageHelper;


/**
 * Front end content element "article teaser".
 *
 * @package IIDO\BasicBundle
 * @author Stephan Preßl <development@prestep.at>
 */
class ArticleTeaserElement extends \ContentElement
{

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'ce_iido_articleTeaser';



    /**
     * Generate the content element
     */
    protected function compile()
    {
        global $objPage;

        $arrArticles    = array();
        $objArticles    = \ArticleModel::findPublishedByPidAndColumn( $objPage->id, "main");

        if( $objArticles )
        {
            while( $objArticles->next() )
            {
                if( $objArticles->hideInMenu || $objArticles->noContent )
                {
                    continue;
                }

                $objArticle     = $objArticles->current();
                $arrHeadline    = \StringUtil::deserialize( $objArticle->teaserHeadline, TRUE );
                $arrImages      = ImageHelper::getMultipleImages( $objArticle->teaserMultiSRC, $objArticle->orderSRC );

                if( is_array($arrHeadline) && count($arrHeadline) )
                {
                    $objArticle->teaserHeadline = $arrHeadline['value'];
                }

                $objArticle->teaserImages   = $arrImages;

                $arrArticles[] = $objArticle;
            }
        }

        $this->Template->articles = $arrArticles;
    }
}
