<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace IIDO\BasicBundle\ContentElement;
use IIDO\BasicBundle\Helper\ImageHelper;


/**
 * Front end content element "text".
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class WeatherElement extends \ContentElement
{

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'ce_iido_weather';


    /**
     * Generate the content element
     */
    protected function compile()
    {
        global $objPage;

        $rootDir = $rootDir            = dirname(\System::getContainer()->getParameter('kernel.root_dir'));;

        if( file_exists($rootDir . '/system/tmp/weather-data.txt') )
        {
            $arrFileData    = json_decode( file_get_contents( $rootDir . '/system/tmp/weather-data.txt' ) );
            $objData        = $arrFileData->current_observation;

            $this->Template->imageSRC       = 'http://icons.wxug.com/i/c/i/' . $objData->icon . '.gif';
            $this->Template->iconName       = $objData->weather;

            $this->Template->temperature    = $objData->temp_c;
        }
    }
}
