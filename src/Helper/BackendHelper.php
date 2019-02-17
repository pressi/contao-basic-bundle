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


/**
 * Class Helper
 *
 * @package IIDO\BasicBundle
 * @TODO: überarbeiten!! funktioniert nicht!!
 */
class BackendHelper
{

    public static function getLinkClasses()
    {
        $fieldPrefix    = BundleConfig::getTableFieldPrefix();

        $strClasses = '';
        $arrClasses = \StringUtil::deserialize( \Config::get( $fieldPrefix . 'linkClasses' ), TRUE );

        if( is_array($arrClasses) && count($arrClasses) )
        {
            foreach( $arrClasses as $arrClass )
            {
                $strTitle = preg_replace(array('/&#40;/', '/&#41;/'), array('(', ')'), $arrClass['title']);

                $strClasses .= "{title: '" . $strTitle . "', value: '" . $arrClass['value'] . "'},";
            }
        }

        return $strClasses;
    }



    public static function renderOptions( $arrOptions, $keyField = 'key', $valueField = 'value', $isObject = false )
    {
        $strOptions = '';

        foreach($arrOptions as $arrOption)
        {
            $strOptions .= '<option value="' . (($isObject) ? $arrOption->$keyField : $arrOption[ $keyField ]) . '">' . (($isObject) ? $arrOption->$valueField : $arrOption[ $valueField ]) . '</option>';
        }

        return $strOptions;
    }



    public static function renderFormField( $arrField, $legendConfig )
    {
        $strFieldName = $arrField['name'];

//        echo "<pre>"; print_r( $arrField ); echo "</pre>";

        if( !$arrField['eval']['colorpicker'] )
        {
            $strFieldName = 'field[' . $arrField['name'] . ']';
        }

//        echo "<pre>"; print_r( $strFieldName ); echo "</pre>";

        $arrWidget = \Widget::getAttributesFromDca($arrField, $strFieldName, $arrField['value'],  $strFieldName);
        $objWidget = new $GLOBALS['BE_FFL'][ $arrField['inputType'] ]( $arrWidget );

        foreach( $arrField['eval'] as $key => $value )
        {
            $objWidget->$key = $value;
        }

        if( $arrField['eval']['colorpicker'] )
        {
            $objWidget->fieldAddon = 'field';
//            echo "<pre>"; print_r( $objWidget ); exit;
        }

        return '<h3>' . $objWidget->generateLabel() . $objWidget->xlabel . '</h3>' . $objWidget->generateWithError( true );

//        $objTemplate = new \BackendTemplate( "be_form_field" );
//
//        $objTemplate->label     = $arrField['label'];
//
//        $objTemplate->name      = 'field[' . $arrField['name'] . ']';
//        $objTemplate->type      = $arrField['type'];
//
//        $objTemplate->value     = $arrField['value'];
//
//        $objTemplate->legendConfig = $legendConfig;
////if( $arrField['type'] === "multiColumnWizard")
////{
////    echo "<pre>"; print_r( $arrField ); exit;
////}
//        return $objTemplate->parse();
    }
}