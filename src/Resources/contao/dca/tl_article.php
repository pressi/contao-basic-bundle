<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

\Controller::loadLanguageFile("tl_page");

\Controller::loadDataContainer("tl_page");
\Controller::loadDataContainer("tl_content");

$objArticle         = FALSE;
$objParentPage      = FALSE;

$act                = \Input::get("act");
$id                 = \Input::get("id");
$table              = \Input::get("table");
$strFileName        = \ArticleModel::getTable();

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

$GLOBALS['TL_DCA'][ $strFileName ]['list']['label']['label_callback']     = array('\IIDO\BasicBundle\Table\ArticleTable', 'addIcon');



/**
 * Buttons
 */

$GLOBALS['TL_DCA'][ $strFileName ]['list']['operations']['edit']['button_callback']   = array('\IIDO\BasicBundle\Table\ArticleTable', 'editArticle');



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


    ->applyToPalette('default', $strFileName);


if( $objParentPage && $objParentPage->submenuNoPages && $objParentPage->submenuSRC == "articles")
{
    Contao\CoreBundle\DataContainer\PaletteManipulator::create()
        ->addField('submenuSRC', 'hideInMenu', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER)

        ->applyToPalette('default', $strFileName);
}


if( $objParentPage && $objParentPage->enableFullpage )
{
    Contao\CoreBundle\DataContainer\PaletteManipulator::create()
        ->addField('toNextArrow', 'noContent', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER)

        ->applyToPalette('default', $strFileName);
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

    \IIDO\BasicBundle\Helper\DcaHelper::removeField($arrRemove, $strFileName);
    \IIDO\BasicBundle\Helper\DcaHelper::removeLegend($arrRemoveLegends, $strFileName);
}



/**
 * Subpalettes
 */

\IIDO\BasicBundle\Helper\DcaHelper::addSubpalette("fullHeight", "opticalHeight,textMiddle", $strFileName);
\IIDO\BasicBundle\Helper\DcaHelper::addSubpalette("textMiddle", "textMiddleOptical", $strFileName);
\IIDO\BasicBundle\Helper\DcaHelper::addSubpalette("fullWidth", "fullWidthInside", $strFileName);
\IIDO\BasicBundle\Helper\DcaHelper::addSubpalette("addBackgroundVideo", "videoSRC,posterSRC", $strFileName);
\IIDO\BasicBundle\Helper\DcaHelper::addSubpalette("submenuSRC_news", "submenuNewsArchive", $strFileName);
\IIDO\BasicBundle\Helper\DcaHelper::addSubpalette("addAnimation", "animationType,animateRun,animationWait,animationOffset", $strFileName);
\IIDO\BasicBundle\Helper\DcaHelper::addSubpalette("isFixed", "position,articleWidth,articleHeight", $strFileName);
\IIDO\BasicBundle\Helper\DcaHelper::addSubpalette("navLinkMode_intern", "navLinkPage", $strFileName);
\IIDO\BasicBundle\Helper\DcaHelper::addSubpalette("navLinkMode_extern", "navLinkUrl,navLinkNewWindow", $strFileName);



/**
 * Fields
 */

$GLOBALS['TL_DCA'][ $strFileName ]['fields']['title']['eval']['tl_class'] = trim($GLOBALS['TL_DCA'][ $strFileName ]['fields']['title']['eval']['tl_class'] . ' w50');


// Title Legend
\IIDO\BasicBundle\Helper\DcaHelper::addSelectField("articleType", $strFileName, array(), '', false, '', false, true);
\IIDO\BasicBundle\Helper\DcaHelper::addField("navTitle", "text", $strFileName, array('doNotCopy'=>true));
\IIDO\BasicBundle\Helper\DcaHelper::copyField("navSubTitle", $strFileName, 'navTitle');



// Config Legend
\IIDO\BasicBundle\Helper\DcaHelper::addField("fullHeight", "checkbox__selector", $strFileName, array(), "clr no-clr-after");
\IIDO\BasicBundle\Helper\DcaHelper::addField("fullWidth", "checkbox__selector", $strFileName, array(), "clr no-clr-after");
\IIDO\BasicBundle\Helper\DcaHelper::addField("fullWidthInside", "checkbox", $strFileName);
\IIDO\BasicBundle\Helper\DcaHelper::addField("opticalHeight", "checkbox", $strFileName);
\IIDO\BasicBundle\Helper\DcaHelper::addField("textMiddle", "checkbox__selector", $strFileName, array(), "clr no-clr-after");
\IIDO\BasicBundle\Helper\DcaHelper::addField("textMiddleOptical", "checkbox", $strFileName);



// Design Legend
\IIDO\BasicBundle\Helper\DcaHelper::addField("bgImage", "imagefield", $strFileName);
\IIDO\BasicBundle\Helper\DcaHelper::addField("bgColor", "color", $strFileName, array(), 'no-clr-after-clr');
\IIDO\BasicBundle\Helper\DcaHelper::addField("bgPosition", "select", $strFileName, array('includeBlankOption'=>true), "clr");
\IIDO\BasicBundle\Helper\DcaHelper::addField("bgRepeat", "select", $strFileName, array('includeBlankOption'=>true));
\IIDO\BasicBundle\Helper\DcaHelper::addField("gradientAngle", "text", $strFileName, array('maxlength'=>32));
\IIDO\BasicBundle\Helper\DcaHelper::addField("gradientColors", "text", $strFileName, array('maxlength'=>128,'multiple'=>true,'size'=>4,'decodeEntities'=>true));
\IIDO\BasicBundle\Helper\DcaHelper::addField("bgAttachment", "select", $strFileName, array('includeBlankOption'=>true));
\IIDO\BasicBundle\Helper\DcaHelper::addField("bgSize", "imagesize", $strFileName);

\IIDO\BasicBundle\Helper\DcaHelper::copyField("addBackgroundVideo", $strFileName, 'fullHeight');
\IIDO\BasicBundle\Helper\DcaHelper::copyField("addBackgroundOverlay", $strFileName, 'fullHeight');


$GLOBALS['TL_DCA'][ $strFileName ]['fields']['videoSRC'] = array
(
    'label'                   => &$GLOBALS['TL_LANG'][ $strFileName ]['videoSRC'],
    'exclude'                 => true,
    'inputType'               => 'fileTree',
    'eval'                    => array('multiple'=>true, 'fieldType'=>'checkbox', 'files'=>true, 'mandatory'=>true, 'tl_class'=>'clr w50 hauto'),
    'sql'                     => "blob NULL"
);

\IIDO\BasicBundle\Helper\DcaHelper::addField("posterSRC", "imagefield", $strFileName);



// Navigation Legend
\IIDO\BasicBundle\Helper\DcaHelper::addField("hideInMenu", "checkbox", $strFileName, array(), "clr no-clr-after");
\IIDO\BasicBundle\Helper\DcaHelper::copyField("navSubTitle", $strFileName, 'navTitle');
\IIDO\BasicBundle\Helper\DcaHelper::addField("submenuSRC__page", "select__selector", $strFileName, array('includeBlankOption'=>true));
\IIDO\BasicBundle\Helper\DcaHelper::copyFieldFromTable('submenuNewsArchive', $strFileName, 'submenuNewsArchive', 'page');

\IIDO\BasicBundle\Helper\DcaHelper::addField("overviewImage", "imagefield", $strFileName);


\IIDO\BasicBundle\Helper\DcaHelper::addField("enableSticky", "checkbox", $strFileName);
// Expert Legend
\IIDO\BasicBundle\Helper\DcaHelper::addField("hiddenArea", "checkbox", $strFileName);
\IIDO\BasicBundle\Helper\DcaHelper::addField("noContent", "checkbox", $strFileName);
\IIDO\BasicBundle\Helper\DcaHelper::addField("toNextArrow", "checkbox", $strFileName);

\IIDO\BasicBundle\Helper\DcaHelper::addField("navLinkMode", "select__selector", $strFileName, array('includeBlankOption'=>true), 'clr');
\IIDO\BasicBundle\Helper\DcaHelper::addField("navLinkPage", "page", $strFileName);
\IIDO\BasicBundle\Helper\DcaHelper::addField("navLinkUrl", "url", $strFileName);
\IIDO\BasicBundle\Helper\DcaHelper::addField("navLinkNewWindow", "checkbox", $strFileName);

\IIDO\BasicBundle\Helper\DcaHelper::copyFieldFromTable('teaserHeadline', $strFileName, 'headline', 'content');
\IIDO\BasicBundle\Helper\DcaHelper::addField('teaserMultiSRC', "multisrc", $strFileName);
\IIDO\BasicBundle\Helper\DcaHelper::copyFieldFromTable('orderSRC', $strFileName, 'orderSRC', 'content');



// Animation Legend
\IIDO\BasicBundle\Helper\DcaHelper::addField("addAnimation__content", "checkbox__selector", $strFileName);
\IIDO\BasicBundle\Helper\DcaHelper::addField("animationType__content", "select__short", $strFileName, array('includeBlankOption'=>true));
\IIDO\BasicBundle\Helper\DcaHelper::addField("animationOffset__content", "text", $strFileName);
\IIDO\BasicBundle\Helper\DcaHelper::addField("animationWait__content", "checkbox", $strFileName);
\IIDO\BasicBundle\Helper\DcaHelper::addField("animateRun__content", "select", $strFileName);



// TYPE: header, Config Legend
\IIDO\BasicBundle\Helper\DcaHelper::addField("isFixed", "checkbox__selector", $strFileName);
\IIDO\BasicBundle\Helper\DcaHelper::addField("enableSticky", "checkbox", $strFileName, array(), "clr w50");
\IIDO\BasicBundle\Helper\DcaHelper::addField("position", "select", $strFileName);
\IIDO\BasicBundle\Helper\DcaHelper::addField("articleWidth", "unit", $strFileName, array(), "clr");
\IIDO\BasicBundle\Helper\DcaHelper::addField("articleHeight", "unit", $strFileName);