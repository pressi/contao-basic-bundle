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

namespace IIDO\BasicBundle\Dca;


use IIDO\BasicBundle\Config\BundleConfig;


class ExistTable extends Table
{

    public function __construct($tableName, $withoutSQL = FALSE)
    {
        if( preg_match('/\//', $tableName) )
        {
            $tableName  = BundleConfig::getTableName( $tableName );
        }

        $this->strTable     = $tableName;
        $this->withoutSQL   = $withoutSQL;

        $sorting = $GLOBALS['TL_DCA'][ $tableName ]['list']['sorting'];

//        if( $sorting && is_array($sorting) && count($sorting) )
//        {
//            $this->addSorting = true;
//        }
    }

}