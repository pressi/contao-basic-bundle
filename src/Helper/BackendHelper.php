<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\Helper;


use IIDO\BasicBundle\Config\BundleConfig;


/**
 * Class Helper
 *
 * @package IIDO\BasicBundle
 * @TODO: überarbeiten!! funktioniert nicht!!
 */
class BackendHelper
{

    public static function getLinkClasses()
    {
        $fieldPrefix    = BundleConfig::getTableFieldPrefix();

        $strClasses = '';
        $arrClasses = \StringUtil::deserialize( \Config::get( $fieldPrefix . 'linkClasses' ), TRUE );

        if( is_array($arrClasses) && count($arrClasses) )
        {
            foreach( $arrClasses as $arrClass )
            {
                $strTitle = preg_replace(array('/&#40;/', '/&#41;/'), array('(', ')'), $arrClass['title']);

                $strClasses .= "{title: '" . $strTitle . "', value: '" . $arrClass['value'] . "'},";
            }
        }

        return $strClasses;
    }
}