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
 * Label
 */

$GLOBALS['TL_DCA'][ $strTable ]['list']['label']['label_callback']     = array('\IIDO\BasicBundle\Table\ArticleTable', 'addIcon');



/**
 * Buttons
 */

$GLOBALS['TL_DCA'][ $strTable ]['list']['operations']['edit']['button_callback']   = array('\IIDO\BasicBundle\Table\ArticleTable', 'editArticle');



/**
 * Palettes
 */

Contao\CoreBundle\DataContainer\PaletteManipulator::create()
    ->addLegend('config_legend', 'layout_legend', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER)
    ->addLegend('design_legend', 'config_legend', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER)
    ->addLegend('navigation_legend', 'teaser_legend', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_BEFORE)
    ->addLegend('animation_legend', 'expert_legend', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER)


    ->addField(array('fullHeight', 'fullWidth'), 'config_legend', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_PREPEND)
//-    ->addField('textMiddle', 'fullHeight', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER)
//-    ->addField('fullWidth', 'textMiddle', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER)

    ->addField(array('bgColor', 'bgImage', 'bgPosition', 'bgRepeat', 'gradientAngle', 'gradientColors', 'bgAttachment', 'bgSize', 'addBackgroundOverlay', 'addBackgroundVideo'), 'design_legend', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_PREPEND)
    ->addField(array('hideInMenu', 'overviewImage', 'navLinkMode'), 'navigation_legend', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_PREPEND)

    ->addField(array('articleType', 'navTitle', 'navSubTitle'), 'title', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER)

    ->addField('teaserHeadline', 'teaser', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_BEFORE)
    ->addField('teaserMultiSRC', 'teaser', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER)

    ->addField(array('hiddenArea', 'noContent'), 'guests', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER)

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


if( $objArticle && ($objArticle->articleType === "header" || $objArticle->articleType === "footer") )
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
        'config' => array('isFixed', 'enableSticky'), 'navigation', 'teaser', 'syndication',
        'protected', 'animation'
    );

    \IIDO\BasicBundle\Helper\DcaHelper::removeField($arrRemove, $strTable);
    \IIDO\BasicBundle\Helper\DcaHelper::removeLegend($arrRemoveLegends, $strTable);
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
\IIDO\BasicBundle\Helper\DcaHelper::addSubpalette("isFixed", "position,articleWidth,articleHeight", $strTable);
\IIDO\BasicBundle\Helper\DcaHelper::addSubpalette("navLinkMode_intern", "navLinkPage", $strTable);
\IIDO\BasicBundle\Helper\DcaHelper::addSubpalette("navLinkMode_extern", "navLinkUrl,navLinkNewWindow", $strTable);



/**
 * Fields
 */

$GLOBALS['TL_DCA'][ $strTable ]['fields']['title']['eval']['tl_class'] = trim($GLOBALS['TL_DCA'][ $strTable ]['fields']['title']['eval']['tl_class'] . ' w50');


// Title Legend
\IIDO\BasicBundle\Helper\DcaHelper::addField("articleType", "select__selector", $strTable);
\IIDO\BasicBundle\Helper\DcaHelper::addField("navTitle", "text", $strTable, array('doNotCopy'=>true));
\IIDO\BasicBundle\Helper\DcaHelper::copyField("navSubTitle", $strTable, 'navTitle');



// Config Legend
\IIDO\BasicBundle\Helper\DcaHelper::addField("fullHeight", "checkbox__selector", $strTable, array(), "clr no-clr-after");
\IIDO\BasicBundle\Helper\DcaHelper::addField("fullWidth", "checkbox__selector", $strTable, array(), "clr no-clr-after");
\IIDO\BasicBundle\Helper\DcaHelper::addField("fullWidthInside", "checkbox", $strTable);
\IIDO\BasicBundle\Helper\DcaHelper::addField("opticalHeight", "checkbox", $strTable);
\IIDO\BasicBundle\Helper\DcaHelper::addField("textMiddle", "checkbox__selector", $strTable, array(), "clr no-clr-after");
\IIDO\BasicBundle\Helper\DcaHelper::addField("textMiddleOptical", "checkbox", $strTable);



// Design Legend
\IIDO\BasicBundle\Helper\DcaHelper::addField("bgImage", "imagefield", $strTable);
\IIDO\BasicBundle\Helper\DcaHelper::addField("bgColor", "color", $strTable, array(), 'no-clr-after-clr');
\IIDO\BasicBundle\Helper\DcaHelper::addField("bgPosition", "select", $strTable, array('includeBlankOption'=>true), "clr");
\IIDO\BasicBundle\Helper\DcaHelper::addField("bgRepeat", "select", $strTable, array('includeBlankOption'=>true));
\IIDO\BasicBundle\Helper\DcaHelper::addField("gradientAngle", "text", $strTable, array('maxlength'=>32));
\IIDO\BasicBundle\Helper\DcaHelper::addField("gradientColors", "text", $strTable, array('maxlength'=>128,'multiple'=>true,'size'=>4,'decodeEntities'=>true));
\IIDO\BasicBundle\Helper\DcaHelper::addField("bgAttachment", "select", $strTable, array('includeBlankOption'=>true));
\IIDO\BasicBundle\Helper\DcaHelper::addField("bgSize", "imagesize", $strTable);

\IIDO\BasicBundle\Helper\DcaHelper::copyField("addBackgroundVideo", $strTable, 'fullHeight');
\IIDO\BasicBundle\Helper\DcaHelper::copyField("addBackgroundOverlay", $strTable, 'fullHeight');


$GLOBALS['TL_DCA'][ $strTable ]['fields']['videoSRC'] = array
(
    'label'                   => &$GLOBALS['TL_LANG'][ $strTable ]['videoSRC'],
    'exclude'                 => true,
    'inputType'               => 'fileTree',
    'eval'                    => array('multiple'=>true, 'fieldType'=>'checkbox', 'files'=>true, 'mandatory'=>true, 'tl_class'=>'clr w50 hauto'),
    'sql'                     => "blob NULL"
);

\IIDO\BasicBundle\Helper\DcaHelper::addField("posterSRC", "imagefield", $strTable);



// Navigation Legend
\IIDO\BasicBundle\Helper\DcaHelper::addField("hideInMenu", "checkbox", $strTable, array(), "clr no-clr-after");
\IIDO\BasicBundle\Helper\DcaHelper::copyField("navSubTitle", $strTable, 'navTitle');
\IIDO\BasicBundle\Helper\DcaHelper::addField("submenuSRC__page", "select__selector", $strTable, array('includeBlankOption'=>true));
\IIDO\BasicBundle\Helper\DcaHelper::copyFieldFromTable('submenuNewsArchive', $strTable, 'submenuNewsArchive', 'page');

\IIDO\BasicBundle\Helper\DcaHelper::addField("overviewImage", "imagefield", $strTable);


\IIDO\BasicBundle\Helper\DcaHelper::addField("enableSticky", "checkbox", $strTable);
// Expert Legend
\IIDO\BasicBundle\Helper\DcaHelper::addField("hiddenArea", "checkbox", $strTable);
\IIDO\BasicBundle\Helper\DcaHelper::addField("noContent", "checkbox", $strTable);
\IIDO\BasicBundle\Helper\DcaHelper::addField("toNextArrow", "checkbox", $strTable);

\IIDO\BasicBundle\Helper\DcaHelper::addField("navLinkMode", "select__selector", $strTable, array('includeBlankOption'=>true), 'clr');
\IIDO\BasicBundle\Helper\DcaHelper::addField("navLinkPage", "page", $strTable);
\IIDO\BasicBundle\Helper\DcaHelper::addField("navLinkUrl", "url", $strTable);
\IIDO\BasicBundle\Helper\DcaHelper::addField("navLinkNewWindow", "checkbox", $strTable);

\IIDO\BasicBundle\Helper\DcaHelper::copyFieldFromTable('teaserHeadline', $strTable, 'headline', 'content');
\IIDO\BasicBundle\Helper\DcaHelper::addField('teaserMultiSRC', "multisrc", $strTable);
\IIDO\BasicBundle\Helper\DcaHelper::copyFieldFromTable('orderSRC', $strTable, 'orderSRC', 'content');



// Animation Legend
\IIDO\BasicBundle\Helper\DcaHelper::addField("addAnimation__content", "checkbox__selector", $strTable);
\IIDO\BasicBundle\Helper\DcaHelper::addField("animationType__content", "select__short", $strTable, array('includeBlankOption'=>true));
\IIDO\BasicBundle\Helper\DcaHelper::addField("animationOffset__content", "text", $strTable);
\IIDO\BasicBundle\Helper\DcaHelper::addField("animationWait__content", "checkbox", $strTable);
\IIDO\BasicBundle\Helper\DcaHelper::addField("animateRun__content", "select", $strTable);



// TYPE: header, Config Legend
\IIDO\BasicBundle\Helper\DcaHelper::addField("isFixed", "checkbox__selector", $strTable);
\IIDO\BasicBundle\Helper\DcaHelper::addField("enableSticky", "checkbox", $strTable, array(), "clr w50");
\IIDO\BasicBundle\Helper\DcaHelper::addField("position", "select", $strTable);
\IIDO\BasicBundle\Helper\DcaHelper::addField("articleWidth", "unit", $strTable, array(), "clr");
\IIDO\BasicBundle\Helper\DcaHelper::addField("articleHeight", "unit", $strTable);