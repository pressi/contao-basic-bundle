<?php
/*******************************************************************
 * (c) 2019 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\FormField;



/**
 * Class Text Field
 *
 */
class TextField extends \FormTextField
{

    /**
     * Generate the widget and return it as string
     *
     * @return string The widget markup
     */
    public function parse()
    {
        $strBuffer = parent::parse();

        if( strlen(trim($this->description)) > 2 )
        {
            $strDesc    = '<div class="description">' . $this->description . '</div>';
            $strBuffer  = preg_replace('/<input(.*)>/', '<input$1>' . $strDesc, $strBuffer);
        }

        return $strBuffer;
    }
}