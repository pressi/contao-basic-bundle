<?php
/*******************************************************************
 * (c) 2017 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasciBundle\Connection;

use Contao\System;

class MasterConnection
{
    static $configFile      = 'Resources/config/master-connection.json';
    static $bundlePath      = 'vendor/2do/contaco-basic-bundle';


    public static function getData()
    {
        $configData     = static::getConfigData();
        $connectionUrl  = $configData->domain . $configData->connection->publicPath . $configData->connection->file;
        $arrData        = file_get_contents( $connectionUrl );

        return $arrData;
    }



    public function getConfigData()
    {
        $rootDir = dirname(System::getContainer()->getParameter('kernel.root_dir'));
        return json_decode(file_get_contents($rootDir . '/' . static::$bundlePath . '/' . static::$configFile));
    }
}