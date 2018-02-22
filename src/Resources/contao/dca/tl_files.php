<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/


/**
 * Fields
 */

$GLOBALS['TL_DCA']['tl_files']['fields']['meta']['eval']['metaFields']['description']   = 'textarea';

$GLOBALS['TL_DCA']['tl_files']['fields']['meta']['eval']['metaFields']['imageText']     = 'text';
$GLOBALS['TL_DCA']['tl_files']['fields']['meta']['eval']['metaFields']['imageSubline']  = 'text';
$GLOBALS['TL_DCA']['tl_files']['fields']['meta']['eval']['metaFields']['it_position']   = 'select_blank';
$GLOBALS['TL_DCA']['tl_files']['fields']['meta']['eval']['metaFields']['it_pos_margin'] = 'text_4';
$GLOBALS['TL_DCA']['tl_files']['fields']['meta']['eval']['metaFields']['it_text_align'] = 'select';

$GLOBALS['TL_DCA']['tl_files']['fields']['meta']['eval']['metaFields']['hoverTitle']    = 'text';
$GLOBALS['TL_DCA']['tl_files']['fields']['meta']['eval']['metaFields']['hoverSubTitle'] = 'text';

$GLOBALS['TL_DCA']['tl_files']['fields']['meta']['eval']['metaFields']['cssClass']      = 'text';
$GLOBALS['TL_DCA']['tl_files']['fields']['meta']['eval']['metaFields']['categories']    = 'iidoTag';

$GLOBALS['TL_DCA']['tl_files']['fields']['meta']['eval']['metaFields']['color']         = 'color';