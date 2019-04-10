<?php
/*******************************************************************
 * (c) 2019 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\Helper;



use IIDO\BasicBundle\Model\GlobalCategoryModel;


/**
 * Class Global Elements Helper
 *
 * @package IIDO\BasicBundle
 */
class GlobalCategoriesHelper
{
    static $fieldsPrefix = 'gc_';


    static $strTable = 'tl_iido_global_categories';



    public static function addValueToTable( $strValue, $strField, $intID, $strTable )
    {
        self::deleteValueFromTable( $strField, $intID, $strTable );

        $arrValue       = \StringUtil::trimsplit(',', $strValue);
        $strCategory    = preg_replace('/^' . self::$fieldsPrefix . '/', '', $strField);

        foreach($arrValue as $catID)
        {
            $arrSet = array
            (
                'item_id'       => $intID,
                'category_id'   => $catID,
                'refTable'      => $strTable,
                'refCategory'   => $strCategory
            );

            \Database::getInstance()->prepare("INSERT INTO " . self::$strTable . " %s")->set( $arrSet )->execute();
        }
    }



    public static function loadValueFromTable( $strField, $intID, $strTable )
    {
        $arrValues      = array();
        $strCategory    = preg_replace('/^' . self::$fieldsPrefix . '/', '', $strField);

        $objRecords     = \Database::getInstance()->prepare("SELECT * FROM " . self::$strTable . " WHERE item_id=? AND refTable=? AND refCategory=?")->execute( $intID, $strTable, $strCategory );

        if( $objRecords && $objRecords->count() )
        {
            while( $objRecords->next() )
            {
                $arrValues[] = $objRecords->category_id;
            }
        }

        return $arrValues;
    }



    public static function deleteValueFromTable( $strField, $intID, $strTable )
    {
        $strCategory    = preg_replace('/^' . self::$fieldsPrefix . '/', '', $strField);

        \Database::getInstance()->prepare("DELETE FROM " . self::$strTable . " WHERE item_id=? AND refTable=? AND refCategory=?")->execute( $intID, $strTable, $strCategory );
    }



    public static function getCategoriesFromTable( $strTable )
    {
        $arrGCs = array();
        $objGCs = GlobalCategoryModel::findPublishedByPid(0);

        if( $objGCs )
        {
            while( $objGCs->next() )
            {
                $enableIn = \StringUtil::deserialize($objGCs->enableCategoriesIn, TRUE);

                if( count($enableIn) && in_array( $strTable, $enableIn ) )
                {
                    $arrGCs[] = $objGCs->current();
                }
            }
        }

        return $arrGCs;
    }

}
