<?php
/******************************************************************
 *
 * (c) 2015 Stephan Preßl <development@prestep.at>
 * All rights reserved
 *
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *
 ******************************************************************/

namespace IIDO\BasicBundle\Helper;


/**
 * Class Helper
 * @package IIDO\BasicBundle
 */
class FilterHelper extends \Frontend
{

    public static function renderFilter( $strName )
    {
//        $GLOBALS['TL_JAVASCRIPT']['isotope'] = '';

        $strName = strtolower( preg_replace(array('/ & /', '/ /'), array('_and_', '_'), $strName) );

        return $strName;
    }

}
