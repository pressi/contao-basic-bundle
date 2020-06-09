<?php
/*******************************************************************
 *
 * (c) 2020 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 *
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *
 *******************************************************************/

namespace IIDO\BasicBundle\Permission;


use Contao\ArticleModel;
use Contao\CoreBundle\Framework\FrameworkAwareInterface;
use Contao\CoreBundle\Framework\FrameworkAwareTrait;
use Contao\StringUtil;
use Contao\System;
use Doctrine\DBAL\Connection;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;


class BackendPermissionChecker implements FrameworkAwareInterface
{
    use FrameworkAwareTrait;


    /**
     * @var Connection
     */
    private $db;


    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;



    /**
     * PermissionChecker constructor.
     *
     * @param Connection            $db
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(Connection $db, TokenStorageInterface $tokenStorage)
    {
        $this->db = $db;
        $this->tokenStorage = $tokenStorage;
    }



    public function hasFullAccessTo( $strTable, $fieldName, $model = false, $modelFieldName = '' )
    {
        $accessFieldName = $fieldsFieldName = $tableClassName = false;

        switch( $strTable )
        {
            case "article":
            case "articles":
            case "tl_article":
                $accessFieldName    = 'includeArticleFields';
                $fieldsFieldName    = 'articleFields';
                $tableClassName     = ArticleModel::class;
                break;
        }

        if( $accessFieldName )
        {
            $objConfig  = System::getContainer()->get('iido.basic.config');
            $arrFields  = StringUtil::deserialize( $objConfig->get( $fieldsFieldName ), true);

            if( $objConfig->get( $accessFieldName ) && in_array( $fieldName, $arrFields) )
            {
                if( $model )
                {
                    if( !$model instanceof $tableClassName )
                    {
                        $model = $tableClassName::findByPk( $model );
                    }

                    if( $model->$modelFieldName )
                    {
                        return true;
                    }
                }
                else
                {
                    return true;
                }
            }
        }

        return false;
    }

}