<?php
declare(strict_types=1);

/*******************************************************************
 * (c) 2020 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\Helper;


use Contao\Controller;
use Contao\NewsModel;
use Haste\Model\Model;


class NewsHelper
{

    public static function checkNewsInCategory( $category, $archive )
    {
        $strLang = strtolower( BasicHelper::getLanguage() );

        $newsIds    = Model::getReferenceValues('tl_news', 'categories', $category['id']);
        $newsIds    = self::parseIds( $newsIds );

        if( count( $newsIds ) )
        {
            $arrNews = [];
            foreach( $newsIds as $newsId )
            {
                $objNews = NewsModel::findByPk( $newsIds );

                if( $objNews )
                {
                    if( $objNews->published )
                    {
                        if( $objNews->productMarket === 'default' )
                        {
                            if( $strLang === 'de' || $strLang === 'en' || ($strLang === 'en_us' && $objNews->isAlsoUSProduct) )
                            {
                                $arrNews[] = $objNews;
                            }
                        }
                        elseif( $objNews->productMarket === 'usa' && $strLang === 'en_us' )
                        {
                            $arrNews[] = $objNews;
                        }
                    }
                }
            }

            if( count($arrNews) )
            {
                return true;
            }
        }

        return false;
    }



    /**
     * Parse the record IDs.
     *
     * @param array $ids
     *
     * @return array
     */
    public static function parseIds( array $ids )
    {
        $ids = \array_map('intval', $ids);
        $ids = \array_filter($ids);
        $ids = \array_unique($ids);

        return \array_values($ids);
    }

}