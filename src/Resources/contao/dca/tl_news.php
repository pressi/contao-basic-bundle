<?php
/*******************************************************************
 * (c) 2020 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/


$strFileName = 'tl_news';

$GLOBALS['TL_DCA'][ $strFileName ]['config']['backlink'] = 'do=news&ref=' . Input::get('ref');



/**
 * Global Operations
 */

if( Input::get('id') == '1' )
{
    array_insert($GLOBALS['TL_DCA'][ $strFileName ]['list']['global_operations'], 3,
    [
        'areasOfApplication' =>
            [
                'label' => &$GLOBALS['TL_LANG'][ $strFileName ]['global_operation']['areasOfApplication'],
                'href' => 'table=tl_news_areaOfApplication',
                'icon' => 'bundles/codefognewscategories/icon.png',
                'attributes' => 'onclick="Backend.getScrollOffset()"',
            ],

        'uses' =>
            [
                'label' => &$GLOBALS['TL_LANG'][ $strFileName ]['global_operation']['uses'],
                'href' => 'table=tl_news_usage',
                'icon' => 'bundles/codefognewscategories/icon.png',
                'attributes' => 'onclick="Backend.getScrollOffset()"',
            ]
    ]
    );

    $GLOBALS['TL_DCA'][ $strFileName ]['list']['sorting']['flag']   = 2;
    $GLOBALS['TL_DCA'][ $strFileName ]['list']['sorting']['fields'] = ['headline'];
}



/**
 * Palettes
 */


//$GLOBALS['TL_DCA'][ $strFileName ]['palettes']['default'] = str_replace(',categories', ',categories,areasOfApplication', $GLOBALS['TL_DCA'][ $strFileName ]['palettes']['default']);
$GLOBALS['TL_DCA'][ $strFileName ]['palettes']['default'] = str_replace(',categories', ',categories,areasOfApplication,usage', $GLOBALS['TL_DCA'][ $strFileName ]['palettes']['default']);



/**
 * Fields
 */

$GLOBALS['TL_DCA'][ $strFileName ]['fields']['areasOfApplication'] = [
    'label'             => &$GLOBALS['TL_LANG'][ $strFileName ]['areasOfApplication'],
    'exclude'           => true,
    'filter'            => true,
    'inputType'         => 'newsAreaOfApplicationPicker',
    'foreignKey'        => 'tl_news_areaOfApplication.title',
    'options_callback'  => ['iido.basic.listener.data_container.news', 'onAreaOfApplicationOptionsCallback'],
    'eval'              => ['multiple' => true, 'fieldType' => 'checkbox', 'tl_class' => 'clr w50 hauto'],
    'relation'          => [
        'type'              => 'haste-ManyToMany',
        'load'              => 'lazy',
        'table'             => 'tl_news_areaOfApplication',
        'referenceColumn'   => 'news_id',
        'fieldColumn'       => 'areaOfApplication_id',
        'relationTable'     => 'tl_news_areasOfApplication',
    ],
];

$GLOBALS['TL_DCA'][ $strFileName ]['fields']['usage'] = [
    'label'             => &$GLOBALS['TL_LANG'][ $strFileName ]['usage'],
    'exclude'           => true,
    'filter'            => true,
    'inputType'         => 'newsUsagePicker',
    'foreignKey'        => 'tl_news_usage.title',
    'options_callback'  => ['iido.basic.listener.data_container.news', 'onUsageOptionsCallback'],
    'eval'              => ['multiple' => true, 'fieldType' => 'checkbox', 'tl_class' => 'w50 hauto'],
    'relation'          => [
        'type'              => 'haste-ManyToMany',
        'load'              => 'lazy',
        'table'             => 'tl_news_usage',
        'referenceColumn'   => 'news_id',
        'fieldColumn'       => 'usage_id',
        'relationTable'     => 'tl_news_uses',
    ],
];

if( Input::get('do') === 'news' && Input::get('act') === 'edit' )
{
    $objElement = \Contao\NewsModel::findByPk( Input::get('id') );

    if( $objElement )
    {
        $objArchive = \Contao\NewsArchiveModel::findByPk( $objElement->pid );

        if( $objArchive && $objArchive->id == '1' )
        {
            $GLOBALS['TL_LANG'][ $strFileName ]['category_legend'] = 'Kategorien / Filter';
            $GLOBALS['TL_LANG'][ $strFileName ]['related_news_legend'] = 'Verwandte Produkte';

            $GLOBALS['TL_LANG'][ $strFileName ]['alias'][0] = 'Produktalias';
            $GLOBALS['TL_LANG'][ $strFileName ]['relatedNews'][0] = 'Verwandte Produkte';
        }
    }
}