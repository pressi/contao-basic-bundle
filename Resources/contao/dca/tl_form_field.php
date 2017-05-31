<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */


/**
 * Palettes
 */

$GLOBALS['TL_DCA']['tl_form_field']['palettes']['radioTable']             = str_replace(',options', ',tableHeader,tableOptions', $GLOBALS['TL_DCA']['tl_form_field']['palettes']['radio']);




/**
 * Fields
 */

$GLOBALS['TL_DCA']['tl_form_field']['fields']['tableHeader'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_form_field']['tableHeader'],
    'exclude'                 => true,
    'inputType'               => 'text',
    'eval'                    => array
    (
        'multiple'              => true,
        'size'                  => 4
    ),
    'sql'                     => "varchar(255) NOT NULL default ''"
);


$GLOBALS['TL_DCA']['tl_form_field']['fields']['tableOptions'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_form_field']['options'],
    'exclude'                 => true,
    'inputType'               => 'multiColumnWizard',
    'eval'                    => array
    (
        'mandatory'             => true,
        'allowHtml'             => true,
        'tl_class'              => 'clr mcw-radio-table',
        'columnFields'          => array
        (
            'rowTitle'              => array
            (
                'label'                 => &$GLOBALS['TL_LANG']['tl_form_field']['tableOptions']['rowTitle'],
                'exclude'               => true,
                'inputType'             => 'text',
                'eval'                  => array('style'=>'width:190px')
            ),

            'col1_field'              => array
            (
                'label'                 => &$GLOBALS['TL_LANG']['tl_form_field']['tableOptions']['col1_field'],
                'exclude'               => true,
                'inputType'             => 'text',
                'eval'                  => array('multiple'=>true,'size'=>2,'style'=>'width:160px')
            ),

            'col2_field'              => array
            (
                'label'                 => &$GLOBALS['TL_LANG']['tl_form_field']['tableOptions']['col2_field'],
                'exclude'               => true,
                'inputType'             => 'text',
                'eval'                  => array('multiple'=>true,'size'=>2,'style'=>'width:160px')
            ),

            'col3_field'              => array
            (
                'label'                 => &$GLOBALS['TL_LANG']['tl_form_field']['tableOptions']['col3_field'],
                'exclude'               => true,
                'inputType'             => 'text',
                'eval'                  => array('multiple'=>true,'size'=>2,'style'=>'width:160px')
            )
        )
    ),
    'sql'                     => "blob NULL"
);