<?php
declare(strict_types=1);

/*******************************************************************
 * (c) 2020 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\Helper;


class BackendFormViewHelper
{

    public static function processForm( $formID, $formFields )
    {
        if( \Input::post('FORM_SUBMIT') === $formID )
        {
            echo "<pre>"; print_r( "HU" ); exit;
        }
    }
}