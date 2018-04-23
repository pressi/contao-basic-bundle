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



    public function getScriptVersion( $dc )
    {
        $tableFieldPrefix   = BundleConfig::getTableFieldPrefix();
        $folderPath         = BasicHelper::getRootDir() . '/' . BundleConfig::getBundlePath() . '/Resources/public/javascript/';

        $arrOptions     = array();
        $fieldName      = lcfirst( preg_replace('/^' . $tableFieldPrefix . 'script/', '', $dc->field) );
        $subFolder      = 'lib';

        if( !is_dir( $folderPath . $subFolder . '/' . $fieldName ) )
        {
            $subFolder = 'jquery';
        }

        $folderPath = $folderPath . $subFolder . '/';

        if( is_dir( $folderPath . $fieldName ) )
        {
            $arrVersions = scan( $folderPath . $fieldName );

            foreach( $arrVersions as $intVersion )
            {
                $arrVersion = explode(".", $intVersion);

                if( is_numeric( $arrVersion[0] ) )
                {
                    $arrOptions[ $intVersion ] = $intVersion . ($subFolder ? ' (' . $subFolder . ')' : '');
                }
            }
        }

        return $arrOptions;
    }
}
