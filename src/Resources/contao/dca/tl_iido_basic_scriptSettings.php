<?php
/*******************************************************************
 * (c) 2019 Stephan Preßl, www.prestep.at <development@prestep.at>
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
];

\IIDO\BasicBundle\Helper\DcaHelper::addPalette('default', $arrFields, $strFileName);



/**
 * Fields
 */

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