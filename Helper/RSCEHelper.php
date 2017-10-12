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



    public static function getTextFieldConfig( $label, $newLine = false )
    {
        if( !is_array($label) )
        {
            $label = array($label, '');
        }

        return array
        (
            'label'         => $label,
            'inputType'     => 'text',
            'eval'          => array
            (
                'tl_class'          => 'w50' . ($newLine ? ' clr': '')
            )
        );
    }



    public static function getSelectFieldConfig( $label, $arrOptions, $includeBlank = false )
    {
        if( !is_array($label) )
        {
            $label = array($label, '');
        }

        return array
        (
            'label'     => $label,
            'inputType' => 'select',
            'options'   => $arrOptions,
            'eval'      => array
            (
                'includeBlankOption'    => $includeBlank,
                'tl_class'              => 'w50'
            )
        );
    }



    public static function getCheckboxFieldConfig( $label, $arrOptions = array(), $submitOnChange = false, $clear = false )
    {
        if( !is_array($label) )
        {
            $label = array($label, '');
        }

        $arrConfig = array
        (
            'label'     => $label,
            'inputType' => 'checkbox',
            'eval'      => array
            (
                'tl_class'              => ($clear ?  'clr ' : '') . 'w50'
            )
        );

        if( count($arrOptions) )
        {
            $arrConfig['options']           = $arrOptions;
            $arrConfig['eval']['multiple']  = true;
        }
        else
        {
            $arrConfig['eval']['tl_class']  = $arrConfig['eval']['tl_class'] . ' m12';
        }

        if( $submitOnChange )
        {
            $arrConfig['eval']['submitOnChange']  = true;
        }

        return $arrConfig;
    }



    public static function getLinkFieldConfig( $label )
    {
        if( !is_array($label) )
        {
            $label = array($label, '');
        }

        return array
        (
            'label'         => $label,
            'inputType'     => 'text',
            'eval'          => array
            (
                'rgxp'              => 'url',
                'decodeEntities'    => true,
                'maxlength'         => 255,
                'dcaPicker'         => true,
                'tl_class'          => 'w50 wizard'
            )
        );
    }




    public static function getNewsPickerFieldConfig( $label )
    {
        $arrNewsOptions     = array();
        $objArchive         = \NewsArchiveModel::findAll();

        if( $objArchive )
        {
            while( $objArchive->next() )
            {
                $objNews = \NewsModel::findBy('pid', $objArchive->id);

                if( $objNews )
                {
                    $arrNewsOptions[ $objArchive->title ] = array();

                    while( $objNews->next() )
                    {
                        $arrNewsOptions[ $objArchive->title ][ $objNews->id ] = $objNews->headline . '(' . $objNews->id . ')';
                    }
                }
            }
        }

        if( count($arrNewsOptions) )
        {
            $arrNewsDefaultOptions = array
            (
                'Allgemein' => array
                (
                    'latest'        => 'Immer aktuellste News anzeigen'
                )
            );


            $arrNewsOptions = array_merge($arrNewsDefaultOptions, $arrNewsOptions);
        }

        $arrConfig = array
        (
            'label'         => $label,
            'inputType'     => 'select',
            'options'       => $arrNewsOptions,
            'eval'          => array
            (
                'includeBlankOption'    => true,
                'tl_class'              => 'w50'
            )
        );

        if( count($arrNewsOptions) === 0 )
        {
            $arrConfig['eval']['blankOptionLabel']    = 'Keine News vorhanden!';
        }
        else
        {
            $arrConfig['eval']['blankOptionLabel']    = 'News wählen';
        }

        return $arrConfig;
    }



    public static function getImageSizeFieldConfig( $label )
    {
        if( !is_array($label) )
        {
            $label = array($label, '');
        }

        return array
        (
            'label'         => $label,
            'inputType'     => 'imageSize',
            'reference'     => &$GLOBALS['TL_LANG']['MSC'],
            'eval'          => array
            (
                'rgxp'               => 'natural',
                'includeBlankOption' => true,
                'nospace'            => true,
                'helpwizard'         => true,
                'tl_class'           => 'clr w50'
            ),
            'options_callback' => function ()
            {
                return \System::getContainer()->get('contao.image.image_sizes')->getOptionsForUser(\BackendUser::getInstance());
            },
        );
    }



    public static function getImageAlignFieldConfig( $label )
    {
        if( !is_array($label) )
        {
            $label = array($label, '');
        }

        return array
        (
            'label'         => $label,
            'default'       => 'above',
            'inputType'     => 'radioTable',
            'options'       => array('above', 'left', 'right', 'below'),
            'eval'          => array
            (
                'cols'          => 4,
                'tl_class'      => 'w50'
            ),
            'reference'     => &$GLOBALS['TL_LANG']['MSC'],
        );
    }



    public static function getPagePickerFieldConfig( $label )
    {
        if( !is_array($label) )
        {
            $label = array($label, '');
        }

        return array
        (
            'label'         => $label,
            'inputType'     => 'pageTree',
            'foreignKey'    => 'tl_page.title',
            'eval'          => array
            (
                'fieldType'     => 'radio'
            ),
            'relation'      => array
            (
                'type'          => 'hasOne',
                'load'          => 'lazy'
            )
        );
    }



    public static function getGroupConfig( $label )
    {
        if( !is_array($label) )
        {
            $label = array($label, '');
        }

        return array
        (
            'label'     => $label,
            'inputType' => 'group',
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
