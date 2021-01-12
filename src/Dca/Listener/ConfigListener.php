<?php
/*******************************************************************
 * (c) 2020 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\Dca\Listener;


use Contao\DataContainer;
use Contao\CoreBundle\ServiceAnnotation\Callback;
//use Doctrine\ORM\EntityManager;
use Contao\StringUtil;
use IIDO\BasicBundle\Helper\BasicHelper;
use Terminal42\ServiceAnnotationBundle\ServiceAnnotationInterface;


class ConfigListener implements ServiceAnnotationInterface
{
    /**
     * @Callback(table="tl_iido_config", target="config.onload")
     */
    public function loadTable( DataContainer $dc ): void
    {
        $count = 2;

        if( BasicHelper::isActiveBundle('contao/news-bundle') )
        {
            $count++;
        }

        if( BasicHelper::isActiveBundle('contao/calendar-bundle') )
        {
            $count++;
        }

        if( BasicHelper::isActiveBundle('delahaye/dlh_googlemaps') )
        {
            $count++;
        }

        if( BasicHelper::isActiveBundle('madeyourday/contao-rocksolid-slider') )
        {
            $count++;
        }

        $GLOBALS['TL_DCA']['tl_iido_config']['fields']['navLabels']['eval']['multiColumnEditor']['minRowCount'] = $count;
        $GLOBALS['TL_DCA']['tl_iido_config']['fields']['navLabels']['eval']['multiColumnEditor']['maxRowCount'] = $count;
    }



    /**
     * @Callback(table="tl_iido_config", target="fields.navLabels.load")
     */
    public function loadNavFields( $varValue, DataContainer $dc )
    {
        $arrLabels = [
            ['value'=>'article', 'label'=> $GLOBALS['TL_LANG']['MOD']['article'][0] ],
            ['value'=>'form', 'label'=> $GLOBALS['TL_LANG']['MOD']['form'][0] ],
        ];

        if( BasicHelper::isActiveBundle('contao/news-bundle') )
        {
            $arrLabels[] = ['value'=>'news', 'label'=> $GLOBALS['TL_LANG']['MOD']['news'][0] ];
        }

        if( BasicHelper::isActiveBundle('contao/calendar-bundle') )
        {
            $arrLabels[] = ['value'=>'calendar', 'label'=> $GLOBALS['TL_LANG']['MOD']['calendar'][0] ];
        }

        if( BasicHelper::isActiveBundle('delahaye/dlh_googlemaps') )
        {
            $arrLabels[] = ['value'=>'dlh_googlemaps', 'label'=> $GLOBALS['TL_LANG']['MOD']['dlh_googlemaps'][0] ];
        }

        if( BasicHelper::isActiveBundle('madeyourday/contao-rocksolid-slider') )
        {
            $arrLabels[] = ['value'=>'rocksolid_slider', 'label'=> $GLOBALS['TL_LANG']['MOD']['rocksolid_slider'][0] ];
        }

        if( !$varValue )
        {
//            $varValue = StringUtil::deserialize( $arrLabels );
            $varValue = $arrLabels;
        }

        return $varValue;
    }
}