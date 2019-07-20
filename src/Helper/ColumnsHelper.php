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
 */
class ColumnsHelper
{

    protected static $maxColumns = 12;


    public static function getColumnWidth( $arrColumns, $columnIndex )
    {
        $arrColumn = (array) $arrColumns[ $columnIndex ];

        if( $arrColumn['width'] )
        {
            return (int) $arrColumn['width'];
        }
        else
        {
            $columnWidth = self::$maxColumns;

            foreach( $arrColumns as $index => $column )
            {
                $column = (array) $column;

                if( $index === $columnIndex )
                {
                    $columnWidth = ($columnWidth - (int) $column['offset_left'] - (int) $column['offset_right']);
                }
                else
                {
                    $columnWidth = ($columnWidth - (int) $column['offset_left'] - (int) $column['offset_right'] - (int) $column['width']);
                }
            }

            if( $columnWidth === 12 )
            {
                return (int) (self::$maxColumns / count($arrColumns));
            }

            if( $columnWidth > 0 )
            {
                return (int) $columnWidth;
            }
        }

        return 12;
    }
}