<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\Helper;


use Contao\Database\Result;


/**
 * Description
 *
 */
class PageHelper
{

    /**
     * check
     *
     * @param integer|\PageModel|Result $pageId
     *
     * @return boolean
     * @TODO: add check if removePageLoader active!!
     */
    public static function checkIfParentPagesHasPageLoader( $objPageId )
    {
        $db = \Database::getInstance();

        if( $objPageId instanceof \PageModel || $objPageId instanceof Result)
        {
            $objSearchPage = $objPageId;
        }
        else
        {
//            $objSearchPage = \PageModel::findByPk( $objPageId );
            $objSearchPage = $db->prepare("SELECT * FROM tl_page WHERE id=?")->limit(1)->execute( $objPageId );
        }

        if( $objSearchPage )
        {
//            $objParentPage = \PageModel::findByPk( $objSearchPage->pid );
            $objParentPage = $db->prepare("SELECT * FROM tl_page WHERE id=?")->limit(1)->execute( $objSearchPage->pid );

            if( $objParentPage )
            {
                if( !$objParentPage->addPageLoader && $objParentPage->pid > 0 )
                {
                    return self::checkIfParentPagesHasPageLoader( $objParentPage );
                }
                else
                {
                    return true;
                }

            }
        }

        return false;
    }
}
