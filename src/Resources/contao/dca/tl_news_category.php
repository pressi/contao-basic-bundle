<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/


$strFileTable   = \IIDO\BasicBundle\Config\BundleConfig::getTableName( __FILE__ );
//$objTable       = new \IIDO\BasicBundle\Dca\ExistTable( $strFileTable );



/**
 * Palettes
 */

$GLOBALS['TL_DCA'][ $strFileTable ]['palettes']['default'] = str_replace(',image', ',image,images', $GLOBALS['TL_DCA'][ $strFileTable ]['palettes']['default']);



/**
 * Fields
 */

\IIDO\BasicBundle\Helper\DcaHelper::addImagesField('images', $strFileTable);

//$GLOBALS['TL_DCA'][ $strFileTable ]['fields']['imagesOrder'] = array
//(
//);