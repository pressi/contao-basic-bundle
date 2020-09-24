<?php
/*******************************************************************
 * (c) 2020 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\EventListener;


use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\FrontendTemplate;
use Contao\Module;
use Contao\NewsArchiveModel;
use Contao\PageModel;
use IIDO\BasicBundle\Helper\BasicHelper;
use IIDO\BasicBundle\Model\NewsModel;


/**
 * Class ParseArticlesListener
 *
 * @package IIDO\BasicBundle\EventListener
 *
 * @Hook("parseArticles")
 */
class ParseArticlesListener
{
    public function __invoke( FrontendTemplate $template, array $newsEntry, Module $module ): void
    {
        $alias      = $newsEntry['alias'];
        $strLang    = strtolower( BasicHelper::getLanguage() );

        $generateUrl    = false;
        $jumpTo         = 0;

        if( $strLang === 'en' || $strLang === 'en_us' )
        {
            $generateUrl = true;

            $objArchive = NewsArchiveModel::findByPk( $newsEntry['pid'] );
            $jumpToEN   = $objArchive->jumpToEN;

            $aliasEN = $newsEntry['aliasEN'];

            if( $aliasEN )
            {
                $alias = $aliasEN;
            }

            if( $jumpToEN )
            {
                $jumpTo = $jumpToEN;
            }

            if( $strLang === 'en_us' )
            {
                $jumpToUS   = $objArchive->jumpToUS;
                $aliasUS    = $newsEntry['aliasUS'];

                if( $aliasUS )
                {
                    $alias = $aliasUS;
                }

                if( $jumpToUS )
                {
                    $jumpTo = $jumpToUS;
                }
            }
        }

        if( $generateUrl )
        {
            if( $jumpTo )
            {
                $objJumpTo = PageModel::findByPk( $jumpTo );
                $template->link = $objJumpTo->getFrontendUrl('/'. $alias );
            }
            else
            {
                $template->link = preg_replace('/' . $newsEntry['alias'] . '.html$/', $alias . '.html', $template->link);
            }
        }
    }

}

class_alias(NewsModel::class, 'NewsModel');