<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace IIDO\BasicBundle\ContentElement;

use IIDO\BasicBundle\Helper\BasicHelper;


/**
 * Front end content element "text".
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class NavigationElement extends \ContentElement
{

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'ce_navigation';


    /**
     * Generate the content element
     */
    protected function compile()
    {
        $this->Template->content = BasicHelper::renderNavigation( $this->navModule, 'main', $this->cssID[1] );
    }
}
