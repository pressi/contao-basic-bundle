<?php
/*******************************************************************
 * (c) 2020 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\Config;


use Contao\System;
use Doctrine\DBAL\Connection;
use IIDO\BasicBundle\Helper\BasicHelper;
use IIDO\BasicBundle\Model\ConfigModel;
use Symfony\Component\Yaml\Yaml;


class IIDOConfig
{
    /**
     * @var ConfigModel
     */
    protected $dbObject;


    protected string $modelClass = ConfigModel::class;


    protected string $filePath = 'config/iido.basic.config.yml';

    protected ?array $config = [];



    /**
     * IIDOConfig constructor.
     */
    public function __construct(Connection $connection)
    {
//        $rootDir = BasicHelper::getRootDir( true );

        $this->checkFile( true );

//        $config = Yaml::parseFile( $rootDir . $this->filePath );

//        echo "<pre>"; print_r( $config ); exit;

//        $className = $this->modelClass;

//        if( $connection->getSchemaManager()->tablesExist( $className::getTable()) )
//        {
//            if( $className::countAll() )
//            {
//                $this->dbObject = $className::findAll()->current();
//            }
//        }
//        else
//        {
//            $this->dbObject = new \stdClass();
//        }

//        $this->connection = $connection;
//        $this->config = $config;
    }



    /**
     * @param $varName
     *
     * @return mixed|null
     */
    public function get( $varName )
    {
        $this->checkFile( true );

        return $this->config[ $varName ];

//        $className = $this->modelClass;

//        if( $this->connection->getSchemaManager()->tablesExist( $className::getTable() ) )
//        {
//            return $this->dbObject->$varName;
//        }

//        return null;
    }



    /**
     * @param $varName
     * @param $varValue
     *
     * @return array|null
     */
    public function set( $varName, $varValue )
    {
//        $this->dbObject->{$varName} = $varValue;

        $this->config[ $varName ] = $varValue;

        return $this->config;
    }



    /**
     * @return array|null
     */
    public function save()
    {
        $rootDir = BasicHelper::getRootDir( true );

//        $className = $this->modelClass;

//        if( $this->connection->getSchemaManager()->tablesExist( $className::getTable()) )
//        {
//            return $this->dbObject->save();
//        }

        $fileContent = Yaml::dump( $this->config );

        file_put_contents( $rootDir . $this->filePath, $fileContent );

        return $this->config;
    }



    public function getLink(): string
    {
        $router = System::getContainer()->get('router');
        /* @var $router \Symfony\Component\Routing\RouterInterface */

        return $router->generate('contao_backend', ['do' => 'config-settings', 'table' => 'tl_iido_config', 'act' => 'edit', 'id' => 1, 'rt' => REQUEST_TOKEN, 'ref' => TL_REFERER_ID]);
    }



    protected function checkFile( $setConfig = true )
    {
        $rootDir = BasicHelper::getRootDir( true );

        if( !file_exists( $rootDir . $this->filePath) )
        {
            fopen( $rootDir . $this->filePath, "w");
        }

        if( $setConfig )
        {
            $this->config = Yaml::parseFile( $rootDir . $this->filePath );
        }
    }



    public function getConfigFilePath()
    {
        return $this->filePath;
    }
}