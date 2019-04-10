<?php
/*******************************************************************
 * (c) 2019 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

$strTable = UserGroupModel::getTable();

//\System::loadLanguageFile( UserModel::getTable() );



/**
 * Extend the default palette
 */
Contao\CoreBundle\DataContainer\PaletteManipulator::create()
    ->addLegend('iido_WebsiteConfig_legend', 'amg_legend', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_BEFORE)
    ->addField(array('iidoWebsiteConfigs'), 'iido_WebsiteConfig_legend', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('default', $strTable);



/**
 * Add fields to tl_user_group
 */
$GLOBALS['TL_DCA']['tl_user_group']['fields']['iidoWebsiteConfigs'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_user_group']['iidoWebsiteConfigs'],
    'exclude'                 => true,
    'inputType'               => 'checkbox',
    'options_callback'        => array('IIDO\BasicBundle\Table\UserTable', 'getWebsiteConfigs'),
    'eval'                    => ['multiple' => true, 'tl_class' => 'clr'],
//    'sql'                     => "varchar(32) NOT NULL default ''"
    'sql'                     => "blob NULL"
);