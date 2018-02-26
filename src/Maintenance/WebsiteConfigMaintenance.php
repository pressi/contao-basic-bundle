<?php

namespace IIDO\BasicBundle\Maintenance;


/**
 *
 */
class WebsiteConfigMaintenance extends \Backend implements \executable
{

    /**
     * Return true if the module is active
     *
     * @return boolean
     */
    public function isActive()
    {
        return \Input::get('act') == 'iido_maintenance';
    }



    /**
     * Generate the module
     *
     * @return string
     */
    public function run()
    {
        $objUser        = \BackendUser::getInstance();
        $strUsername    = $objUser->getUsername();

        if( $strUsername !== "zomedia" && $strUsername !== "develop" && $strUsername !== "stephan" )
        {
            return '';
        }

        /** @var \BackendTemplate|object $objTemplate */
        $objTemplate            = new \BackendTemplate('be_iido_maintenance');

        $objTemplate->action    = ampersand(\Environment::get('request'));
        $objTemplate->isActive  = $this->isActive();

        $objTemplate->headline          = $GLOBALS['TL_LANG']['tl_maintenance']['iido']['maintenanceHeadline'];
        $objTemplate->label_openLink    = $GLOBALS['TL_LANG']['tl_maintenance']['iido']['open_connectTool'];

        return $objTemplate->parse();
    }
}