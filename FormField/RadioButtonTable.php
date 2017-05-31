<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace IIDO\BasicBundle\FormField;


/**
 * Class FormRadioButton
 *
 * @property boolean $mandatory
 * @property array   $options
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class RadioButtonTable extends \RadioButton
{

    /**
     * Add specific attributes
     *
     * @param string $strKey   The attribute key
     * @param mixed  $varValue The attribute value
     */
    public function __set($strKey, $varValue)
    {
        switch ($strKey)
        {
            case 'options':
            case 'tableOptions':
                $this->arrTableOptions  = \StringUtil::deserialize($varValue);
                $this->arrOptions       = $this->getRealOptions( $this->arrTableOptions );
                break;

            default:
                parent::__set($strKey, $varValue);
                break;
        }
    }



    public function getRealOptions( $arrOptions )
    {
        if( key_exists('rowTitle', $arrOptions[0]) )
        {
            $arrNewOptions = array();

            foreach($arrOptions as $arrValue)
            {
                $rowTitle = $arrValue['rowTitle'];

                foreach($arrValue as $key => $arrOption)
                {
                    if( $key == "rowTitle" || (!strlen($arrOption[0]) && !strlen($arrOption[1])) )
                    {
                        continue;
                    }

                    $arrNewOptions[] = array('value'=>$rowTitle . ' ' . $arrOption[0],'label'=>$arrOption[1]);
                }
            }

            $arrOptions = $arrNewOptions;
        }

        return $arrOptions;
    }


    /**
     * Return a parameter
     *
     * @param string $strKey The parameter name
     *
     * @return mixed The parameter value
     */
    public function __get($strKey)
    {
        if ($strKey == 'options' || $strKey == "tableOptions")
        {
            return $this->arrOptions;
        }

        return parent::__get($strKey);
    }


    /**
     * Check for a valid option (see #4383)
     */
    public function validate()
    {
        $varValue = $this->getPost($this->strName);

        if (!empty($varValue) && !$this->isValidOption($varValue))
        {
            $this->addError($GLOBALS['TL_LANG']['ERR']['invalid']);
        }

        parent::validate();
    }


    /**
     * Generate the options
     *
     * @return array The options array
     */
    protected function getOptions( $setEmpty = true )
    {
        $arrOptions = array();

        foreach ($this->arrOptions as $i => $arrOption)
        {
            foreach($arrOption as $arrOpt)
            {
                $arrOptConfig = array();

                if( is_array($arrOpt) )
                {
                    $arrOpt['value']    = $arrOpt[0];
                    $arrOpt['label']    = $arrOpt[1];

                    $arrOptConfig = array
                    (
                        'type'       => 'option',
                        'name'       => $this->strName,
                        'id'         => $this->strId . '_' . $i,
                        'value'      => $arrOption['value'],
                        'checked'    => $this->isChecked($arrOpt),
                        'attributes' => $this->getAttributes(),
                        'label'      => $arrOption['label']
                    );
                }
                else
                {
                    if( $setEmpty )
                    {
                        $arrOptConfig = array
                        (
                            'type'       => 'option',
                            'name'       => $this->strName,
                            'id'         => $this->strId . '_' . $i,
                            'value'      => $arrOpt,
                            'checked'    => FALSE,
                            'attributes' => array(),
                            'label'      => ''
                        );
                    }
                }

                if( count($arrOptConfig) )
                {
                    $arrOptions[] = array_replace
                    (
                        $arrOpt,
                        $arrOptConfig
                    );
                }
            }
        }

        return $arrOptions;
    }



    /**
     * Generate the widget and return it as string
     *
     * @return string The widget markup
     */
    public function generate()
    {
        $strOptions = '';

        $arrHeader  = deserialize($this->tableHeader, TRUE);
        $strHeader  = '';
        $strBody    = '';

        foreach($arrHeader as $headerValue)
        {
            if( strlen($headerValue) )
            {
                if( !strlen($strHeader) )
                {
                    $strHeader = '<thead><th>' . $headerValue . '</th>';
                }
                else
                {
                    $strHeader .= '<th>' . $headerValue . '</th>';
                }
            }
        }

        if( strlen($strHeader) )
        {
            $strHeader .= '</thead>';
        }

        foreach ($this->arrTableOptions as $i => $arrOption)
        {
            if( !strlen($strBody) )
            {
                $strBody = '<tbody>';
            }

            $strBody .= '<tr>';

            $col = 0;
            foreach($arrOption as $arrField)
            {
                if( !is_array($arrField) )
                {
                    $strBody .= '<td>' . $arrField . '</td>';
                }
                else
                {
                    $strName    = $arrField[0];
                    $strLabel   = $arrField[1];

                    if( strlen($strName) )
                    {
                        $strBody .= sprintf('<td><span><input type="radio" name="%s" id="opt_%s" class="radio" value="%s"%s%s%s <label id="lbl_%s" for="opt_%s">%s</label></span></td>',
                            $this->strName,
                            $this->strId . '_' . $i . '_' . $col,
                            $arrOption['rowTitle'] . ' ' . $strName,
                            $this->isChecked( array('value'=>$strName,'label'=>$strLabel) ),
                            $this->getAttributes(),
                            $this->strTagEnding,
                            $this->strId . '_' . $i . '_' . $col,
                            $this->strId . '_' . $i . '_' . $col,
                            $strLabel
                        );
                    }
                }

                $col++;
            }

            $strBody .= '</tr>';
        }

        if( !strlen($strBody) )
        {
            $strBody .= '</tbody>';
        }

        return ((strlen($strBody)) ? '<div class="widget widget-radio widget-radio-table"><table>' . $strHeader . $strBody . '</table></div>' : '');
    }
}
