<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace IIDO\BasicBundle\ContentElement;

use IIDO\BasicBundle\Helper\ImageHelper;


/**
 * Front end content element "text".
 *
 * @author Leo Feyer <https://github.com/leofeyer>
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
