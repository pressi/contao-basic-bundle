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
use IIDO\BasicBundle\Model\ConfigModel;


class IIDOConfig
{
    /**
     * @var ConfigModel
     */
    protected $dbObject;


    /**
     * @var string
     */
    protected $modelClass = ConfigModel::class;



    /**
     * IIDOConfig constructor.
     */
    public function __construct(Connection $connection)
    {
        $className = $this->modelClass;

        if( $connection->getSchemaManager()->tablesExist( $className::getTable()) )
        {
            if( $className::countAll() )
            {
                $this->dbObject = $className::findAll()->current();
            }
        }
        else
        {
            $this->dbObject = new \stdClass();
        }

        $this->connection = $connection;
    }



    /**
     * @param $varName
     *
     * @return mixed|null
     */
    public function get( $varName )
    {
        $className = $this->modelClass;

        if( $this->connection->getSchemaManager()->tablesExist( $className::getTable() ) )
        {
            return $this->dbObject->$varName;
        }

        return null;
    }



    /**
     * @param $varName
     * @param $varValue
     *
     * @return ConfigModel
     */
    public function set( $varName, $varValue )
    {
        $this->dbObject->{$varName} = $varValue;

        return $this->dbObject;
    }



    /**
     * @return ConfigModel|null
     */
    public function save()
    {
        $className = $this->modelClass;

        if( $this->connection->getSchemaManager()->tablesExist( $className::getTable()) )
        {
            return $this->dbObject->save();
        }

        return null;
    }



    public function getLink(): string
    {
        $router = System::getContainer()->get('router');
        /* @var $router \Symfony\Component\Routing\RouterInterface */

        return $router->generate('contao_backend', ['do' => 'config-settings', 'table' => 'tl_iido_config', 'act' => 'edit', 'id' => 1, 'rt' => REQUEST_TOKEN, 'ref' => TL_REFERER_ID]);
    }
}