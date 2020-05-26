<?php
/*******************************************************************
 * (c) 2020 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

$strTable = UserGroupModel::getTable();

\System::loadLanguageFile( UserModel::getTable() );
\Controller::loadDataContainer( UserModel::getTable() );



/**
 * Extend the default palette
 */
Contao\CoreBundle\DataContainer\PaletteManipulator::create()
    ->addLegend('newsAreaOfApplication_legend', 'news_legend', \Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER)
    ->addField('newsAreaOfApplication', 'newsAreaOfApplication_legend', \Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_APPEND)
    ->addField('newsAreaOfApplication_roots', 'newsAreaOfApplication_legend', \Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_APPEND)
    ->addField('newsAreaOfApplication_default', 'newsAreaOfApplication_legend', \Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_APPEND)

    ->applyToPalette('default', $strTable);



/**
 * Add fields to tl_user_group
 */

$GLOBALS['TL_DCA']['tl_user_group']['fields']['newsAreaOfApplication']           = &$GLOBALS['TL_DCA']['tl_user']['fields']['newsAreaOfApplication'];
$GLOBALS['TL_DCA']['tl_user_group']['fields']['newsAreaOfApplication_default']   = &$GLOBALS['TL_DCA']['tl_user']['fields']['newsAreaOfApplication_default'];
$GLOBALS['TL_DCA']['tl_user_group']['fields']['newsAreaOfApplication_roots']     = &$GLOBALS['TL_DCA']['tl_user']['fields']['newsAreaOfApplication_roots'];
