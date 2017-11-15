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
class DcaHelper extends \Frontend
{
    protected static $paletteStart    = '{type_legend},type,headline;';
    protected static $paletteEnd      = '{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space;{invisible_legend:hide},invisible,start,stop;';



    public static function addField($fieldName, $fieldType, $strTable, $eval = array(), $classes = '', $replaceClasses = false, $defaultValue = '', $defaultConfig = array())
    {
        $arrFieldType   = explode("_", $fieldType);

        $fieldType      = $arrFieldType[0];
        $typeAdd        = (($arrFieldType[1] === "short" || $arrFieldType[1] === "selector" || $arrFieldType[1] === "rte") ? TRUE : FALSE);
        $langTable      = (strlen($arrFieldType[2]) ? 'tl_' . $arrFieldType[2] : '');

        $arrName    = explode('_', $fieldName);
        $fieldName  = $arrName[0];

        if( !$langTable && count($arrName) > 1 && strlen($arrName[1]) )
        {
            $langTable  = 'tl_' . $arrName[1];
        }

        switch( strtolower($fieldType) )
        {
            case "checkbox":
                self::addCheckboxField($fieldName, $strTable, $eval, $classes, $replaceClasses, $typeAdd, $langTable);
//                self::addCheckboxField($fieldName, $strTable, $eval, $classes, $replaceClasses, $typeAdd, $langTable, $defaultConfig);
                break;

            case "text":
            case "textfield":
                self::addTextField($fieldName, $strTable, $eval, $classes, $replaceClasses, $langTable);
//            self::addTextField($fieldName, $strTable, $eval, $classes, $replaceClasses, $langTable, $defaultConfig);
                break;

            case "color":
            case "colorfield":
                self::addColorField($fieldName, $strTable, $eval, $classes, $replaceClasses, $langTable);
                break;

            case "textarea":
                self::addTextareaField($fieldName, $strTable, $eval, $classes, $replaceClasses, $typeAdd, $langTable);
//                self::addTextareaField($fieldName, $strTable, $eval, $classes, $replaceClasses, $typeAdd, $langTable, $defaultConfig);
                break;

            case "select":
                if( $arrFieldType[ 1 ] === "selector" )
                {
                    $typeAdd    = FALSE;
                    $isSelector = TRUE;
                }

//                self::addSelectField($fieldName, $strTable, $eval, $classes, $replaceClasses, $defaultValue, $typeAdd, $langTable);
                self::addSelectField($fieldName, $strTable, $eval, $classes, $replaceClasses, $defaultValue, $typeAdd, $isSelector, $langTable, $defaultConfig);
                break;

            case "size":
            case "imagesize":
                self::addImageSizeField($fieldName, $strTable, $eval, $classes, $replaceClasses, $typeAdd, $langTable);
                break;

            case "filetree":
            case "singlesrc":
            case "image":
            case "imagefield":
                self::addImageField($fieldName, $strTable, $eval, $classes, $replaceClasses, $langTable);
//            self::addImageField($fieldName, $strTable, $eval, $classes, $replaceClasses, $langTable, $defaultConfig);
                break;

            case "multisrc":
            case "gallery":
            case "images":
            case "imagesfield":
                self::addImagesField($fieldName, $strTable, $eval, $classes, $replaceClasses, $langTable);
                break;

            case "page":
            case "pagetree":
                self::addPageField($fieldName, $strTable, $eval, $classes, $replaceClasses, $langTable);
                break;

            case "url":
            case "link":
                self::addUrlField($fieldName, $strTable, $eval, $classes, $replaceClasses, $langTable);
                break;
        }
    }


    public static function addPalette($strName, $arrFields, $strTable, $override = FALSE, $addDefaultBefore = TRUE, $addDefaultAfter = TRUE)
    {
        $strFields = $arrFields;

        if( is_array($arrFields) )
        {
            $strFields = implode(",", $arrFields);
        }

        if( key_exists($strName, $GLOBALS['TL_DCA'][ $strTable ]['palettes']) )
        {
            if( !$override )
            {
                $arrNewFields   = explode(",", $strFields);
                $arrUsedFields  = explode(",", $GLOBALS['TL_DCA'][ $strTable ]['palettes'][ $strName ]);

                foreach($arrNewFields as $strField)
                {
                    if( !in_array($strField, $arrUsedFields))
                    {
                        $arrUsedFields[] = $strField;
                    }
                }

                $strFields = implode(",", $arrUsedFields);
            }
        }

        if( $addDefaultBefore )
        {
            $strFields = static::$paletteStart . $strFields;
        }

        if( $addDefaultAfter )
        {
            $strFields = $strFields . static::$paletteEnd;
        }

        $GLOBALS['TL_DCA'][ $strTable ]['palettes'][ $strName ] = $strFields;
    }



    public static function replacePaletteFields($strName, $oldFields, $newFields, $strTable)
    {
        $GLOBALS['TL_DCA'][ $strTable ]['palettes'][ $strName ] = preg_replace('/' . $oldFields . '/', $newFields, $GLOBALS['TL_DCA'][ $strTable ]['palettes'][ $strName ]);
    }



    public static function addSubpalette($strName, $arrFields, $strTable, $override = FALSE)
    {
        $strFields = $arrFields;

        if( is_array($arrFields) )
        {
            $strFields = implode(",", $arrFields);
        }

        if( key_exists($strName, $GLOBALS['TL_DCA'][ $strTable ]['subpalettes']) )
        {
            if( !$override )
            {
                $arrNewFields   = explode(",", $strFields);
                $arrUsedFields  = explode(",", $GLOBALS['TL_DCA'][ $strTable ]['subpalettes'][ $strName ]);

                foreach($arrNewFields as $strField)
                {
                    if( !in_array($strField, $arrUsedFields))
                    {
                        $arrUsedFields[] = $strField;
                    }
                }

                $strFields = implode(",", $arrUsedFields);
            }

            $GLOBALS['TL_DCA'][ $strTable ]['subpalettes'][ $strName ] = $strFields;
        }
        else
        {
            $GLOBALS['TL_DCA'][ $strTable ]['subpalettes'][ $strName ] = $strFields;
        }
    }



    public static function replaceSubpaletteFields($strName, $oldFields, $newFields, $strTable)
    {
        $GLOBALS['TL_DCA'][ $strTable ]['subpalettes'][ $strName ] = preg_replace('/' . $oldFields . '/', $newFields, $GLOBALS['TL_DCA'][ $strTable ]['subpalettes'][ $strName ]);
    }



    public static function copyFieldFromTable($fieldName, $strTable, $fromFieldName, $fromFieldTable)
    {
        if( !preg_match('/^tl_/', $fromFieldTable) )
        {
            $fromFieldTable = 'tl_' . $fromFieldTable;
        }

        \Controller::loadLanguageFile( $fromFieldTable );
        \Controller::loadDataContainer( $fromFieldTable );

        $GLOBALS['TL_DCA'][ $strTable ]['fields'][ $fieldName ] = $GLOBALS['TL_DCA'][ $fromFieldTable ]['fields'][ $fromFieldName ];
    }



    public static function copyField($fieldName, $strTable, $fromFieldName)
    {
        $GLOBALS['TL_DCA'][ $strTable ]['fields'][ $fieldName ]                   = $GLOBALS['TL_DCA'][ $strTable ]['fields'][ $fromFieldName ];
        $GLOBALS['TL_DCA'][ $strTable ]['fields'][ $fieldName ]['label']          = &$GLOBALS['TL_LANG'][ $strTable ][ $fieldName ];
    }



    protected static function addCheckboxField($fieldName, $strTable, $eval = array(), $classes = '', $replaceClasses = false, $isSelector = false, $langTable = '')
    {
        if( strlen($langTable) )
        {
            \Controller::loadLanguageFile( $langTable );
        }

        $defaultEval = array
        (
            'tl_class'          => ($replaceClasses ? $classes : 'w50 m12' . (strlen($classes) ? ' ' . $classes : ''))
        );

        if( count($eval) )
        {
            $defaultEval = array_merge($defaultEval, $eval);
        }

        if( $isSelector )
        {
            $defaultEval['submitOnChange'] = true;

            $GLOBALS['TL_DCA'][ $strTable ]['palettes']['__selector__'][] = $fieldName;
        }

        $GLOBALS['TL_DCA'][ $strTable ]['fields'][ $fieldName ] = array
        (
            'label'                 => &$GLOBALS['TL_LANG'][ $langTable?:$strTable ][ $fieldName ],
            'inputType'             => "checkbox",
            'eval'                  => $defaultEval,
            'sql'                   => "char(1) NOT NULL default ''"
        );
    }



    protected static function addTextField($fieldName, $strTable, $eval = array(), $classes = '', $replaceClasses = false, $langTable = '')
    {
        if( strlen($langTable) )
        {
            \Controller::loadLanguageFile( $langTable );
        }

        $defaultEval = array
        (
            'maxlength'         => 255,
            'decodeEntities'    => TRUE,
            'tl_class'          => ($replaceClasses ? $classes : 'w50' . (strlen($classes) ? ' ' . $classes : ''))
        );

        if( count($eval) )
        {
            $defaultEval = array_merge($defaultEval, $eval);
        }

        $GLOBALS['TL_DCA'][ $strTable ]['fields'][ $fieldName ] = array
        (
            'label'                 => &$GLOBALS['TL_LANG'][ $langTable?:$strTable ][ $fieldName ],
            'exclude'               => TRUE,
            'inputType'             => 'text',
            'eval'                  => $defaultEval,
            'sql'                   => "varchar(255) NOT NULL default ''"
        );
    }



    protected static function addColorField($fieldName, $strTable, $eval = array(), $classes = '', $replaceClasses = false, $langTable = '')
    {
        if( strlen($langTable) )
        {
            \Controller::loadLanguageFile( $langTable );
        }

        $defaultEval = array
        (
            'maxlength'         => 6,
            'multiple'          => TRUE,
            'size'              => 2,
            'colorpicker'       => TRUE,
            'isHexColor'        => TRUE,
            'decodeEntities'    => TRUE,
            'tl_class'          => ($replaceClasses ? $classes : 'w50 wizard' . (strlen($classes) ? ' ' . $classes : ''))
        );

        if( count($eval) )
        {
            $defaultEval = array_merge($defaultEval, $eval);
        }

        $GLOBALS['TL_DCA'][ $strTable ]['fields'][ $fieldName ] = array
        (
            'label'                 => &$GLOBALS['TL_LANG'][ $langTable?:$strTable ][ $fieldName ],
            'inputType'             => 'text',
            'eval'                  => $defaultEval,
            'sql'                   => "varchar(64) NOT NULL default ''"
        );
    }



    protected static function addSelectField($fieldName, $strTable, $eval = array(), $classes = '', $replaceClasses = false, $defaultValue = '', $shortOptions = FALSE, $isSelector = FALSE, $langTable = '', $defaultConfig = array())
    {
        if( strlen($langTable) )
        {
            \Controller::loadLanguageFile( $langTable );
        }

        $defaultEval = array
        (
            'maxlength'         => 32,
            'tl_class'          => ($replaceClasses ? $classes : 'w50' . (strlen($classes) ? ' ' . $classes : ''))
        );

        if( count($eval) )
        {
            $defaultEval = array_merge($defaultEval, $eval);
        }

        $arrOptions = $GLOBALS['TL_LANG'][ $langTable?:$strTable ]['options'][ $fieldName ];

        if( $shortOptions )
        {
            $arrOptions = $GLOBALS['TL_LANG'][ $langTable?:$strTable ]['options_' . $fieldName ];
        }

        if( $isSelector )
        {
            $defaultEval['submitOnChange'] = TRUE;

            $GLOBALS['TL_DCA'][ $strTable ]['palettes']['__selector__'][] = $fieldName;
        }

        $GLOBALS['TL_DCA'][ $strTable ]['fields'][ $fieldName ] = array
        (
            'label'                 => &$GLOBALS['TL_LANG'][ $langTable?:$strTable ][ $fieldName ],
            'exclude'               => TRUE,
            'inputType'             => 'select',
            'options'               => $arrOptions,
            'eval'                  => $defaultEval,
            'sql'                   => "varchar(" . $defaultEval['maxlength']?:32  . ") NOT NULL default ''"
        );

        if( strlen($defaultValue) )
        {
            $GLOBALS['TL_DCA'][ $strTable ]['fields'][ $fieldName ]['default'] = $defaultValue;
        }

        if( count( $defaultConfig) )
        {
            foreach( $defaultConfig as $configKey => $configValue )
            {
                $GLOBALS['TL_DCA'][ $strTable ]['fields'][ $fieldName ][ $configKey ] = $configValue;
            }
        }
    }



    protected static function addImageField($fieldName, $strTable, $eval = array(), $classes = '', $replaceClasses = false, $langTable = '')
    {
        \Controller::loadDataContainer("tl_content");

        if( strlen($langTable) )
        {
            \Controller::loadLanguageFile( $langTable );
        }

        $GLOBALS['TL_DCA'][ $strTable ]['fields'][ $fieldName ]                     = $GLOBALS['TL_DCA']['tl_content']['fields']['singleSRC'];

        $defaultEval = array
        (
            'mandatory'         => FALSE,
            'tl_class'          => ($replaceClasses ? $classes : 'clr w50 hauto' . (strlen($classes) ? ' ' . $classes : ''))
        );

        if( count($eval) )
        {
            $defaultEval = array_merge($GLOBALS['TL_DCA'][ $strTable ]['fields'][ $fieldName ]['eval'], $defaultEval, $eval);
        }
        else
        {
            $defaultEval = array_merge($GLOBALS['TL_DCA'][ $strTable ]['fields'][ $fieldName ]['eval'], $defaultEval);
        }

        $GLOBALS['TL_DCA'][ $strTable ]['fields'][ $fieldName ]['label']            = &$GLOBALS['TL_LANG'][ $langTable?:$strTable ][ $fieldName ];
        $GLOBALS['TL_DCA'][ $strTable ]['fields'][ $fieldName ]['eval']             = $defaultEval;
        $GLOBALS['TL_DCA'][ $strTable ]['fields'][ $fieldName ]['load_callback']    = array();
        $GLOBALS['TL_DCA'][ $strTable ]['fields'][ $fieldName ]['save_callback']    = array();
    }



    protected static function addTextareaField($fieldName, $strTable, $eval = array(), $classes = '', $replaceClasses = false, $useRTE = false, $langTable = '')
    {
        \Controller::loadDataContainer("tl_content");

        if( strlen($langTable) )
        {
            \Controller::loadLanguageFile( $langTable );
        }

        $GLOBALS['TL_DCA'][ $strTable ]['fields'][ $fieldName ]                = $GLOBALS['TL_DCA']['tl_content']['fields']['text'];

        $defaultEval = array
        (
            'mandatory'         => FALSE,
            'tl_class'          => ($replaceClasses ? $classes : 'clr' . (strlen($classes) ? ' ' . $classes : ''))
        );

        if( $useRTE )
        {
            $defaultEval['rte']         = 'tinyMCE';
            $defaultEval['helpwizard']  = true;
        }
        else
        {
            $defaultEval['decodeEntities']  = true;
            $defaultEval['style']           = 'height:60px';
        }

        if( count($eval) )
        {
            $defaultEval = array_merge($GLOBALS['TL_DCA'][ $strTable ]['fields'][ $fieldName ]['eval'], $defaultEval, $eval);
        }

        if( !$useRTE )
        {
            unset( $defaultEval['rte'] );
            unset( $defaultEval['helpwizard'] );

            unset( $GLOBALS['TL_DCA'][ $strTable ]['fields'][ $fieldName ]['explanation'] );
        }

        unset( $GLOBALS['TL_DCA'][ $strTable ]['fields'][ $fieldName ]['search'] );

        $GLOBALS['TL_DCA'][ $strTable ]['fields'][ $fieldName ]['label']    = &$GLOBALS['TL_LANG'][ $langTable?:$strTable ][ $fieldName ];
        $GLOBALS['TL_DCA'][ $strTable ]['fields'][ $fieldName ]['eval']     = $defaultEval;
        $GLOBALS['TL_DCA'][ $strTable ]['fields'][ $fieldName ]['sql']      = $useRTE ? "mediumtext NULL" : "text NULL";
    }



    protected static function addImageSizeField($fieldName, $strTable, $eval = array(), $classes = '', $replaceClasses = false, $shortOptions = false, $langTable = '')
    {
        if( strlen($langTable) )
        {
            \Controller::loadLanguageFile( $langTable );
        }

        $defaultEval = array
        (
            'rgxp'                  => 'natural',
            'includeBlankOption'    => true,
            'nospace'               => true,
            'helpwizard'            => true,
            'tl_class'              => ($replaceClasses ? $classes : 'w50 bg-size' . (strlen($classes) ? ' ' . $classes : ''))
        );

        if( count($eval) )
        {
            $defaultEval = array_merge($defaultEval, $eval);
        }

        $arrOptions = $GLOBALS['TL_LANG'][ $langTable?:$strTable ]['options'][ $fieldName ];

        if( $shortOptions )
        {
            $arrOptions = $GLOBALS['TL_LANG'][ $langTable?:$strTable ]['options_' . $fieldName ];
        }

        $GLOBALS['TL_DCA'][ $strTable ]['fields'][ $fieldName ] = array
        (
            'label'                 => &$GLOBALS['TL_LANG'][ $langTable?:$strTable ][ $fieldName ],
            'inputType'             => 'imageSize',
            'options'               => $arrOptions,
            'eval'                  => $defaultEval,
            'sql'                   => "varchar(64) NOT NULL default ''"
        );
    }



    protected static function addImagesField($fieldName, $strTable, $eval = array(), $classes = '', $replaceClasses = false, $langTable = '')
    {
        if( strlen($langTable) )
        {
            \Controller::loadLanguageFile( $langTable );
        }

        $GLOBALS['TL_DCA'][ $strTable ]['fields'][ $fieldName ]                = $GLOBALS['TL_DCA']['tl_content']['fields']['multiSRC'];
        $GLOBALS['TL_DCA'][ $strTable ]['fields'][ $fieldName ]['label']       = &$GLOBALS['TL_LANG'][ $langTable?:$strTable ][ $fieldName ];

        $GLOBALS['TL_DCA'][ $strTable ]['fields'][ $fieldName ]['eval']['mandatory']    = FALSE;
        $GLOBALS['TL_DCA'][ $strTable ]['fields'][ $fieldName ]['eval']['isGallery']    = TRUE;
        $GLOBALS['TL_DCA'][ $strTable ]['fields'][ $fieldName ]['eval']['extensions']   = \Config::get('validImageTypes');
        $GLOBALS['TL_DCA'][ $strTable ]['fields'][ $fieldName ]['eval']['tl_class']     = ($replaceClasses ? $classes : 'clr w50 hauto' . (strlen($classes) ? ' ' . $classes : ''));

        if( count($eval) )
        {
            $GLOBALS['TL_DCA'][ $strTable ]['fields'][ $fieldName ]['eval'] = array_merge($GLOBALS['TL_DCA'][ $strTable ]['fields'][ $fieldName ]['eval'], $eval);
        }
    }



    protected static function addPageField($fieldName, $strTable, $eval = array(), $classes = '', $replaceClasses = false, $langTable = '')
    {
        if( strlen($langTable) )
        {
            \Controller::loadLanguageFile( $langTable );
        }

        $defaultEval = array
        (
            'fieldType'         => 'radio',
            'tl_class'          => ($replaceClasses ? $classes : 'w50 hauto' . (strlen($classes) ? ' ' . $classes : ''))
        );

        if( count($eval) )
        {
            $defaultEval = array_merge($defaultEval, $eval);
        }

        $GLOBALS['TL_DCA'][ $strTable ]['fields'][ $fieldName ] = array
        (
            'label'                 => &$GLOBALS['TL_LANG'][ $langTable?:$strTable ][ $fieldName ],
            'exclude'               => true,
            'inputType'             => 'pageTree',
            'foreignKey'            => 'tl_page.title',
            'eval'                  => $defaultEval,
            'sql'                   => "int(10) unsigned NOT NULL default '0'",
            'relation'              => array('type'=>'hasOne', 'load'=>'lazy')
        );
    }



    protected static function addUrlField($fieldName, $strTable, $eval = array(), $classes = '', $replaceClasses = false, $langTable = '')
    {
        if( strlen($langTable) )
        {
            \Controller::loadLanguageFile( $langTable );
        }

        $defaultEval = array
        (
            'mandatory'         => true,
            'rgxp'              => 'url',
            'decodeEntities'    => true,
            'maxlength'         => 255,
            'tl_class'          => ($replaceClasses ? $classes : 'w50' . (strlen($classes) ? ' ' . $classes : ''))
        );

        if( count($eval) )
        {
            $defaultEval = array_merge($defaultEval, $eval);
        }

        $GLOBALS['TL_DCA'][ $strTable ]['fields'][ $fieldName ] = array
        (
            'label'                 => &$GLOBALS['TL_LANG'][ $langTable?:$strTable ][ $fieldName ],
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => $defaultEval,
            'sql'                   => "varchar(" . $defaultEval['maxlength']?:255 . ") NOT NULL default ''"
        );
    }



    public static function addProtectedFieldsToTable( $strTable, $toPalette = '', $replaceLegend = '', $replacePosition = 'after' )
    {
        \Controller::loadLanguageFile( $strTable );

        $strLegend = '{protected_legend:hide},protected;';

        $GLOBALS['TL_DCA'][ $strTable ]['fields']['protected'] = array
        (
            'label'                   => &$GLOBALS['TL_LANG'][ $strTable ]['protected'],
            'exclude'                 => true,
            'filter'                  => true,
            'inputType'               => 'checkbox',
            'eval'                    => array
            (
                'submitOnChange'    => true
            ),
            'sql'                     => "char(1) NOT NULL default ''"
        );

        $GLOBALS['TL_DCA'][ $strTable ]['fields']['groups'] = array
        (
            'label'                   => &$GLOBALS['TL_LANG'][ $strTable ]['groups'],
            'exclude'                 => true,
            'inputType'               => 'checkbox',
            'foreignKey'              => 'tl_member_group.name',
            'eval'                    => array
            (
                'mandatory'         => true,
                'multiple'          => true
            ),
            'sql'                     => "blob NULL",
            'relation'                => array('type'=>'hasMany', 'load'=>'lazy')
        );

        $GLOBALS['TL_DCA'][ $strTable ]['palettes']['__selector__'][] = 'protected';
        $GLOBALS['TL_DCA'][ $strTable ]['subpalettes']['protected'] = 'groups';

        self::renderTableLegend( $strTable, $strLegend, $toPalette, $replaceLegend, $replacePosition);
    }



    public static function addPublishedFieldsToTable( $strTable, $toPalette = '', $replaceLegend = '', $replacePosition = 'after' )
    {
        \Controller::loadLanguageFile( $strTable );

        $strLegend = '{publish_legend},published,start,stop;';

        $GLOBALS['TL_DCA'][ $strTable ]['fields']['published'] = array
        (
            'label'                   => &$GLOBALS['TL_LANG'][ $strTable ]['published'],
            'exclude'                 => true,
            'filter'                  => true,
            'flag'                    => 1,
            'inputType'               => 'checkbox',
            'eval'                    => array
            (
                'doNotCopy'         => true
            ),
            'sql'                     => "char(1) NOT NULL default ''"
        );

        $GLOBALS['TL_DCA'][ $strTable ]['fields']['start'] = array
        (
            'label'                   => &$GLOBALS['TL_LANG'][ $strTable ]['start'],
            'exclude'                 => true,
            'inputType'               => 'text',
            'eval'                    => array
            (
                'rgxp'              => 'datim',
                'datepicker'        => true,
                'tl_class'          => 'w50 wizard'
            ),
            'sql'                     => "varchar(10) NOT NULL default ''"
        );

        $GLOBALS['TL_DCA'][ $strTable ]['fields']['stop'] = array
        (
            'label'                   => &$GLOBALS['TL_LANG'][ $strTable ]['stop'],
            'exclude'                 => true,
            'inputType'               => 'text',
            'eval'                    => array
            (
                'rgxp'              => 'datim',
                'datepicker'        => true,
                'tl_class'          => 'w50 wizard'
            ),
            'sql'                     => "varchar(10) NOT NULL default ''"
        );

        unset($GLOBALS['TL_DCA'][ $strTable ]['config']['sql']['keys']['pid']);
        $GLOBALS['TL_DCA'][ $strTable ]['config']['sql']['keys']['pid,start,stop,published'] = 'index';

        self::renderTableLegend( $strTable, $strLegend, $toPalette, $replaceLegend, $replacePosition);
    }



    protected static function renderTableLegend( $strTable, $strLegend, $toPalette = '', $replaceLegend = false, $replacePosition = 'after')
    {
        if( !strlen($toPalette) )
        {
            foreach($GLOBALS['TL_DCA'][ $strTable ]['palettes'] as $strPalette => $strFields)
            {
                if( $strPalette === "__selector__" )
                {
                    continue;
                }

                $strFields = self::renderLegendFields( $strFields, $strLegend, $replaceLegend, $replacePosition);

                $GLOBALS['TL_DCA'][ $strTable ]['palettes'][ $strPalette ] = $strFields;
            }
        }
        else
        {
            $strFields = self::renderLegendFields( $GLOBALS['TL_DCA'][ $strTable ]['palettes'][ $toPalette ], $strLegend, $replaceLegend, $replacePosition);

            $GLOBALS['TL_DCA'][ $strTable ]['palettes'][ $toPalette ] = $strFields;
        }
    }



    protected static function renderLegendFields( $strFields, $strLegend, $replaceLegend = false, $replacePosition = 'after' )
    {
        if( $replaceLegend )
        {
            if( !preg_match('/_legend$/', $replaceLegend) )
            {
                $replaceLegend = $replaceLegend . '_legend';
            }

            if( $replacePosition === "after" )
            {
                $strFields = preg_replace('/\{' . preg_quote($replaceLegend, '/') . '([A-Za-z0-9\-:]{0,})\},([A-Za-z0-9\-_,;.:]);/', '{' . $replaceLegend . '$1},$2;' . $strLegend, $strFields);
            }
            else
            {
                $strFields = preg_replace('/\{' . preg_quote($replaceLegend, '/') . '([A-Za-z0-9\-:]{0,})\}/', $strLegend . '{' . $replaceLegend . '$1}', $strFields);
            }
        }
        else
        {
            $strFields = $strFields . $strLegend;
        }

        return $strFields;
    }
}
