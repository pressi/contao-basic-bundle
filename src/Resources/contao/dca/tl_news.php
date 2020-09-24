<?php
/*******************************************************************
 * (c) 2020 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

\Contao\Controller::loadLanguageFile('tl_news');


$strFileName = 'tl_news';

$GLOBALS['TL_DCA'][ $strFileName ]['config']['backlink'] = 'do=news&ref=' . Input::get('ref');

$arrLangFields = ['headline', 'alias', 'pageTitle', 'description', 'url'];



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
//$GLOBALS['TL_DCA'][ $strFileName ]['palettes']['default'] = str_replace(',categories', ',categories,areasOfApplication,usage', $GLOBALS['TL_DCA'][ $strFileName ]['palettes']['default']);

foreach( $GLOBALS['TL_DCA'][ $strFileName ]['palettes'] as $palette => $fields )
{
    if( $palette === '__selector__' )
    {
        continue;
    }

    $GLOBALS['TL_DCA'][ $strFileName ]['palettes'][ $palette ] = str_replace(',categories', ',categories,areasOfApplication,usage', $fields);
}


foreach( $arrLangFields as $langField )
{
    foreach( $GLOBALS['TL_DCA'][ $strFileName ]['palettes'] as $palette => $fields )
    {
        if( $palette === '__selector__' )
        {
            continue;
        }

        $GLOBALS['TL_DCA'][ $strFileName ]['palettes'][ $palette ] = str_replace(',' . $langField, ',' . $langField . ',' . $langField . 'EN,' . $langField . 'US', $fields);
    }
}



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

$GLOBALS['TL_DCA'][ $strFileName ]['fields']['productMarket'] =
[
    'label'         => $GLOBALS['TL_LANG'][ $strFileName ]['productMarket'],
    'default'       => 'default',
    'exclude'       => true,
    'filter'        => true,
    'inputType'     => 'select',
    'options'       => $GLOBALS['TL_LANG'][ $strFileName ]['options']['productMarket'],
    'eval'          => ['tl_class'=>'w50','submitOnChange'=>true],
    'sql'           => "varchar(32) NOT NULL default 'default'"
];

$GLOBALS['TL_DCA'][ $strFileName ]['fields']['isAlsoUSProduct'] =
[
    'label'         => $GLOBALS['TL_LANG'][ $strFileName ]['isAlsoUSProduct'],
    'exclude'       => true,
    'filter'        => true,
    'inputType'     => 'checkbox',
    'eval'          => ['tl_class'=>'w50 m12',],
    'sql'           => "char(1) NOT NULL default ''"
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

            foreach( $GLOBALS['TL_DCA'][ $strFileName ]['palettes'] as $palette => $fields )
            {
                $GLOBALS['TL_DCA'][ $strFileName ]['palettes'][ $palette ] = str_replace(',headline,', ',productMarket,headline,', $fields);
            }

            if( $objElement->productMarket === 'default' )
            {
                foreach( $GLOBALS['TL_DCA'][ $strFileName ]['palettes'] as $palette => $fields )
                {
                    $GLOBALS['TL_DCA'][ $strFileName ]['palettes'][ $palette ] = str_replace(',productMarket,', ',productMarket,isAlsoUSProduct,', $fields);
                }
            }
        }
    }
}



$GLOBALS['TL_DCA'][ $strFileName ]['fields'][ 'headline' ]['eval']['tl_class'] = 'clr ' . $GLOBALS['TL_DCA'][ $strFileName ]['fields'][ 'headline' ]['eval']['tl_class'];



foreach( $arrLangFields as $langField )
{
    $strLangFileName = '';

    if( $langField === 'url' )
    {
        $strLangFileName = 'MSC';
    }

    $GLOBALS['TL_DCA'][ $strFileName ]['fields'][ $langField . 'EN'] = $GLOBALS['TL_DCA'][ $strFileName ]['fields'][ $langField ];
    $GLOBALS['TL_DCA'][ $strFileName ]['fields'][ $langField . 'US'] = $GLOBALS['TL_DCA'][ $strFileName ]['fields'][ $langField ];

    unset( $GLOBALS['TL_DCA'][ $strFileName ]['fields'][ $langField . 'EN']['save_callback'] );
    unset( $GLOBALS['TL_DCA'][ $strFileName ]['fields'][ $langField . 'US']['save_callback'] );

    $strIconEN = '<img src="/files/hhsystem/images/backend/flag-en.svg" width="22" height="22">';
    $strIconUS = '<img src="/files/hhsystem/images/backend/flag-us.svg" width="22" height="22">';


    $GLOBALS['TL_DCA'][ $strFileName ]['fields'][ $langField ]['eval']['tl_class'] = preg_replace(['/w50/', '/[\s]{2,}/'], ['w33', '', ''], $GLOBALS['TL_DCA'][ $strFileName ]['fields'][ $langField ]['eval']['tl_class']);
    $GLOBALS['TL_DCA'][ $strFileName ]['fields'][ $langField . 'EN']['eval']['tl_class'] = preg_replace(['/w50/', '/clr/', '/[\s]{2,}/'], ['w33', '', ''], $GLOBALS['TL_DCA'][ $strFileName ]['fields'][ $langField . 'EN']['eval']['tl_class']);
    $GLOBALS['TL_DCA'][ $strFileName ]['fields'][ $langField . 'US']['eval']['tl_class'] = preg_replace(['/w50/', '/clr/', '/[\s]{2,}/'], ['w33', '', ''], $GLOBALS['TL_DCA'][ $strFileName ]['fields'][ $langField . 'US']['eval']['tl_class']);

    unset( $GLOBALS['TL_DCA'][ $strFileName ]['fields'][ $langField ]['label'] );
    $GLOBALS['TL_DCA'][ $strFileName ]['fields'][ $langField ]['label'] = $GLOBALS['TL_LANG'][ $strLangFileName?:$strFileName ][ $langField ];

    unset( $GLOBALS['TL_DCA'][ $strFileName ]['fields'][ $langField . 'EN']['label'] );
    $GLOBALS['TL_DCA'][ $strFileName ]['fields'][ $langField . 'EN']['label'] = $GLOBALS['TL_LANG'][ $strLangFileName?:$strFileName ][ $langField ];

    unset( $GLOBALS['TL_DCA'][ $strFileName ]['fields'][ $langField . 'US']['label'] );
    $GLOBALS['TL_DCA'][ $strFileName ]['fields'][ $langField . 'US']['label'] = $GLOBALS['TL_LANG'][ $strLangFileName?:$strFileName ][ $langField ];

    $GLOBALS['TL_DCA'][ $strFileName ]['fields'][ $langField . 'EN']['label'][0] = $strIconEN . $GLOBALS['TL_LANG'][ $strLangFileName?:$strFileName ][ $langField ][0];
    $GLOBALS['TL_DCA'][ $strFileName ]['fields'][ $langField . 'US']['label'][0] = $strIconUS . $GLOBALS['TL_LANG'][ $strLangFileName?:$strFileName ][ $langField ][0];

    $GLOBALS['TL_DCA'][ $strFileName ]['fields'][ $langField . 'EN']['eval']['mandatory'] = false;
    $GLOBALS['TL_DCA'][ $strFileName ]['fields'][ $langField . 'US']['eval']['mandatory'] = false;
}

$GLOBALS['TL_DCA'][ $strFileName ]['fields']['description']['eval']['tl_class'] = 'clr ' . $GLOBALS['TL_DCA'][ $strFileName ]['fields']['description']['eval']['tl_class'];