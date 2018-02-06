<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

/**
 * Config
 */

$GLOBALS['TL_DCA']['tl_theme']['config']['onsubmit_callback'][] = array('IIDO\BasicBundle\Table\ThemeTable', 'saveThemeTable');



/**
 * Fields
 */

$GLOBALS['TL_DCA']['tl_theme']['fields']['master_ID'] = array
(
    'sql'                       => "int(10) unsigned NOT NULL"
);