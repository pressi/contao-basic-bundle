<?php

$strFileName    = \IIDO\BasicBundle\Config\BundleConfig::getFileTable( __FILE__ );
$objPageTable   = new \IIDO\BasicBundle\Dca\ExistTable( $strFileName );



/**
 * Palettes
 */

$objPageTable->copyPalette( 'default', 'global_element' );



/**
 * Update DCA
 */

$objPageTable->updateDca();