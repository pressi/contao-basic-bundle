<?php
/*******************************************************************
 * (c) 2020 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/


$strFileTable   = \IIDO\BasicBundle\Config\BundleConfig::getTableName( __FILE__ );
//$objTable       = new \IIDO\BasicBundle\Dca\ExistTable( $strFileTable );

$objTable = new \IIDO\BasicBundle\Dca\Table( $strFileTable );

$objTable->setTableListener('iido.basic.listener.data_container.news');
$objTable->addTableButtonsLabel(['new'=>'r Verwendungszweck'], 'de');



// SORTING
$objTable->addSorting(5, ['icon'=>'bundles/iidobasic/icon.png']);
//$objTable->addSortingFields(['title']);
$objTable->addSortingPanel('filter;search');
$objTable->addSortingConfig('paste_button_callback', ['codefog_news_categories.listener.data_container.news_category', 'onPasteButtonCallback' ]);

//'label' => [
//    'fields' => ['title', 'frontendTitle'],
//    'format' => '%s <span style="padding-left:3px;color:#b3b3b3;">[%s]</span>',
//    'label_callback' => ['codefog_news_categories.listener.data_container.news_category', 'onLabelCallback'],
//],


// GLOBAL OPERATIONS
$arrGlobalOperations = [
    'toggleNodes' => [
        'label' => &$GLOBALS['TL_LANG']['MSC']['toggleAll'],
        'href' => 'ptg=all',
        'class' => 'header_toggle',
    ]
];

$objTable->addGlobalOperations(true, $arrGlobalOperations );



// OPERATIONS
$objTable->addOperations('default');
$objTable->addOperation('editHeader' );
//$objTable->addOperation('copyChilds' );

$objTable->removeOperation('edit' );
$objTable->removeOperation('toggle' );



// TABLE CONFIG
$objTable->addTableConfig('label', 'Verwendungszweck');
$objTable->addTableConfig('backlink', 'do=news&table=tl_news&id=' . Input::get('id') . '&rt=' . Input::get('rt'). '&ref=' . Input::get('ref'));



// LIST CONFIG
$objTable->addLabel(['title', 'frontendTitle'], '%s <span style="padding-left:3px;color:#b3b3b3;">[%s]</span>');
//'label_callback' => ['codefog_news_categories.listener.data_container.news_category', 'onLabelCallback']



/**
 * Palettes
 */

//$objTable->addPalette('default', '{title_legend},title,alias,frontendTitle,,cssClass,mainCategory;{details_legend:hide},image,address,geo,website,phone,email,oppening,description,map;{video_legend},addVideo;{modules_legend:hide},hideInList,hideInReader,excludeInRelated;{redirect_legend:hide},jumpTo;');
$objTable->addPalette('default', '{title_legend},title,titleEN,titleUS,alias,aliasEN,aliasUS,frontendTitle,frontendTitleEN,frontendTitleUS,cssClass;{description_legend},description,descriptionEN,descriptionUS;{redirect_legend:hide},jumpTo;{modules_legend:hide},hideInList,hideInReader,excludeInRelated;');



/**
 * Subpalette
 */

//$objTable->addSubpalette('addVideo', 'videoMode');
//$objTable->addSubpalette('videoMode_player', 'playerSRC,posterSRC,playerSize');
//$objTable->addSubpalette('videoMode_vimeo', 'vimeoID');
//$objTable->addSubpalette('videoMode_youtube', 'youtubeID');



/**
 * Fields
 */

\IIDO\BasicBundle\Dca\Field::create('title')
    ->addToSearch()
    ->addEval('mandatory', true)
    ->addEval('tl_class', 'w33', true)
    ->addToTable( $objTable );

\IIDO\BasicBundle\Dca\Field::create('titleEN')
    ->setLabelPrefix('<img src="/files/hhsystem/images/backend/flag-en.svg" width="22" height="22">')
    ->addLabelName('title')
    ->addEval('tl_class', 'w33', true)
    ->addToTable( $objTable );
\IIDO\BasicBundle\Dca\Field::create('titleUS')
    ->addEval('tl_class', 'w33', true)
    ->addLabelName('title')
    ->setLabelPrefix('<img src="/files/hhsystem/images/backend/flag-us.svg" width="22" height="22">')
    ->addToTable( $objTable );



\IIDO\BasicBundle\Dca\Field::create('frontendTitle')
    ->addToSearch()
    ->addEval('tl_class', 'w33', true)
    ->addToTable( $objTable );

\IIDO\BasicBundle\Dca\Field::create('frontendTitleEN')
->addLabelName('frontendTitle')
    ->addEval('tl_class', 'w33', true)
    ->setLabelPrefix('<img src="/files/hhsystem/images/backend/flag-en.svg" width="22" height="22">')
    ->addToTable( $objTable );
\IIDO\BasicBundle\Dca\Field::create('frontendTitleUS')
    ->addLabelName('frontendTitle')
    ->addEval('tl_class', 'w33', true)
    ->setLabelPrefix('<img src="/files/hhsystem/images/backend/flag-us.svg" width="22" height="22">')
    ->addToTable( $objTable );


$objTable->addSortingField();
$objTable->addPidField( false );
//$objTable->addAliasField();


$objAliasField = $objTable->addAliasField('usage');
$objAliasField->addEval('tl_class', 'w33', true);

\IIDO\BasicBundle\Dca\Field::create('aliasEN')
    ->addLabelName('alias')
    ->addEval('tl_class', 'w33', true)
    ->setLabelPrefix('<img src="/files/hhsystem/images/backend/flag-en.svg" width="22" height="22">')
    ->addToTable( $objTable );
\IIDO\BasicBundle\Dca\Field::create('aliasUS')
    ->addLabelName('alias')
    ->addEval('tl_class', 'w33', true)
    ->setLabelPrefix('<img src="/files/hhsystem/images/backend/flag-us.svg" width="22" height="22">')
    ->addToTable( $objTable );


$objTable->addPublishedFields('', '', '', false);

\IIDO\BasicBundle\Dca\Field::create('cssClass')->addToTable( $objTable );


\IIDO\BasicBundle\Dca\Field::create('description', 'textarea')
    ->setUseRTE()
    ->addToTable( $objTable );

\IIDO\BasicBundle\Dca\Field::create('descriptionEN', 'textarea')
    ->addLabelName('description')
    ->setUseRTE()
    ->setLabelPrefix('<img src="/files/hhsystem/images/backend/flag-en.svg" width="22" height="22">')
    ->addToTable( $objTable );
\IIDO\BasicBundle\Dca\Field::create('descriptionUS', 'textarea')
    ->addLabelName('description')
    ->setUseRTE()
    ->setLabelPrefix('<img src="/files/hhsystem/images/backend/flag-us.svg" width="22" height="22">')
    ->addToTable( $objTable );

//\IIDO\BasicBundle\Dca\Field::create('address', 'textarea')
//    ->setUseRTE()
//    ->addToTable( $objTable );
//
//\IIDO\BasicBundle\Dca\Field::create('geo')->addToTable( $objTable );
//
//\IIDO\BasicBundle\Dca\Field::create('website')
//    ->addToTable( $objTable );
//
//\IIDO\BasicBundle\Dca\Field::create('phone')
//    ->addEval('rgxp', 'phone')
//    ->addToTable( $objTable );
//
//\IIDO\BasicBundle\Dca\Field::create('email')
//    ->addEval('rgxp', 'email')
//    ->addToTable( $objTable );
//
//\IIDO\BasicBundle\Dca\Field::create('oppening', 'textarea')
//    ->setUseRTE()
//    ->addToTable( $objTable );
//
//\IIDO\BasicBundle\Dca\Field::create('image', 'fileTree')->addToTable( $objTable );
//
//\IIDO\BasicBundle\Dca\Field::create('map', 'fileTree')->addToTable( $objTable );

\IIDO\BasicBundle\Dca\Field::create('hideInList', 'checkbox')
    ->addToFilter()
    ->addToTable( $objTable );

\IIDO\BasicBundle\Dca\Field::create('hideInReader', 'checkbox')
    ->addToFilter()
    ->addToTable( $objTable );

\IIDO\BasicBundle\Dca\Field::create('excludeInRelated', 'checkbox')
    ->addToFilter()
    ->addToTable( $objTable );

\IIDO\BasicBundle\Dca\Field::create('jumpTo', 'pageTree')->addToTable( $objTable );



// VIDEO

//\IIDO\BasicBundle\Dca\Field::create('addVideo', 'checkbox')
//    ->setSelector()
//    ->addToTable( $objTable );
//
//\IIDO\BasicBundle\Dca\Field::create('videoMode', 'select')
//    ->setSelector()
//    ->addEval('includeBlankOption', true)
//    ->addToTable( $objTable );
//
//\IIDO\BasicBundle\Dca\Field::copy('playerSRC', 'tl_content')
//    ->addToTable( $objTable );
//
//\IIDO\BasicBundle\Dca\Field::copy('posterSRC', 'tl_content')
//    ->addToTable( $objTable );
//
//\IIDO\BasicBundle\Dca\Field::copy('playerSize', 'tl_content')
//    ->addToTable( $objTable );
//
//\IIDO\BasicBundle\Dca\Field::copy('youtubeID', 'tl_content', 'youtube')
//    ->addToTable( $objTable );
//
//\IIDO\BasicBundle\Dca\Field::copy('vimeoID', 'tl_content', 'vimeo')
//    ->addToTable( $objTable );



// Category
//\IIDO\BasicBundle\Dca\Field::create('mainCategory', 'select')->addToTable( $objTable );



$objTable->createDca();
