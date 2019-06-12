<?php
/*******************************************************************
 * (c) 2019 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\EventListener;



use IIDO\BasicBundle\Model\GlobalCategoryModel;


/**
 * IIDO Dca Listener
 *
 * @package IIDO\BasicBundle
 * @author Stephan Preßl <development@prestep.at>
 */
class DcaListener extends DefaultListener
{

    public function onLoadDataContainer( $strTable )
    {
        if( $strTable === "tl_files" )
        {
            return;
        }

        $db = \Database::getInstance();

        if( $db->tableExists('tl_iido_global_category') )
        {
            $arrChangedTables       = array();
            $objGlobalCategories    = GlobalCategoryModel::findBy("pid", '0');

            if( $objGlobalCategories )
            {
                while( $objGlobalCategories->next() )
                {
                    $arrCatTables = \StringUtil::deserialize( $objGlobalCategories->enableCategoriesIn, TRUE );

                    if( count($arrCatTables) )
                    {
                        if( in_array( $strTable, $arrCatTables) )
                        {
                            $arrChangedTables[] = $objGlobalCategories->current();
                        }
                    }
                }
            }

            if( count($arrChangedTables) )
            {
//            $GLOBALS['TL_DCA'][ $strTable ]['config']['onload_callback'][]    = array('iido_basic.table.global_category', 'onCategoriesTableLoadCallback');
//            $GLOBALS['TL_DCA'][ $strTable ]['config']['onsubmit_callback'][]  = array('iido_basic.table.global_category', 'onCategoriesTableSubmitCallback');

                foreach( $GLOBALS['TL_DCA'][ $strTable ]['palettes'] as $strPalette => $strFields )
                {
                    if( $strPalette === '__selector__' ) continue;

//                $addCloser = false;

//                if( !preg_match('/;$/', $strFields) )
//                {
//                    $addCloser = true;
//                }

                    $strLegendFields = '';

                    foreach( $arrChangedTables as $objGC )
                    {
                        $strLegendFields .= ',gc_' . $objGC->alias;
                    }

                    if( FALSE !== strpos( $GLOBALS['TL_DCA'][ $strTable ]['palettes'][ $strPalette ], 'categories_legend' ) )
                    {
                        $GLOBALS['TL_DCA'][ $strTable ]['palettes'][ $strPalette ] = preg_replace('/\{categories_legend/', '{globalCategories_legend}' . $strLegendFields . ';{categories_legend', $GLOBALS['TL_DCA'][ $strTable ]['palettes'][ $strPalette ], 1);
                    }
                    else
                    {
                        $GLOBALS['TL_DCA'][ $strTable ]['palettes'][ $strPalette ] = preg_replace('/\{([A-Za-z0-9\-\s_:]{0,})\},([A-Za-z0-9\-_,]{0,});/', '{$1},$2;{globalCategories_legend}' . $strLegendFields . ';', $GLOBALS['TL_DCA'][ $strTable ]['palettes'][ $strPalette ], 1);
                    }

//                $GLOBALS['TL_DCA'][ $strTable ]['palettes'][ $strPalette ] = $strFields . ($addCloser ? ';' : '') . '{globalCategories_legend}' . $strLegendFields . ';';
                }

                foreach( $arrChangedTables as $objGC )
                {
                    if( $objGC->subCategoriesArePages )
                    {
                        $GLOBALS['TL_DCA'][ $strTable ]['fields'][ 'gc_' . $objGC->alias ] = array
                        (
                            'label'             => [$objGC->title],
//                        'foreignKey'        => 'tl_page.title',
                            'inputType'         => 'pageTree',
                            'eval'              => array('fieldType'=>'checkbox','multiple'=>true, 'tl_class'=>'w50 hauto'),
                            'input_field_callback' => array('iido_basic.table.global_category', 'renderCategoriesField')
//                        'relation'          => array('type'=>'hasMany', 'load'=>'lazy')
                        );

                        if( $objGC->subPagesRoot )
                        {
                            $GLOBALS['TL_DCA'][ $strTable ]['fields'][ 'gc_' . $objGC->alias ]['eval']['rootNodes'] = [$objGC->subPagesRoot];
                        }
                    }
                    else
                    {
                        $GLOBALS['TL_DCA'][ $strTable ]['fields'][ 'gc_' . $objGC->alias ] = array
                        (
                            'label'             => [$objGC->title],
//                        'exclude'           => true,
                            'foreignKey'        => 'tl_iido_global_category.title',
                            'inputType'         => 'globalCategoriesPicker',
                            'eval'              => array('fieldType'=>'checkbox','rootNodes'=>[$objGC->id], 'tl_class'=>'w50 hauto'),
                            'options_callback'  => array('iido_basic.table.global_category', 'onCategoriesOptionsCallback'),
                            'input_field_callback' => array('iido_basic.table.global_category', 'renderCategoriesField')
//                        'relation'          => array('type'=>'haste-ManyToMany', 'load'=>'lazy', 'table'=>'tl_iido_global_category', 'referenceColumn'=>'item_id', 'fieldColumn'=>'category_id', 'relationTable'=>'tl_iido_global_categories', 'skipInstall'=>true),
                        );
                    }
                }
            }
        }
    }



    public function addGlobalCategoryFieldsToTable( $arrDefinitions )
    {
//        echo "<pre>";
//        print_r( $arrDefinitions );
//        exit;

        return $arrDefinitions;
    }

}
