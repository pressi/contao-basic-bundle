<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\Helper;


use IIDO\BasicBundle\Config\BundleConfig;
use IIDO\BasicBundle\Table\AllTables;


/**
 * Class DCA Helper
 *
 * @package IIDO\BasicBundle
 */
class DcaHelper extends \Frontend
{
    protected static $paletteStart    = '{type_legend},type,headline;';
    protected static $paletteEnd      = '{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space;{invisible_legend:hide},invisible,start,stop';



    /**
     * Create new table dca
     *
     * @param string  $strTable
     * @param boolean $isFile
     * @param boolean $addPublished
     */
    public static function createNewTable( $strTable, $isFile = false, $addPublished = false )
    {
        $GLOBALS['TL_DCA'][ $strTable ] = array
        (
            'config'        => array
            (
                'dataContainer'     => ($isFile ? 'File' : 'Table')
            ),

            'palettes'      => array
            (
                '__selector__'      => array(),
                'default'           => ''
            ),

            'subpalettes'   => array(),
            'fields'        => array()
        );

        if( $isFile )
        {
            $GLOBALS['TL_DCA'][ $strTable ]['config']['closed'] = true;
        }
        else
        {
            $GLOBALS['TL_DCA'][ $strTable ]['config']['switchToEdit']       = true;
            $GLOBALS['TL_DCA'][ $strTable ]['config']['enableVersioning']   = true;

            $GLOBALS['TL_DCA'][ $strTable ]['config']['sql']['keys']['id']    = 'primary';

            $GLOBALS['TL_DCA'][ $strTable ]['fields'] = array
            (
                'id'        => array( 'sql' => "int(10) unsigned NOT NULL auto_increment" ),
                'tstamp'    => array( 'sql' => "int(10) unsigned NOT NULL default '0'" )
            );
        }

        if( $addPublished )
        {
            self::addPublishedFieldsToTable($strTable, 'default', '', 'after', true);
        }
    }



    public static function addField($fieldName, $fieldType, $strTable, $eval = array(), $classes = '', $replaceClasses = false, $defaultValue = '', $defaultConfig = array())
    {
        $arrFieldType   = explode("__", $fieldType);

        $fieldType      = $arrFieldType[0];
        $typeAdd        = (($arrFieldType[1] === "short" || $arrFieldType[1] === "selector" || $arrFieldType[1] === "rte" || $arrFieldType[1] === "search") ? TRUE : FALSE);
        $langTable      = (strlen($arrFieldType[2]) ? 'tl_' . $arrFieldType[2] : '');

        $arrName    = explode('__', $fieldName);
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

            case "headline":
                self::addHeadlineField($fieldName, $strTable, $eval, $classes, $replaceClasses, $typeAdd, $langTable);
                break;

            case "unit":
            case "inputunit":
            case "input_unit":
                self::addUnitField($fieldName, $strTable, $eval, $classes, $replaceClasses, $langTable);
                break;

            case "text":
            case "textfield":
                self::addTextField($fieldName, $strTable, $eval, $classes, $replaceClasses, $langTable);
//            self::addTextField($fieldName, $strTable, $eval, $classes, $replaceClasses, $langTable, $defaultConfig);
                break;

            case "alias":
                self::addAliasField($fieldName, $strTable, $eval, $classes, $replaceClasses, $langTable);
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
//                self::addSelectField($fieldName, $strTable, $eval, $classes, $replaceClasses, $defaultValue, $typeAdd, $langTable);

                if( $arrFieldType[1] === "selector" )
                {
                    $typeAdd = FALSE;
                    $isSelector = TRUE;
                    $eval['submitOnChange'] = true;

                    $GLOBALS['TL_DCA'][ $strTable ]['palettes']['__selector__'][] = $fieldName;
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

            case "trbl":
            case "position":
                $typeAdd = FALSE;$shortOptions = FALSE;

                if( $arrFieldType[1] === "unit" || $arrFieldType[1] === "units" )
                {
                    $typeAdd = TRUE;
                }

                self::addPositionField($fieldName, $strTable, $eval, $classes, $replaceClasses, $shortOptions, $typeAdd, $langTable, $defaultConfig);
                break;
        }
    }



    public static function addPalette($strName, $arrFields, $strTable, $override = FALSE, $addDefaultBefore = TRUE, $addDefaultAfter = TRUE)
    {
        $strFields = $arrFields;

        if( is_array($arrFields) )
        {
            $strFields = '';

            foreach($arrFields as $strLegend => $arrLegendFields)
            {
                $strFields .= (strlen($strFields) ? ';' : '') . '{' . $strLegend . '},' . implode(',', $arrLegendFields);
            }
        }

        if( key_exists($strName, $GLOBALS['TL_DCA'][ $strTable ]['palettes']) )
        {
            if( !$override )
            {
                $arrNewFields   = explode(",", $strFields);
                $arrUsedFields  = explode(",", $GLOBALS['TL_DCA'][ $strTable ]['palettes'][ $strName ]);
//                $arrUsedLegends = explode(";", $GLOBALS['TL_DCA'][ $strTable ]['palettes'][ $strName ]);

//                foreach($arrUsedLegends as $strUsedLegend)
//                {
//                    $arrUsedLegendFields = explode(",", $strUsedLegend);
//                }

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

        if( $strTable === "tl_content" )
        {
            if( $addDefaultBefore )
            {
                $strFields = static::$paletteStart . $strFields;
            }

            if( $addDefaultAfter )
            {
                $strFields = $strFields . static::$paletteEnd;
            }
        }

        if( preg_match('/^,/', $strFields) )
        {
            $strFields = preg_replace('/^,/', '', $strFields);
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



    public static function copyFieldFromTable($fieldName, $strTable, $fromFieldName, $fromFieldTable, $overrideLang = false, $strClass = '')
    {
        if( !preg_match('/^tl_/', $fromFieldTable) )
        {
            $fromFieldTable = 'tl_' . $fromFieldTable;
        }

        \Controller::loadLanguageFile( $fromFieldTable );
        \Controller::loadDataContainer( $fromFieldTable );

        $GLOBALS['TL_DCA'][ $strTable ]['fields'][ $fieldName ] = $GLOBALS['TL_DCA'][ $fromFieldTable ]['fields'][ $fromFieldName ];

        if( $overrideLang )
        {
            $GLOBALS['TL_DCA'][ $strTable ]['fields'][ $fieldName ]['label'] = self::renderFieldLabel($strTable, '', $fieldName); //&$GLOBALS['TL_LANG'][ $strTable ][ $fieldName ];
        }

        if( $strClass )
        {
            $GLOBALS['TL_DCA'][ $strTable ]['fields'][ $fieldName ]['eval']['tl_class'] = $strClass;
        }
    }



    public static function copyField($fieldName, $strTable, $fromFieldName)
    {
        $GLOBALS['TL_DCA'][ $strTable ]['fields'][ $fieldName ]                   = $GLOBALS['TL_DCA'][ $strTable ]['fields'][ $fromFieldName ];
        $GLOBALS['TL_DCA'][ $strTable ]['fields'][ $fieldName ]['label']          = self::renderFieldLabel($strTable, '', $fieldName); //&$GLOBALS['TL_LANG'][ $strTable ][ $fieldName ];
    }



    public static function addCheckboxField($fieldName, $strTable, $eval = array(), $classes = '', $replaceClasses = false, $isSelector = false, $langTable = '', $defaultConfig = array())
    {
        $sql = "char(1) NOT NULL default ''";
        $langTable = self::renderLangTable( $langTable );

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

        if( $defaultEval['multiple'] )
        {
            $sql = "blob NULL";

            $defaultEval['tl_class'] = $defaultEval['tl_class'] . ' hauto';
        }

        $GLOBALS['TL_DCA'][ $strTable ]['fields'][ $fieldName ] = array
        (
            'label'                 => self::renderFieldLabel($strTable, $langTable, $fieldName),
            'exclude'               => true,
            'inputType'             => "checkbox",
            'eval'                  => $defaultEval,
            'sql'                   => $sql
        );

        if( $defaultEval['multiple'] )
        {
            $arrOptions = $GLOBALS['TL_LANG'][ $langTable?:$strTable ]['options'][ $fieldName ];

            if( !is_array($arrOptions) || is_array($arrOptions) && !count($arrOptions) )
            {
                $arrOptions = $GLOBALS['TL_LANG'][ $langTable?:$strTable ]['options_' . $fieldName ];
            }

            $GLOBALS['TL_DCA'][ $strTable ]['fields'][ $fieldName ]['options'] = $arrOptions;
        }

        if( count( $defaultConfig) )
        {
            foreach( $defaultConfig as $configKey => $configValue )
            {
                $GLOBALS['TL_DCA'][ $strTable ]['fields'][ $fieldName ][ $configKey ] = $configValue;
            }
        }
    }



    public static function addTextField($fieldName, $strTable, $eval = array(), $classes = '', $replaceClasses = false, $langTable = '', $defaultConfig = array())
    {
        $langTable = self::renderLangTable( $langTable );

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
            'label'                 => self::renderFieldLabel($strTable, $langTable, $fieldName),
            'exclude'               => TRUE,
            'inputType'             => 'text',
            'eval'                  => $defaultEval,
            'sql'                   => "varchar(" . ($defaultEval['maxlength']?:255) . ") NOT NULL default ''"
        );

        if( count( $defaultConfig) )
        {
            foreach( $defaultConfig as $configKey => $configValue )
            {
                $GLOBALS['TL_DCA'][ $strTable ]['fields'][ $fieldName ][ $configKey ] = $configValue;
            }
        }
    }



    public static function addColorField($fieldName, $strTable, $eval = array(), $classes = '', $replaceClasses = false, $langTable = '')
    {
        $langTable = self::renderLangTable( $langTable );

        if( strlen($langTable) )
        {
            \Controller::loadLanguageFile( $langTable );
        }

        $defaultEval = array
        (
            'maxlength'         => 64,
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
            'label'                 => self::renderFieldLabel($strTable, $langTable, $fieldName),
            'inputType'             => 'text',
            'eval'                  => $defaultEval,
            'sql'                   => "varchar(" . ($defaultEval['maxlength']?:64) . ") NOT NULL default ''"
        );
    }



    protected static function addHeadlineField($fieldName, $strTable, $eval = array(), $classes = '', $replaceClasses = false, $useSearch = false, $langTable = '')
    {
        $langTable = self::renderLangTable( $langTable );

        if( strlen($langTable) )
        {
            \Controller::loadLanguageFile( $langTable );
        }

        $defaultEval = array
        (
            'maxlength'         => 200,
            'decodeEntities'    => TRUE,
            'tl_class'          => ($replaceClasses ? $classes : 'w50 clr' . (strlen($classes) ? ' ' . $classes : ''))
        );

        if( count($eval) )
        {
            $defaultEval = array_merge($defaultEval, $eval);
        }

        $GLOBALS['TL_DCA'][ $strTable ]['fields'][ $fieldName ] = array
        (
            'label'                 => &$GLOBALS['TL_LANG'][ $langTable?:$strTable ][ $fieldName ],
            'exclude'               => TRUE,
            'search'                => $useSearch,
            'inputType'             => 'inputUnit',
            'options'               => array('h1', 'h2', 'h3', 'h4', 'h5', 'h6'),
            'eval'                  => $defaultEval,
            'sql'                   => "varchar(255) NOT NULL default ''"
        );
    }



    public static function addUnitField($fieldName, $strTable, $eval = array(), $classes = '', $replaceClasses = false, $langTable = '')
    {
        $langTable = self::renderLangTable( $langTable );

        $defaultEval = array
        (
            'includeBlankOption'    => TRUE,
            'rgxp'                  => 'digit_auto_inherit',
            'maxlength'             => 20,
            'tl_class'              => ($replaceClasses ? $classes : 'w50' . (strlen($classes) ? ' ' . $classes : ''))
        );

        if( count($eval) )
        {
            $defaultEval = array_merge($defaultEval, $eval);
        }

        $GLOBALS['TL_DCA'][ $strTable ]['fields'][ $fieldName ] = array
        (
            'label'                   => self::renderFieldLabel($strTable, $langTable, $fieldName),
            'inputType'               => 'inputUnit',
            'options'                 => $GLOBALS['TL_CSS_UNITS'],
            'eval'                    => $defaultEval,
            'sql'                     => "varchar(64) NOT NULL default ''"
        );
    }



    public static function addSelectField($fieldName, $strTable, $eval = array(), $classes = '', $replaceClasses = false, $defaultValue = '', $shortOptions = FALSE, $isSelector = FALSE, $langTable = '', $defaultConfig = array())
    {
        $langTable = self::renderLangTable( $langTable );

        if( strlen($langTable) )
        {
            \Controller::loadLanguageFile( $langTable );
        }

        $noCallback = true;

        $defaultEval = array
        (
            'maxlength'         => 32,
            'tl_class'          => ($replaceClasses ? $classes : 'w50' . (strlen($classes) ? ' ' . $classes : ''))
        );

        if( is_array($eval) && count($eval) )
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
            'label'                 => self::renderFieldLabel($strTable, $langTable, $fieldName),
            'exclude'               => TRUE,
            'inputType'             => 'select',
            'eval'                  => $defaultEval,
            'sql'                   => "varchar(" . ($defaultEval['maxlength']?:32)  . ") NOT NULL default ''"
        );

        if( is_array($defaultConfig) && count($defaultConfig) && key_exists('options_callback', $defaultConfig) )
        {
            $noCallback = false;
        }

        if( $noCallback )
        {
            $GLOBALS['TL_DCA'][ $strTable ]['fields'][ $fieldName ]['options'] = $arrOptions;
        }

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



    public static function addImageField($fieldName, $strTable, $eval = array(), $classes = '', $replaceClasses = false, $langTable = '')
    {
        $langTable = self::renderLangTable( $langTable );

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

        $GLOBALS['TL_DCA'][ $strTable ]['fields'][ $fieldName ]['label']            = self::renderFieldLabel($strTable, $langTable, $fieldName);
        $GLOBALS['TL_DCA'][ $strTable ]['fields'][ $fieldName ]['eval']             = $defaultEval;
        $GLOBALS['TL_DCA'][ $strTable ]['fields'][ $fieldName ]['load_callback']    = array();
        $GLOBALS['TL_DCA'][ $strTable ]['fields'][ $fieldName ]['save_callback']    = array();
    }



    public static function addTextareaField($fieldName, $strTable, $eval = array(), $classes = '', $replaceClasses = false, $useRTE = false, $langTable = '')
    {
        $langTable = self::renderLangTable( $langTable );

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

        $GLOBALS['TL_DCA'][ $strTable ]['fields'][ $fieldName ]['label']    = self::renderFieldLabel($strTable, $langTable, $fieldName);
        $GLOBALS['TL_DCA'][ $strTable ]['fields'][ $fieldName ]['eval']     = $defaultEval;
        $GLOBALS['TL_DCA'][ $strTable ]['fields'][ $fieldName ]['sql']      = $useRTE ? "mediumtext NULL" : "text NULL";
    }



    public static function addImageSizeField($fieldName, $strTable, $eval = array(), $classes = '', $replaceClasses = false, $shortOptions = false, $langTable = '')
    {
        $langTable = self::renderLangTable( $langTable );

        if( strlen($langTable) )
        {
            \Controller::loadLanguageFile( $langTable );
        }

        $defaultEval = array
        (
            'rgxp'                  => ($fieldName === "bgSize" ? 'extnd' : 'natural'),
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
            'label'                 => self::renderFieldLabel($strTable, $langTable, $fieldName),
            'inputType'             => 'imageSize',
            'options'               => $arrOptions,
            'eval'                  => $defaultEval,
            'sql'                   => "varchar(64) NOT NULL default ''"
        );
    }



    protected static function addPositionField($fieldName, $strTable, $eval, $classes, $replaceClasses, $shortOptions, $useUnits, $langTable, $defaultConfig)
    {
        $langTable = self::renderLangTable( $langTable );

        if( strlen($langTable) )
        {
            \Controller::loadLanguageFile( $langTable );
        }

        $defaultEval = array
        (
            'tl_class'              => ($replaceClasses ? $classes : 'w50' . (strlen($classes) ? ' ' . $classes : ''))
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

        if( $useUnits )
        {
            $arrOptions = $GLOBALS['TL_CSS_UNITS'];
        }

        $GLOBALS['TL_DCA'][ $strTable ]['fields'][ $fieldName ] = array
        (
            'label'             => self::renderFieldLabel($strTable, $langTable, $fieldName),
            'exclude'           => TRUE,
            'inputType'         => 'trbl',
            'options'           => $arrOptions,
            'eval'              => $defaultEval,
            'sql'               => "varchar(255) NOT NULL default ''"
        );

        if( count( $defaultConfig) )
        {
            foreach( $defaultConfig as $configKey => $configValue )
            {
                $GLOBALS['TL_DCA'][ $strTable ]['fields'][ $fieldName ][ $configKey ] = $configValue;
            }
        }
    }



    public static function addImagesField($fieldName, $strTable, $eval = array(), $classes = '', $replaceClasses = false, $langTable = '')
    {
        $langTable = self::renderLangTable( $langTable );

        \Controller::loadDataContainer( 'tl_content' );

        if( strlen($langTable) )
        {
            \Controller::loadLanguageFile( $langTable );
        }

        $orderFieldName = preg_replace('/SRC/', 'OrderSRC', $fieldName);

        $GLOBALS['TL_DCA'][ $strTable ]['fields'][ $fieldName ]                = $GLOBALS['TL_DCA']['tl_content']['fields']['multiSRC'];
        $GLOBALS['TL_DCA'][ $strTable ]['fields'][ $fieldName ]['label']       = self::renderFieldLabel($strTable, $langTable, $fieldName);

        $GLOBALS['TL_DCA'][ $strTable ]['fields'][ $fieldName ]['eval']['mandatory']    = FALSE;
        $GLOBALS['TL_DCA'][ $strTable ]['fields'][ $fieldName ]['eval']['isGallery']    = TRUE;
        $GLOBALS['TL_DCA'][ $strTable ]['fields'][ $fieldName ]['eval']['extensions']   = \Config::get('validImageTypes');
        $GLOBALS['TL_DCA'][ $strTable ]['fields'][ $fieldName ]['eval']['tl_class']     = ($replaceClasses ? $classes : 'clr w50 hauto' . (strlen($classes) ? ' ' . $classes : ''));
        $GLOBALS['TL_DCA'][ $strTable ]['fields'][ $fieldName ]['eval']['orderField']   = $orderFieldName;

        unset( $GLOBALS['TL_DCA'][ $strTable ]['fields'][ $fieldName ]['load_callback'] );

        if( count($eval) )
        {
            $GLOBALS['TL_DCA'][ $strTable ]['fields'][ $fieldName ]['eval'] = array_merge($GLOBALS['TL_DCA'][ $strTable ]['fields'][ $fieldName ]['eval'], $eval);
        }

        $GLOBALS['TL_DCA'][ $strTable ]['fields'][ $orderFieldName ] = $GLOBALS['TL_DCA']['tl_content']['fields']['orderSRC'];
    }



    public static function removeField($strField, $strTable, $strPalette = 'default')
    {
        if( is_array($strField) )
        {
            foreach($strField as $fieldName)
            {
                $GLOBALS['TL_DCA'][ $strTable ]['palettes'][ $strPalette ] = str_replace(',' . $fieldName, '', $GLOBALS['TL_DCA'][ $strTable ]['palettes'][ $strPalette ]);
            }
        }
        else
        {
            $GLOBALS['TL_DCA'][ $strTable ]['palettes'][ $strPalette ] = str_replace(',' . $strField, '', $GLOBALS['TL_DCA'][ $strTable ]['palettes'][ $strPalette ]);
        }
    }



    public static function addPageField($fieldName, $strTable, $eval = array(), $classes = '', $replaceClasses = false, $langTable = '')
    {
        $langTable = self::renderLangTable( $langTable );

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
            'label'                 => self::renderFieldLabel($strTable, $langTable, $fieldName),
            'exclude'               => true,
            'inputType'             => 'pageTree',
            'foreignKey'            => 'tl_page.title',
            'eval'                  => $defaultEval,
            'sql'                   => "int(10) unsigned NOT NULL default '0'",
            'relation'              => array('type'=>'hasOne', 'load'=>'lazy')
        );
    }



    public static function addUrlField($fieldName, $strTable, $eval = array(), $classes = '', $replaceClasses = false, $langTable = '')
    {
        $langTable = self::renderLangTable( $langTable );

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
            'label'                 => self::renderFieldLabel($strTable, $langTable, $fieldName),
            'exclude'               => true,
            'inputType'             => 'text',
            'eval'                  => $defaultEval,
            'sql'                   => "varchar(" . ($defaultEval['maxlength']?:255) . ") NOT NULL default ''"
        );
    }



    public static function addAliasField($fieldName, $strTable, $eval = array(), $classes = '', $replaceClasses = false, $langTable = '')
    {
        $langTable = self::renderLangTable( $langTable );

        if( strlen($langTable) )
        {
            \Controller::loadLanguageFile( $langTable );
        }

        $defaultEval = array
        (
            'rgxp'          => 'alias',
            'doNotCopy'     => true,
            'maxlength'     => 128,
            'tl_class'      => ($replaceClasses ? $classes : 'w50 clr' . (strlen($classes) ? ' ' . $classes : ''))
        );

        if( count($eval) )
        {
            $defaultEval = array_merge($defaultEval, $eval);
        }

        $GLOBALS['TL_DCA'][ $strTable ]['fields'][ $fieldName ] = array
        (
            'label'                   => self::renderFieldLabel('tl_article', '', 'alias'), //&$GLOBALS['TL_LANG']['tl_article']['alias'],
            'exclude'                 => true,
            'inputType'               => 'text',
            'search'                  => true,
            'eval'                    => $defaultEval,
            'save_callback'           => array
            (
                array(AllTables::class, 'generateAlias')
            ),
            'sql'                     => "varchar(" . ($defaultEval['maxlength']?:128) . ") COLLATE utf8mb4_bin NOT NULL default ''"
        );
    }



    public static function addProtectedFieldsToTable( $strTable, $toPalette = '', $replaceLegend = '', $replacePosition = 'after' )
    {
        \Controller::loadLanguageFile( $strTable );

        $strLegend = '{protected_legend:hide},protected;';

        $GLOBALS['TL_DCA'][ $strTable ]['fields']['protected'] = array
        (
            'label'                   => self::renderFieldLabel($strTable, '', 'protected'), //&$GLOBALS['TL_LANG'][ $strTable ]['protected'],
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
            'label'                   => self::renderFieldLabel($strTable, '', 'groups'), //&$GLOBALS['TL_LANG'][ $strTable ]['groups'],
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



    public static function addPublishedFieldsToTable( $strTable, $toPalette = '', $replaceLegend = '', $replacePosition = 'after', $addToList = false )
    {
        \Controller::loadLanguageFile( $strTable );

        $strLegend = '{publish_legend},published,start,stop;';

        $GLOBALS['TL_DCA'][ $strTable ]['fields']['published'] = array
        (
            'label'                   => self::renderFieldLabel($strTable, '', 'published'), //&$GLOBALS['TL_LANG'][ $strTable ]['published'],
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
            'label'                   => self::renderFieldLabel($strTable, '', 'start'), //&$GLOBALS['TL_LANG'][ $strTable ]['start'],
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
            'label'                   => self::renderFieldLabel($strTable, '', 'stop'), //&$GLOBALS['TL_LANG'][ $strTable ]['stop'],
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

        $strKeys = 'start,stop,published';

        if( isset($GLOBALS['TL_DCA'][ $strTable ]['fields']['pid']) )
        {
            unset($GLOBALS['TL_DCA'][ $strTable ]['config']['sql']['keys']['pid']);
            $strKeys = 'pid,' .$strKeys;
        }

        $GLOBALS['TL_DCA'][ $strTable ]['config']['sql']['keys'][ $strKeys ] = 'index';

        self::renderTableLegend( $strTable, $strLegend, $toPalette, $replaceLegend, $replacePosition);

        if( $addToList )
        {
            $intIndex = (count($GLOBALS['TL_DCA'][ $strTable ]['list']['operations']) - 1);

            if( isset($GLOBALS['TL_DCA'][ $strTable ]['list']['operations']['feature']) )
            {
                $intIndex = ($intIndex - 1);
            }

            array_insert($GLOBALS['TL_DCA'][ $strTable ]['list']['operations'], $intIndex, array
            (
                'toggle' => array
                (
                    'label'               => self::renderFieldLabel($strTable, '', 'toggle'), //&$GLOBALS['TL_LANG'][ $strTable ]['toggle'],
                    'icon'                => 'visible.svg',
                    'attributes'          => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s)"',
                    'button_callback'     => array(BundleConfig::getTableClass( $strTable ), 'toggleIcon')
                )
            ));
        }
    }



    public static function renderTableLegend( $strTable, $strLegend, $toPalette = '', $replaceLegend = false, $replacePosition = 'after')
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



    /**
     * Render Legend Fields
     *
     * @param string $strFields
     * @param string $strLegend
     * @param bool   $replaceLegend
     * @param string $replacePosition
     *
     * @return null|string
     */
    public static function renderLegendFields( $strFields, $strLegend, $replaceLegend = false, $replacePosition = 'after' )
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



    /**
     * Remove Legend
     *
     * @param string|array $strLegend
     * @param string       $strTable
     * @param string       $strPalette
     * @param string|array $arrNewFields
     */
    public static function removeLegend($strLegend, $strTable, $strPalette = 'default', $arrNewFields = '')
    {
        if( is_array($strLegend) )
        {
            foreach($strLegend as $legendName => $arrNewFields)
            {
                if( is_numeric($legendName) )
                {
                    $legendName     = $arrNewFields;
                    $arrNewFields   = array();
                }
                else
                {
                    if( !is_array($arrNewFields) )
                    {
                        $arrNewFields = explode(",", $arrNewFields);
                    }
                }

                if( !preg_match('/_legend$/', $legendName) )
                {
                    $legendName = $legendName . '_legend';
                }

                $GLOBALS['TL_DCA'][ $strTable ]['palettes'][ $strPalette ] = preg_replace('/\{' . $legendName . '([A-Za-z0-9\s_}:,]{0,});/', (count($arrNewFields) ? '{' . $legendName . '},' . implode(',', $arrNewFields) . ';' : ''), $GLOBALS['TL_DCA'][ $strTable ]['palettes'][ $strPalette ]);
            }
        }
        else
        {
            if( !preg_match('/_legend$/', $strLegend) )
            {
                $strLegend = $strLegend . '_legend';
            }

            if( !is_array($arrNewFields) )
            {
                $arrNewFields = explode(",", $arrNewFields);
            }

//            $GLOBALS['TL_DCA'][ $strTable ]['palettes'][ $strPalette ] = preg_replace('/\{' . $strLegend . '([A-Za-z0-9\s_}:,]{0,});/', '', $GLOBALS['TL_DCA'][ $strTable ]['palettes'][ $strPalette ]);
            $GLOBALS['TL_DCA'][ $strTable ]['palettes'][ $strPalette ] = preg_replace('/\{' . $strLegend . '([A-Za-z0-9\s_}:,]{0,});/', (count($arrNewFields) ? '{' . $strLegend . '},' . implode(',', $arrNewFields) . ';' : ''), $GLOBALS['TL_DCA'][ $strTable ]['palettes'][ $strPalette ]);
        }
    }



    protected static function renderFieldLabel( $strTable, $langTable, $fieldName )
    {
        $fieldName = preg_replace('/^iido([A-Za-z0-9]{0,})_/', '', $fieldName);

        return $GLOBALS['TL_LANG'][ $langTable?:$strTable ][ $fieldName ];
    }



    protected static function renderLangTable( $langTable )
    {
        if( strlen($langTable) )
        {
            if( !preg_match('/^tl_/', $langTable) )
            {
                $langTable = 'tl_' . $langTable;
            }
        }

        return $langTable;
    }
}
