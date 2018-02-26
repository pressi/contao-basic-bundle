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
 * Description
 *
 */
class CronHelper
{

    public static function isActive( $cronName )
    {
        $tableFieldPrefix = BundleConfig::getTableFieldPrefix();
        return \Config::get( $tableFieldPrefix . 'enable' . ucfirst($cronName) . 'Cron' );
    }
}
