<?php
/******************************************************************
 *
 * (c) 2016 Stephan Preßl <development@prestep.at>
 * All rights reserved
 *
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *
 ******************************************************************/

\Controller::loadLanguageFile("tl_page");
\Controller::loadDataContainer("tl_page");

$objArticle         = FALSE;
$objParentPage      = FALSE;

$act                = \Input::get("act");
$id                 = \Input::get("id");

if( $act == "edit" )
{
    $objArticle = \ArticleModel::findByPk( $id );
}

if( $objArticle )
{
    $objParentPage = \PageModel::findByPk( $objArticle->pid );
}



/**
 * Palettes - Selectors
 */

$GLOBALS['TL_DCA']['tl_article']['palettes']['__selector__'][]           = 'fullHeight';
$GLOBALS['TL_DCA']['tl_article']['palettes']['__selector__'][]           = 'textMiddle';
$GLOBALS['TL_DCA']['tl_article']['palettes']['__selector__'][]           = 'fullWidth';
$GLOBALS['TL_DCA']['tl_article']['palettes']['__selector__'][]           = 'addBackgroundVideo';
$GLOBALS['TL_DCA']['tl_article']['palettes']['__selector__'][]           = 'submenuSRC';



/**
 * Palettes
 */

Contao\CoreBundle\DataContainer\PaletteManipulator::create()
    ->addLegend('config_legend', 'layout_legend', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER)
    ->addLegend('design_legend', 'config_legend', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER)
    ->addLegend('navigation_legend', 'teaser_legend', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_BEFORE)


    ->addField('fullHeight', 'config_legend', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_PREPEND)
    ->addField('textMiddle', 'fullHeight', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER)
    ->addField('fullWidth', 'textMiddle', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER)

    ->addField('bgColor', 'design_legend', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_PREPEND)
    ->addField('bgImage', 'bgColor', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER)
    ->addField('bgPosition', 'bgImage', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER)
    ->addField('bgRepeat', 'bgPosition', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER)
    ->addField('gradientAngle', 'bgRepeat', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER)
    ->addField('gradientColors', 'gradientAngle', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER)
    ->addField('bgAttachment', 'gradientColors', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER)
    ->addField('bgSize', 'bgAttachment', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER)
    ->addField('addBackgroundVideo', 'bgSize', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER)

    ->addField('hideInMenu', 'navigation_legend', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_PREPEND)
//    ->addField('bgImage', 'hideInMenu', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER)


    ->addField('navTitle', 'title', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER)
    ->addField('navSubTitle', 'navTitle', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER)


    ->applyToPalette('default', 'tl_article');


if( $objParentPage && $objParentPage->submenuNoPages && $objParentPage->submenuSRC == "articles")
{
    Contao\CoreBundle\DataContainer\PaletteManipulator::create()

        ->addField('submenuSRC', 'hideInMenu', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER)

        ->applyToPalette('default', 'tl_article');
}

/**
 * Subpalettes
 */

$GLOBALS['TL_DCA']['tl_article']['subpalettes']['fullHeight']           = 'opticalHeight';
$GLOBALS['TL_DCA']['tl_article']['subpalettes']['textMiddle']           = 'textMiddleOptical';
$GLOBALS['TL_DCA']['tl_article']['subpalettes']['fullWidth']            = 'fullWidthInside';
$GLOBALS['TL_DCA']['tl_article']['subpalettes']['addBackgroundVideo']   = 'videoSRC,posterSRC';

$GLOBALS['TL_DCA']['tl_article']['subpalettes']['submenuSRC_news']      = 'submenuNewsArchive';



/**
 * Fields
 */

$GLOBALS['TL_DCA']['tl_article']['fields']['title']['eval']['tl_class'] = trim($GLOBALS['TL_DCA']['tl_article']['fields']['title']['eval']['tl_class'] . ' w50');


$GLOBALS['TL_DCA']['tl_article']['fields']['fullHeight'] = array
(
    'label'                 => &$GLOBALS['TL_LANG']['tl_article']['fullHeight'],
    'exclude'               => true,
    'inputType'             => 'checkbox',
    'eval'                  => array
    (
        'submitOnChange'        => TRUE,
        'tl_class'              => 'clr w50'
    ),
    'sql'                   => "char(1) NOT NULL default ''"
);


$GLOBALS['TL_DCA']['tl_article']['fields']['fullWidth']                 = $GLOBALS['TL_DCA']['tl_article']['fields']['fullHeight'];
$GLOBALS['TL_DCA']['tl_article']['fields']['fullWidth']['label']        = &$GLOBALS['TL_LANG']['tl_article']['fullWidth'];


$GLOBALS['TL_DCA']['tl_article']['fields']['fullWidthInside']           = $GLOBALS['TL_DCA']['tl_article']['fields']['fullWidth'];
$GLOBALS['TL_DCA']['tl_article']['fields']['fullWidthInside']['label']  = &$GLOBALS['TL_LANG']['tl_article']['fullWidthInside'];
$GLOBALS['TL_DCA']['tl_article']['fields']['fullWidthInside']['eval']['tl_class']       = "w50";
$GLOBALS['TL_DCA']['tl_article']['fields']['fullWidthInside']['eval']['submitOnChange'] = FALSE;



$GLOBALS['TL_DCA']['tl_article']['fields']['opticalHeight']             = $GLOBALS['TL_DCA']['tl_article']['fields']['fullWidthInside'];
$GLOBALS['TL_DCA']['tl_article']['fields']['opticalHeight']['label']    = &$GLOBALS['TL_LANG']['tl_article']['opticalHeight'];

$GLOBALS['TL_DCA']['tl_article']['fields']['textMiddle']                = $GLOBALS['TL_DCA']['tl_article']['fields']['fullWidth'];
$GLOBALS['TL_DCA']['tl_article']['fields']['textMiddle']['label']       = &$GLOBALS['TL_LANG']['tl_article']['textMiddle'];
$GLOBALS['TL_DCA']['tl_article']['fields']['textMiddle']['eval']['tl_class'] = "o50 w50";

$GLOBALS['TL_DCA']['tl_article']['fields']['textMiddleOptical']                = $GLOBALS['TL_DCA']['tl_article']['fields']['fullWidthInside'];
$GLOBALS['TL_DCA']['tl_article']['fields']['textMiddleOptical']['label']       = &$GLOBALS['TL_LANG']['tl_article']['textMiddleOptical'];
$GLOBALS['TL_DCA']['tl_article']['fields']['textMiddleOptical']['eval']['tl_class'] = "o50 w50";


$GLOBALS['TL_DCA']['tl_article']['fields']['bgColor'] = array
(
    'label'                 => &$GLOBALS['TL_LANG']['tl_article']['bgColor'],
    'inputType'             => 'text',
    'eval'                  => array
    (
        'maxlength'             => 6,
        'multiple'              => TRUE,
        'size'                  => 2,
        'colorpicker'           => TRUE,
        'isHexColor'            => TRUE,
        'decodeEntities'        => TRUE,
        'tl_class'              => 'w50 wizard'
    ),
    'sql'                   => "varchar(64) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_article']['fields']['bgImage'] = array
(
    'label'                 => &$GLOBALS['TL_LANG']['tl_article']['bgImage'],
    'inputType'             => 'text',
    'eval'                  => array
    (
        'filesOnly'             => TRUE,
        'extensions'            => Config::get('validImageTypes'),
        'fieldType'             => 'radio',
        'tl_class'              => 'w50 wizard'
    ),
    'wizard' => array
    (
        array('iido_basic.table.all', 'filePicker')
    ),
    'sql'                   => "varchar(255) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_article']['fields']['bgPosition'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_article']['bgPosition'],
    'inputType'               => 'select',
    'options'                 => array('left top', 'left center', 'left bottom', 'center top', 'center center', 'center bottom', 'right top', 'right center', 'right bottom'),
    'reference'               => $GLOBALS['TL_LANG']['tl_article']['reference']['bgPosition'],
    'eval'                    => array('includeBlankOption'=>true, 'tl_class'=>'w50'),
    'sql'                     => "varchar(32) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_article']['fields']['bgRepeat'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_article']['bgRepeat'],
    'inputType'               => 'select',
    'options'                 => array('repeat', 'repeat-x', 'repeat-y', 'no-repeat'),
    'reference'               => $GLOBALS['TL_LANG']['tl_article']['reference']['bgRepeat'],
    'eval'                    => array('includeBlankOption'=>true, 'tl_class'=>'w50'),
    'sql'                     => "varchar(32) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_article']['fields']['gradientAngle'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_article']['gradientAngle'],
    'inputType'               => 'text',
    'eval'                    => array('maxlength'=>32, 'tl_class'=>'w50'),
    'sql'                     => "varchar(32) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_article']['fields']['gradientColors'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_article']['gradientColors'],
    'inputType'               => 'text',
    'eval'                    => array('multiple'=>true, 'size'=>4, 'decodeEntities'=>true, 'tl_class'=>'w50'),
    'sql'                     => "varchar(128) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_article']['fields']['bgAttachment']              = $GLOBALS['TL_DCA']['tl_article']['fields']['bgRepeat'];
$GLOBALS['TL_DCA']['tl_article']['fields']['bgAttachment']['label']     = &$GLOBALS['TL_LANG']['tl_article']['bgAttachment'];
$GLOBALS['TL_DCA']['tl_article']['fields']['bgAttachment']['options']   = $GLOBALS['TL_LANG']['tl_article']['options']['bgAttachment'];

$GLOBALS['TL_DCA']['tl_article']['fields']['bgSize'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_article']['bgSize'],
    'inputType'               => 'imageSize',
    'options'                 => &$GLOBALS['TL_LANG']['tl_article']['options']['bgSize'],
    'eval'                    => array
    (
        'rgxp'=>'natural',
        'includeBlankOption'=>true,
        'nospace'=>true,
        'helpwizard'=>true,
        'tl_class'=>'w50 bg-size'
    ),
    'sql'                     => "varchar(64) NOT NULL default ''"
);


$submenuSRC = $GLOBALS['TL_LANG']['tl_page']['options']['submenuSRC'];
unset( $submenuSRC['articles'] );

$GLOBALS['TL_DCA']['tl_article']['fields']['submenuSRC']            = $GLOBALS['TL_DCA']['tl_page']['fields']['submenuSRC'];
$GLOBALS['TL_DCA']['tl_article']['fields']['submenuSRC']['options'] = $submenuSRC;
$GLOBALS['TL_DCA']['tl_article']['fields']['submenuSRC']['eval']['includeBlankOption'] = TRUE;

$GLOBALS['TL_DCA']['tl_article']['fields']['submenuNewsArchive']    = $GLOBALS['TL_DCA']['tl_page']['fields']['submenuNewsArchive'];


$GLOBALS['TL_DCA']['tl_article']['fields']['videoSRC'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_article']['videoSRC'],
    'exclude'                 => true,
    'inputType'               => 'fileTree',
    'eval'                    => array('multiple'=>true, 'fieldType'=>'checkbox', 'files'=>true, 'mandatory'=>true, 'tl_class'=>'clr w50 hauto'),
    'sql'                     => "blob NULL"
);

$GLOBALS['TL_DCA']['tl_article']['fields']['posterSRC']             = $GLOBALS['TL_DCA']['tl_page']['fields']['overviewImage'];
$GLOBALS['TL_DCA']['tl_article']['fields']['posterSRC']['label']    = &$GLOBALS['TL_LANG']['tl_article']['posterSRC'];
$GLOBALS['TL_DCA']['tl_article']['fields']['posterSRC']['eval']['tl_class'] = 'w50 hauto';


$GLOBALS['TL_DCA']['tl_article']['fields']['navTitle'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_article']['navTitle'],
    'exclude'                 => true,
    'inputType'               => 'text',
    'eval'                    => array
    (
        'maxlength'=>255,
        'decodeEntities'=>true,
        'tl_class'=>'w50',
        'doNotCopy'=>true
    ),
    'sql'                     => "varchar(255) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_article']['fields']['navSubTitle']           = $GLOBALS['TL_DCA']['tl_article']['fields']['navTitle'];
$GLOBALS['TL_DCA']['tl_article']['fields']['navSubTitle']['label']  = &$GLOBALS['TL_LANG']['tl_article']['navSubTitle'];

$GLOBALS['TL_DCA']['tl_article']['fields']['hideInMenu']            = $GLOBALS['TL_DCA']['tl_article']['fields']['fullHeight'];
$GLOBALS['TL_DCA']['tl_article']['fields']['hideInMenu']['label']   = &$GLOBALS['TL_LANG']['tl_article']['hideInMenu'];
$GLOBALS['TL_DCA']['tl_article']['fields']['hideInMenu']['eval']['submitOnChange'] = FALSE;

$GLOBALS['TL_DCA']['tl_article']['fields']['addBackgroundVideo']            = $GLOBALS['TL_DCA']['tl_article']['fields']['fullHeight'];
$GLOBALS['TL_DCA']['tl_article']['fields']['addBackgroundVideo']['label']   = &$GLOBALS['TL_LANG']['tl_article']['addBackgroundVideo'];