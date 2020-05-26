<?php
/*******************************************************************
 * (c) 2020 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

$strTable = 'tl_user';



/**
 * Extend the default palettes
 */
Contao\CoreBundle\DataContainer\PaletteManipulator::create()

    ->addLegend('newsAreaOfApplication_legend', 'news_legend', \Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER)
    ->addField('newsAreaOfApplication', 'newsAreaOfApplication_legend', \Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_APPEND)
    ->addField('newsAreaOfApplication_roots', 'newsAreaOfApplication_legend', \Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_APPEND)
    ->addField('newsAreaOfApplication_default', 'newsAreaOfApplication_legend', \Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_APPEND)

    ->applyToPalette('extend', $strTable)
    ->applyToPalette('custom', $strTable)
;


/**
 * Add fields to tl_user_group
 */

$GLOBALS['TL_DCA']['tl_user']['fields']['newsAreaOfApplication'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_user']['newsAreaOfApplication'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'options' => ['manage'],
    'reference' => &$GLOBALS['TL_LANG']['tl_user']['newsAreaOfApplicationRef'],
    'eval' => ['multiple' => true, 'tl_class' => 'clr'],
    'sql' => ['type' => 'string', 'length' => 32, 'default' => ''],
];

$GLOBALS['TL_DCA']['tl_user']['fields']['newsAreaOfApplication_roots'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_user']['newsAreaOfApplication_roots'],
    'exclude' => true,
    'inputType' => 'newsAreaOfApplicationPicker',
    'foreignKey' => 'tl_news_areaOfApplication.title',
    'eval' => ['multiple' => true, 'fieldType' => 'checkbox'],
    'sql' => ['type' => 'blob', 'notnull' => false],
];

$GLOBALS['TL_DCA']['tl_user']['fields']['newsAreaOfApplication_default'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_user']['newsAreaOfApplication_default'],
    'exclude' => true,
    'inputType' => 'newsAreaOfApplicationPicker',
    'foreignKey' => 'tl_news_areaOfApplication.title',
    'eval' => ['multiple' => true, 'fieldType' => 'checkbox'],
    'sql' => ['type' => 'blob', 'notnull' => false],
];
