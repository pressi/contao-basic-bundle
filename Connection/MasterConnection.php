<?php
/*******************************************************************
 * (c) 2017 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\Connection;

use Contao\System;
use IIDO\BasicBundle\Config\BundleConfig;


class MasterConnection
{
    /**
     *
     * @var MasterConnection
     */
    private static $instance;
    
    
    private static $password; // iido543master12

    static $configFile      = 'Resources/config/master-connection.json';
    static $bundlePath;
    

    private function __construct()
    {
        static::$bundlePath = BundleConfig::getBundlePath();
        
        if( \Input::post("password") )
        {
            $this->setPassword( \Input::post("password") );
        }

        if( !strlen(static::$password) && !\Input::get("method") == "login" )
        {
            $this->validate( $this->getActionData("checkPassword", array(), true) );
        }
    }

    
    public static function getInstance()
    {
        if ( is_null( self::$instance ) )
        {
            self::$instance = new self();
        }
        
        return self::$instance;
    }
    
    
    
    public function setPassword( $pwd )
    {
        self::$password = md5( $pwd );
    }



    public function isPasswordValid()
    {
        $arrData = $this->getActionData("checkPassword", array(), true);

        if( key_exists("ERROR", $arrData) )
        {
            return false;
        }

        return true;
    }



    public function getData($returnAsArray = false)
    {
        $connectionUrl  = $this->getConnectionUrl();
        $arrData        = file_get_contents( $connectionUrl );
        $objData        = json_decode($arrData, $returnAsArray);

        return $objData;
    }



    public function getActionData( $actionName, array $actionParams = array(), $returnAsArray = false)
    {
        $connectionUrl  = $this->getConnectionUrl() . '&act=' . $actionName . (count($actionParams)?'&':'') . implode('&', $actionParams);
        $arrData        = file_get_contents( $connectionUrl );

        return json_decode($arrData, $returnAsArray);
    }



    public function getConfigData()
    {
        $rootDir = dirname(System::getContainer()->getParameter('kernel.root_dir'));
        return json_decode(file_get_contents($rootDir . '/' . static::$bundlePath . '/' . static::$configFile));
    }



    public function redirectTo( $method = "", array $msg = array() )
    {
        if( count($msg) )
        {
            \Session::getInstance()->set("iidoMessage", $msg);
        }

        if( $method )
        {
            \Controller::redirect( \Controller::addToUrl("do=iidoConfigContao&amp;method=" . $method) );
        }
        else
        {
            \Controller::redirect( \Controller::addToUrl("do=iidoConfigContao") );
        }
    }


    public static function redirect( $method = "", array $msg = array() )
    {
        $self = new self();
        $self->redirectTo( $method, $msg );
    }



    protected function getConnectionUrl()
    {
        $configData = $this->getConfigData();
        return $configData->domain . $configData->connection->publicPath . $configData->connection->file . '?pwd=' . static::$password;
    }



    protected function validate( array $arrData )
    {
        if( key_exists("ERROR", $arrData) )
        {
            if( $arrData['ERROR'] == 'No correct password.' )
            {
                if( !\Input::get("method") == "login" )
                {
                    \Controller::redirect( \Controller::addToUrl("do=iidoConfigContao&amp;method=login") );
                }
            }
        }
    }
}