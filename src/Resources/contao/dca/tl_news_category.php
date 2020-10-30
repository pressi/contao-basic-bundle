<?php
/*******************************************************************
 * (c) 2020 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

\Contao\Controller::loadLanguageFile('tl_news_category');


$strFileName = 'tl_news_category';

$arrLangFields = ['title', 'alias', 'frontendTitle', 'description'];
$activeLangFields = false;



/**
 * Palettes
 */


//$GLOBALS['TL_DCA'][ $strFileName ]['palettes']['default'] = str_replace(',categories', ',categories,areasOfApplication', $GLOBALS['TL_DCA'][ $strFileName ]['palettes']['default']);
$GLOBALS['TL_DCA'][ $strFileName ]['palettes']['default'] = str_replace(',categories', ',categories,areasOfApplication,usage', $GLOBALS['TL_DCA'][ $strFileName ]['palettes']['default']);

if( $activeLangFields )
{
    foreach( $arrLangFields as $langField )
    {
        $GLOBALS['TL_DCA'][ $strFileName ]['palettes']['default'] = str_replace(',' . $langField, ',' . $langField . ',' . $langField . 'EN,' . $langField . 'US', $GLOBALS['TL_DCA'][ $strFileName ]['palettes']['default']);
    }
}



/**
 * Fields
 */

foreach( $arrLangFields as $langField )
{
    $arrLabel = $GLOBALS['TL_LANG'][ $strFileName ][ $langField ];


    $GLOBALS['TL_DCA'][ $strFileName ]['fields'][ $langField . 'EN'] = $GLOBALS['TL_DCA'][ $strFileName ]['fields'][ $langField ];
    $GLOBALS['TL_DCA'][ $strFileName ]['fields'][ $langField . 'US'] = $GLOBALS['TL_DCA'][ $strFileName ]['fields'][ $langField ];

    $strIconEN = '<img src="/files/hhsystem/images/backend/flag-en.svg" width="22" height="22">';
    $strIconUS = '<img src="/files/hhsystem/images/backend/flag-us.svg" width="22" height="22">';

    unset( $GLOBALS['TL_DCA'][ $strFileName ]['fields'][ $langField ]['label'] );
    $GLOBALS['TL_DCA'][ $strFileName ]['fields'][ $langField ]['label'] = $arrLabel;

    if( $GLOBALS['TL_DCA'][ $strFileName ]['fields'][ $langField ]['type'] !== 'textarea' )
    {
        $GLOBALS['TL_DCA'][ $strFileName ]['fields'][ $langField ]['eval']['tl_class'] = preg_replace(['/w50/', '/[\s]{2,}/'], ['w33', ''], $GLOBALS['TL_DCA'][ $strFileName ]['fields'][ $langField ]['eval']['tl_class']);
        $GLOBALS['TL_DCA'][ $strFileName ]['fields'][ $langField . 'EN']['eval']['tl_class'] = preg_replace(['/w50/', '/clr/', '/[\s]{2,}/'], ['w33', '', ''], $GLOBALS['TL_DCA'][ $strFileName ]['fields'][ $langField . 'EN']['eval']['tl_class']);
        $GLOBALS['TL_DCA'][ $strFileName ]['fields'][ $langField . 'US']['eval']['tl_class'] = preg_replace(['/w50/', '/clr/', '/[\s]{2,}/'], ['w33', '', ''], $GLOBALS['TL_DCA'][ $strFileName ]['fields'][ $langField . 'US']['eval']['tl_class']);
    }

    unset( $GLOBALS['TL_DCA'][ $strFileName ]['fields'][ $langField . 'EN']['label'] );
    $GLOBALS['TL_DCA'][ $strFileName ]['fields'][ $langField . 'EN']['label'] = $GLOBALS['TL_LANG'][ $strFileName ][ $langField ];

    unset( $GLOBALS['TL_DCA'][ $strFileName ]['fields'][ $langField . 'US']['label'] );
    $GLOBALS['TL_DCA'][ $strFileName ]['fields'][ $langField . 'US']['label'] = $GLOBALS['TL_LANG'][ $strFileName ][ $langField ];

    $GLOBALS['TL_DCA'][ $strFileName ]['fields'][ $langField . 'EN']['label'][0] = $strIconEN . $GLOBALS['TL_LANG'][ $strFileName ][ $langField ][0];
    $GLOBALS['TL_DCA'][ $strFileName ]['fields'][ $langField . 'US']['label'][0] = $strIconUS . $GLOBALS['TL_LANG'][ $strFileName ][ $langField ][0];

    $GLOBALS['TL_DCA'][ $strFileName ]['fields'][ $langField . 'EN']['eval']['mandatory'] = false;
    $GLOBALS['TL_DCA'][ $strFileName ]['fields'][ $langField . 'US']['eval']['mandatory'] = false;
}