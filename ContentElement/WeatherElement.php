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


use IIDO\BasicBundle\Cron\WeatherDataCron;
use IIDO\BasicBundle\Helper\ImageHelper;


/**
 * Front end content element "weather".
 *
 * @package IIDO\BasicBundle
 * @author Stephan Preßl <development@prestep.at>
 */
class WeatherElement extends \ContentElement
{

    /**
     * Template
     *
     * @var string
     */
    protected $strTemplate = 'ce_iido_weather';



    /**
     * Generate the content element
     */
    protected function compile()
    {
        global $objPage;

        $rootDir = dirname(\System::getContainer()->getParameter('kernel.root_dir'));

        if( !file_exists($rootDir . '/system/tmp/weather-data.txt') )
        {
            $objWeatherCron = new WeatherDataCron();
            $objWeatherCron->generateCustomizeWeatherData();
        }

        if( file_exists($rootDir . '/system/tmp/weather-data.txt') )
        {
            $arrFileData    = json_decode( file_get_contents( $rootDir . '/system/tmp/weather-data.txt' ) );
            $objData        = $arrFileData->current_observation;

            // TODO: make icons changeable // own icons // icons from weather page
//            $this->Template->imageSRC       = 'http://icons.wxug.com/i/c/i/' . $objData->icon . '.gif';
            $this->Template->imageSRC       = 'files/skischule-russbach/Uploads/Icons/Wetter/' . $objData->icon . '.png';
            $this->Template->iconName       = $objData->weather;

            $this->Template->temperature    = $objData->temp_c;
        }
    }
}
