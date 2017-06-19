<?php
/******************************************************************
 *
 * (c) 2017 Stephan Preßl <development@prestep.at>
 * All rights reserved
 *
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *
 ******************************************************************/

namespace IIDO\BasicBundle\Page;



/**
 * Provide methods to handle a regular front end page.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class RegularRedirectPage extends \PageRegular
{

    /**
     * Create a new template
     *
     * @param \PageModel   $objCurrentPage
     * @param \LayoutModel $objLayout
     */
    protected function createTemplate($objCurrentPage, $objLayout)
    {
        parent::createTemplate($objCurrentPage, $objLayout);

        if( $objCurrentPage->jumpTo )
        {
            $strFrontendUrl = \PageModel::findByPk( $objCurrentPage->jumpTo )->getFrontendUrl();
            $timeout        = $this->redirectTimeout?:5;

            $this->Template->viewport = $this->Template->viewport . "\n" . '<meta http-equiv="refresh" content="' . $timeout . '; URL=' . $strFrontendUrl . '">';
        }
    }
}
