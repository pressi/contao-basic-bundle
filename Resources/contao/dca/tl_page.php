<?php
/******************************************************************
 *
 * (c) 2017 Stephan Preßl <development@prestep.at>
 * All rights reserved
 *
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *
 ******************************************************************/

\Controller::loadDataContainer("tl_content");

$config         = \Config::getInstance();
$db             = \Database::getInstance();

$do             = \Input::get("do");
$act            = \Input::get("act");
$id             = \Input::get("id");

$strTable       = 'tl_page';

$objCurrentPage = null;
$objParentPage  = null;


if($act == "edit" && $do == "page" && is_numeric($id))
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

$GLOBALS['TL_DCA']['tl_page']['config']['oncreate_version_callback'][]  = array('IIDO\BasicBundle\Table\PageTable', 'checkThemeStylesheet');
$GLOBALS['TL_DCA']['tl_page']['config']['onsubmit_callback'][]          = array('IIDO\BasicBundle\Table\PageTable', 'generateExtraArticle');



/**
 * List
 */

$GLOBALS['TL_DCA']['tl_page']['list']['label']['label_callback']        = array('IIDO\BasicBundle\Table\PageTable', 'pageLabel');



/**
 * Selectors
 */

//$GLOBALS['TL_DCA']['tl_page']['palettes']['__selector__'][]             = 'subPagesHasRequestLink';
//$GLOBALS['TL_DCA']['tl_page']['palettes']['__selector__'][]             = 'submenuNoPages';
//$GLOBALS['TL_DCA']['tl_page']['palettes']['__selector__'][]             = 'submenuSRC';
//$GLOBALS['TL_DCA']['tl_page']['palettes']['__selector__'][]             = 'submenuPageCombination';



/**
 * Palettes
 */

$GLOBALS['TL_DCA']['tl_page']['palettes']['regular_redirect']           = $GLOBALS['TL_DCA']['tl_page']['palettes']['regular'];



$pageFields = '';

foreach($GLOBALS['TL_DCA']['tl_page']['palettes'] as $strPalette => $strFields)
{
    if( $strPalette == "__selector__" )
    {
        continue;
    }

    $pageFields     = ',type,alt_pagename,subtitle,navTitle,navSubtitle,subtitlePosition';
    $backLinkFields = ',subPagesHasBacklink';


    if($objParentPage != null && $objParentPage->subPagesHasBacklink)
    {
        $backLinkFields .= ',thisPageHasNoBacklink';
    }

    $backLinkFields .= ',subPagesHasRequestLink';

    if($objParentPage != null && $objParentPage->subPagesHasRequestLink)
    {
        $backLinkFields .= ',thisPageHasNoRequestLink';
    }


    if( $objCurrentPage->type != "root" )
    {
        $strFields      = str_replace(',guests', ',guests' . $backLinkFields, $strFields);

        $strFields      = str_replace(',type', $pageFields, $strFields);

        if( $objCurrentPage->type == "regular_redirect" )
        {
            $strFields      = str_replace("{meta_legend", "{redirect_legend},jumpTo,redirectTimeout;{meta_legend", $strFields);
        }

        $strFields      = str_replace(',hide', '', $strFields);
        $strFields      = str_replace(',guests', '', $strFields);
        $strFields      = str_replace(',includeLayout', ',includeLayout,removeHeader,removeFooter,removeLeft,removeRight,addPageLoader', $strFields);

        $strFields      = str_replace('{meta_legend', '{page_legend},enableFullpage;{meta_legend', $strFields);
        $strFields      = str_replace('{meta_legend', '{navigation_legend},submenuNoPages,hide,hideTitle,openPageInLightbox,guests,overviewImage,pageColor,overviewText;{meta_legend', $strFields);

        if( $config->get("folderUrl") )
        {
//            $strFields = str_replace(',guests', ',guests,excludeFromFolderUrl', $strFields);
            $strFields      = str_replace(',alias', ',alias,excludeFromFolderUrl', $strFields);

            $strFields      = str_replace(',type', '', $strFields);
            $strFields      = str_replace(',title', ',title,type', $strFields);
        }
    }
    else
    {
        $strFields      = str_replace('{cache_legend', '{additional_legend},enablePageFadeEffect,enableCookie,enableLazyLoad;{cache_legend', $strFields);
    }

    $GLOBALS['TL_DCA']['tl_page']['palettes'][ $strPalette ] = $strFields;
}



/**
 * Subpalettes
 */

//$GLOBALS['TL_DCA']['tl_page']['subpalettes']['subPagesHasRequestLink']  = 'requestLinkPage';
//$GLOBALS['TL_DCA']['tl_page']['subpalettes']['submenuNoPages']          = 'submenuSRC,submenuPageCombination';
//$GLOBALS['TL_DCA']['tl_page']['subpalettes']['submenuSRC_news']         = 'submenuNewsArchive';

//$GLOBALS['TL_DCA']['tl_page']['subpalettes']['submenuPageCombination']  = 'submenuPageOrder';

\IIDO\BasicBundle\Helper\DcaHelper::addSubpalette("subPagesHasRequestLink", "requestLinkPage", $strTable);
\IIDO\BasicBundle\Helper\DcaHelper::addSubpalette("submenuNoPages", "submenuSRC,submenuPageCombination", $strTable);
\IIDO\BasicBundle\Helper\DcaHelper::addSubpalette("submenuSRC_news", "submenuNewsArchive", $strTable);
\IIDO\BasicBundle\Helper\DcaHelper::addSubpalette("submenuPageCombination", "submenuPageOrder", $strTable);



/**
 * Fields
 */

foreach($GLOBALS['TL_DCA']['tl_page']['fields']['alias']['save_callback'] as $callbackNum => $arrCallback)
{
    if( $arrCallback[1] == "generateAlias" )
    {
        $GLOBALS['TL_DCA']['tl_page']['fields']['alias']['save_callback'][ $callbackNum ] = array('IIDO\BasicBundle\Table\PageTable', 'generateAlias');
    }
}

$GLOBALS['TL_DCA']['tl_page']['fields']['title']['eval']['alwaysSave']  = TRUE;
$GLOBALS['TL_DCA']['tl_page']['fields']['title']['eval']['allowHtml']   = TRUE;
$GLOBALS['TL_DCA']['tl_page']['fields']['title']['eval']['tl_class']    = trim($GLOBALS['TL_DCA']['tl_page']['fields']['title']['eval']['tl_class'] . ' w50');
$GLOBALS['TL_DCA']['tl_page']['fields']['title']['save_callback'][]     = array('IIDO\BasicBundle\Table\PageTable', 'renameArticle');

$GLOBALS['TL_DCA']['tl_page']['fields']['hide']['eval']['tl_class']    = trim($GLOBALS['TL_DCA']['tl_page']['fields']['hide']['eval']['tl_class'] . ' clr');



//$GLOBALS['TL_DCA']['tl_page']['fields']['openPageInLightbox'] = array
//(
//    'label'                 => &$GLOBALS['TL_LANG']['tl_page']['openPageInLightbox'],
//    'inputType'             => 'checkbox',
//    'eval'                  => array
//    (
//        'tl_class'              => 'w50 m12'
//    ),
//    'sql'                   => "char(1) NOT NULL default ''"
//);
\IIDO\BasicBundle\Helper\DcaHelper::addField('openPageInLightbox', 'checkbox', $strTable);
\IIDO\BasicBundle\Helper\DcaHelper::addField('enableFullpage', 'checkbox', $strTable);
\IIDO\BasicBundle\Helper\DcaHelper::addField('enablePageFadeEffect', 'checkbox', $strTable);
\IIDO\BasicBundle\Helper\DcaHelper::addField('enableCookie', 'checkbox', $strTable);
\IIDO\BasicBundle\Helper\DcaHelper::addField('enableLazyLoad', 'checkbox', $strTable);

//$GLOBALS['TL_DCA']['tl_page']['fields']['enableFullpage']                       = $GLOBALS['TL_DCA']['tl_page']['fields']['openPageInLightbox'];
//$GLOBALS['TL_DCA']['tl_page']['fields']['enableFullpage']['label']              = &$GLOBALS['TL_LANG']['tl_page']['enableFullpage'];


//$GLOBALS['TL_DCA']['tl_page']['fields']['enablePageFadeEffect']                 = $GLOBALS['TL_DCA']['tl_page']['fields']['openPageInLightbox'];
//$GLOBALS['TL_DCA']['tl_page']['fields']['enablePageFadeEffect']['label']        = &$GLOBALS['TL_LANG']['tl_page']['enablePageFadeEffect'];

//$GLOBALS['TL_DCA']['tl_page']['fields']['enableCookie']                         = $GLOBALS['TL_DCA']['tl_page']['fields']['openPageInLightbox'];
//$GLOBALS['TL_DCA']['tl_page']['fields']['enableCookie']['label']                = &$GLOBALS['TL_LANG']['tl_page']['enableCookie'];

//$GLOBALS['TL_DCA']['tl_page']['fields']['enableLazyLoad']                       = $GLOBALS['TL_DCA']['tl_page']['fields']['enableCookie'];
//$GLOBALS['TL_DCA']['tl_page']['fields']['enableLazyLoad']['label']              = &$GLOBALS['TL_LANG']['tl_page']['enableLazyLoad'];



//$GLOBALS['TL_DCA']['tl_page']['fields']['submenuPageCombination']               = $GLOBALS['TL_DCA']['tl_page']['fields']['openPageInLightbox'];
//$GLOBALS['TL_DCA']['tl_page']['fields']['submenuPageCombination']['label']      = &$GLOBALS['TL_LANG']['tl_page']['submenuPageCombination'];
//$GLOBALS['TL_DCA']['tl_page']['fields']['submenuPageCombination']['eval']['submitOnChange'] = TRUE;

\IIDO\BasicBundle\Helper\DcaHelper::addField('submenuPageCombination', 'checkbox__selector', $strTable, array('submitOnChange'=>TRUE));



//$GLOBALS['TL_DCA']['tl_page']['fields']['subPagesHasBacklink']                  = $GLOBALS['TL_DCA']['tl_page']['fields']['openPageInLightbox'];
//$GLOBALS['TL_DCA']['tl_page']['fields']['subPagesHasBacklink']['label']         = &$GLOBALS['TL_LANG']['tl_page']['subPagesHasBacklink'];
//$GLOBALS['TL_DCA']['tl_page']['fields']['subPagesHasBacklink']['eval']['tl_class'] = 'clr w50 m12';

\IIDO\BasicBundle\Helper\DcaHelper::addField('subPagesHasBacklink', 'checkbox', $strTable, array(), 'clr');

//$GLOBALS['TL_DCA']['tl_page']['fields']['thisPageHasNoBacklink']                = $GLOBALS['TL_DCA']['tl_page']['fields']['openPageInLightbox'];
//$GLOBALS['TL_DCA']['tl_page']['fields']['thisPageHasNoBacklink']['label']       = &$GLOBALS['TL_LANG']['tl_page']['thisPageHasNoBacklink'];

\IIDO\BasicBundle\Helper\DcaHelper::addField('thisPageHasNoBacklink', 'checkbox', $strTable);



//$GLOBALS['TL_DCA']['tl_page']['fields']['subPagesHasRequestLink']               = $GLOBALS['TL_DCA']['tl_page']['fields']['openPageInLightbox'];
//$GLOBALS['TL_DCA']['tl_page']['fields']['subPagesHasRequestLink']['label']      = &$GLOBALS['TL_LANG']['tl_page']['subPagesHasRequestLink'];
//$GLOBALS['TL_DCA']['tl_page']['fields']['subPagesHasRequestLink']['eval']['tl_class']       = 'clr w50 m12';
//$GLOBALS['TL_DCA']['tl_page']['fields']['subPagesHasRequestLink']['eval']['submitOnChange'] = TRUE;
\IIDO\BasicBundle\Helper\DcaHelper::addField('subPagesHasRequestLink', 'checkbox__selector', $strTable, array('submitOnChange'=>TRUE), 'clr');

$GLOBALS['TL_DCA']['tl_page']['fields']['requestLinkPage']                      = $GLOBALS['TL_DCA']['tl_page']['fields']['jumpTo'];
$GLOBALS['TL_DCA']['tl_page']['fields']['requestLinkPage']['label']             = &$GLOBALS['TL_LANG']['tl_page']['requestLinkPage'];

//$GLOBALS['TL_DCA']['tl_page']['fields']['thisPageHasNoRequestLink']             = $GLOBALS['TL_DCA']['tl_page']['fields']['openPageInLightbox'];
//$GLOBALS['TL_DCA']['tl_page']['fields']['thisPageHasNoRequestLink']['label']    = &$GLOBALS['TL_LANG']['tl_page']['thisPageHasNoRequestLink'];
\IIDO\BasicBundle\Helper\DcaHelper::addField('thisPageHasNoRequestLink', 'checkbox', $strTable);



//$GLOBALS['TL_DCA']['tl_page']['fields']['excludeFromFolderUrl']                 = $GLOBALS['TL_DCA']['tl_page']['fields']['openPageInLightbox'];
//$GLOBALS['TL_DCA']['tl_page']['fields']['excludeFromFolderUrl']['label']        = &$GLOBALS['TL_LANG']['tl_page']['excludeFromFolderUrl'];

\IIDO\BasicBundle\Helper\DcaHelper::addField('excludeFromFolderUrl', 'checkbox', $strTable);



//$GLOBALS['TL_DCA']['tl_page']['fields']['subtitle'] = array
//(
//    'label'                 => &$GLOBALS['TL_LANG']['tl_page']['subtitle'],
//    'exclude'               => TRUE,
//    'inputType'             => 'text',
//    'eval'                  => array
//    (
//        'maxlength'             => 255,
//        'decodeEntities'        => TRUE,
//        'tl_class'              => 'w50'
//    ),
//    'sql'                   => "varchar(255) NOT NULL default ''"
//);
\IIDO\BasicBundle\Helper\DcaHelper::addField("subtitle", "text", $strTable);

//$GLOBALS['TL_DCA']['tl_page']['fields']['alt_pagename']                 = $GLOBALS['TL_DCA']['tl_page']['fields']['subtitle'];
//$GLOBALS['TL_DCA']['tl_page']['fields']['alt_pagename']['label']        = &$GLOBALS['TL_LANG']['tl_page']['alt_pagename'];
\IIDO\BasicBundle\Helper\DcaHelper::addField("alt_pagename", "text", $strTable);

//$GLOBALS['TL_DCA']['tl_page']['fields']['navTitle']                     = $GLOBALS['TL_DCA']['tl_page']['fields']['subtitle'];
//$GLOBALS['TL_DCA']['tl_page']['fields']['navTitle']['label']            = &$GLOBALS['TL_LANG']['tl_page']['navTitle'];
\IIDO\BasicBundle\Helper\DcaHelper::addField("navTitle", "text", $strTable);

//$GLOBALS['TL_DCA']['tl_page']['fields']['navSubtitle']                  = $GLOBALS['TL_DCA']['tl_page']['fields']['subtitle'];
//$GLOBALS['TL_DCA']['tl_page']['fields']['navSubtitle']['label']         = &$GLOBALS['TL_LANG']['tl_page']['navSubtitle'];
\IIDO\BasicBundle\Helper\DcaHelper::addField("navSubtitle", "text", $strTable);

//$GLOBALS['TL_DCA']['tl_page']['fields']['subtitlePosition'] = array
//(
//    'label'						=> &$GLOBALS['TL_LANG']['tl_page']['subtitlePosition'],
//    'default'					=> 'after',
//    'exclude'					=> true,
//    'inputType'					=> 'select',
//    'options'					=> $GLOBALS['TL_LANG']['tl_page']['options']['subtitlePosition'],
//    'eval'						=> array
//    (
//        'maxlength'					=> 32,
//        'tl_class'					=> 'w50'
//    ),
//    'sql'						=> "varchar(32) NOT NULL default ''"
//);
\IIDO\BasicBundle\Helper\DcaHelper::addField("subtitlePosition", "select", $strTable, array(), "", false, "after");



//$GLOBALS['TL_DCA']['tl_page']['fields']['hideTitle']                    = $GLOBALS['TL_DCA']['tl_page']['fields']['openPageInLightbox'];
//$GLOBALS['TL_DCA']['tl_page']['fields']['hideTitle']['label']           = &$GLOBALS['TL_LANG']['tl_page']['hideTitle'];
//$GLOBALS['TL_DCA']['tl_page']['fields']['hideTitle']['eval']['tl_class']    = 'w50';
\IIDO\BasicBundle\Helper\DcaHelper::addField("hideTitle", "checkbox", $strTable);


//$GLOBALS['TL_DCA']['tl_page']['fields']['removeHeader']                 = $GLOBALS['TL_DCA']['tl_page']['fields']['openPageInLightbox'];
//$GLOBALS['TL_DCA']['tl_page']['fields']['removeHeader']['label']        = &$GLOBALS['TL_LANG']['tl_page']['removeHeader'];
\IIDO\BasicBundle\Helper\DcaHelper::addField('removeHeader', 'checkbox', $strTable);

//$GLOBALS['TL_DCA']['tl_page']['fields']['removeFooter']                 = $GLOBALS['TL_DCA']['tl_page']['fields']['openPageInLightbox'];
//$GLOBALS['TL_DCA']['tl_page']['fields']['removeFooter']['label']        = &$GLOBALS['TL_LANG']['tl_page']['removeFooter'];
\IIDO\BasicBundle\Helper\DcaHelper::addField('removeFooter', 'checkbox', $strTable);

//$GLOBALS['TL_DCA']['tl_page']['fields']['removeLeft']                   = $GLOBALS['TL_DCA']['tl_page']['fields']['openPageInLightbox'];
//$GLOBALS['TL_DCA']['tl_page']['fields']['removeLeft']['label']          = &$GLOBALS['TL_LANG']['tl_page']['removeLeft'];
\IIDO\BasicBundle\Helper\DcaHelper::addField('removeLeft', 'checkbox', $strTable);

//$GLOBALS['TL_DCA']['tl_page']['fields']['removeRight']                  = $GLOBALS['TL_DCA']['tl_page']['fields']['openPageInLightbox'];
//$GLOBALS['TL_DCA']['tl_page']['fields']['removeRight']['label']         = &$GLOBALS['TL_LANG']['tl_page']['removeRight'];
\IIDO\BasicBundle\Helper\DcaHelper::addField('removeRight', 'checkbox', $strTable);



//$GLOBALS['TL_DCA']['tl_page']['fields']['overviewImage']                = $GLOBALS['TL_DCA']['tl_content']['fields']['singleSRC'];
//$GLOBALS['TL_DCA']['tl_page']['fields']['overviewImage']['label']       = &$GLOBALS['TL_LANG']['tl_page']['overviewImage'];
//$GLOBALS['TL_DCA']['tl_page']['fields']['overviewImage']['eval']['mandatory']   = FALSE;
//$GLOBALS['TL_DCA']['tl_page']['fields']['overviewImage']['eval']['tl_class']    = trim( $GLOBALS['TL_DCA']['tl_page']['fields']['overviewImage']['eval']['tl_class'] . ' w50 hauto');
//$GLOBALS['TL_DCA']['tl_page']['fields']['overviewImage']['load_callback']       = array();
//$GLOBALS['TL_DCA']['tl_page']['fields']['overviewImage']['save_callback']       = array();
\IIDO\BasicBundle\Helper\DcaHelper::addField('overviewImage', 'image', $strTable);

//$GLOBALS['TL_DCA']['tl_page']['fields']['overviewText']                = $GLOBALS['TL_DCA']['tl_content']['fields']['text'];
//$GLOBALS['TL_DCA']['tl_page']['fields']['overviewText']['label']       = &$GLOBALS['TL_LANG']['tl_page']['overviewText'];
//$GLOBALS['TL_DCA']['tl_page']['fields']['overviewText']['eval']['mandatory']    = FALSE;
\IIDO\BasicBundle\Helper\DcaHelper::addField('overviewText', 'textarea__rte', $strTable);


$GLOBALS['TL_DCA']['tl_page']['fields']['pageColor']                    = array
(
    'label'                 => &$GLOBALS['TL_LANG']['tl_page']['pageColor'],
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



$GLOBALS['TL_DCA']['tl_page']['fields']['redirectTimeout'] = array
(
    'label'                 => &$GLOBALS['TL_LANG']['tl_page']['redirectTimeout'],
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


//$GLOBALS['TL_DCA']['tl_page']['fields']['submenuNoPages']               = $GLOBALS['TL_DCA']['tl_page']['fields']['subPagesHasRequestLink'];
//$GLOBALS['TL_DCA']['tl_page']['fields']['submenuNoPages']['label']      = &$GLOBALS['TL_LANG']['tl_page']['submenuNoPages'];
\IIDO\BasicBundle\Helper\DcaHelper::addField('submenuNoPages', 'checkbox__selector', $strTable);

//$GLOBALS['TL_DCA']['tl_page']['fields']['submenuSRC'] = array
//(
//    'label'                 => &$GLOBALS['TL_LANG']['tl_page']['submenuSRC'],
//    'exclude'               => TRUE,
//    'inputType'             => 'select',
//    'options'               => $GLOBALS['TL_LANG']['tl_page']['options']['submenuSRC'],
//    'eval'                  => array
//    (
//        'submitOnChange'        => TRUE,
//        'tl_class'              => 'w50'
//    ),
//    'sql'                   => "varchar(255) NOT NULL default ''"
//);
\IIDO\BasicBundle\Helper\DcaHelper::addField('submenuSRC', 'select', $strTable, array('maxlength'=>255,'submitOnChange'=>TRUE));

//$GLOBALS['TL_DCA']['tl_page']['fields']['submenuNewsArchive'] = array
//(
//    'label'                   => &$GLOBALS['TL_LANG']['tl_page']['submenuNewsArchive'],
//    'exclude'                 => TRUE,
//    'inputType'               => 'select',
//    'foreignKey'              => 'tl_news_archive.title',
//    'eval'                    => array('tl_class'=>'o50 w50'), // do not set mandatory (see #5453)
//    'sql'                     => "int(10) unsigned NOT NULL default '0'",
//    'relation'                => array('type'=>'hasOne', 'load'=>'lazy')
//);
$arrFieldConfig = array
(
    'foreignKey'              => 'tl_news_archive.title',
    'sql'                     => "int(10) unsigned NOT NULL default '0'",
    'relation'                => array('type'=>'hasOne', 'load'=>'lazy')
);
\IIDO\BasicBundle\Helper\DcaHelper::addField('submenuNewsArchive', 'select', $strTable, array(), "o50", false, "", $arrFieldConfig);

//$GLOBALS['TL_DCA']['tl_page']['fields']['submenuPageOrder']             = $GLOBALS['TL_DCA']['tl_page']['fields']['submenuSRC'];
//$GLOBALS['TL_DCA']['tl_page']['fields']['submenuPageOrder']['label']    = &$GLOBALS['TL_LANG']['tl_page']['submenuPageOrder'];
//$GLOBALS['TL_DCA']['tl_page']['fields']['submenuPageOrder']['options']  = $GLOBALS['TL_LANG']['tl_page']['options']['submenuPageOrder'];
//$GLOBALS['TL_DCA']['tl_page']['fields']['submenuPageOrder']['eval']['submitOnChange'] = FALSE;
\IIDO\BasicBundle\Helper\DcaHelper::addField('submenuPageOrder', 'select', $strTable, array('maxlength'=>255));

\IIDO\BasicBundle\Helper\DcaHelper::addField('addPageLoader', 'checkbox', $strTable);