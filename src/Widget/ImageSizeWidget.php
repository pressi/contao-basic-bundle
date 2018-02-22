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
 * Provide methods to handle image size fields.
 *
 * @property integer $maxlength
 * @property array   $options
 *
 * @author Leo Feyer <https://github.com/leofeyer>
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
    protected function validator($varInput)
    {
        $strTable       = $this->arrConfiguration['strTable'];
        $strField       = $this->arrConfiguration['strField'];

        $this->import('BackendUser', 'User');
        \Controller::loadDataContainer( $strTable );

        $varInput[0] = \Widget::validator($varInput[0]);
        $varInput[1] = \Widget::validator($varInput[1]);
        $varInput[2] = preg_replace('/[^a-z0-9_]+/', '', $varInput[2]);

        $imageSizes = \System::getContainer()->get('contao.image.image_sizes');
        $this->arrAvailableOptions = $this->User->isAdmin ? $imageSizes->getAllOptions() : $imageSizes->getOptionsForUser($this->User);
//echo "<pre>";
//print_r($this->arrAvailableOptions);
//exit;
        if( preg_match('/bg-size/', $GLOBALS['TL_DCA'][ $strTable ]['fields'][ $strField ]['eval']['tl_class']) || preg_match('/mapSize|dlh_googlemap_size/', $strField) )
        {
            $this->arrAvailableOptions = $this->renderAvailableOptions( $GLOBALS['TL_DCA'][ $strTable ]['fields'][ $strField ]['options'] );
        }
//print_r( $this->arrAvailableOptions );
//        exit;
        if( !$this->isValidOption($varInput[2]) )
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
                $arrAvailableOptions['-'][] = $strGroup;
            }
//            if( isset($arrValues['value']) && strlen($arrValues['value']) )
//            {
//                $arrAvailableOptions[ 'options' ][] = $arrValues['value'];
//            }
//            else
//            {
//                if( $arrValues['label'] != '-' )
//                {
//                    $arrAvailableOptions[ $strGroup ][] = $arrValues['value'];
//                }
//            }
        }

        return $arrAvailableOptions;
    }
}
