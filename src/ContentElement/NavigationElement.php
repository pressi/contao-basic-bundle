<?php
/*******************************************************************
 *
 * (c) 2017 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 *
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *
 *******************************************************************/

namespace IIDO\BasicBundle\ContentElement;


use IIDO\BasicBundle\Helper\BasicHelper;


/**
 * Front end content element "navigation".
 *
 * @package IIDO\BasicBundle
 * @author Stephan Preßl <development@prestep.at>
 */
class NavigationElement extends \ContentElement
{

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'ce_iido_navigation';


    /**
     * Generate the content element
     */
    protected function compile()
    {
        //TODO: change BasicHelper in NavigationHelper (create new helper class!)
        $this->Template->content = BasicHelper::renderNavigation( $this->navModule, 'main', $this->cssID[1], $this );
    }
}
