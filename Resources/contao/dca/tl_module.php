<?php
/*******************************************************************
 * (c) 2017 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

\Controller::loadDataContainer("tl_article");
\Controller::loadLanguageFile("tl_article");



/**
 * Palettes
 */

$GLOBALS['TL_DCA']['tl_module']['palettes']['iido_inheritArticle'] = '{title_legend},name,headline,type;{config_legend},inheritColumn;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID';



/**
 * Fields
 */

$GLOBALS['TL_DCA']['tl_module']['fields']['master_ID'] = array
(
    'sql'                       => "int(10) unsigned NOT NULL"
);

$GLOBALS['TL_DCA']['tl_module']['fields']['inheritColumn'] = $GLOBALS['TL_DCA']['tl_article']['fields']['inColumn'];