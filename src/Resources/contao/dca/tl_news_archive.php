<?php
/*******************************************************************
 * (c) 2020 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

$strFileName = 'tl_news_archive';

$arrLangFields = ['jumpTo'];



/**
 * Palettes
 */

foreach( $arrLangFields as $langField )
{
    foreach( $GLOBALS['TL_DCA'][ $strFileName ]['palettes'] as $palette => $fields )
    {
        if( $palette === '__selector__' )
        {
            continue;
        }

        $GLOBALS['TL_DCA'][ $strFileName ]['palettes'][ $palette ] = str_replace(',' . $langField, ',' . $langField . ',' . $langField . 'EN,' . $langField . 'US', $fields);
    }
}



/**
 * Fields
 */

foreach( $arrLangFields as $langField )
{
    $strLangFileName = '';

    if( $langField === 'url' )
    {
        $strLangFileName = 'MSC';
    }

    $GLOBALS['TL_DCA'][ $strFileName ]['fields'][ $langField . 'EN'] = $GLOBALS['TL_DCA'][ $strFileName ]['fields'][ $langField ];
    $GLOBALS['TL_DCA'][ $strFileName ]['fields'][ $langField . 'US'] = $GLOBALS['TL_DCA'][ $strFileName ]['fields'][ $langField ];

    unset( $GLOBALS['TL_DCA'][ $strFileName ]['fields'][ $langField . 'EN']['save_callback'] );
    unset( $GLOBALS['TL_DCA'][ $strFileName ]['fields'][ $langField . 'US']['save_callback'] );

    $strIconEN = '<img src="/files/hhsystem/images/backend/flag-en.svg" width="22" height="22">';
    $strIconUS = '<img src="/files/hhsystem/images/backend/flag-us.svg" width="22" height="22">';


    $GLOBALS['TL_DCA'][ $strFileName ]['fields'][ $langField ]['eval']['tl_class'] = preg_replace(['/w50/', '/[\s]{2,}/'], ['w33', '', ''], $GLOBALS['TL_DCA'][ $strFileName ]['fields'][ $langField ]['eval']['tl_class']);
    $GLOBALS['TL_DCA'][ $strFileName ]['fields'][ $langField . 'EN']['eval']['tl_class'] = preg_replace(['/w50/', '/clr/', '/[\s]{2,}/'], ['w33', '', ''], $GLOBALS['TL_DCA'][ $strFileName ]['fields'][ $langField . 'EN']['eval']['tl_class']);
    $GLOBALS['TL_DCA'][ $strFileName ]['fields'][ $langField . 'US']['eval']['tl_class'] = preg_replace(['/w50/', '/clr/', '/[\s]{2,}/'], ['w33', '', ''], $GLOBALS['TL_DCA'][ $strFileName ]['fields'][ $langField . 'US']['eval']['tl_class']);

    unset( $GLOBALS['TL_DCA'][ $strFileName ]['fields'][ $langField ]['label'] );
    $GLOBALS['TL_DCA'][ $strFileName ]['fields'][ $langField ]['label'] = $GLOBALS['TL_LANG'][ $strLangFileName?:$strFileName ][ $langField ];

    unset( $GLOBALS['TL_DCA'][ $strFileName ]['fields'][ $langField . 'EN']['label'] );
    $GLOBALS['TL_DCA'][ $strFileName ]['fields'][ $langField . 'EN']['label'] = $GLOBALS['TL_LANG'][ $strLangFileName?:$strFileName ][ $langField ];

    unset( $GLOBALS['TL_DCA'][ $strFileName ]['fields'][ $langField . 'US']['label'] );
    $GLOBALS['TL_DCA'][ $strFileName ]['fields'][ $langField . 'US']['label'] = $GLOBALS['TL_LANG'][ $strLangFileName?:$strFileName ][ $langField ];

    $GLOBALS['TL_DCA'][ $strFileName ]['fields'][ $langField . 'EN']['label'][0] = $strIconEN . $GLOBALS['TL_LANG'][ $strLangFileName?:$strFileName ][ $langField ][0];
    $GLOBALS['TL_DCA'][ $strFileName ]['fields'][ $langField . 'US']['label'][0] = $strIconUS . $GLOBALS['TL_LANG'][ $strLangFileName?:$strFileName ][ $langField ][0];

    $GLOBALS['TL_DCA'][ $strFileName ]['fields'][ $langField . 'EN']['eval']['mandatory'] = false;
    $GLOBALS['TL_DCA'][ $strFileName ]['fields'][ $langField . 'US']['eval']['mandatory'] = false;
}