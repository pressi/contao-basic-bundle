<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\Table;


/**
 * Class User Table
 *
 * @package IIDO\BasicBundle\Table
 * @author Stephan Preßl <https://github.com/pressi>
 */
class UserTable extends \Backend
{

    /**
     * Table Name
     */
    protected $strTable = 'tl_user';



    public function getWebsiteConfigs( $dc )
    {
        $arrConfigs = array();

        foreach( $GLOBALS['IIDO']['CONFIGS'] as $alias => $config )
        {
            $arrConfigs[ $alias ] = $config['name'];
        }

        return $arrConfigs;
    }
}
