<?php
/*******************************************************************
 * (c) 2020 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\Widget;


use Contao\BackendUser;
use Contao\System;
use Contao\Widget;


/**
 * Provide methods to handle image size fields.
 *
 * @property integer $maxlength
 * @property array   $options
 *
 *
 */
class ImageSizeWidget extends \ImageSize
{

    /**
     * Trim values
     *
     * @param mixed $varInput
     *
     * @return mixed
     */
    protected function validator( $varInput )
    {
        $strTable       = $this->arrConfiguration['strTable'];
        $strField       = $this->arrConfiguration['strField'];

        $varInput[2]    = preg_replace('/[^a-z0-9_]+/', '', $varInput[2]);

        if (!is_numeric($varInput[2]))
        {
            switch ($varInput[2])
            {
                // Validate relative dimensions - width or height required
                case 'proportional':
                case 'box':
                    $this->mandatory = !$varInput[0] && !$varInput[1];
                    break;

                // Validate exact dimensions - width and height required
                case 'crop':
                case 'left_top':
                case 'center_top':
                case 'right_top':
                case 'left_center':
                case 'center_center':
                case 'right_center':
                case 'left_bottom':
                case 'center_bottom':
                case 'right_bottom':
                    $this->mandatory = !$varInput[0] || !$varInput[1];
                    break;
            }

            $varInput[0] = Widget::validator($varInput[0]);
            $varInput[1] = Widget::validator($varInput[1]);
        }

        $this->import(BackendUser::class, 'User');

        $imageSizes = System::getContainer()->get('contao.image.image_sizes');
        $this->arrAvailableOptions = $this->User->isAdmin ? $imageSizes->getAllOptions() : $imageSizes->getOptionsForUser($this->User);

        if( preg_match('/bg-size/', $GLOBALS['TL_DCA'][ $strTable ]['fields'][ $strField ]['eval']['tl_class']) || preg_match('/mapSize|dlh_googlemap_size/', $strField) )
        {
            $this->arrAvailableOptions = $this->renderAvailableOptions( $GLOBALS['TL_DCA'][ $strTable ]['fields'][ $strField ]['options'] );
        }

        if (!$this->isValidOption($varInput[2]))
        {
            $this->addError(sprintf($GLOBALS['TL_LANG']['ERR']['invalid'], $varInput[2]));
        }

        return $varInput;
    }



    protected function renderAvailableOptions( $arrOptions )
    {
        $arrAvailableOptions = array();

        foreach($arrOptions as $strGroup => $arrValues )
        {
            if( !is_array($arrValues) )
            {
                $arrAvailableOptions['exact'][] = $strGroup;
            }
        }

        return $arrAvailableOptions;
    }
}
