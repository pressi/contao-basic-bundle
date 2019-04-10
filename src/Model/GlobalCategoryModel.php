<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\Model;


use Contao\Model;


/**
 * Class WebsiteStyleModel - Fake Model
 *
 * @package IIDO\BasicBundle\Model
 */
class GlobalCategoryModel extends Model
{
    protected static $strTable = 'tl_iido_global_category';



    /**
     * Find published news items by their parent ID
     *
     * @param integer $intId      The news archive ID
     * @param integer $intLimit   An optional limit
     * @param array   $arrOptions An optional options array
     *
     * @return Model\Collection|GlobalCategoryModel[]|GlobalCategoryModel|null A collection of models or null if there are no news
     */
    public static function findPublishedByPid($intId, $intLimit=0, array $arrOptions=array())
    {
        $t = static::$strTable;
        $arrColumns = array("$t.pid=?");

        if (!static::isPreviewMode($arrOptions))
        {
            $time = \Date::floorToMinute();
            $arrColumns[] = "($t.start='' OR $t.start<='$time') AND ($t.stop='' OR $t.stop>'" . ($time + 60) . "') AND $t.published='1'";
        }

        if (!isset($arrOptions['order']))
        {
            $arrOptions['order'] = "$t.sorting DESC";
        }

        if ($intLimit > 0)
        {
            $arrOptions['limit'] = $intLimit;
        }

        return static::findBy($arrColumns, $intId, $arrOptions);
    }
}