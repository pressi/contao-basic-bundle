<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\Widget;


/**
 * Provide methods to handle list items.
 *
 * @property integer $maxlength
 *
 *
 */
class ListWizardWidget extends \ListWizard
{

	/**
	 * Generate the widget and return it as string
	 *
	 * @return string
	 */
	public function generate()
	{
	    $strContent = parent::generate();

	    if( $this->multiple )
        {
            $strInput   = '<input type="text" name="' . $this->strId . '[##ROW##][]" class="tl_text" value="##VALUE##"' . $this->getAttributes() . '>';
            $row        = 0;
            $useLabels  = false;
            $size       = ($this->size - 1);

            if( $this->labels && is_array($this->labels) && count($this->labels) )
            {
                $useLabels  = true;
                $strInput   = '<span class="input-cont">##LABEL##' . $strInput . '</span>';
            }

            $strContent = preg_replace_callback('/<input([A-Za-z0-9\s\-="_,;.:]{0,})name="' . $this->strId . '\[\]"([A-Za-z0-9\s\-="_,;.:]{0,})value="([A-Za-z0-9\s\-="_,;.:\/]{0,})"([A-Za-z0-9\s\-="_,;.:]{0,})>/', function( $matches ) use($strInput, &$row, $size, $useLabels)
            {
                $rowNum = $row;
                $input  = $strInput;
                $col    = 0;
                $addCheckbox = '';

                $input  = preg_replace('/##ROW##/', $rowNum, $input);

                if( $size > 1 )
                {
                    for( $i=0; $i<$size; $i++)
                    {
                        $strNewInput = $strInput;

                        if( $useLabels )
                        {
                            $strNewInput = preg_replace('/##LABEL##/', '<label>' . $this->labels[ $col ] . '</label>', $strNewInput);
                        }

                        $input .= preg_replace('/##VALUE##/', $this->varValue[ $rowNum ][ $col ], $strNewInput);

                        $col++;
                    }
                }
                else
                {
                    $input = preg_replace('/##VALUE##/', $this->varValue[ $rowNum ][ $col ], $input);

                    if( $useLabels )
                    {
                        $input = preg_replace('/##LABEL##/', '<label>' . $this->labels[ $col ] . '</label>', $input);
                    }
                }

                $defaultValue = $this->varValue[ $rowNum ][ ($col + 1) ];

                if( $this->addCheckbox )
                {
                    $checked = false;

                    if( $this->varValue[ $rowNum ][ ($col + 2) ] || $this->varValue[ $rowNum ][ ($col + 2) ] === "1" || $this->varValue[ $rowNum ][ ($col + 2) ] === 1 )
                    {
                        $checked = true;
                    }

                    $addCheckbox = '<input type="checkbox" class="tl_checkbox" name="' . $this->strId . '[' . $rowNum . '][]" value="1"' .  (($checked) ? ' checked' : '') . '>';

                    if( $useLabels )
                    {
                        $addCheckbox   = '<span class="input-cont is-checkbox"><label>' . $this->labels[ ($col + 2) ] . '</label>' . $addCheckbox . '</span>';
                    }
                }

                $row++;

                if( $useLabels )
                {
                    $input = $input . '<span class="input-cont"><label>' . $this->labels[ ($col + 1) ] . '</label>';

                    $addCheckbox = '</span>' . $addCheckbox;
                }

                return $input . '<input' . $matches[1] . 'name="' . $this->strId . '[' .  $rowNum . '][]"' . $matches[2] . 'value="' . $defaultValue . '"' . $matches[3] . '>' . $addCheckbox;
            }, $strContent);

            $strContent = preg_replace('/<li>/', '<li class="size-' . $this->size . ($this->addCheckbox ? ' has-checkbox' : '') . ($useLabels ? ' has-label' : '') . '">', $strContent);

            $strContent = preg_replace('/<script>Backend.listWizard\("ctrl_' . $this->strId . '"\)<\/script>/', '<script>IIDO.Backend.listWizard("ctrl_'.$this->strId.'")</script>', $strContent);
        }

        return $strContent;
	}
}
