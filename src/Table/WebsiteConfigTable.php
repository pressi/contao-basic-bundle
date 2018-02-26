<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\Table;


use IIDO\BasicBundle\Config\BundleConfig;
use IIDO\BasicBundle\Helper\BasicHelper;


/**
 * Class Website Config Table
 */
class WebsiteConfigTable
{

    /**
     * Table name
     *
     * @var string
     */
    protected $strTable             = 'tl_iido_website_config';



    /**
     * Get weather data form config json file
     *
     * @return array
     */
    protected function getWeatherData()
    {
        return BasicHelper::getWeatherData();
    }



    public function getIconsSet()
    {
        $arrOptions = array();
        $arrData    = $this->getWeatherData();

        if( is_array($arrData) && count($arrData) )
        {
            $arrOptions = $arrData['icons']['sets'];
        }

        $arrOptions['own'] = 'Eigene Icons';

        return $arrOptions;
    }



    public function getLanguage()
    {
        $arrOptions = array();
        $arrData    = $this->getWeatherData();

        if( is_array($arrData) && count($arrData) )
        {
            $arrOptions = $arrData['languages'];
        }

        return $arrOptions;
    }
}
