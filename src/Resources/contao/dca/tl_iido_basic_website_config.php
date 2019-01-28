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
    ],

    'scripts_legend' =>
    [
        $fieldPrefix . 'scriptExplanation',

        $fieldPrefix . 'scriptFancybox',
        $fieldPrefix . 'scriptLazyload',

        $fieldPrefix . 'scriptIsotope',
        $fieldPrefix . 'scriptInfiniteScroll',
        $fieldPrefix . 'scriptMasonry',
        $fieldPrefix . 'scriptWaypoints',

        $fieldPrefix . 'scriptFullpage',
        $fieldPrefix . 'scriptScrolloverflow',

        $fieldPrefix . 'scriptCookie',
        $fieldPrefix . 'scriptScrollMagic',

        $fieldPrefix . 'scriptBarba',
        $fieldPrefix . 'scriptVelocity',

        $fieldPrefix . 'scriptNav',
        $fieldPrefix . 'scriptPickdate',
    ],

    'support_legend' =>
    [
        $fieldPrefix . 'enableSupportForm'
    ]
];

\IIDO\BasicBundle\Helper\DcaHelper::addPalette('default', $arrFields, $strFileName);



/**
 * Subpalettes
 */

$arrSupportFormSubpalette =
[
    'supportCompany',
    'supportEmployee',
    'supportStreet',
    'supportPostal',
    'supportCity',

    'supportMail',
    'supportPhone'
];

\IIDO\BasicBundle\Helper\DcaHelper::addSubpalette($fieldPrefix . 'weatherIconsSet_own', $fieldPrefix . 'weatherIconsUrl', $strFileName);
\IIDO\BasicBundle\Helper\DcaHelper::addSubpalette($fieldPrefix . 'enableSupportForm', $fieldPrefix . implode( ',' . $fieldPrefix, $arrSupportFormSubpalette), $strFileName);



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



// Scripts
\IIDO\BasicBundle\Helper\DcaHelper::addSelectField($fieldPrefix . 'scriptFancybox', $strFileName, array(), 'clr', false, '', false, false,'', array('options_callback'=>array($tableClass, 'getScriptVersion')));
\IIDO\BasicBundle\Helper\DcaHelper::addSelectField($fieldPrefix . 'scriptFullpage', $strFileName, array(), '', false, '', false, false,'', array('options_callback'=>array($tableClass, 'getScriptVersion')));
\IIDO\BasicBundle\Helper\DcaHelper::addSelectField($fieldPrefix . 'scriptIsotope', $strFileName, array(), '', false, '', false, false,'', array('options_callback'=>array($tableClass, 'getScriptVersion')));
\IIDO\BasicBundle\Helper\DcaHelper::addSelectField($fieldPrefix . 'scriptLazyload', $strFileName, array(), '', false, '', false, false,'', array('options_callback'=>array($tableClass, 'getScriptVersion')));
\IIDO\BasicBundle\Helper\DcaHelper::addSelectField($fieldPrefix . 'scriptScrolloverflow', $strFileName, array(), '', false, '', false, false,'', array('options_callback'=>array($tableClass, 'getScriptVersion')));
\IIDO\BasicBundle\Helper\DcaHelper::addSelectField($fieldPrefix . 'scriptWaypoints', $strFileName, array(), '', false, '', false, false,'', array('options_callback'=>array($tableClass, 'getScriptVersion')));

\IIDO\BasicBundle\Helper\DcaHelper::addSelectField($fieldPrefix . 'scriptScrollMagic', $strFileName, array(), '', false, '', false, false,'', array('options_callback'=>array($tableClass, 'getScriptVersion')));
\IIDO\BasicBundle\Helper\DcaHelper::addSelectField($fieldPrefix . 'scriptCookie', $strFileName, array(), '', false, '', false, false,'', array('options_callback'=>array($tableClass, 'getScriptVersion')));
\IIDO\BasicBundle\Helper\DcaHelper::addSelectField($fieldPrefix . 'scriptInfiniteScroll', $strFileName, array(), '', false, '', false, false,'', array('options_callback'=>array($tableClass, 'getScriptVersion')));
\IIDO\BasicBundle\Helper\DcaHelper::addSelectField($fieldPrefix . 'scriptMasonry', $strFileName, array(), '', false, '', false, false,'', array('options_callback'=>array($tableClass, 'getScriptVersion')));
\IIDO\BasicBundle\Helper\DcaHelper::addSelectField($fieldPrefix . 'scriptBarba', $strFileName, array(), '', false, '', false, false,'', array('options_callback'=>array($tableClass, 'getScriptVersion')));
\IIDO\BasicBundle\Helper\DcaHelper::addSelectField($fieldPrefix . 'scriptVelocity', $strFileName, array(), '', false, '', false, false,'', array('options_callback'=>array($tableClass, 'getScriptVersion')));

\IIDO\BasicBundle\Helper\DcaHelper::addSelectField($fieldPrefix . 'scriptNav', $strFileName, array(), '', false, '', false, false,'', array('options_callback'=>array($tableClass, 'getScriptVersion')));
\IIDO\BasicBundle\Helper\DcaHelper::addSelectField($fieldPrefix . 'scriptPickdate', $strFileName, array(), '', false, '', false, false,'', array('options_callback'=>array($tableClass, 'getScriptVersion')));


$GLOBALS['TL_DCA'][ $strFileName ]['fields'][$fieldPrefix . 'scriptExplanation'] = array
(
    'inputType'               => 'explanation',
    'eval'                    => array
    (
        'text'              => $GLOBALS['TL_LANG'][ $strFileName ]['explanation']['scripts'],
        'class'             => 'tl_info',
        'tl_class'          => 'long'
    )
);



// Support
\IIDO\BasicBundle\Helper\DcaHelper::addCheckboxField($fieldPrefix . 'enableSupportForm', $strFileName, array(), '', false, true);

foreach( $arrSupportFormSubpalette as $strFieldName)
{
    \IIDO\BasicBundle\Helper\DcaHelper::addTextField($fieldPrefix . $strFieldName, $strFileName);
}