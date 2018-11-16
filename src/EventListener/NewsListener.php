<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\EventListener;


use Contao\System;
use Contao\Config;
use IIDO\BasicBundle\Config\BundleConfig;
use IIDO\BasicBundle\Helper\BasicHelper;


/**
 * IIDO System Listener
 *
 * @package IIDO\BasicBundle
 * @author Stephan Preßl <development@prestep.at>
 */
class NewsListener extends DefaultListener
{

    public function parseCustomizeArticles(&$objTemplate, $arrData, $objClass)
    {
        if( $objTemplate->hasText || $objTemplate->hasTeaser )
        {
            $objTemplate->addPlus = true;
        }

        if( $arrData['text'] )
        {
            $objTemplate->hasText = TRUE;

            $objTemplate->strText = $arrData['text'];
        }
    }

}
