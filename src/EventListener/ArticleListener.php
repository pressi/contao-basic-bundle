<?php
/*******************************************************************
 * (c) 2020 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\EventListener;



use Contao\StringUtil;
use Contao\System;
use IIDO\BasicBundle\Helper\ColorHelper;
use IIDO\BasicBundle\Helper\ScriptHelper;
use Terminal42\ServiceAnnotationBundle\ServiceAnnotationInterface;
use Contao\CoreBundle\ServiceAnnotation\Hook;


/**
 * IIDO Article Listener
 *
 * @package IIDO\BasicBundle
 * @author Stephan Preßl <development@prestep.at>
 */
class ArticleListener implements ServiceAnnotationInterface
{

    /**
     * @Hook("getArticle")
     */
    public function onGetArticle( $objRow )
    {
        $objPermission = System::getContainer()->get('iido.basic.backend.permission_checker');
        /* @var $objPermission \IIDO\BasicBundle\Permission\BackendPermissionChecker */

        $classes    = $objRow->classes;
        $cssID      = StringUtil::deserialize( $objRow->cssID, true );
//        $classes[]  = "row";
//        $classes[] = "row-direction-$objRow->layout_direction";

        if( $objPermission->hasFullAccessTo('article', 'bg_color') )
        {
            $bgColor    = ColorHelper::compileColor( $objRow->bgColor );

            if( $bgColor !== 'transparent' )
            {
                $arrArticleClasses[] = 'has-bg-color';
            }
        }

        if( $objPermission->hasFullAccessTo('article', 'bg_image') )
        {
            if( $objRow->bgImage )
            {
                $classes[] = 'has-bg-image';
            }
        }

        if( $objPermission->hasFullAccessTo('article', 'width') )
        {
            if( $objRow->width )
            {
                $classes[] = 'width-' . $objRow->width;
            }
        }

        if( $objPermission->hasFullAccessTo('article', 'height') )
        {
            if( $objRow->height )
            {
                $classes[] = 'height-' . $objRow->height;
            }
        }

        if( ScriptHelper::hasPageFullPage( true ) )
        {
            if( !$objRow->noSection && false === strpos($cssID[1], 'no-section') )
            {
                $classes[] = 'section';
            }

            if( false !== strpos($cssID[1], 'show-from-') )
            {
//                preg_match_all('/show-from-([A-Za-z]+)/', $cssID[1], $matches);

                $classes[] = 'nav-section';
            }
        }

        $cssID[1] = trim(preg_replace('/no-section/', '', $cssID[1]));

        $objRow->cssID      = serialize( $cssID );
        $objRow->classes    = $classes;
    }


}
