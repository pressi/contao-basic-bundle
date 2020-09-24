<?php

$strContentFileName = \IIDO\BasicBundle\Config\BundleConfig::getFileTable( __FILE__ );
$objContentTable    = new \IIDO\BasicBundle\Dca\ExistTable( $strContentFileName );

$objContentTable->setTableListener( 'iido.basic.dca.content' );

//$config = \Contao\System::getContainer()->get('iido.config.config');

$objElement = $isNews = false;

if( Input::get('act') === 'edit' )
{
    $objElement = \Contao\ContentModel::findByPk( Input::get('id') );
}


if( $objElement )
{
    if( $objElement->ptable === 'tl_news' )
    {
        $isNews = true;
    }
}



/**
 * Sorting
 */

$objContentTable->addSortingConfig('child_record_callback', array('iido.config.dca.content', 'addNewsLanguageFlags'));

//if( $config->get('enableLayout') )
//{
//    $objContentTable->addSortingConfig('panelLayout', '', true);
//}



/**
 * Palettes
 */

//$arrFields      = StringUtil::deserialize( $objConfig->get('elementFields'), true);
$arrFields      = StringUtil::deserialize( \IIDO\BasicBundle\Config\IIDOConfig::get('elementFields'), true);

//$removeHeadline = $objConfig->get('removeHeadlineFieldFromElements');
$removeHeadline = \IIDO\BasicBundle\Config\IIDOConfig::get('removeHeadlineFieldFromElements');


//if( $objConfig->get('includeElementFields') )
if( \IIDO\BasicBundle\Config\IIDOConfig::get('includeElementFields') )
{
    $headlineFields = '';


    // HEADLINE

    if( in_array('topHeadline', $arrFields) )
    {
        $headlineFields = ',topHeadline,headline';
    }

    if( in_array('subHeadline', $arrFields) )
    {
        if( !$headlineFields )
        {
            $headlineFields = ',headline';
        }

        $headlineFields .= ',subHeadline';
    }

    if( $headlineFields && !$removeHeadline )
    {
        $objContentTable->replacePaletteFields('all', ',headline', $headlineFields);
    }

    if( $removeHeadline && $headlineFields )
    {
        $objContentTable->replacePaletteFields('headline', ',headline', $headlineFields);
    }


    // ANIMATION

    if( in_array('animation', $arrFields) )
    {
        $objContentTable->replaceFieldLegendInPalette('all', '{animation_legend},addAnimation;', 'end', ['boxStop','accordionStop','sliderStop','html','code','alias','article']);
    }


//    $objContentTable->replacePaletteFields('all', ',headline', ',topHeadline,topHeadlineFloating,topHeadlineColor,headline');
//    $objContentTable->replacePaletteFields('all', ',headline', ',headline,headlineFloating,headlineColor');
}

//if( $objConfig->get('enableLayout') )
//{
//    $objContentTable->replacePaletteFields('all', '{expert_legend:hide}', '{layout_legend:hide},layout_cond_width,layout_col_mobile,layout_col_tablet,layout_col_desktop,layout_col_wide,layout_align_mobile,layout_align_tablet,layout_align_desktop,layout_align_wide;{expert_legend:hide}');
//}

if( $removeHeadline )
{
    $objContentTable->removeFieldFromPalette('headline', 'all', 'headline');
}

//$palette = $objContentTable->getDefaultPaletteFields( $strContentFileName, ['elements' => ['iido_elements']]);
//$objContentTable->addPalette('iido_column_master', $palette);

if( $isNews )
{
    $objContentTable->replacePaletteFields('all', ',type', ',type,showInLanguage');
}



/**
 * Subpalettes
 */

$objContentTable->addSubpalette("addAnimation", "animationType,animateRun,animationWait,animationOffset");



/**
 * Fields
 */

\IIDO\BasicBundle\Dca\Field::create('showInLanguage', 'checkbox')
    ->addDefault(serialize(['de']))
    ->addEval('multiple', true)
    ->addToTable( $objContentTable );

//\IIDO\BasicBundle\Dca\Field::copy('pfield', 'tl_fieldpalette', 'pfield')
//    ->addFieldToTable( $objContentTable );
//
//\IIDO\BasicBundle\Dca\Field::create('iido_elements', 'fieldpalette')
//    ->addEval('tl_class', 'clr', true)
//    ->addConfig('foreignKey', 'tl_iido_content.id')
//    ->addConfig('relation', ['type' => 'hasMany', 'load' => 'eager'])
//    ->addConfig('fieldpalette', [
//        'config' =>
//            [
//                'hidePublished'     => true,
//                'table'             => 'tl_iido_content'
//            ],
//
//        'list' =>
//            [
//                'label' =>
//                    [
//                        'fields'    => ['type', 'internName'],
//                        'format'    => '%s <span style="color:#b3b3b3;padding-left:3px">[%s]</span>',
//                        'label_callback' => ['\IIDO\BasicBundle\Dca\Table\ContentTable', 'getMasterColumnElementLabel']
//                    ]
//            ]
//    ])
//    ->addSQL('blob NULL')
//    ->addFieldToTable( $objContentTable);



// ANIMATION

\IIDO\BasicBundle\Dca\Field::create('addAnimation', 'checkbox')
    ->setAsSelector()
    ->addToTable( $objContentTable );

\IIDO\BasicBundle\Dca\Field::create('animationType', 'select')
    ->addEval('includeBlankOption', true)
    ->addToTable( $objContentTable );

\IIDO\BasicBundle\Dca\Field::create('animationOffset' )
    ->addEval('maxlength', 80)
    ->addToTable( $objContentTable );

\IIDO\BasicBundle\Dca\Field::create('animationWait', 'checkbox')
    ->addToTable( $objContentTable );

\IIDO\BasicBundle\Dca\Field::create('animateRun', 'select')
    ->addToTable( $objContentTable );



// HEADLINE

\IIDO\BasicBundle\Dca\Field::create('topHeadline')
    ->addEval('tl_class', 'clr')
    ->addToTable( $objContentTable );

//\IIDO\BasicBundle\Dca\Field::create('topHeadlineFloating', 'select')
//    ->addOptions(['field', 'headlineFloating'])
//    ->addDefault('left')
//    ->addEval('tl_class', 'w25', true)
//    ->addToTable( $objContentTable );

//\IIDO\BasicBundle\Dca\Field::create('topHeadlineColor', 'select')
//    ->addConfig('options_callback', [$objContentTable->getTableListener(), 'getColor'])
//    ->addEval('includeBlankOption', true)
//    ->addEval('tl_class', 'w25', true)
//    ->addToTable( $objContentTable );


//\IIDO\BasicBundle\Dca\Field::create('headlineFloating', 'select')
//    ->addDefault('left')
//    ->addEval('tl_class', 'w25', true)
//    ->addToTable( $objContentTable );

//\IIDO\BasicBundle\Dca\Field::create('headlineColor', 'select')
//    ->addConfig('options_callback', [$objContentTable->getTableListener(), 'getColor'])
//    ->addEval('includeBlankOption', true)
//    ->addEval('tl_class', 'w25', true)
//    ->addToTable( $objContentTable );

\IIDO\BasicBundle\Dca\Field::create('subHeadline')
    ->addEval('tl_class', 'clr')
    ->addToTable( $objContentTable );



// LAYOUT
/*
// -- Visibility
\IIDO\BasicBundle\Dca\Field::create('layout_cond_width', 'checkbox')
    ->addEval('tl_class', 'clr cw25', true)
    ->addEval('multiple', true)
    ->addEval('mandatory', true)
    ->addSQL("blob NULL")
    ->addDefault(['mobile', 'tablet', 'desktop', 'wide'])
    ->addToTable( $objContentTable );


// -- COLS
\IIDO\BasicBundle\Dca\Field::create('layout_col_mobile', 'select')
    ->addConfig('search', true)
    ->addEval('tl_class', 'w25', true)
    ->addSQL("varchar(2) NOT NULL default '12'")
    ->addToTable( $objContentTable );

\IIDO\BasicBundle\Dca\Field::create('layout_col_tablet', 'select')
    ->addOptionsName('layout_col_mobile')
    ->addConfig('search', true)
    ->addEval('tl_class', 'w25', true)
    ->addEval('includeBlankOption', true)
    ->addEval('blankOptionLabel', 'wie Smartphone')
    ->addSQL("varchar(2) NOT NULL default ''")
    ->addToTable( $objContentTable );

\IIDO\BasicBundle\Dca\Field::create('layout_col_desktop', 'select')
    ->addOptionsName('layout_col_mobile')
    ->addConfig('search', true)
    ->addEval('tl_class', 'w25', true)
    ->addEval('includeBlankOption', true)
    ->addEval('blankOptionLabel', 'wie Tablet')
    ->addSQL("varchar(2) NOT NULL default ''")
    ->addToTable( $objContentTable );

\IIDO\BasicBundle\Dca\Field::create('layout_col_wide', 'select')
    ->addOptionsName('layout_col_mobile')
    ->addConfig('search', true)
    ->addEval('tl_class', 'w25', true)
    ->addEval('includeBlankOption', true)
    ->addEval('blankOptionLabel', 'wie Desktop')
    ->addSQL("varchar(2) NOT NULL default ''")
    ->addToTable( $objContentTable );


// -- Alignemnt

\IIDO\BasicBundle\Dca\Field::create('layout_align_mobile', 'select')
    ->addEval('tl_class', 'w25', true)
//    ->addEval('includeBlankOption', true)
//    ->addEval('blankOptionLabel', 'Normal')
    ->addSQL("varchar(8) NOT NULL default ''")
    ->addToTable( $objContentTable );

\IIDO\BasicBundle\Dca\Field::create('layout_align_tablet', 'select')
    ->addOptionsName('layout_align_mobile')
    ->addEval('tl_class', 'w25', true)
    ->addEval('includeBlankOption', true)
    ->addEval('blankOptionLabel', 'wie Smartphone')
    ->addSQL("varchar(8) NOT NULL default ''")
    ->addToTable( $objContentTable );

\IIDO\BasicBundle\Dca\Field::create('layout_align_desktop', 'select')
    ->addOptionsName('layout_align_mobile')
    ->addEval('tl_class', 'w25', true)
    ->addEval('includeBlankOption', true)
    ->addEval('blankOptionLabel', 'wie Tablet')
    ->addSQL("varchar(8) NOT NULL default ''")
    ->addToTable( $objContentTable );

\IIDO\BasicBundle\Dca\Field::create('layout_align_wide', 'select')
    ->addOptionsName('layout_align_mobile')
    ->addEval('tl_class', 'w25', true)
    ->addEval('includeBlankOption', true)
    ->addEval('blankOptionLabel', 'wie Desktop')
    ->addSQL("varchar(8) NOT NULL default ''")
    ->addToTable( $objContentTable );
*/

//if( $objConfig->get('includeElementFields') && in_array('cssID', $arrFields) )
if( \IIDO\BasicBundle\Config\IIDOConfig::get('includeElementFields') && in_array('cssID', $arrFields) )
{
    \IIDO\BasicBundle\Dca\Field::update('cssID', $objContentTable)
        ->addEval('tl_class', 'css-id-field')
        ->updateField();
}

$objContentTable->updateDca();