<?php
/*******************************************************************
 * (c) 2017 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\Helper;

class Message
{
    /**
     * Template
     * @var string
     */
    protected static $strTemplate  = 'iido_message';



    public static function render( $arrMessage )
    {
        $objTemplate    = new \FrontendTemplate( self::$strTemplate );

        foreach($arrMessage as $strClass => $strMessage)
        {
            $objTemplate->class     = $strClass;
            $objTemplate->message   = $strMessage;

            break;
        }

        return $objTemplate->parse();
    }
}