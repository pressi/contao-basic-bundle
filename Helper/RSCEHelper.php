<?php
/******************************************************************
 *
 * (c) 2015 Stephan Preßl <development@prestep.at>
 * All rights reserved
 *
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *
 ******************************************************************/

namespace IIDO\BasicBundle\Helper;


/**
 * Class Helper
 * @package IIDO\BasicBundle
 */
class RSCEHelper extends \Frontend
{

    public static function getImageFieldConfig( $label )
    {
        return array
        (
            'label'         => $label,
            'inputType'     => 'fileTree',
            'eval'          => array
            (
                'filesOnly'         => true,
                'fieldType'         => 'radio',
                'tl_class'          => 'clr w50 hauto',
            )
        );
    }



    public static function getTextareaConfig( $label, $rte = true)
    {
        if( !is_array($label) )
        {
            $label = array($label, '');
        }

        return array
        (
            'label'         => $label,
            'inputType'     => 'textarea',
            'eval'          => array
            (
                'helpwizard'        => true,
                'rte'               => 'tinyMCE',
                'tl_class'          => 'clr'
            ),
            'explanation'   => 'insertTags'
        );
    }



    public static function getImageTag( $image )
    {
        $strContent = '';

        if( $image )
        {
            $strContent = '<figure class="image_container"><img src="' . $image->src . '" alt="' .  $image->alt . '"' . $image->imgSize . '></figure>';
        }

        return $strContent;
    }

}
