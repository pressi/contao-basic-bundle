<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\FormField;


use IIDO\BasicBundle\Helper\BasicHelper;
use IIDO\BasicBundle\Helper\ScriptHelper;
use IIDO\BasicBundle\Helper\StyleSheetHelper;


/**
 * Class Database Select
 *
 */
class PickDate extends \FormTextField
{
    /**
     * Template
     *
     * @var string
     */
    protected $strTemplate = 'form_pickdate';


    /**
     * The CSS class prefix
     *
     * @var string
     */
    protected $strPrefix = 'widget widget-text widget-pickdate';


    /**
     * Always set rgxp to `date`
     *
     * @param array $arrAttributes An optional attributes array
     */
    public function __construct($arrAttributes = null)
    {
        parent::__construct($arrAttributes);

        $this->rgxp = 'date';
    }



    /**
     * Parse the template file and return it as string
     *
     * @param array $arrAttributes An optional attributes array
     *
     * @return string The template markup
     */
    public function parse($arrAttributes=null)
    {
        // do not add in back end
        if (TL_MODE == 'BE')
        {
            return parent::parse($arrAttributes);
        }

        global $objPage;

        //THEME > TODO: auswählbar im backend!!
        StyleSheetHelper::addThemeStyle('pickdate', 'default,default.date');


        ScriptHelper::addScript('pickdate', false, true);

        if( BasicHelper::getLanguage() !== 'en' )
        {
            ScriptHelper::addTranslateScript('pickdate', BasicHelper::getLanguage());
        }

        if( !$this->value )
        {
            if( $this->name === "arrival" )
            {
                $this->value = date('d.m.Y', strtotime('+1day'));
            }
            elseif( $this->name === "depature" )
            {
                $this->value = date('d.m.Y', strtotime('+4days'));
            }
        }

        return parent::parse($arrAttributes);
    }



    /**
     * Generate the widget and return it as string
     *
     * @return array|string
     */
    public function generate()
    {
        $strWidget  = parent::generate();

        return $strWidget;
    }
}
