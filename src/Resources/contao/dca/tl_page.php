<?php

$strFileName    = \IIDO\BasicBundle\Config\BundleConfig::getFileTable( __FILE__ );
$objPageTable   = new \IIDO\BasicBundle\Dca\ExistTable( $strFileName );



/**
 * Palettes
 */

$objPageTable->copyPalette( 'default', 'global_element' );
$objPageTable->replacePaletteFields('regular', ',includeLayout', ',includeLayout,enableFullPage');



/**
 * Fields
 */

\IIDO\BasicBundle\Dca\Field::create('enableFullPage', 'checkbox')
    ->addToTable( $objPageTable );



/**
 * Update DCA
 */

$objPageTable->updateDca();