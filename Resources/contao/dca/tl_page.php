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

$GLOBALS['TL_DCA']['tl_page']['config']['onsubmit_callback'][]          = array('IIDO\BasicBundle\Table\PageTable', 'generateExtraArticle');



/**
 * Selectors
 */

$GLOBALS['TL_DCA']['tl_page']['palettes']['__selector__'][]             = 'subPagesHasRequestLink';


$GLOBALS['TL_DCA']['tl_page']['palettes']['regular_redirect']           = $GLOBALS['TL_DCA']['tl_page']['palettes']['regular'];

/**
 * Palettes
 */

$pageFields = '';

foreach($GLOBALS['TL_DCA']['tl_page']['palettes'] as $strPalette => $strFields)
{
    if( $strPalette == "__selector__" )
    {
        continue;
    }

    $pageFields     = ',type,alt_pagename,subtitle,navTitle,navSubtitle';
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

        $strFields      = str_replace("{meta_legend", "{overview_legend},overviewImage;{meta_legend", $strFields);
        $strFields      = str_replace('{meta_legend', '{page_legend},openPageInLightbox,enableFullpage;{meta_legend', $strFields);

        $strFields      = str_replace(',hide', ',hide,hideTitle', $strFields);
        $strFields      = str_replace(',includeLayout', ',includeLayout,removeHeader,removeFooter', $strFields);

        if( $config->get("folderUrl") )
        {
//            $strFields = str_replace(',guests', ',guests,excludeFromFolderUrl', $strFields);
            $strFields      = str_replace(',alias', ',alias,excludeFromFolderUrl', $strFields);

            $strFields      = str_replace(',type', '', $strFields);
            $strFields      = str_replace(',title', ',title,type', $strFields);
        }
    }

    $GLOBALS['TL_DCA']['tl_page']['palettes'][ $strPalette ] = $strFields;
}



/**
 * Subpalettes
 */

$GLOBALS['TL_DCA']['tl_page']['subpalettes']['subPagesHasRequestLink']  = 'requestLinkPage';



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



$GLOBALS['TL_DCA']['tl_page']['fields']['openPageInLightbox'] = array
(
    'label'                 => &$GLOBALS['TL_LANG']['tl_page']['openPageInLightbox'],
    'inputType'             => 'checkbox',
    'eval'                  => array
    (
        'tl_class'              => 'w50 m12'
    ),
    'sql'                   => "char(1) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_page']['fields']['enableFullpage']                       = $GLOBALS['TL_DCA']['tl_page']['fields']['openPageInLightbox'];
$GLOBALS['TL_DCA']['tl_page']['fields']['enableFullpage']['label']              = &$GLOBALS['TL_LANG']['tl_page']['enableFullpage'];



$GLOBALS['TL_DCA']['tl_page']['fields']['subPagesHasBacklink']                  = $GLOBALS['TL_DCA']['tl_page']['fields']['openPageInLightbox'];
$GLOBALS['TL_DCA']['tl_page']['fields']['subPagesHasBacklink']['label']         = &$GLOBALS['TL_LANG']['tl_page']['subPagesHasBacklink'];
$GLOBALS['TL_DCA']['tl_page']['fields']['subPagesHasBacklink']['eval']['tl_class'] = 'clr w50 m12';

$GLOBALS['TL_DCA']['tl_page']['fields']['thisPageHasNoBacklink']                = $GLOBALS['TL_DCA']['tl_page']['fields']['openPageInLightbox'];
$GLOBALS['TL_DCA']['tl_page']['fields']['thisPageHasNoBacklink']['label']       = &$GLOBALS['TL_LANG']['tl_page']['thisPageHasNoBacklink'];



$GLOBALS['TL_DCA']['tl_page']['fields']['subPagesHasRequestLink']               = $GLOBALS['TL_DCA']['tl_page']['fields']['openPageInLightbox'];
$GLOBALS['TL_DCA']['tl_page']['fields']['subPagesHasRequestLink']['label']      = &$GLOBALS['TL_LANG']['tl_page']['subPagesHasRequestLink'];
$GLOBALS['TL_DCA']['tl_page']['fields']['subPagesHasRequestLink']['eval']['tl_class']       = 'clr w50 m12';
$GLOBALS['TL_DCA']['tl_page']['fields']['subPagesHasRequestLink']['eval']['submitOnChange'] = TRUE;

$GLOBALS['TL_DCA']['tl_page']['fields']['requestLinkPage']                      = $GLOBALS['TL_DCA']['tl_page']['fields']['jumpTo'];
$GLOBALS['TL_DCA']['tl_page']['fields']['requestLinkPage']['label']             = &$GLOBALS['TL_LANG']['tl_page']['requestLinkPage'];

$GLOBALS['TL_DCA']['tl_page']['fields']['thisPageHasNoRequestLink']             = $GLOBALS['TL_DCA']['tl_page']['fields']['openPageInLightbox'];
$GLOBALS['TL_DCA']['tl_page']['fields']['thisPageHasNoRequestLink']['label']    = &$GLOBALS['TL_LANG']['tl_page']['thisPageHasNoRequestLink'];



$GLOBALS['TL_DCA']['tl_page']['fields']['excludeFromFolderUrl']                 = $GLOBALS['TL_DCA']['tl_page']['fields']['openPageInLightbox'];
$GLOBALS['TL_DCA']['tl_page']['fields']['excludeFromFolderUrl']['label']        = &$GLOBALS['TL_LANG']['tl_page']['excludeFromFolderUrl'];



$GLOBALS['TL_DCA']['tl_page']['fields']['subtitle'] = array
(
    'label'                 => &$GLOBALS['TL_LANG']['tl_page']['subtitle'],
    'exclude'               => TRUE,
    'inputType'             => 'text',
    'eval'                  => array
    (
        'maxlength'             => 255,
        'decodeEntities'        => TRUE,
        'tl_class'              => 'w50'
    ),
    'sql'                   => "varchar(255) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_page']['fields']['alt_pagename']                 = $GLOBALS['TL_DCA']['tl_page']['fields']['subtitle'];
$GLOBALS['TL_DCA']['tl_page']['fields']['alt_pagename']['label']        = &$GLOBALS['TL_LANG']['tl_page']['alt_pagename'];


$GLOBALS['TL_DCA']['tl_page']['fields']['navTitle']                     = $GLOBALS['TL_DCA']['tl_page']['fields']['subtitle'];
$GLOBALS['TL_DCA']['tl_page']['fields']['navTitle']['label']            = &$GLOBALS['TL_LANG']['tl_page']['navTitle'];

$GLOBALS['TL_DCA']['tl_page']['fields']['navSubtitle']                  = $GLOBALS['TL_DCA']['tl_page']['fields']['subtitle'];
$GLOBALS['TL_DCA']['tl_page']['fields']['navSubtitle']['label']         = &$GLOBALS['TL_LANG']['tl_page']['navSubtitle'];



$GLOBALS['TL_DCA']['tl_page']['fields']['hideTitle']                    = $GLOBALS['TL_DCA']['tl_page']['fields']['openPageInLightbox'];
$GLOBALS['TL_DCA']['tl_page']['fields']['hideTitle']['label']           = &$GLOBALS['TL_LANG']['tl_page']['hideTitle'];


$GLOBALS['TL_DCA']['tl_page']['fields']['removeHeader']                 = $GLOBALS['TL_DCA']['tl_page']['fields']['openPageInLightbox'];
$GLOBALS['TL_DCA']['tl_page']['fields']['removeHeader']['label']        = &$GLOBALS['TL_LANG']['tl_page']['removeHeader'];

$GLOBALS['TL_DCA']['tl_page']['fields']['removeFooter']                 = $GLOBALS['TL_DCA']['tl_page']['fields']['openPageInLightbox'];
$GLOBALS['TL_DCA']['tl_page']['fields']['removeFooter']['label']        = &$GLOBALS['TL_LANG']['tl_page']['removeFooter'];



$GLOBALS['TL_DCA']['tl_page']['fields']['overviewImage']                = $GLOBALS['TL_DCA']['tl_content']['fields']['singleSRC'];
$GLOBALS['TL_DCA']['tl_page']['fields']['overviewImage']['label']       = &$GLOBALS['TL_LANG']['tl_page']['overviewImage'];
$GLOBALS['TL_DCA']['tl_page']['fields']['overviewImage']['eval']['mandatory']   = FALSE;
$GLOBALS['TL_DCA']['tl_page']['fields']['overviewImage']['load_callback']       = array();
$GLOBALS['TL_DCA']['tl_page']['fields']['overviewImage']['save_callback']       = array();



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