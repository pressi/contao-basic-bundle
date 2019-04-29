<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/


/**
 * Fields
 */

$GLOBALS['TL_DCA']['tl_files']['config']['onsubmit_callback'][] = array('IIDO\BasicBundle\Table\FilesTable', 'onSubmitCallback');
$GLOBALS['TL_DCA']['tl_files']['config']['onload_callback'][]   = array('IIDO\BasicBundle\Table\FilesTable', 'onLoadCallback');

$GLOBALS['TL_DCA']['tl_files']['fields']['meta']['eval']['metaFields']['link']          = 'link';

$GLOBALS['TL_DCA']['tl_files']['fields']['meta']['eval']['metaFields']['link_title']    = 'text';
$GLOBALS['TL_DCA']['tl_files']['fields']['meta']['eval']['metaFields']['description']   = 'textarea';

$GLOBALS['TL_DCA']['tl_files']['fields']['meta']['eval']['metaFields']['imageText']     = 'text';
$GLOBALS['TL_DCA']['tl_files']['fields']['meta']['eval']['metaFields']['imageSubline']  = 'text';
$GLOBALS['TL_DCA']['tl_files']['fields']['meta']['eval']['metaFields']['it_position']   = 'select_blank';
$GLOBALS['TL_DCA']['tl_files']['fields']['meta']['eval']['metaFields']['it_pos_margin'] = 'text_4';
$GLOBALS['TL_DCA']['tl_files']['fields']['meta']['eval']['metaFields']['it_text_align'] = 'select';

$GLOBALS['TL_DCA']['tl_files']['fields']['meta']['eval']['metaFields']['hoverTitle']    = 'text';
$GLOBALS['TL_DCA']['tl_files']['fields']['meta']['eval']['metaFields']['hoverSubTitle'] = 'text';

$GLOBALS['TL_DCA']['tl_files']['fields']['meta']['eval']['metaFields']['cssClass']      = 'text';
$GLOBALS['TL_DCA']['tl_files']['fields']['meta']['eval']['metaFields']['categories']    = 'iidoTag';

$GLOBALS['TL_DCA']['tl_files']['fields']['meta']['eval']['metaFields']['color']         = 'color';



$objGloablCategories = \IIDO\BasicBundle\Model\GlobalCategoryModel::findPublishedByPid(0);

if( $objGloablCategories )
{
    while( $objGloablCategories->next() )
    {
        $arrEnableIn = \StringUtil::deserialize( $objGloablCategories->enableCategoriesIn, TRUE );

        if( is_array($arrEnableIn) && in_array('tl_files', $arrEnableIn) )
        {
            array_insert($GLOBALS['TL_DCA']['tl_files']['fields']['meta']['eval']['metaFields'], 0, array
            (
                'gc_' . $objGloablCategories->alias => 'globalCategoriesPicker_' . $objGloablCategories->id
            ));


            $GLOBALS['TL_LANG']['MSC']['aw_gc_' . $objGloablCategories->alias ] = $objGloablCategories->title;
        }
    }
}