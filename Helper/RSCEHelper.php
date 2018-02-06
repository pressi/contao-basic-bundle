<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\Helper;


/**
 * Class Helper
 * @package IIDO\BasicBundle
 */
class RSCEHelper extends \Frontend
{

    /**
     * Get Imagefield Config
     *
     * @param string|array $label
     *
     * @return array
     */
    public static function getImageFieldConfig( $label )
    {
        return array
        (
            'label'         => self::renderLabel( $label ),
            'inputType'     => 'fileTree',
            'eval'          => array
            (
                'filesOnly'         => true,
                'fieldType'         => 'radio',
                'tl_class'          => 'clr w50 hauto',
            )
        );
    }



    /**
     * Get Textareafield Config
     *
     * @param string|array $label
     * @param bool         $rte
     *
     * @return array
     */
    public static function getTextareaConfig( $label, $rte = true)
    {
        return array
        (
            'label'         => self::renderLabel( $label ),
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



    /**
     * Get Textfield Config
     *
     * @param string|array $label
     * @param bool         $newLine
     * @param bool         $isLong
     * @param array        $eval
     *
     * @return array
     */
    public static function getTextFieldConfig( $label, $newLine = false, $isLong = false, array $eval = array() )
    {
        $defaultEval = array
        (
            'tl_class'      => ($isLong ? 'long' : 'w50') . ($newLine ? ' clr': '')
        );

        if( count($eval) )
        {
            $defaultEval = array_merge($defaultEval, $eval);
        }

        return array
        (
            'label'         => self::renderLabel( $label ),
            'inputType'     => 'text',
            'eval'          => $defaultEval
        );
    }



    /**
     * Get Double Textfield Config
     * @param string|array $label
     * @param bool         $newLine
     * @param bool         $isLong
     *
     * @return array
     */
    public static function getDoubleTextFieldConfig( $label, $newLine = false, $isLong = false )
    {
        return array
        (
            'label'         => self::renderLabel( $label ),
            'inputType'     => 'text',
            'eval'          => array
            (
                'maxlength'         => 255,
                'multiple'          => true,
                'size'              => 2,
                'tl_class'          => ($isLong ? 'long' : 'w50') . ($newLine ? ' clr': '')
            )
        );
    }



    /**
     * Get Colorfield Config
     *
     * @param string|array $label
     * @param bool         $newLine
     *
     * @return array
     */
    public static function getColorFieldConfig( $label, $newLine = false )
    {
        return array
        (
            'label'         => self::renderLabel( $label ),
            'inputType'     => 'text',
            'eval'          => array
            (
                'maxlength'         => 64,
                'multiple'          => true,
                'size'              => 2,
                'colorpicker'       => true,
                'isHexColor'        => true,
                'decodeEntities'    => true,
                'tl_class'          => 'w50 wizard' . ($newLine ? ' clr': '')
            ),
        );
    }



    /**
     * Get Selectfield Config
     *
     * @param string|array $label
     * @param array        $arrOptions
     * @param bool         $includeBlank
     *
     * @return array
     */
    public static function getSelectFieldConfig( $label, $arrOptions, $includeBlank = false )
    {
        return array
        (
            'label'         => self::renderLabel( $label ),
            'inputType'     => 'select',
            'options'       => $arrOptions,
            'eval'          => array
            (
                'includeBlankOption'    => $includeBlank,
                'tl_class'              => 'w50'
            )
        );
    }



    /**
     * Get Checkboxfield Config
     *
     * @param string|array $label
     * @param array        $arrOptions
     * @param bool         $submitOnChange
     * @param bool         $clear
     *
     * @return array
     */
    public static function getCheckboxFieldConfig( $label, $arrOptions = array(), $submitOnChange = false, $clear = false )
    {
        $arrConfig = array
        (
            'label'         => self::renderLabel( $label ),
            'inputType'     => 'checkbox',
            'eval'          => array
            (
                'tl_class'          => ($clear ?  'clr ' : '') . 'w50'
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



    /**
     * Get Linkfield Config
     *
     * @param string|array $label
     *
     * @return array
     */
    public static function getLinkFieldConfig( $label )
    {
        return array
        (
            'label'         => self::renderLabel( $label ),
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



    /**
     * Get NewsPicker Field Config
     *
     * @param string|array $label
     *
     * @return array
     */
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
            'label'         => self::renderLabel( $label ),
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
            $arrConfig['eval']['blankOptionLabel'] = 'Keine News vorhanden!';
        }
        else
        {
            $arrConfig['eval']['blankOptionLabel'] = 'News wählen';
        }

        return $arrConfig;
    }



    /**
     * Get ImageSize Field Config
     *
     * @param string|array $label
     *
     * @return array
     */
    public static function getImageSizeFieldConfig( $label )
    {
        return array
        (
            'label'         => self::renderLabel( $label ),
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



    /**
     * Get ImageAlign Field Config
     *
     * @param string|array $label
     *
     * @return array
     */
    public static function getImageAlignFieldConfig( $label )
    {
        return array
        (
            'label'         => self::renderLabel( $label ),
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



    /**
     * Get PagePicker Field Config
     *
     * @param string|array $label
     *
     * @return array
     */
    public static function getPagePickerFieldConfig( $label )
    {
        return array
        (
            'label'         => self::renderLabel( $label ),
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


    /**
     * Get Input Unit Field Config
     *
     * @param string|array $label
     * @param array        $arrUnits
     * @param bool         $newLine
     * @param array        $eval
     *
     * @return array
     */
    public static function getUnitFieldConfig( $label, array $arrUnits = array(), $newLine = false, array $eval = array() )
    {
        if( !count($arrUnits) )
        {
            $arrUnits = $GLOBALS['TL_CSS_UNITS'];
        }

        $defaultEval = array
        (
            'maxlength'     => 200,
            'tl_class'      => ($newLine ?  'clr ' : '') . 'w50'
        );

        if( count($eval) )
        {
            $defaultEval = array_merge($defaultEval, $eval);
        }

        return array
        (
            'label'         => self::renderLabel( $label ),
            'inputType'     => 'inputUnit',
            'options'       => $arrUnits,
            'eval'          => $defaultEval
        );
    }



    /**
     * Render Field Label
     *
     * @param string $strLabel
     *
     * @return array
     */
    protected static function renderLabel( $strLabel )
    {
        if( !is_array($strLabel) )
        {
            $strLabel = array($strLabel, '');
        }

        return $strLabel;
    }



    /**
     * Get Group (Legend) Field Config
     *
     * @param string|array $label
     *
     * @return array
     */
    public static function getGroupConfig( $label )
    {
        return array
        (
            'label'         => self::renderLabel( $label ),
            'inputType'     => 'group',
        );
    }


    
    /**
     * Get Picture
     *
     * @param object $objClass
     * @param        $image
     * @param array  $arrSize
     *
     * @return mixed
     */
    public static function getPicture( &$objClass, $image, $arrSize = array() )
    {
        return $objClass->getImageObject($image, $arrSize);
    }



    public static function getPictureSRC( &$objClass, $image, $arrSize = array() )
    {
        $objPicture = self::getPicture($objClass, $image, $arrSize);

        return $objPicture->src;
    }



    public static function getImageTag( $image, $arrSize = array(), &$objClass = false, $returnPath = false )
    {
        $strContent = '';

        if( $image )
        {
            if( is_string($image) && $objClass )
            {
                $image      = $objClass->getImageObject($image, $arrSize);
            }

            if( $returnPath )
            {
                $strContent = $image->picture['img']['src'];
            }
            else
            {
                $strContent = '<figure class="image_container"><img src="' . trim($image->src?:$image->picture['img']['src']) . '" alt="' .  trim($image->alt?:$image->picture['alt']) . '"' . $image->imgSize . '></figure>';
            }
        }

        return $strContent;
    }

}
