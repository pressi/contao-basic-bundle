<?php
/*******************************************************************
* (c) 2020 Stephan Preßl, www.prestep.at <development@prestep.at>
* All rights reserved
* Modification, distribution or any other action on or with
* this file is permitted unless explicitly granted by IIDO
* www.iido.at <development@iido.at>
*******************************************************************/


/**
* Fields
*/

$GLOBALS['TL_DCA']['tl_files']['fields']['meta']['eval']['metaFields']['previewImage'] =
[
    'inputType' => 'fileTree',
    'eval' =>
    [
        'filesOnly'     => true,
        'fieldType'     => 'radio',
        'mandatory'     => true,
        'tl_class'      => 'clr',
        'extensions'    => Config::get('validImageTypes')
    ]
];