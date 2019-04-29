<?php
/*******************************************************************
 * (c) 2019 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\Helper;


use Contao\NewsArchiveModel;


/**
 * Class News Archive Helper
 *
 * @package IIDO\BasicBundle
 */
class NewsArchiveHelper
{

    public static function getArchiveLanguage( $objArchiveOrId, $returnRootID = false, $returnBoth = false )
    {
        if( !is_object($objArchiveOrId) )
        {
            $objArchiveOrId = NewsArchiveModel::findByPk( $objArchiveOrId );
        }

        if( $objArchiveOrId )
        {
            if( $objArchiveOrId->jumpTo )
            {
                $objJumpTo = \PageModel::findByPk( $objArchiveOrId->jumpTo )->loadDetails();

                return $returnRootID ? $returnBoth ? array($objJumpTo->rootLanguage, $objJumpTo->rootId) : $objJumpTo->rootId : $objJumpTo->rootLanguage;
            }
        }

        return null;
    }



    public static function getArchivesByLanguage( $strLanguage )
    {
        $arrArchives    = array();
        $objArchives    = NewsArchiveModel::findAll();
        $objRootPages   = \PageModel::findByLanguage( $strLanguage );

        if( $objRootPages && $objArchives )
        {
            $arrRootIDs = $objRootPages->fetchEach('id');

            while( $objArchives->next() )
            {
                if( $objArchives->jumpTo )
                {
                    $objJumpTo = \PageModel::findByPk( $objArchives->jumpTo )->loadDetails();

                    if( in_array($objJumpTo->rootId, $arrRootIDs) )
                    {
                        $arrArchives[] = $objArchives->current();
                    }
                }
            }
        }

        return $arrArchives;
    }
}