<?php
/*******************************************************************
 *
 * (c) 2017 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 *
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *
 *******************************************************************/


/**
 * Palettes
 */

$GLOBALS['TL_DCA']['tl_news_archive']['palettes']['default'] = str_replace(',title', ',title,newsTyps', $GLOBALS['TL_DCA']['tl_news_archive']['palettes']['default']);
$GLOBALS['TL_DCA']['tl_news_archive']['palettes']['default'] = str_replace(',jumpTo;', ',jumpTo;{expert_legend},manualSorting,hideContentElements;', $GLOBALS['TL_DCA']['tl_news_archive']['palettes']['default']);



/**
 * Fields
 */

$GLOBALS['TL_DCA']['tl_news_archive']['fields']['title']['eval']['tl_class']    = trim($GLOBALS['TL_DCA']['tl_news_archive']['fields']['title']['eval']['tl_class'] . " w50");

$GLOBALS['TL_DCA']['tl_news_archive']['fields']['jumpTo']['eval']['tl_class']   = trim($GLOBALS['TL_DCA']['tl_news_archive']['fields']['jumpTo']['eval']['tl_class'] . " clr");
$GLOBALS['TL_DCA']['tl_news_archive']['fields']['jumpTo']['eval']['mandatory']  = false;

$GLOBALS['TL_DCA']['tl_news_archive']['fields']['newsTyps'] = array
(
    'label'					=> &$GLOBALS['TL_LANG']['tl_news_archive']['newsTyps'],
    'exclude'				=> true,
    'inputType'				=> 'select',
    'options'				=> &$GLOBALS['TL_LANG']['tl_news_archive']['options']['newsTyps'],
    'eval'					=> array
    (
        'includeBlankOption'	=> true,
        'tl_class'				=> 'w50',
//		'submitOnChange'		=> true
    ),
    'sql'					=> "varchar(255) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_news_archive']['fields']['manualSorting'] = array
(
    'label'						=> &$GLOBALS['TL_LANG']['tl_news_archive']['manualSorting'],
    'exclude'					=> true,
    'inputType'					=> 'checkbox',
    'eval'						=> array
    (
//		'submitOnChange'			=> true
        'tl_class'					=> 'w50 m12'
    ),
    'sql'						=> "char(1) NOT NULL default ''"
);


$GLOBALS['TL_DCA']['tl_news_archive']['fields']['hideContentElements'] = array
(
    'label'						=> &$GLOBALS['TL_LANG']['tl_news_archive']['hideContentElements'],
    'exclude'					=> true,
    'inputType'					=> 'checkbox',
    'eval'						=> array
    (
//		'submitOnChange'			=> true
        'tl_class'					=> 'w50 m12'
    ),
    'sql'						=> "char(1) NOT NULL default ''"
);
