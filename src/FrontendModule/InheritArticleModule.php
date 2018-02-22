<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\FrontendModule;



/**
 * Frontend Module: News List
 *
 * @author Stephan Preßl <development@prestep.at>
 */
class InheritArticleModule extends \Module
{

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'mod_iido_inherit';



    /**
     * Display a wildcard in the back end
     *
     * @return string
     */
    public function generate()
    {
        return parent::generate();
    }



    /**
     * Generate the module
     */
    protected function compile()
    {
        global $objPage;

        $inherit        = true;
        $objArticles    = \ArticleModel::findPublishedByPidAndColumn( $objPage->id, $this->inheritColumn);

        if( $objArticles )
        {
            while( $objArticles->next() )
            {
                $objElements = \ContentModel::findPublishedByPidAndTable( $objArticles->id, "tl_article");

                if( $objElements )
                {
                    $inherit = false;
                }
            }
        }

        if( $inherit )
        {
            if( $objPage->pid !== $objPage->rootId )
            {
                $this->Template->content = $this->getElementsFromParent( $objPage->pid, $this->inheritColumn );
            }
        }
    }



    protected function getElementsFromParent( $pid, $inheritColumn )
    {
        global $objPage;

        $strContent     = '';
        $inherit        = true;
        $objArticles    = \ArticleModel::findPublishedByPidAndColumn( $pid, $inheritColumn );

        if( $objArticles )
        {
            while( $objArticles->next() )
            {
                $objElements = \ContentModel::findPublishedByPidAndTable( $objArticles->id, "tl_article");

                if( $objElements )
                {
                    $inherit = false;

                    $strContent .= \Controller::getArticle( $objArticles->id, false, true, $inheritColumn);
                }
            }
        }

        if( $inherit )
        {
            if( $pid !== $objPage->rootId )
            {
                $objparentPage = \PageModel::findByPk( $pid );

                return $this->getElementsFromParent( $objparentPage->pid, $inheritColumn );
            }
        }

        return $strContent?'<div class="mod_article full-height first last block">' . $strContent . '<div class="hover-article-image"><div class="hover-inside"></div><div class="hover-close"></div></div></div>':'';
    }
}
