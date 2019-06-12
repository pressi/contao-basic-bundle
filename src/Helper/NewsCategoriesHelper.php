<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\Helper;


use Codefog\NewsCategoriesBundle\Model\NewsCategoryModel;
use Contao\Date;
use Contao\NewsModel;
use Haste\Model\Model;


class NewsCategoriesHelper
{

    public static function getUsageNews(array $archives = [], $category = null, $includeSubcategories = false, array $cumulativeCategories = [], $options = [], $addColumns = [], $addValues = [])
    {
        $t = NewsModel::getTable();

        // Include the subcategories
        if (null !== $category && $includeSubcategories)
        {
            $category = NewsCategoryModel::getAllSubcategoriesIds($category);
        }

        $ids = Model::getReferenceValues($t, 'categories', $category);

        // Also filter by cumulative categories
        if (count($cumulativeCategories) > 0)
        {
            $cumulativeIds = null;

            foreach ($cumulativeCategories as $cumulativeCategory)
            {
                $tmp = Model::getReferenceValues($t, 'categories', $cumulativeCategory);

                // Include the subcategories
                if ($includeSubcategories)
                {
                    $tmp = NewsCategoryModel::getAllSubcategoriesIds($tmp);
                }

                if ($cumulativeIds === null)
                {
                    $cumulativeIds = $tmp;
                }
                else
                {
                    $cumulativeIds = array_intersect($cumulativeIds, $tmp);
                }
            }

            $ids = array_intersect($ids, $cumulativeIds);
        }

        if (0 === \count($ids))
        {
            return 0;
        }

        $columns = ["$t.id IN (".\implode(',', \array_unique($ids)).')'];
        $values = [];

        // Filter by archives
        if (\count($archives))
        {
            $columns[] = "$t.pid IN (".\implode(',', \array_map('intval', $archives)).')';
        }

        if (!BE_USER_LOGGED_IN)
        {
            $time = Date::floorToMinute();
            $columns[] = "($t.start=? OR $t.start<=?) AND ($t.stop=? OR $t.stop>?) AND $t.published=?";
            $values = \array_merge($values, ['', $time, '', $time + 60, 1]);
        }

        if( count($addColumns) )
        {
            foreach($addColumns as $num => $addColumn)
            {
                $columns[] = $addColumn;
                $values[] = $addValues[ $num ];
            }
        }

        return NewsModel::findBy($columns, $values, $options);
    }

}