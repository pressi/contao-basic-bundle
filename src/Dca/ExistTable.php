<?php
/*******************************************************************
 *
 * (c) 2019 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 *
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *
 *******************************************************************/

namespace IIDO\BasicBundle\Dca;


class ExistTable extends Table
{

    public function __construct($tableName, $withoutSQL = FALSE)
    {
        $this->strTable     = $tableName;
        $this->withoutSQL   = $withoutSQL;
    }

}