<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\Helper;


class StylesHelper
{

    public static function renderTrblStyles( $arrPosition, $prefix = '', $returnAsArray = false )
    {
        $strStyles  = '';
        $arrStyles  = array();

        if( !is_array($arrPosition) )
        {
            $arrPosition = deserialize($arrPosition, TRUE);
        }

        if( $prefix && !preg_match('/\-$/', $prefix) )
        {
            $prefix = $prefix . "-";
        }

        if( $arrPosition['top'] || $arrPosition['right'] || $arrPosition['bottom'] || $arrPosition['left'] )
        {
            $unit = $arrPosition['unit']?:'px';

            if( $arrPosition['top'] )
            {
                $strStyles .= $prefix . "top:" . $arrPosition['top'] . $unit . ";";

                $arrStyles['top'] = $arrPosition['top'] . $unit;
            }

            if( $arrPosition['right'] )
            {
                $strStyles .= $prefix . "right:" . $arrPosition['right'] . $unit . ";";

                $arrStyles['right'] = $arrPosition['right'] . $unit;
            }

            if( $arrPosition['bottom'] )
            {
                $strStyles .= $prefix . "bottom:" . $arrPosition['bottom'] . $unit . ";";

                $arrStyles['bottom'] = $arrPosition['bottom'] . $unit;
            }

            if( $arrPosition['left'] )
            {
                $strStyles .= $prefix . "left:" . $arrPosition['left'] . $unit . ";";

                $arrStyles['left'] = $arrPosition['left'] . $unit;
            }
        }

        return $returnAsArray ? $arrStyles : $strStyles;
    }

}