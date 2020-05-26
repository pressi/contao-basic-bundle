<?php

$strContentFileName = \IIDO\BasicBundle\Config\BundleConfig::getFileTable( __FILE__ );
$objContentTable    = new \IIDO\BasicBundle\Dca\ExistTable( $strContentFileName );

$objContentTable->setTableListener( 'iido.basic.dca.content' );

//$config = \Contao\System::getContainer()->get('iido.basic.config');


/**
 * Sorting
 */

//if( $config->get('enableLayout') )
//{
//    $objContentTable->addSortingConfig('panelLayout', '', true);
//}



/**
 * Palettes
 */

//if( $config->get('includeElementFields') )
//{
//    $objContentTable->replacePaletteFields('all', ',headline', ',topHeadline,topHeadlineFloating,topHeadlineColor,headline');
//    $objContentTable->replacePaletteFields('all', ',headline', ',headline,headlineFloating,headlineColor');
//}

//if( $config->get('enableLayout') )
//{
//    $objContentTable->replacePaletteFields('all', '{expert_legend:hide}', '{layout_legend:hide},layout_cond_width,layout_col_mobile,layout_col_tablet,layout_col_desktop,layout_col_wide,layout_align_mobile,layout_align_tablet,layout_align_desktop,layout_align_wide;{expert_legend:hide}');
//}

$palette = $objContentTable->getDefaultPaletteFields( $strContentFileName, ['elements' => ['iido_elements']]);

$objContentTable->addPalette('iido_column_master', $palette);

//$objContentTable->removeFieldFromPalette('headline', 'all', 'headline');

$objContentTable->replaceFieldLegendInPalette('all', '{animation_legend},addAnimation;', 'end', ['boxStop','accordionStop','sliderStop','html','code','alias','article']);



/**
 * Subpalettes
 */

$objContentTable->addSubpalette("addAnimation", "animationType,animateRun,animationWait,animationOffset");



/**
 * Fields
 */

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
/*
\IIDO\BasicBundle\Dca\Field::create('topHeadline')
    ->addEval('tl_class', 'clr')
    ->addToTable( $objContentTable );

\IIDO\BasicBundle\Dca\Field::create('topHeadlineFloating', 'select')
    ->addOptions(['field', 'headlineFloating'])
    ->addDefault('left')
    ->addEval('tl_class', 'w25', true)
    ->addToTable( $objContentTable );

\IIDO\BasicBundle\Dca\Field::create('topHeadlineColor', 'select')
    ->addConfig('options_callback', [$objContentTable->getTableListener(), 'getColor'])
    ->addEval('includeBlankOption', true)
    ->addEval('tl_class', 'w25', true)
    ->addToTable( $objContentTable );


\IIDO\BasicBundle\Dca\Field::create('headlineFloating', 'select')
    ->addDefault('left')
    ->addEval('tl_class', 'w25', true)
    ->addToTable( $objContentTable );

\IIDO\BasicBundle\Dca\Field::create('headlineColor', 'select')
    ->addConfig('options_callback', [$objContentTable->getTableListener(), 'getColor'])
    ->addEval('includeBlankOption', true)
    ->addEval('tl_class', 'w25', true)
    ->addToTable( $objContentTable );
*/


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

$objContentTable->updateDca();