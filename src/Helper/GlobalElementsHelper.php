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
 * Class Global Elements Helper
 *
 * @package IIDO\BasicBundle
 */
class GlobalElementsHelper
{
    static $elementsPrefix = 'ge_';



    public static function get( $strName, $rootAlias = '' )
    {
        if( !strlen($rootAlias) )
        {
            global $objPage;

            $rootAlias = $objPage->rootAlias;
        }

        $elementName = self::$elementsPrefix . $strName . '_' . $rootAlias;

        if( ($strOutput = \Controller::getArticle($elementName, false, true)) !== false )
        {
            return ltrim( $strOutput );
        }

        return false;
    }



    public static function getObject( $strName, $rootAlias = '' )
    {
        if( !strlen($rootAlias) )
        {
            global $objPage;

            $rootAlias = $objPage->rootAlias;
        }

        return \ArticleModel::findByAlias(self::$elementsPrefix . $strName . '_' . $rootAlias);
    }

}
