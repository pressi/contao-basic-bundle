<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

//Controller::loadDataContainer("tl_article");
//Controller::loadLanguageFile("tl_article");

$strTable = \IIDO\BasicBundle\Config\BundleConfig::getFileTable(__FILE__);



/**
 * Palettes
 */

$GLOBALS['TL_DCA']['tl_module']['palettes']['iido_inheritArticle'] = '{title_legend},name,headline,type;{config_legend},inheritColumn;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID';



/**
 * Fields
 */

$GLOBALS['TL_DCA'][ $strTable ]['fields']['master_ID'] = array
(
    'sql'                       => "int(10) unsigned NOT NULL"
);

//$GLOBALS['TL_DCA']['tl_module']['fields']['inheritColumn'] = $GLOBALS['TL_DCA']['tl_article']['fields']['inColumn'];
\IIDO\BasicBundle\Helper\DcaHelper::copyFieldFromTable('inheritColumn', $strTable, 'inColumn', 'article');

if( !is_array($GLOBALS['TL_DCA'][ $strTable ]['fields']['inheritColumn']) || (is_array($GLOBALS['TL_DCA'][ $strTable ]['fields']['inheritColumn']) && count($GLOBALS['TL_DCA'][ $strTable ]['fields']['inheritColumn'])) )
{
    $GLOBALS['TL_DCA'][ $strTable ]['fields']['inheritColumn'] = array
    (
        'label'                   => &$GLOBALS['TL_LANG']['tl_article']['inColumn'],
        'exclude'                 => true,
        'filter'                  => true,
        'default'                 => 'main',
        'inputType'               => 'select',
        'options_callback'        => array('tl_article', 'getActiveLayoutSections'),
        'eval'                    => array('tl_class'=>'w50'),
        'reference'               => &$GLOBALS['TL_LANG']['COLS'],
        'sql'                     => "varchar(32) NOT NULL default ''"
    );
}