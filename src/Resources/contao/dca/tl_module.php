<?php

$strModuleFileName = \IIDO\BasicBundle\Config\BundleConfig::getFileTable( __FILE__ );
//$objModuleTable    = new \IIDO\BasicBundle\Dca\ExistTable( $strModuleFileName );

//$objContentTable->setTableListener( 'iido.config.dca.module' );



/**
 * Palettes
 */

//$objModuleTable->replaceFieldLegendInPalette('newslist', '', 'end', ['boxStop','accordionStop','sliderStop','html','code','alias','article']);
//$objModuleTable->replacePaletteFields('newslist', '{redirect_legend', '{filter_legend},news_filterUsage,news_filterAreaOfApplication;{redirect_legend');
$GLOBALS['TL_DCA'][ $strModuleFileName ]['palettes']['newslist'] = str_replace('{redirect_legend', '{filter_legend},news_filterUsage,news_filterAreasOfApplication;{redirect_legend', $GLOBALS['TL_DCA'][ $strModuleFileName ]['palettes']['newslist']);

$GLOBALS['TL_DCA'][ $strModuleFileName ]['palettes']['areaOfApplicationList'] = $GLOBALS['TL_DCA'][ $strModuleFileName ]['palettes']['newscategories'];



/**
 * Fields
 */

$GLOBALS['TL_DCA'][ $strModuleFileName ]['fields']['news_filterAreasOfApplication'] = [
    'label'             => &$GLOBALS['TL_LANG'][ $strModuleFileName ]['areasOfApplication'],
    'exclude'           => true,
    'filter'            => true,
    'inputType'         => 'newsAreaOfApplicationPicker',
    'foreignKey'        => 'tl_news_areaOfApplication.title',
    'eval' => ['multiple' => true, 'fieldType' => 'checkbox', 'tl_class' => 'clr'],
    'sql' => ['type' => 'blob', 'notnull' => false]

//    'options_callback'  => ['iido.config.listener.data_container.news', 'onAreaOfApplicationOptionsCallback'],
//    'eval'              => ['multiple' => true, 'fieldType' => 'checkbox', 'tl_class' => 'clr w50 hauto'],
//    'relation'          => [
//        'type'              => 'haste-ManyToMany',
//        'load'              => 'lazy',
//        'table'             => 'tl_news_areaOfApplication',
//        'referenceColumn'   => 'news_id',
//        'fieldColumn'       => 'areaOfApplication_id',
//        'relationTable'     => 'tl_news_areasOfApplication',
//    ],
];

$GLOBALS['TL_DCA'][ $strModuleFileName ]['fields']['news_filterUsage'] = [
    'label'             => &$GLOBALS['TL_LANG'][ $strModuleFileName ]['usage'],
    'exclude'           => true,
    'filter'            => true,
    'inputType'         => 'newsUsagePicker',
    'foreignKey'        => 'tl_news_usage.title',
    'eval' => ['multiple' => true, 'fieldType' => 'checkbox', 'tl_class' => 'clr'],
    'sql' => ['type' => 'blob', 'notnull' => false]

//    'options_callback'  => ['iido.config.listener.data_container.news', 'onUsageOptionsCallback'],
//    'eval'              => ['multiple' => true, 'fieldType' => 'checkbox', 'tl_class' => 'w50 hauto'],
//    'relation'          => [
//        'type'              => 'haste-ManyToMany',
//        'load'              => 'lazy',
//        'table'             => 'tl_news_usage',
//        'referenceColumn'   => 'news_id',
//        'fieldColumn'       => 'usage_id',
//        'relationTable'     => 'tl_news_uses',
//    ],
];
