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
    'width_legend:hide' =>
    [
        $fieldPrefix . 'pageWidth',
        $fieldPrefix . 'pageContentWidth',
    ],

    'colors_legend:hide' =>
    [
        $fieldPrefix . 'colors',

        $fieldPrefix . 'colorPrimary',
        $fieldPrefix . 'colorSecondary',
    ],

    'buttons_legend:hide' =>
    [
        $fieldPrefix . 'buttonColorPrimary',
        $fieldPrefix . 'buttonFontColorPrimary',
        $fieldPrefix . 'buttonHoverColorPrimary',
        $fieldPrefix . 'buttonHoverFontColorPrimary',

        $fieldPrefix . 'buttonColorSecondary',
        $fieldPrefix . 'buttonFontColorSecondary',
        $fieldPrefix . 'buttonHoverColorSecondary',
        $fieldPrefix . 'buttonHoverFontColorSecondary'
    ],

    'pageLoader_legend:hide' =>
     [
         $fieldPrefix . 'pageLoaderBackgroundColor',
         $fieldPrefix . 'pageLoaderStyle',
     ],
];

\IIDO\BasicBundle\Helper\DcaHelper::addPalette('default', $arrFields, $strFileName);



/**
 * Fields
 */


// Width
\IIDO\BasicBundle\Helper\DcaHelper::addUnitField( $fieldPrefix . 'pageWidth', $strFileName);
\IIDO\BasicBundle\Helper\DcaHelper::addUnitField( $fieldPrefix . 'pageContentWidth', $strFileName);



// Page Loader
\IIDO\BasicBundle\Helper\DcaHelper::addColorField( $fieldPrefix . 'pageLoaderBackgroundColor', $strFileName, array('disableSelect'=>true));
\IIDO\BasicBundle\Helper\DcaHelper::addSelectField( $fieldPrefix . 'pageLoaderStyle', $strFileName);



// Colors

\IIDO\BasicBundle\Helper\DcaHelper::addNewField( $fieldPrefix . 'colors', $strFileName, "multiColumnWizard", "", array
(
    'columnFields' => array
    (
        'color'     => array
        (
            'label'     => &$GLOBALS['TL_LANG'][ $strFileName ]['fields']['colors']['color'],
            'inputType' => 'text',
            'eval'      => array
            (
                'maxlength'         => 6,
                'isHexColor'        => TRUE,
                'decodeEntities'    => TRUE
            )
        ),

        'name'      => array
        (
            'label'     => &$GLOBALS['TL_LANG'][ $strFileName ]['fields']['colors']['name'],
            'inputType' => 'text',
        ),

        'variable'  => array
        (
            'label'     => &$GLOBALS['TL_LANG'][ $strFileName ]['fields']['colors']['variable'],
            'inputType' => 'text',
        ),

        'rootPage'  => array
        (
            'label'     => &$GLOBALS['TL_LANG'][ $strFileName ]['fields']['colors']['rootPage'],
            'inputType' => 'pageTree',
            'foreignKey' => 'tl_page.title',
            'eval'      => array('fieldType'=>'radio')
        )
    )
));

\IIDO\BasicBundle\Helper\DcaHelper::addColorField( $fieldPrefix . 'colorPrimary', $strFileName, array('disableSelect'=>true), 'space-top');
\IIDO\BasicBundle\Helper\DcaHelper::addColorField( $fieldPrefix . 'colorSecondary', $strFileName, array('disableSelect'=>true), 'space-top');



// Buttons
\IIDO\BasicBundle\Helper\DcaHelper::addColorField( $fieldPrefix . 'buttonColorPrimary', $strFileName, array('disableSelect'=>true));
\IIDO\BasicBundle\Helper\DcaHelper::addColorField( $fieldPrefix . 'buttonFontColorPrimary', $strFileName, array('disableSelect'=>true));
\IIDO\BasicBundle\Helper\DcaHelper::addColorField( $fieldPrefix . 'buttonHoverColorPrimary', $strFileName, array('disableSelect'=>true));
\IIDO\BasicBundle\Helper\DcaHelper::addColorField( $fieldPrefix . 'buttonHoverFontColorPrimary', $strFileName, array('disableSelect'=>true));

\IIDO\BasicBundle\Helper\DcaHelper::addColorField( $fieldPrefix . 'buttonColorSecondary', $strFileName, array('disableSelect'=>true), 'space-top');
\IIDO\BasicBundle\Helper\DcaHelper::addColorField( $fieldPrefix . 'buttonFontColorSecondary', $strFileName, array('disableSelect'=>true), 'space-top');
\IIDO\BasicBundle\Helper\DcaHelper::addColorField( $fieldPrefix . 'buttonHoverColorSecondary', $strFileName, array('disableSelect'=>true));
\IIDO\BasicBundle\Helper\DcaHelper::addColorField( $fieldPrefix . 'buttonHoverFontColorSecondary', $strFileName, array('disableSelect'=>true));