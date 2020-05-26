<?php
/*******************************************************************
 * (c) 2020 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\Dca\Table;


use Contao\StringUtil;
use IIDO\BasicBundle\Helper\StyleSheetHelper;


//class ContentTable extends DefaultTable
class ContentTable
{
    protected $strTableName = 'tl_content';



    public function getColor()
    {
        return StyleSheetHelper::getValueFromVariables('colors', true);
    }



    public function getMasterColumnElementLabel($arrRow, $label, $objClass, $folderAttribute, $checkBoolean, $protected)
    {
        if( !$arrRow['internName'] )
        {
            $intro = '';

            switch( $arrRow['type'] )
            {
                case "headline":
                    $arrHeadline = StringUtil::deserialize($arrRow['headline']);
                    $intro = $arrHeadline['value'];
                    break;

                case "text":
                default:
                    $strText = preg_replace('/<br>/', ' ', $arrRow['text']);
                    $strText = strip_tags($strText);

                    $intro = substr($strText, 0, 50) . ((strlen($strText) > 50 ) ? '...' : '');
                    break;

            }

            if( $intro )
            {
                $label = str_replace('[]', "[$intro]", $label);
            }
            else
            {
                $label = str_replace('[]', "", $label);
            }
        }

        return $label;
    }
}