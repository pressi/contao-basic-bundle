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
\Controller::loadDataContainer("tl_content");

$objArticle         = FALSE;
$objParentPage      = FALSE;

$act                = \Input::get("act");
$id                 = \Input::get("id");
$table              = \Input::get("table");
$strTable           = \ArticleModel::getTable();

if( $act == "edit" )
{
    $objArticle = \ArticleModel::findByPk( $id );
}
else
{
    if( $table === "tl_content" && $id )
    {
        $objArticle = \ArticleModel::findByPk( $id );

        if( $objArticle->noContent )
        {
            \Backend::redirect('/contao?do=article&ref=' . TL_REFERER_ID);
        }
    }
}


if( $objArticle )
{
    $objParentPage = \PageModel::findByPk( $objArticle->pid );
}



/**
 * Buttons
 */

$GLOBALS['TL_DCA']['tl_article']['list']['operations']['edit']['button_callback']   = array('IIDO\BasicBundle\Table\ArticleTable', 'editArticle');



/**
 * Palettes - Selectors
 */

//$GLOBALS['TL_DCA']['tl_article']['palettes']['__selector__'][]           = 'fullHeight';
//$GLOBALS['TL_DCA']['tl_article']['palettes']['__selector__'][]           = 'textMiddle';
//$GLOBALS['TL_DCA']['tl_article']['palettes']['__selector__'][]           = 'fullWidth';
//$GLOBALS['TL_DCA']['tl_article']['palettes']['__selector__'][]           = 'addBackgroundVideo';
//$GLOBALS['TL_DCA']['tl_article']['palettes']['__selector__'][]           = 'submenuSRC';
//$GLOBALS['TL_DCA']['tl_article']['palettes']['__selector__'][]           = 'addAnimation';



/**
 * Palettes
 */

Contao\CoreBundle\DataContainer\PaletteManipulator::create()
    ->addLegend('config_legend', 'layout_legend', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER)
    ->addLegend('design_legend', 'config_legend', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER)
    ->addLegend('navigation_legend', 'teaser_legend', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_BEFORE)
    ->addLegend('animation_legend', 'expert_legend', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER)


    ->addField('fullHeight', 'config_legend', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_PREPEND)
//    ->addField('textMiddle', 'fullHeight', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER)
//    ->addField('fullWidth', 'textMiddle', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER)
    ->addField('fullWidth', 'fullHeight', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER)

    ->addField('bgColor', 'design_legend', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_PREPEND)
    ->addField('bgImage', 'bgColor', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER)
    ->addField('bgPosition', 'bgImage', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER)
    ->addField('bgRepeat', 'bgPosition', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER)
    ->addField('gradientAngle', 'bgRepeat', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER)
    ->addField('gradientColors', 'gradientAngle', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER)
    ->addField('bgAttachment', 'gradientColors', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER)
    ->addField('bgSize', 'bgAttachment', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER)
    ->addField('addBackgroundOverlay', 'bgSize', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER)
    ->addField('addBackgroundVideo', 'addBackgroundOverlay', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER)

    ->addField('hideInMenu', 'navigation_legend', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_PREPEND)
    ->addField('overviewImage', 'hideInMenu', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER)

    ->addField('navTitle', 'title', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER)
    ->addField('navSubTitle', 'navTitle', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER)

    ->addField('teaserHeadline', 'teaser', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_BEFORE)
    ->addField('teaserMultiSRC', 'teaser', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER)

    ->addField('hiddenArea', 'guests', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER)
    ->addField('noContent', 'hiddenArea', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER)

    ->addField('addAnimation', 'animation_legend', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_PREPEND)


    ->applyToPalette('default', $strTable);


if( $objParentPage && $objParentPage->submenuNoPages && $objParentPage->submenuSRC == "articles")
{
    Contao\CoreBundle\DataContainer\PaletteManipulator::create()
        ->addField('submenuSRC', 'hideInMenu', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER)

        ->applyToPalette('default', $strTable);
}


if( $objParentPage && $objParentPage->enableFullpage )
{
    Contao\CoreBundle\DataContainer\PaletteManipulator::create()
        ->addField('toNextArrow', 'noContent', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER)

        ->applyToPalette('default', $strTable);
}



/**
 * Subpalettes
 */

\IIDO\BasicBundle\Helper\DcaHelper::addSubpalette("fullHeight", "opticalHeight,textMiddle", $strTable);
\IIDO\BasicBundle\Helper\DcaHelper::addSubpalette("textMiddle", "textMiddleOptical", $strTable);
\IIDO\BasicBundle\Helper\DcaHelper::addSubpalette("fullWidth", "fullWidthInside", $strTable);
\IIDO\BasicBundle\Helper\DcaHelper::addSubpalette("addBackgroundVideo", "videoSRC,posterSRC", $strTable);
\IIDO\BasicBundle\Helper\DcaHelper::addSubpalette("submenuSRC_news", "submenuNewsArchive", $strTable);
\IIDO\BasicBundle\Helper\DcaHelper::addSubpalette("addAnimation", "animationType,animateRun,animationWait,animationOffset", $strTable);



/**
 * Fields
 */

$GLOBALS['TL_DCA']['tl_article']['fields']['title']['eval']['tl_class'] = trim($GLOBALS['TL_DCA']['tl_article']['fields']['title']['eval']['tl_class'] . ' w50');


\IIDO\BasicBundle\Helper\DcaHelper::addField("fullHeight", "checkbox_selector", $strTable, array(), "clr no-clr-after");
\IIDO\BasicBundle\Helper\DcaHelper::addField("fullWidth", "checkbox_selector", $strTable, array(), "clr no-clr-after");
\IIDO\BasicBundle\Helper\DcaHelper::addField("fullWidthInside", "checkbox", $strTable);
\IIDO\BasicBundle\Helper\DcaHelper::addField("opticalHeight", "checkbox", $strTable);
\IIDO\BasicBundle\Helper\DcaHelper::addField("textMiddle", "checkbox_selector", $strTable, array(), "clr no-clr-after");
\IIDO\BasicBundle\Helper\DcaHelper::addField("textMiddleOptical", "checkbox", $strTable);



//$GLOBALS['TL_DCA']['tl_article']['fields']['bgColor'] = array
//(
//    'label'                 => &$GLOBALS['TL_LANG']['tl_article']['bgColor'],
//    'inputType'             => 'text',
//    'eval'                  => array
//    (
//        'maxlength'             => 6,
//        'multiple'              => TRUE,
//        'size'                  => 2,
//        'colorpicker'           => TRUE,
//        'isHexColor'            => TRUE,
//        'decodeEntities'        => TRUE,
//        'tl_class'              => 'w50 wizard'
//    ),
//    'sql'                   => "varchar(64) NOT NULL default ''"
//);

\IIDO\BasicBundle\Helper\DcaHelper::addField("bgColor", "color", $strTable, array(), 'no-clr-after-clr');

//$GLOBALS['TL_DCA']['tl_article']['fields']['bgImage'] = array
//(
//    'label'                 => &$GLOBALS['TL_LANG']['tl_article']['bgImage'],
//    'inputType'             => 'text',
//    'eval'                  => array
//    (
//        'filesOnly'             => TRUE,
//        'extensions'            => Config::get('validImageTypes'),
//        'fieldType'             => 'radio',
//        'tl_class'              => 'w50 wizard'
//    ),
//    'wizard' => array
//    (
//        array('iido_basic.table.all', 'filePicker')
//    ),
//    'sql'                   => "varchar(255) NOT NULL default ''"
//);

//$GLOBALS['TL_DCA']['tl_article']['fields']['bgPosition'] = array
//(
//    'label'                   => &$GLOBALS['TL_LANG']['tl_article']['bgPosition'],
//    'inputType'               => 'select',
//    'options'                 => array('left top', 'left center', 'left bottom', 'center top', 'center center', 'center bottom', 'right top', 'right center', 'right bottom'),
//    'reference'               => $GLOBALS['TL_LANG']['tl_article']['reference']['bgPosition'],
//    'eval'                    => array(, 'tl_class'=>'clr w50'),
//    'sql'                     => "varchar(32) NOT NULL default ''"
//);
\IIDO\BasicBundle\Helper\DcaHelper::addField("bgPosition", "select", $strTable, array('includeBlankOption'=>true), "clr");

//$GLOBALS['TL_DCA']['tl_article']['fields']['bgRepeat'] = array
//(
//    'label'                   => &$GLOBALS['TL_LANG']['tl_article']['bgRepeat'],
//    'inputType'               => 'select',
//    'options'                 => array('repeat', 'repeat-x', 'repeat-y', 'no-repeat'),
//    'reference'               => $GLOBALS['TL_LANG']['tl_article']['reference']['bgRepeat'],
//    'eval'                    => array('includeBlankOption'=>true, 'tl_class'=>'w50'),
//    'sql'                     => "varchar(32) NOT NULL default ''"
//);
\IIDO\BasicBundle\Helper\DcaHelper::addField("bgRepeat", "select", $strTable, array('includeBlankOption'=>true));

//$GLOBALS['TL_DCA']['tl_article']['fields']['gradientAngle'] = array
//(
//    'label'                   => &$GLOBALS['TL_LANG']['tl_article']['gradientAngle'],
//    'inputType'               => 'text',
//    'eval'                    => array('maxlength'=>32, 'tl_class'=>'w50'),
//    'sql'                     => "varchar(32) NOT NULL default ''"
//);
\IIDO\BasicBundle\Helper\DcaHelper::addField("gradientAngle", "text", $strTable, array('maxlength'=>32));

//$GLOBALS['TL_DCA']['tl_article']['fields']['gradientColors'] = array
//(
//    'label'                   => &$GLOBALS['TL_LANG']['tl_article']['gradientColors'],
//    'inputType'               => 'text',
//    'eval'                    => array('multiple'=>true, 'size'=>4, 'decodeEntities'=>true, 'tl_class'=>'w50'),
//    'sql'                     => "varchar(128) NOT NULL default ''"
//);
\IIDO\BasicBundle\Helper\DcaHelper::addField("gradientColors", "text", $strTable, array('maxlength'=>128,'multiple'=>true,'size'=>4,'decodeEntities'=>true));

//$GLOBALS['TL_DCA']['tl_article']['fields']['bgAttachment']              = $GLOBALS['TL_DCA']['tl_article']['fields']['bgRepeat'];
//$GLOBALS['TL_DCA']['tl_article']['fields']['bgAttachment']['label']     = &$GLOBALS['TL_LANG']['tl_article']['bgAttachment'];
//$GLOBALS['TL_DCA']['tl_article']['fields']['bgAttachment']['options']   = $GLOBALS['TL_LANG']['tl_article']['options']['bgAttachment'];
\IIDO\BasicBundle\Helper\DcaHelper::addField("bgAttachment", "select", $strTable, array('includeBlankOption'=>true));

//$GLOBALS['TL_DCA']['tl_article']['fields']['bgSize'] = array
//(
//    'label'                   => &$GLOBALS['TL_LANG']['tl_article']['bgSize'],
//    'inputType'               => 'imageSize',
//    'options'                 => &$GLOBALS['TL_LANG']['tl_article']['options']['bgSize'],
//    'eval'                    => array
//    (
//        'rgxp'=>'natural',
//        'includeBlankOption'=>true,
//        'nospace'=>true,
//        'helpwizard'=>true,
//        'tl_class'=>'w50 bg-size'
//    ),
//    'sql'                     => "varchar(64) NOT NULL default ''"
//);
\IIDO\BasicBundle\Helper\DcaHelper::addField("bgSize", "imagesize", $strTable);


//$submenuSRC = $GLOBALS['TL_LANG']['tl_page']['options']['submenuSRC'];
//unset( $submenuSRC['articles'] );

//$GLOBALS['TL_DCA']['tl_article']['fields']['submenuSRC']            = $GLOBALS['TL_DCA']['tl_page']['fields']['submenuSRC'];
//$GLOBALS['TL_DCA']['tl_article']['fields']['submenuSRC']['options'] = $submenuSRC;
//$GLOBALS['TL_DCA']['tl_article']['fields']['submenuSRC']['eval']['includeBlankOption'] = TRUE;
\IIDO\BasicBundle\Helper\DcaHelper::addField("submenuSRC_page", "select_selector", $strTable, array('includeBlankOption'=>true));

//$GLOBALS['TL_DCA']['tl_article']['fields']['submenuNewsArchive']    = $GLOBALS['TL_DCA']['tl_page']['fields']['submenuNewsArchive'];
//\IIDO\BasicBundle\Helper\DcaHelper::addField("submenuNewsArchive_page", "select", $strTable, array('includeBlankOption'=>true));
\IIDO\BasicBundle\Helper\DcaHelper::copyFieldFromTable('submenuNewsArchive', $strTable, 'submenuNewsArchive', 'page');


$GLOBALS['TL_DCA']['tl_article']['fields']['videoSRC'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_article']['videoSRC'],
    'exclude'                 => true,
    'inputType'               => 'fileTree',
    'eval'                    => array('multiple'=>true, 'fieldType'=>'checkbox', 'files'=>true, 'mandatory'=>true, 'tl_class'=>'clr w50 hauto'),
    'sql'                     => "blob NULL"
);

//$GLOBALS['TL_DCA']['tl_article']['fields']['posterSRC']             = $GLOBALS['TL_DCA']['tl_page']['fields']['overviewImage'];
//$GLOBALS['TL_DCA']['tl_article']['fields']['posterSRC']['label']    = &$GLOBALS['TL_LANG']['tl_article']['posterSRC'];
//$GLOBALS['TL_DCA']['tl_article']['fields']['posterSRC']['eval']['tl_class'] = 'w50 hauto';
\IIDO\BasicBundle\Helper\DcaHelper::addField("posterSRC", "imagefield", $strTable);


//$GLOBALS['TL_DCA']['tl_article']['fields']['navTitle'] = array
//(
//    'label'                   => &$GLOBALS['TL_LANG']['tl_article']['navTitle'],
//    'exclude'                 => true,
//    'inputType'               => 'text',
//    'eval'                    => array
//    (
//        'maxlength'=>255,
//        'decodeEntities'=>true,
//        'tl_class'=>'w50',
//
//    ),
//    'sql'                     => "varchar(255) NOT NULL default ''"
//);
\IIDO\BasicBundle\Helper\DcaHelper::addField("navTitle", "text", $strTable, array('doNotCopy'=>true));

//$GLOBALS['TL_DCA']['tl_article']['fields']['navSubTitle']                   = $GLOBALS['TL_DCA']['tl_article']['fields']['navTitle'];
//$GLOBALS['TL_DCA']['tl_article']['fields']['navSubTitle']['label']          = &$GLOBALS['TL_LANG']['tl_article']['navSubTitle'];
\IIDO\BasicBundle\Helper\DcaHelper::copyField("navSubTitle", $strTable, 'navTitle');

//$GLOBALS['TL_DCA']['tl_article']['fields']['hideInMenu']                    = $GLOBALS['TL_DCA']['tl_article']['fields']['fullHeight'];
//$GLOBALS['TL_DCA']['tl_article']['fields']['hideInMenu']['label']           = &$GLOBALS['TL_LANG']['tl_article']['hideInMenu'];
//$GLOBALS['TL_DCA']['tl_article']['fields']['hideInMenu']['eval']['submitOnChange'] = FALSE;
\IIDO\BasicBundle\Helper\DcaHelper::addField("hideInMenu", "checkbox", $strTable, array(), "clr no-clr-after");

//$GLOBALS['TL_DCA']['tl_article']['fields']['addBackgroundVideo']            = $GLOBALS['TL_DCA']['tl_article']['fields']['fullHeight'];
//$GLOBALS['TL_DCA']['tl_article']['fields']['addBackgroundVideo']['label']   = &$GLOBALS['TL_LANG']['tl_article']['addBackgroundVideo'];
\IIDO\BasicBundle\Helper\DcaHelper::copyField("addBackgroundVideo", $strTable, 'fullHeight');

//$GLOBALS['TL_DCA']['tl_article']['fields']['addBackgroundOverlay']          = $GLOBALS['TL_DCA']['tl_article']['fields']['addBackgroundVideo'];
//$GLOBALS['TL_DCA']['tl_article']['fields']['addBackgroundOverlay']['label'] = &$GLOBALS['TL_LANG']['tl_article']['addBackgroundOverlay'];
//$GLOBALS['TL_DCA']['tl_article']['fields']['addBackgroundOverlay']['eval']['submitOnChange'] = FALSE;
\IIDO\BasicBundle\Helper\DcaHelper::copyField("addBackgroundOverlay", $strTable, 'hideInMenu');




//$GLOBALS['TL_DCA']['tl_article']['fields']['overviewImage']                 = $GLOBALS['TL_DCA']['tl_content']['fields']['singleSRC'];
//$GLOBALS['TL_DCA']['tl_article']['fields']['overviewImage']['label']        = &$GLOBALS['TL_LANG']['tl_page']['overviewImage'];
//$GLOBALS['TL_DCA']['tl_article']['fields']['overviewImage']['eval']['mandatory']   = FALSE;
//$GLOBALS['TL_DCA']['tl_article']['fields']['overviewImage']['eval']['tl_class']    = 'w50 hauto';
//$GLOBALS['TL_DCA']['tl_article']['fields']['overviewImage']['load_callback']       = array();
//$GLOBALS['TL_DCA']['tl_article']['fields']['overviewImage']['save_callback']       = array();
\IIDO\BasicBundle\Helper\DcaHelper::addField("overviewImage", "imagefield", $strTable);


//$GLOBALS['TL_DCA']['tl_article']['fields']['bgImage']                       = $GLOBALS['TL_DCA']['tl_article']['fields']['overviewImage'];
//$GLOBALS['TL_DCA']['tl_article']['fields']['bgImage']['label']              = &$GLOBALS['TL_LANG']['tl_article']['bgImage'];
\IIDO\BasicBundle\Helper\DcaHelper::addField("bgImage", "imagefield", $strTable);



//$GLOBALS['TL_DCA']['tl_article']['fields']['teaserHeadline']                = $GLOBALS['TL_DCA']['tl_content']['fields']['headline'];
//$GLOBALS['TL_DCA']['tl_article']['fields']['teaserHeadline']['label']       = &$GLOBALS['TL_LANG']['tl_article']['teaserHeadline'];
\IIDO\BasicBundle\Helper\DcaHelper::copyFieldFromTable('teaserHeadline', $strTable, 'headline', 'content');


//$GLOBALS['TL_DCA']['tl_article']['fields']['teaserMultiSRC']                = $GLOBALS['TL_DCA']['tl_content']['fields']['multiSRC'];
//$GLOBALS['TL_DCA']['tl_article']['fields']['teaserMultiSRC']['label']       = &$GLOBALS['TL_LANG']['tl_article']['teaserMultiSRC'];
//$GLOBALS['TL_DCA']['tl_article']['fields']['teaserMultiSRC']['eval']['mandatory']   = FALSE;
//$GLOBALS['TL_DCA']['tl_article']['fields']['teaserMultiSRC']['eval']['isGallery']   = TRUE;
//$GLOBALS['TL_DCA']['tl_article']['fields']['teaserMultiSRC']['eval']['extensions']  = Config::get('validImageTypes');
\IIDO\BasicBundle\Helper\DcaHelper::addField('teaserMultiSRC', "multisrc", $strTable);


//$GLOBALS['TL_DCA']['tl_article']['fields']['orderSRC']                      = $GLOBALS['TL_DCA']['tl_content']['fields']['orderSRC'];
\IIDO\BasicBundle\Helper\DcaHelper::copyFieldFromTable('orderSRC', $strTable, 'orderSRC', 'content');


//$GLOBALS['TL_DCA']['tl_article']['fields']['hiddenArea']                    = $GLOBALS['TL_DCA']['tl_article']['fields']['addBackgroundOverlay'];
//$GLOBALS['TL_DCA']['tl_article']['fields']['hiddenArea']['label']           = &$GLOBALS['TL_LANG']['tl_article']['hiddenArea'];
//$GLOBALS['TL_DCA']['tl_article']['fields']['hiddenArea']['eval']['tl_class'] = 'w50 m12';
\IIDO\BasicBundle\Helper\DcaHelper::addField("hiddenArea", "checkbox", $strTable);

//$GLOBALS['TL_DCA']['tl_article']['fields']['noContent']                    = $GLOBALS['TL_DCA']['tl_article']['fields']['hiddenArea'];
//$GLOBALS['TL_DCA']['tl_article']['fields']['noContent']['label']           = &$GLOBALS['TL_LANG']['tl_article']['noContent'];
\IIDO\BasicBundle\Helper\DcaHelper::addField("noContent", "checkbox", $strTable);

//$GLOBALS['TL_DCA']['tl_article']['fields']['toNextArrow']                  = $GLOBALS['TL_DCA']['tl_article']['fields']['hiddenArea'];
//$GLOBALS['TL_DCA']['tl_article']['fields']['toNextArrow']['label']         = &$GLOBALS['TL_LANG']['tl_article']['toNextArrow'];
\IIDO\BasicBundle\Helper\DcaHelper::addField("toNextArrow", "checkbox", $strTable);

\IIDO\BasicBundle\Helper\DcaHelper::addField("enableSticky", "checkbox", $strTable);


\IIDO\BasicBundle\Helper\DcaHelper::addField("addAnimation_content", "checkbox_selector", $strTable);
\IIDO\BasicBundle\Helper\DcaHelper::addField("animationType_content", "select_short", $strTable, array('includeBlankOption'=>true));
\IIDO\BasicBundle\Helper\DcaHelper::addField("animationOffset_content", "text", $strTable);
\IIDO\BasicBundle\Helper\DcaHelper::addField("animationWait_content", "checkbox", $strTable);
\IIDO\BasicBundle\Helper\DcaHelper::addField("animateRun_content", "select", $strTable);

//echo "<pre>"; print_r( $GLOBALS['TL_DCA']['tl_article'] ); exit;