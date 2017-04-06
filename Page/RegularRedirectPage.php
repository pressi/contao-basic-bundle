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
     * @param \PageModel   $objPage
     * @param \LayoutModel $objLayout
     */
    protected function createTemplate($objPage, $objLayout)
    {
        parent::createTemplate($objPage, $objLayout);

        if( $objPage->jumpTo )
        {
            $strFrontendUrl = \PageModel::findByPk( $objPage->jumpTo )->getFrontendUrl();
            $timeout        = $this->redirectTimeout?:5;

            $this->Template->viewport = $this->Template->viewport . "\n" . '<meta http-equiv="refresh" content="' . $timeout . '; URL=' . $strFrontendUrl . '">';
        }
    }
}
