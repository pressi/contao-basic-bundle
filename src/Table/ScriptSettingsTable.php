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
class ScriptSettingsTable
{

    /**
     * Table name
     *
     * @var string
     */
    protected $strTable             = 'tl_iido_basic_scriptSettings';



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
