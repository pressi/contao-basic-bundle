<?php
/*******************************************************************
 * (c) 2020 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\EventListener\DataContainer;


use Contao\DataContainer;
use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\PageModel;
use IIDO\BasicBundle\Helper\PageHelper;
use IIDO\BasicBundle\Helper\StyleSheetHelper;
use Terminal42\ServiceAnnotationBundle\ServiceAnnotationInterface;


class ArticleListener implements ServiceAnnotationInterface
{
    /**
     * @Callback(table="tl_article", target="fields.bgColor.options")
     */
    public function loadLabel( ?DataContainer $dc ): array
    {
        $options =
        [
            'black'   => 'Schwarz (#333)',
            'white'   => 'Weiß (#fff)'
        ];

//        $colors = ColorUtil::getWebsiteColors( $dc );

//        if( count($colors) )
//        {
//        }

        return $options;
    }



    /**
     * @Callback(table="tl_article", target="config.onsubmit")
     */
    public function onSubmit( DataContainer $dc ): void
    {
        $activeRecord = $dc->activeRecord;

        if( $activeRecord->articleType === 'header' )
        {
            $objParentPage = PageModel::findByPk( $activeRecord->pid );

            $value = '01';

            if( $activeRecord->layout === 'left' )
            {
                $value = '02';
            }
            elseif( $activeRecord->layout === 'right' )
            {
                $value = '03';
            }

            StyleSheetHelper::addConfigVar('headerLayout', 'layout' . $value, $objParentPage->rootAlias);
        }
    }
}