<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

$strFileName    = \IIDO\BasicBundle\Config\BundleConfig::getFileTable( __FILE__ );
$fieldPrefix    = \IIDO\BasicBundle\Config\BundleConfig::getTableFieldPrefix();
$tableClass     = \IIDO\BasicBundle\Config\BundleConfig::getTableClass( $strFileName );

\IIDO\BasicBundle\Helper\DcaHelper::createNewTable( $strFileName, true );



/**
 * Palettes
 */

$arrFields =
[
    'weather_legend' =>
    [
        $fieldPrefix . 'weatherExplanation',

        $fieldPrefix . 'enableWeatherCron',

        $fieldPrefix . 'weatherIconsSet',
        $fieldPrefix . 'weatherLanguage',

        $fieldPrefix . 'weatherUrl',
        $fieldPrefix . 'weatherKey',
        $fieldPrefix . 'weatherCity'
    ]
];

\IIDO\BasicBundle\Helper\DcaHelper::addPalette('default', $arrFields, $strFileName);



/**
 * Subpalettes
 */

\IIDO\BasicBundle\Helper\DcaHelper::addSubpalette($fieldPrefix . 'weatherIconsSet_own', $fieldPrefix . 'weatherIconsUrl', $strFileName);



/**
 * Fields
 */

\IIDO\BasicBundle\Helper\DcaHelper::addCheckboxField( $fieldPrefix . 'enableWeatherCron', $strFileName, array(), '');

\IIDO\BasicBundle\Helper\DcaHelper::addSelectField($fieldPrefix . 'weatherIconsSet', $strFileName, array('includeBlankOption'=>true), 'clr', false, '', false, true,'', array('options_callback'=>array($tableClass, 'getIconsSet')));
\IIDO\BasicBundle\Helper\DcaHelper::addTextField($fieldPrefix . 'weatherIconsUrl', $strFileName, array('placeholder'=>'files/{foldername}/Uploads/Icons/Wetter/##IMG###.png'));

\IIDO\BasicBundle\Helper\DcaHelper::addSelectField($fieldPrefix . 'weatherLanguage', $strFileName, array('includeBlankOption'=>true,'chosen'=>true), 'clr', false, '', false, false,'', array('options_callback'=>array($tableClass, 'getLanguage')));

\IIDO\BasicBundle\Helper\DcaHelper::addTextField($fieldPrefix . 'weatherUrl', $strFileName, array('placeholder'=>'http://api.wunderground.com/api/'), 'clr');
\IIDO\BasicBundle\Helper\DcaHelper::addTextField($fieldPrefix . 'weatherKey', $strFileName, array('placeholder'=>'9d1d71aa8a832b9f'));
\IIDO\BasicBundle\Helper\DcaHelper::addTextField($fieldPrefix . 'weatherCity', $strFileName, array('placeholder'=>'zmw:00000.24.11357'));


$GLOBALS['TL_DCA'][ $strFileName ]['fields'][$fieldPrefix . 'weatherExplanation'] = array
(
    'inputType'               => 'explanation',
    'eval'                    => array
    (
        'text'              => $GLOBALS['TL_LANG'][ $strFileName ]['explanation']['weather'],
        'class'             => 'tl_info',
        'tl_class'          => 'long'
    )
);