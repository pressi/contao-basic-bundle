<?php

$strConfTable = \IIDO\BasicBundle\Config\BundleConfig::getFileTable( __FILE__ );
$objConfTable = new \IIDO\BasicBundle\Dca\Table( $strConfTable );

//$objTable->setTableListener('iido.confif.dca.iido_config');



/**
 * Config
 */

$objConfTable->addTableConfig('enableVersioning', true);
$objConfTable->addTableConfig('closed', true);
$objConfTable->addTableConfig('switchToEdit', false);



/**
 * Palettes
 */

$arrPalette =
[
    'default' =>
    [
        'previewMode', 'backendStyles', 'customLogin'
    ],

    'navigation' =>
    [
        'enableMobileNavigation',
    ],

    'elements' =>
    [
        'includeElementFields',
        'removeHeadlineFieldFromElements',
        'enableLayout'
    ],

    'articles' =>
    [
        'includeArticleFields'
    ],

    'pages' =>
    [
        'includePageFields'
    ],

    'backend' =>
    [
        'navLabels'
    ]

];

$objConfTable->addPalette('default', $arrPalette);



/**
 * Subpalettes
 */

$objConfTable->addSubpalette('enableMobileNavigation', 'showMobileNavOnTablet,showMobileNavBurgerDark');

$objConfTable->addSubpalette('includeElementFields', 'elementFields');
$objConfTable->addSubpalette('includeArticleFields', 'articleFields');
$objConfTable->addSubpalette('includePageFields', 'pageFields');

$objConfTable->addSubpalette('customLogin', 'loginImageSRC,loginLogoSRC,loginShowPublisherLink,loginShowImageCopyright');
$objConfTable->addSubpalette('loginShowPublisherLink', 'loginPublisher');



/**
 * Fields
 */

// DEFAULT

\IIDO\BasicBundle\Dca\Field::create('previewMode', 'checkbox')
    ->addToTable( $objConfTable );

\IIDO\BasicBundle\Dca\Field::create('backendStyles', 'checkbox')
    ->addToTable( $objConfTable );

\IIDO\BasicBundle\Dca\Field::create('customLogin', 'checkbox')
    ->setAsSelector()
    ->addToTable( $objConfTable );

\IIDO\BasicBundle\Dca\Field::create('loginImageSRC', 'images')
    ->addToTable( $objConfTable );

\IIDO\BasicBundle\Dca\Field::create('loginLogoSRC', 'image')
    ->addToTable( $objConfTable );

\IIDO\BasicBundle\Dca\Field::create('loginShowPublisherLink', 'checkbox')
    ->setAsSelector()
    ->addToTable( $objConfTable );

\IIDO\BasicBundle\Dca\Field::create('loginPublisher', 'select')
    ->addToTable( $objConfTable );

\IIDO\BasicBundle\Dca\Field::create('loginShowImageCopyright', 'checkbox')
//    ->setAsSelector()
    ->addToTable( $objConfTable );



// NAVIGATION

\IIDO\BasicBundle\Dca\Field::create('enableMobileNavigation', 'checkbox')
    ->setAsSelector()
    ->addToTable( $objConfTable );

\IIDO\BasicBundle\Dca\Field::create('showMobileNavOnTablet', 'checkbox')
    ->addToTable( $objConfTable );

\IIDO\BasicBundle\Dca\Field::create('showMobileNavBurgerDark', 'checkbox')
    ->addToTable( $objConfTable );



// ELEMENTS

\IIDO\BasicBundle\Dca\Field::create('includeElementFields', 'checkbox')
    ->setAsSelector()
    ->addToTable( $objConfTable );

\IIDO\BasicBundle\Dca\Field::create('elementFields', 'checkbox')
    ->addEval('multiple', true)
    ->addToTable( $objConfTable );

\IIDO\BasicBundle\Dca\Field::create('removeHeadlineFieldFromElements', 'checkbox')
    ->addEval('tl_class', 'clr')
    ->addToTable( $objConfTable );

\IIDO\BasicBundle\Dca\Field::create('enableLayout', 'checkbox')
//    ->addEval('tl_class', 'clr')
    ->addToTable( $objConfTable );



// ARTICLES

\IIDO\BasicBundle\Dca\Field::create('includeArticleFields', 'checkbox')
    ->setAsSelector()
    ->addToTable( $objConfTable );

\IIDO\BasicBundle\Dca\Field::create('articleFields', 'checkbox')
    ->addEval('multiple', true)
    ->addToTable( $objConfTable );



// PAGES

\IIDO\BasicBundle\Dca\Field::create('includePageFields', 'checkbox')
    ->setAsSelector()
    ->addToTable( $objConfTable );

\IIDO\BasicBundle\Dca\Field::create('pageFields', 'checkbox')
    ->addEval('multiple', true)
    ->addToTable( $objConfTable );



// BACKEND

\IIDO\BasicBundle\Dca\Field::create('navLabels', 'optionWizard')
    ->addSQL("blob NULL")
    ->addToTable( $objConfTable );



$objConfTable->createDca();