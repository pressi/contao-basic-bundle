<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

//\Controller::loadLanguageFile("tl_page");

//\Controller::loadDataContainer("tl_page");
//\Controller::loadDataContainer("tl_content");

$objArticle         = FALSE;
$objParentPage      = FALSE;

$db                 = \Database::getInstance();
$do                 = \Input::get("do");
$act                = \Input::get("act");
$id                 = \Input::get("id");
$table              = \Input::get("table");
$articleCounter     = 1;

$strFileName        = \IIDO\BasicBundle\Config\BundleConfig::getTableName( __FILE__ );
$strFileClass       = \IIDO\BasicBundle\Config\BundleConfig::getTableClass( $strFileName );


if( $act === "edit" )
{
    $objArticle = $db->prepare("SELECT * FROM $strFileName WHERE id=?")->limit(1)->execute( $id ); //\ArticleModel::findByPk( $id );
}
else
{
    if( $table === "tl_content" && $id && \Input::get("do") === "article" )
    {
        $objArticle = $db->prepare("SELECT * FROM $strFileName WHERE id=?")->limit(1)->execute( $id ); //\ArticleModel::findByPk( $id );

        if( $objArticle->noContent )
        {
            \Backend::redirect('/contao?do=article&ref=' . TL_REFERER_ID);
        }
    }
}


if( $objArticle )
{
    $objParentPage = $db->prepare("SELECT * FROM tl_page WHERE id=?")->limit(1)->execute( $objArticle->pid );

    if( $objParentPage )
    {
        $objArticles    = $db->prepare("SELECT id FROM $strFileName WHERE pid=?")->execute( $objParentPage->id );
        $articleCounter = $objArticles->count();
    }
}



/**
 * Label
 */

$GLOBALS['TL_DCA'][ $strFileName ]['list']['label']['label_callback']     = array($strFileClass, 'addIcon');



/**
 * Buttons
 */

$GLOBALS['TL_DCA'][ $strFileName ]['list']['operations']['edit']['button_callback']   = array($strFileClass, 'editArticle');



/**
 * Palettes
 */

Contao\CoreBundle\DataContainer\PaletteManipulator::create()
    ->addLegend('dimensions_legend', 'layout_legend', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER)
    ->addLegend('design_legend', 'dimensions_legend', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER)
    ->addLegend('navigation_legend', 'design_legend', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_BEFORE)
    ->addLegend('animation_legend', 'expert_legend', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER, true)
    ->addLegend('divider_legend', 'animation_legend', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER, true)
    ->addLegend('inside_legend', 'design_legend', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER)



    ->addField(array('fullHeight', 'fullWidth'), 'dimensions_legend', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_PREPEND)
    ->addField(array('bgColor', 'gradientAngle', 'gradientColors', 'addBackgroundImage', 'addBackgroundOverlay', 'addBackgroundVideo'), 'design_legend', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_PREPEND)
    ->addField(array('hideInMenu', 'overviewImage', 'navLinkMode'), 'navigation_legend', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_PREPEND)

    ->addField(array('articleType', 'navTitle', 'navSubTitle'), 'title', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER)

    ->addField('teaserHeadline', 'teaser', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_BEFORE)
    ->addField('teaserMultiSRC', 'teaser', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER)

    ->addField(array('hiddenArea', 'noContent'), 'guests', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER)

    ->addField('addAnimation', 'animation_legend', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_PREPEND)

    ->addField('addDivider', 'divider_legend', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_PREPEND)

    ->addField(array('padding'), 'inside_legend', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_PREPEND)


    ->applyToPalette('default', $strFileName);


if( $objParentPage && $objParentPage->submenuNoPages && $objParentPage->submenuSRC == "articles")
{
    Contao\CoreBundle\DataContainer\PaletteManipulator::create()
        ->addField('submenuSRC', 'hideInMenu', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER)

        ->applyToPalette('default', $strFileName);
}


if( $objParentPage && ($objParentPage->enableFullpage || $articleCounter > 1) )
{
    Contao\CoreBundle\DataContainer\PaletteManipulator::create()
        ->addField(array('toNextArrow'), 'noContent', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER)

        ->applyToPalette('default', $strFileName);
}


if($do === "article" && $objArticle && ($objArticle->articleType === "header" || $objArticle->articleType === "headerTopBar" || $objArticle->articleType === "footer") )
{
    $arrRemove = array
    (
        "navTitle", "navSubTitle",
        "keywords",
        "guests", "hiddenArea", "noContent",
        "start", "stop"
    );

    $arrRemoveLegends = array
    (
        'dimensions', 'navigation', 'teaser', 'syndication',
        'protected', 'animation', 'divider'
    );

    \IIDO\BasicBundle\Helper\DcaHelper::removeField($arrRemove, $strFileName);
    \IIDO\BasicBundle\Helper\DcaHelper::removeLegend($arrRemoveLegends, $strFileName);

    Contao\CoreBundle\DataContainer\PaletteManipulator::create()
        ->addLegend('config_legend', 'layout_legend', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER)

        ->addField(array('isFixed', 'enableSticky'), 'config_legend', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_PREPEND)

        ->applyToPalette('default', $strFileName);
}



/**
 * Subpalettes
 */

\IIDO\BasicBundle\Helper\DcaHelper::addSubpalette("fullHeight", "opticalHeight,textMiddle", $strFileName);
\IIDO\BasicBundle\Helper\DcaHelper::addSubpalette("textMiddle", "textMiddleOptical", $strFileName);
\IIDO\BasicBundle\Helper\DcaHelper::addSubpalette("fullWidth", "fullWidthInside", $strFileName);
\IIDO\BasicBundle\Helper\DcaHelper::addSubpalette("addBackgroundImage", array('bgImage', 'bgPosition', 'bgRepeat', 'bgAttachment', 'bgSize', 'enableBackgroundParallax'), $strFileName);
\IIDO\BasicBundle\Helper\DcaHelper::addSubpalette("addBackgroundVideo", "videoSRC,posterSRC", $strFileName);
\IIDO\BasicBundle\Helper\DcaHelper::addSubpalette("submenuSRC_news", "submenuNewsArchive", $strFileName);
\IIDO\BasicBundle\Helper\DcaHelper::addSubpalette("isFixed", "position,positionMargin,isAbsolute,articleWidth,articleHeight", $strFileName);
\IIDO\BasicBundle\Helper\DcaHelper::addSubpalette("navLinkMode_intern", "navLinkPage", $strFileName);
\IIDO\BasicBundle\Helper\DcaHelper::addSubpalette("navLinkMode_extern", "navLinkUrl,navLinkNewWindow", $strFileName);
\IIDO\BasicBundle\Helper\DcaHelper::addSubpalette("addAnimation", "animationType,animateRun,animationWait,animationOffset", $strFileName);
\IIDO\BasicBundle\Helper\DcaHelper::addSubpalette("addDivider", "dividerStyle", $strFileName);

\IIDO\BasicBundle\Helper\DcaHelper::addSubpalette("toNextArrow", "toNextArrowStyle,toNextArrowColor,toNextArrowHoverColor,toNextArrowAddTitle,toNextArrowFixed,toNextArrowPosition,toNextArrowPositionMargin", $strFileName);



/**
 * Fields
 */

$GLOBALS['TL_DCA'][ $strFileName ]['fields']['title']['eval']['tl_class'] = trim($GLOBALS['TL_DCA'][ $strFileName ]['fields']['title']['eval']['tl_class'] . ' w50');


// Title Legend
\IIDO\BasicBundle\Helper\DcaHelper::addSelectField("articleType", $strFileName, array(), '', false, '', false, true);
\IIDO\BasicBundle\Helper\DcaHelper::addTextField("navTitle", $strFileName, array('doNotCopy'=>true));
\IIDO\BasicBundle\Helper\DcaHelper::copyField("navSubTitle", $strFileName, 'navTitle');



// Dimensions Legend
\IIDO\BasicBundle\Helper\DcaHelper::addCheckboxField("fullHeight", $strFileName, array(), 'clr no-clr-after', false, true);
\IIDO\BasicBundle\Helper\DcaHelper::addCheckboxField("fullWidth", $strFileName, array(), 'clr no-clr-after', false, true);
\IIDO\BasicBundle\Helper\DcaHelper::addCheckboxField("fullWidthInside", $strFileName);
\IIDO\BasicBundle\Helper\DcaHelper::addCheckboxField("opticalHeight", $strFileName);
\IIDO\BasicBundle\Helper\DcaHelper::addCheckboxField("textMiddle", $strFileName, array(), 'clr no-clr-after', false, true);
\IIDO\BasicBundle\Helper\DcaHelper::addCheckboxField("textMiddleOptical", $strFileName);



// Design Legend
//\IIDO\BasicBundle\Helper\DcaHelper::copyField("addBackgroundImage", $strFileName, 'fullHeight');
\IIDO\BasicBundle\Helper\DcaHelper::addCheckboxField("addBackgroundImage", $strFileName, array(), 'clr no-clr-after', false, true);

\IIDO\BasicBundle\Helper\DcaHelper::addImageField("bgImage", $strFileName, array('mandatory'=>true));
\IIDO\BasicBundle\Helper\DcaHelper::addColorField("bgColor", $strFileName, array(), 'no-clr-after-clr');
\IIDO\BasicBundle\Helper\DcaHelper::addSelectField("bgPosition", $strFileName, array('includeBlankOption'=>true), 'clr');
\IIDO\BasicBundle\Helper\DcaHelper::addSelectField("bgRepeat", $strFileName, array('includeBlankOption'=>true));
\IIDO\BasicBundle\Helper\DcaHelper::addTextField("gradientAngle", $strFileName, array('maxlength'=>32));
\IIDO\BasicBundle\Helper\DcaHelper::addTextField("gradientColors", $strFileName, array('maxlength'=>128,'multiple'=>true,'size'=>4,'decodeEntities'=>true));
\IIDO\BasicBundle\Helper\DcaHelper::addSelectField("bgAttachment", $strFileName, array('includeBlankOption'=>true));
\IIDO\BasicBundle\Helper\DcaHelper::addImageSizeField("bgSize", $strFileName);

\IIDO\BasicBundle\Helper\DcaHelper::addCheckboxField("enableBackgroundParallax", $strFileName, array(), 'clr');


//\IIDO\BasicBundle\Helper\DcaHelper::copyField("addBackgroundVideo", $strFileName, 'fullHeight');
//\IIDO\BasicBundle\Helper\DcaHelper::copyField("addBackgroundOverlay", $strFileName, 'fullHeight');
\IIDO\BasicBundle\Helper\DcaHelper::addCheckboxField("addBackgroundVideo", $strFileName, array(), 'clr no-clr-after', false, true);
\IIDO\BasicBundle\Helper\DcaHelper::addCheckboxField("addBackgroundOverlay", $strFileName, array(), 'clr no-clr-after', false, true);


$GLOBALS['TL_DCA'][ $strFileName ]['fields']['videoSRC'] = array
(
    'label'                   => &$GLOBALS['TL_LANG'][ $strFileName ]['videoSRC'],
    'exclude'                 => true,
    'inputType'               => 'fileTree',
    'eval'                    => array('multiple'=>true, 'fieldType'=>'checkbox', 'files'=>true, 'mandatory'=>true, 'tl_class'=>'clr w50 hauto'),
    'sql'                     => "blob NULL"
);

\IIDO\BasicBundle\Helper\DcaHelper::addImageField("posterSRC", $strFileName);



// Navigation Legend
\IIDO\BasicBundle\Helper\DcaHelper::addCheckboxField("hideInMenu", $strFileName, array(), "clr no-clr-after");
\IIDO\BasicBundle\Helper\DcaHelper::copyField("navSubTitle", $strFileName, 'navTitle');
\IIDO\BasicBundle\Helper\DcaHelper::addSelectField("submenuSRC", $strFileName, array('includeBlankOption'=>true),'', false, '', false, true, 'page');
//\IIDO\BasicBundle\Helper\DcaHelper::copyFieldFromTable('submenuNewsArchive', $strFileName, 'submenuNewsArchive', 'page');
\IIDO\BasicBundle\Helper\DcaHelper::addField('submenuNewsArchive', 'select', $strFileName, array(), "o50", false, "", $arrFieldConfig);

\IIDO\BasicBundle\Helper\DcaHelper::addImageField("overviewImage", $strFileName);



// Expert Legend
\IIDO\BasicBundle\Helper\DcaHelper::addCheckboxField("hiddenArea", $strFileName);
\IIDO\BasicBundle\Helper\DcaHelper::addCheckboxField("noContent", $strFileName);

\IIDO\BasicBundle\Helper\DcaHelper::addCheckboxField("toNextArrow", $strFileName, array(), 'clr', false, true);
\IIDO\BasicBundle\Helper\DcaHelper::addSelectField("toNextArrowStyle", $strFileName);
\IIDO\BasicBundle\Helper\DcaHelper::addCheckboxField("toNextArrowAddTitle", $strFileName);
\IIDO\BasicBundle\Helper\DcaHelper::addCheckboxField("toNextArrowFixed", $strFileName);
\IIDO\BasicBundle\Helper\DcaHelper::addSelectField("toNextArrowPosition", $strFileName, array(), '', false, 'center-bottom');
\IIDO\BasicBundle\Helper\DcaHelper::addPositionField("toNextArrowPositionMargin", $strFileName);
\IIDO\BasicBundle\Helper\DcaHelper::addColorField("toNextArrowColor", $strFileName);
\IIDO\BasicBundle\Helper\DcaHelper::addColorField("toNextArrowHoverColor", $strFileName, array(), 'clr o50');


\IIDO\BasicBundle\Helper\DcaHelper::addSelectField("navLinkMode", $strFileName, array('includeBlankOption'=>true), 'clr', false, '', false, true);
\IIDO\BasicBundle\Helper\DcaHelper::addPageField("navLinkPage", $strFileName);
\IIDO\BasicBundle\Helper\DcaHelper::addUrlField("navLinkUrl", $strFileName);
\IIDO\BasicBundle\Helper\DcaHelper::addCheckboxField("navLinkNewWindow", $strFileName);

\IIDO\BasicBundle\Helper\DcaHelper::copyFieldFromTable('teaserHeadline', $strFileName, 'headline', 'content');
\IIDO\BasicBundle\Helper\DcaHelper::addImagesField("teaserMultiSRC", $strFileName);
\IIDO\BasicBundle\Helper\DcaHelper::copyFieldFromTable('orderSRC', $strFileName, 'orderSRC', 'content');



// Animation Legend
\IIDO\BasicBundle\Helper\DcaHelper::addCheckboxField("addAnimation", $strFileName, array(), '', false, true, "content");
\IIDO\BasicBundle\Helper\DcaHelper::addSelectField("animationType", $strFileName, array('includeBlankOption'=>true), '', false, '', true, false, "content");
\IIDO\BasicBundle\Helper\DcaHelper::addTextField("animationOffset", $strFileName, array(), '', false, 'content');
\IIDO\BasicBundle\Helper\DcaHelper::addCheckboxField("animationWait", $strFileName, array(), '', false, false, "content");
\IIDO\BasicBundle\Helper\DcaHelper::addSelectField("animateRun", $strFileName, array(), '', false, '', false, false, "content");



// TYPE: header, Config Legend
\IIDO\BasicBundle\Helper\DcaHelper::addCheckboxField("isFixed", $strFileName, array(), '', false, true);
\IIDO\BasicBundle\Helper\DcaHelper::addCheckboxField("enableSticky", $strFileName, array(), 'clr w50');
\IIDO\BasicBundle\Helper\DcaHelper::addSelectField("position", $strFileName);
\IIDO\BasicBundle\Helper\DcaHelper::addPositionField("positionMargin", $strFileName);
\IIDO\BasicBundle\Helper\DcaHelper::addUnitField("articleWidth", $strFileName, array(), 'clr');
\IIDO\BasicBundle\Helper\DcaHelper::addUnitField("articleHeight", $strFileName);
\IIDO\BasicBundle\Helper\DcaHelper::addCheckboxField("isAbsolute", $strFileName);



// Divider Legend
//\IIDO\BasicBundle\Helper\DcaHelper::copyField("addDivider", $strFileName, 'fullHeight');
\IIDO\BasicBundle\Helper\DcaHelper::addCheckboxField("addDivider", $strFileName, array(), 'clr no-clr-after', false, true);

\IIDO\BasicBundle\Helper\DcaHelper::addSelectField("dividerStyle", $strFileName);



// Inside Legend
\IIDO\BasicBundle\Helper\DcaHelper::addField('padding', 'trbl__units', $strFileName);