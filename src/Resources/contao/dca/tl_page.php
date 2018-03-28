<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

$db             = \Database::getInstance();

$do             = \Input::get("do");
$act            = \Input::get("act");
$id             = \Input::get("id");

$strTableName   = \IIDO\BasicBundle\Config\BundleConfig::getTableName( __FILE__ );
$strTableClass  = \IIDO\BasicBundle\Config\BundleConfig::getTableClass( $strTableName );

$objCurrentPage = null;
$objParentPage  = null;


if($act === "edit" && $do === "page" && is_numeric($id))
{
    $objCurrentPage = $db->prepare("SELECT * FROM tl_page WHERE id=?")->limit(1)->execute($id);

    if($objCurrentPage->numRows > 0)
    {
        $objCurrentPage = $objCurrentPage->first();

        if($objCurrentPage->pid > 0)
        {
            $objParentPage = $db->prepare("SELECT * FROM tl_page WHERE id=?")->limit(1)->execute($objCurrentPage->pid);

            if($objParentPage->numRows > 0)
            {
                $objParentPage = $objParentPage->first();
            }
        }
    }
}



/**
 * Config
 */

$GLOBALS['TL_DCA'][ $strTableName ]['config']['oncreate_version_callback'][]  = array($strTableClass, 'checkThemeStylesheet');
$GLOBALS['TL_DCA'][ $strTableName ]['config']['onsubmit_callback'][]          = array($strTableClass, 'generateExtraArticle');



/**
 * List
 */

$GLOBALS['TL_DCA'][ $strTableName ]['list']['label']['label_callback']        = array($strTableClass, 'pageLabel');



/**
 * Selectors
 */

//$GLOBALS['TL_DCA'][ $strTableName ]['palettes']['__selector__'][]             = 'subPagesHasRequestLink';
//$GLOBALS['TL_DCA'][ $strTableName ]['palettes']['__selector__'][]             = 'submenuNoPages';
//$GLOBALS['TL_DCA'][ $strTableName ]['palettes']['__selector__'][]             = 'submenuSRC';
//$GLOBALS['TL_DCA'][ $strTableName ]['palettes']['__selector__'][]             = 'submenuPageCombination';



/**
 * Palettes
 */

$GLOBALS['TL_DCA'][ $strTableName ]['palettes']['regular_redirect']           = $GLOBALS['TL_DCA'][ $strTableName ]['palettes']['regular'];



$pageTableFields = '';

foreach($GLOBALS['TL_DCA'][ $strTableName ]['palettes'] as $strPalette => $strFields)
{
    if( $strPalette === "__selector__" )
    {
        continue;
    }

    $pageTableFields  = ',type,alt_pagename,subtitle,navTitle,navSubtitle,subtitlePosition';
    $backLinkFields   = ',subPagesHasBacklink';


    if($objParentPage != null && $objParentPage->subPagesHasBacklink)
    {
        $backLinkFields .= ',thisPageHasNoBacklink';
    }

    $backLinkFields .= ',subPagesHasRequestLink';

    if($objParentPage != null && $objParentPage->subPagesHasRequestLink)
    {
        $backLinkFields .= ',thisPageHasNoRequestLink';
    }


    if( $objCurrentPage->type !== "root" )
    {
        $strFields      = str_replace(',guests', ',guests' . $backLinkFields, $strFields);

        $strFields      = str_replace(',type', $pageTableFields, $strFields);

        if( $objCurrentPage->type == "regular_redirect" )
        {
            $strFields      = str_replace("{meta_legend", "{redirect_legend},jumpTo,redirectTimeout;{meta_legend", $strFields);
        }

        $strFields      = str_replace(',hide', '', $strFields);
        $strFields      = str_replace(',guests', '', $strFields);
        $strFields      = str_replace(',includeLayout', ',includeLayout,removeHeader,removeFooter,removeLeft,removeRight,addPageLoader', $strFields);

        $strFields      = str_replace('{meta_legend', '{page_legend},enableFullpage;{meta_legend', $strFields);
        $strFields      = str_replace('{meta_legend', '{navigation_legend},submenuNoPages,hide,hideTitle,openPageInLightbox,guests,overviewImage,pageColor,overviewText;{meta_legend', $strFields);

        if( \Config::get("folderUrl") )
        {
//            $strFields = str_replace(',guests', ',guests,excludeFromFolderUrl', $strFields);
            $strFields      = str_replace(',alias', ',alias,excludeFromFolderUrl', $strFields);

            $strFields      = str_replace(',type', '', $strFields);
            $strFields      = str_replace(',title', ',title,type', $strFields);
        }
    }
    else
    {
        $strFields      = str_replace('{cache_legend', '{additional_legend},enablePageFadeEffect,addPageLoader,enableCookie,enableLazyLoad;{cache_legend', $strFields);
    }

    if( $objCurrentPage->type !== "root" && !$objCurrentPage->addPageLoader && \IIDO\BasicBundle\Helper\PageHelper::checkIfParentPagesHasPageLoader( $id ) )
    {
        $strFields = str_replace(',addPageLoader', ',removePageLoader', $strFields);
    }

    $GLOBALS['TL_DCA'][ $strTableName ]['palettes'][ $strPalette ] = $strFields;
}



/**
 * Subpalettes
 */

\IIDO\BasicBundle\Helper\DcaHelper::addSubpalette("enableFullpage", "fullpageDirection", $strTableName);
\IIDO\BasicBundle\Helper\DcaHelper::addSubpalette("subPagesHasRequestLink", "requestLinkPage", $strTableName);
\IIDO\BasicBundle\Helper\DcaHelper::addSubpalette("submenuNoPages", "submenuSRC,submenuPageCombination", $strTableName);
\IIDO\BasicBundle\Helper\DcaHelper::addSubpalette("submenuSRC_news", "submenuNewsArchive", $strTableName);
\IIDO\BasicBundle\Helper\DcaHelper::addSubpalette("submenuPageCombination", "submenuPageOrder", $strTableName);



/**
 * Fields
 */

foreach($GLOBALS['TL_DCA'][ $strTableName ]['fields']['alias']['save_callback'] as $callbackNum => $arrCallback)
{
    if( $arrCallback[1] == "generateAlias" )
    {
        $GLOBALS['TL_DCA'][ $strTableName ]['fields']['alias']['save_callback'][ $callbackNum ] = array($strTableClass, 'generateAlias');
    }
}

$GLOBALS['TL_DCA'][ $strTableName ]['fields']['title']['eval']['alwaysSave']  = TRUE;
$GLOBALS['TL_DCA'][ $strTableName ]['fields']['title']['eval']['allowHtml']   = TRUE;
$GLOBALS['TL_DCA'][ $strTableName ]['fields']['title']['eval']['tl_class']    = trim($GLOBALS['TL_DCA'][ $strTableName ]['fields']['title']['eval']['tl_class'] . ' w50');
//$GLOBALS['TL_DCA'][ $strTableName ]['fields']['title']['save_callback'][]     = array($strTableClass, 'renameArticle'); //TODO: change function!! error with fullpage script!!

$GLOBALS['TL_DCA'][ $strTableName ]['fields']['hide']['eval']['tl_class']    = trim($GLOBALS['TL_DCA'][ $strTableName ]['fields']['hide']['eval']['tl_class'] . ' clr');



\IIDO\BasicBundle\Helper\DcaHelper::addField('openPageInLightbox', 'checkbox', $strTableName);

\IIDO\BasicBundle\Helper\DcaHelper::addField('enableFullpage', 'checkbox__selector', $strTableName);
\IIDO\BasicBundle\Helper\DcaHelper::addField('fullpageDirection', 'select', $strTableName);

\IIDO\BasicBundle\Helper\DcaHelper::addField('enablePageFadeEffect', 'checkbox', $strTableName);
\IIDO\BasicBundle\Helper\DcaHelper::addField('enableCookie', 'checkbox', $strTableName);
\IIDO\BasicBundle\Helper\DcaHelper::addField('enableLazyLoad', 'checkbox', $strTableName);


\IIDO\BasicBundle\Helper\DcaHelper::addField('submenuPageCombination', 'checkbox__selector', $strTableName, array('submitOnChange'=>TRUE));


\IIDO\BasicBundle\Helper\DcaHelper::addField('subPagesHasBacklink', 'checkbox', $strTableName, array(), 'clr');
\IIDO\BasicBundle\Helper\DcaHelper::addField('thisPageHasNoBacklink', 'checkbox', $strTableName);
\IIDO\BasicBundle\Helper\DcaHelper::addField('subPagesHasRequestLink', 'checkbox__selector', $strTableName, array('submitOnChange'=>TRUE), 'clr');

$GLOBALS['TL_DCA'][ $strTableName ]['fields']['requestLinkPage']                      = $GLOBALS['TL_DCA'][ $strTableName ]['fields']['jumpTo'];
$GLOBALS['TL_DCA'][ $strTableName ]['fields']['requestLinkPage']['label']             = &$GLOBALS['TL_LANG'][ $strTableName ]['requestLinkPage'];

\IIDO\BasicBundle\Helper\DcaHelper::addField('thisPageHasNoRequestLink', 'checkbox', $strTableName);


\IIDO\BasicBundle\Helper\DcaHelper::addField('excludeFromFolderUrl', 'checkbox', $strTableName);


\IIDO\BasicBundle\Helper\DcaHelper::addField("subtitle", "text", $strTableName);
\IIDO\BasicBundle\Helper\DcaHelper::addField("alt_pagename", "text", $strTableName);
\IIDO\BasicBundle\Helper\DcaHelper::addField("navTitle", "text", $strTableName);
\IIDO\BasicBundle\Helper\DcaHelper::addField("navSubtitle", "text", $strTableName);
\IIDO\BasicBundle\Helper\DcaHelper::addField("subtitlePosition", "select", $strTableName, array(), "", false, "after");
\IIDO\BasicBundle\Helper\DcaHelper::addField("hideTitle", "checkbox", $strTableName);


\IIDO\BasicBundle\Helper\DcaHelper::addField('removeHeader', 'checkbox', $strTableName);
\IIDO\BasicBundle\Helper\DcaHelper::addField('removeFooter', 'checkbox', $strTableName);
\IIDO\BasicBundle\Helper\DcaHelper::addField('removeLeft', 'checkbox', $strTableName);
\IIDO\BasicBundle\Helper\DcaHelper::addField('removeRight', 'checkbox', $strTableName);


\IIDO\BasicBundle\Helper\DcaHelper::addField('overviewImage', 'image', $strTableName);
\IIDO\BasicBundle\Helper\DcaHelper::addField('overviewText', 'textarea__rte', $strTableName);


$GLOBALS['TL_DCA'][ $strTableName ]['fields']['pageColor']                    = array
(
    'label'                 => &$GLOBALS['TL_LANG'][ $strTableName ]['pageColor'],
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

$GLOBALS['TL_DCA'][ $strTableName ]['fields']['redirectTimeout'] = array
(
    'label'                 => &$GLOBALS['TL_LANG'][ $strTableName ]['redirectTimeout'],
    'default'               => 0,
    'exclude'               => true,
    'inputType'             => 'text',
    'eval'                  => array
    (
        'rgxp'                  => 'natural',
        'tl_class'              => 'w50'
    ),
    'sql'                   => "smallint(5) unsigned NOT NULL default '0'"
);


\IIDO\BasicBundle\Helper\DcaHelper::addField('submenuNoPages', 'checkbox__selector', $strTableName);
\IIDO\BasicBundle\Helper\DcaHelper::addField('submenuSRC', 'select', $strTableName, array('maxlength'=>255,'submitOnChange'=>TRUE));


$arrFieldConfig = array
(
    'foreignKey'              => 'tl_news_archive.title',
    'sql'                     => "int(10) unsigned NOT NULL default '0'",
    'relation'                => array('type'=>'hasOne', 'load'=>'lazy')
);
\IIDO\BasicBundle\Helper\DcaHelper::addField('submenuNewsArchive', 'select', $strTableName, array(), "o50", false, "", $arrFieldConfig);
\IIDO\BasicBundle\Helper\DcaHelper::addField('submenuPageOrder', 'select', $strTableName, array('maxlength'=>255));

\IIDO\BasicBundle\Helper\DcaHelper::addCheckboxField('addPageLoader', $strTableName);
\IIDO\BasicBundle\Helper\DcaHelper::addCheckboxField('removePageLoader', $strTableName);