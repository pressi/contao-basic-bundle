<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\ContentElement;


use IIDO\BasicBundle\Config\BundleConfig;
use IIDO\BasicBundle\Cron\WeatherDataCron;
use IIDO\BasicBundle\Helper\BasicHelper;
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

        $rootDir    = BasicHelper::getRootDir();
        $arrData    = BasicHelper::getWeatherData();

        $tmpFile    = str_replace('##ROOT##', $rootDir, $arrData['tmpFile']);

        if( !file_exists( $tmpFile ) )
        {
            $objWeatherCron = new WeatherDataCron();
            $objWeatherCron->generateCustomizeWeatherData();
        }

        if( file_exists( $tmpFile ) )
        {
            $imgSRC             = $arrData['icons']['url'];

            $arrFileData        = json_decode( file_get_contents( $tmpFile ) );
            $objData            = $arrFileData->current_observation;

            $tableFieldPrefix   = BundleConfig::getTableFieldPrefix();
            $iconSet            = \Config::get( $tableFieldPrefix . 'weatherIconsSet' );

            if( $iconSet === "own" )
            {
                $imgSRC = str_replace(array('##IMG##', '##IMG_PATH##'), $objData->icon , \Config::get( $tableFieldPrefix . 'weatherIconsUrl' ));
            }
            else
            {
                $imgSRC = $imgSRC . $iconSet . $objData->icon . '.' . $arrData['icons']['format'];
            }

            $this->Template->imageSRC       = $imgSRC;
            $this->Template->iconName       = $objData->weather;
            $this->Template->temperature    = $objData->temp_c;
        }
    }
}
