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

    public static function addField($fieldName, $fieldType, $strTable, $eval = array(), $classes = '', $replaceClasses = false, $defaultValue = '')
    {
        $arrFieldType   = explode("_", $fieldType);

        $fieldType      = $arrFieldType[0];
        $typeAdd        = (($arrFieldType[1] === "short" || $arrFieldType[1] === "selector") ? TRUE : FALSE);
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
                break;

            case "text":
            case "textfield":
                self::addTextField($fieldName, $strTable, $eval, $classes, $replaceClasses, $langTable);
                break;

            case "select":
                self::addSelectField($fieldName, $strTable, $eval, $classes, $replaceClasses, $defaultValue, $typeAdd, $langTable);
                break;

            case "fileTree":
            case "image":
            case "imagefield":
                self::addTextField($fieldName, $strTable, $eval, $classes, $replaceClasses, $langTable);
                break;
        }
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



    protected static function addCheckboxField($fieldName, $strTable, $eval = array() ,$classes = '', $replaceClasses = false, $isSelector = false, $langTable = '')
    {
        if( strlen($langTable) )
        {
            \Controller::loadLanguageFile( $langTable );
        }

        $defaultEval = array
        (
            'tl_class'              => ($replaceClasses ? $classes : 'w50 m12' . (strlen($classes) ? ' ' . $classes : ''))
        );

        if( count($eval) )
        {
            $defaultEval = array_merge($defaultEval, $eval);
        }

        if( $isSelector )
        {
            $defaultEval['submitOnChange'] = true;

            $GLOBALS['TL_DCA']['tl_content']['palettes']['__selector__'][] = $fieldName;
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
            'maxlength'             => 255,
            'decodeEntities'        => TRUE,
            'tl_class'              => ($replaceClasses ? $classes : 'w50' . (strlen($classes) ? ' ' . $classes : ''))
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



    protected static function addSelectField($fieldName, $strTable, $eval = array(), $classes = '', $replaceClasses = false, $defaultValue = '', $shortOptions = FALSE, $langTable = '')
    {
        if( strlen($langTable) )
        {
            \Controller::loadLanguageFile( $langTable );
        }

        $defaultEval = array
        (
            'maxlength'             => 32,
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

        $GLOBALS['TL_DCA'][ $strTable ]['fields'][ $fieldName ] = array
        (
            'label'                 => &$GLOBALS['TL_LANG'][ $langTable?:$strTable ][ $fieldName ],
            'exclude'               => TRUE,
            'inputType'             => 'select',
            'options'               => $arrOptions,
            'eval'                  => $defaultEval,
            'sql'                   => "varchar(" . $defaultEval['maxlength']  . ") NOT NULL default ''"
        );

        if( strlen($defaultValue) )
        {
            $GLOBALS['TL_DCA'][ $strTable ]['fields'][ $fieldName ]['default'] = $defaultValue;
        }
    }



    protected static function addImageField($fieldName, $strTable, $eval = array(), $classes = '', $replaceClasses = false, $langTable = '')
    {
        \Controller::loadDataContainer("tl_content");

        if( strlen($langTable) )
        {
            \Controller::loadLanguageFile( $langTable );
        }

        $defaultEval = array
        (
            'mandatory'             => FALSE,
            'tl_class'              => ($replaceClasses ? $classes : 'clr w50 hauto' . (strlen($classes) ? ' ' . $classes : ''))
        );

        if( count($eval) )
        {
            $defaultEval = array_merge($defaultEval, $eval);
        }

        $GLOBALS['TL_DCA'][ $strTable ]['fields'][ $fieldName ]                     = $GLOBALS['TL_DCA']['tl_content']['fields']['singleSRC'];
        $GLOBALS['TL_DCA'][ $strTable ]['fields'][ $fieldName ]['label']            = &$GLOBALS['TL_LANG'][ $langTable?:$strTable ][ $fieldName ];
        $GLOBALS['TL_DCA'][ $strTable ]['fields'][ $fieldName ]['eval']             = $defaultEval;
        $GLOBALS['TL_DCA'][ $strTable ]['fields'][ $fieldName ]['load_callback']    = array();
        $GLOBALS['TL_DCA'][ $strTable ]['fields'][ $fieldName ]['save_callback']    = array();
    }
}
