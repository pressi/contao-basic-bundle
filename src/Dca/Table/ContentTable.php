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



    public function addNewsLanguageFlags( $arrRow )
    {
        $tlContent = new \tl_content();
        $label = $tlContent->addCteType( $arrRow );

        if( $arrRow['ptable'] === 'tl_news' )
        {
            $objNews = \NewsModel::findByPk( $arrRow['pid'] );

            if( $objNews && $objNews->productMarket === 'default' )
            {
                $arrLangs = StringUtil::deserialize( $arrRow['showInLanguage'], true );

                $strLangs = '<div class="lang-flags">';

                $isDE = in_array('de', $arrLangs) || !count($arrLangs);
                $strLangs .= '<span class="flag-de' . ($isDE ? ' shown' : '') . '"><img src="files/hhsystem/images/backend/flag-de.svg"></span>';

                $isEN = in_array('en', $arrLangs);
                $strLangs .= '<span class="flag-en' . ($isEN ? ' shown' : '') . '"><img src="files/hhsystem/images/backend/flag-en.svg"></span>';

                $isUS = in_array('en_us', $arrLangs);
                $strLangs .= '<span class="flag-en' . ($isUS ? ' shown' : '') . '"><img src="files/hhsystem/images/backend/flag-us.svg"></span>';

                $strLangs .= '</div>';

                $label = preg_replace('/<\/div>/', $strLangs . '</div>', $label, 1);
            }
        }

        return $label;
    }



    public function getMasterColumnElementLabel( $arrRow, $label, $objClass, $folderAttribute, $checkBoolean, $protected)
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