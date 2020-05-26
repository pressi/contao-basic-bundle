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



use Contao\Config;


class ConfigHelper
{
    public static function get( $configVar )
    {
        if( $configVar === 'cssCustomFiles' )
        {
            return 'fonts,icons,animate,core,buttons,form,forms,layout,hamburgers,hamburgers.min.css,navigation,content,style,styles,page,sidekick,responsive';
        }

        return Config::get( $configVar );
    }
}