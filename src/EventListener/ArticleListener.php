<?php
/*******************************************************************
 * (c) 2020 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\EventListener;



use IIDO\BasicBundle\Helper\ColorHelper;
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
        $bgColor    = ColorHelper::compileColor( $objRow->bgColor );
        $classes    = $objRow->classes;
//        $classes[]  = "row";
//        $classes[] = "row-direction-$objRow->layout_direction";

        if( $objRow->bgImage )
        {
            $classes[] = 'has-bg-image';
        }

        if( $bgColor !== 'transparent' )
        {
            $arrArticleClasses[] = 'has-bg-color';
        }

        if( $objRow->width )
        {
            $classes[] = 'width-' . $objRow->width;
        }

        if( $objRow->height )
        {
            $classes[] = 'height-' . $objRow->height;
        }

        $objRow->classes = $classes;
    }


}
