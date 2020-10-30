<?php
/***************************************************************************
 * (c) 2020 Stephan Preßl, www.stephanpressl.at <mail@stephanpressl.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by Stephan Preßl
 ***************************************************************************/

namespace IIDO\BasicBundle\Helper;



class ArrayHelper
{

    public static function getDcaPositions(): array
    {
        return [
            'left_top'      =>  $GLOBALS['TL_LANG']['DEF']['OPTIONS']['left_top']       ?:'Links Oben',
            'center_top'    =>  $GLOBALS['TL_LANG']['DEF']['OPTIONS']['center_top']     ?:'Mitte Oben',
            'right_top'     =>  $GLOBALS['TL_LANG']['DEF']['OPTIONS']['right_top']      ?:'Rechts Oben',

            'left_center'   =>  $GLOBALS['TL_LANG']['DEF']['OPTIONS']['left_center']    ?:'Links Mitte',
            'center_center' =>  $GLOBALS['TL_LANG']['DEF']['OPTIONS']['center_center']  ?:'Mitte Mitte (Zentriert)',
            'right_center'  =>  $GLOBALS['TL_LANG']['DEF']['OPTIONS']['right_center']   ?:'Rechts Mitte',

            'left_bottom'   =>  $GLOBALS['TL_LANG']['DEF']['OPTIONS']['left_bottom']    ?:'Links Unten',
            'center_bottom' =>  $GLOBALS['TL_LANG']['DEF']['OPTIONS']['center_bottom']  ?:'Mitte Unten',
            'right_bottom'  =>  $GLOBALS['TL_LANG']['DEF']['OPTIONS']['right_bottom']   ?:'Rechts Unten',
        ];
    }



    public static function getDcaPosition(): array
    {
        return self::getDcaPositions();
    }
}
