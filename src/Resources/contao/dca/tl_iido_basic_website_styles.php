<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

\Controller::loadLanguageFile("tl_content");

$strFileName    = \IIDO\BasicBundle\Config\BundleConfig::getFileTable( __FILE__ );
$fieldPrefix    = \IIDO\BasicBundle\Config\BundleConfig::getTableFieldPrefix();
$tableClass     = \IIDO\BasicBundle\Config\BundleConfig::getTableClass( $strFileName );

\IIDO\BasicBundle\Helper\DcaHelper::createNewTable( $strFileName, true );


$GLOBALS['TL_DCA'][ $strFileName ]['config']['onsubmit_callback'] = array
(
    array( $tableClass, 'saveStyleseditor')
);



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
        $fieldPrefix . 'colorTertiary',
        $fieldPrefix . 'colorQuaternary',
    ],

    'headline_legend:hide' =>
    [
        $fieldPrefix . 'headlineStyles'
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
        $fieldPrefix . 'buttonHoverFontColorSecondary',

        $fieldPrefix . 'buttonColorTertiary',
        $fieldPrefix . 'buttonFontColorTertiary',
        $fieldPrefix . 'buttonHoverColorTertiary',
        $fieldPrefix . 'buttonHoverFontColorTertiary',

        $fieldPrefix . 'buttonColorQuaternary',
        $fieldPrefix . 'buttonFontColorQuaternary',
        $fieldPrefix . 'buttonHoverColorQuaternary',
        $fieldPrefix . 'buttonHoverFontColorQuaternary'
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
\IIDO\BasicBundle\Helper\DcaHelper::addColorField( $fieldPrefix . 'colorTertiary', $strFileName, array('disableSelect'=>true));
\IIDO\BasicBundle\Helper\DcaHelper::addColorField( $fieldPrefix . 'colorQuaternary', $strFileName, array('disableSelect'=>true));



// Headline
//\IIDO\BasicBundle\Helper\DcaHelper::addNewField($fieldPrefix . 'headlineStyles', $strFileName, "fieldpalette", "", array(), "", array
//(
//    'fieldpalette' => array
//    (
//        'config' => array
//        (
//            'hidePublished' => false
//        ),
//
//        'list' => array
//        (
//            'label' => array
//            (
//                'fields' => array("name"),
//                'format' => '%s'
//            ),
//            'viewMode' => 1
//        ),
//
//        'palettes' => array
//        (
//            'default' => 'name'
//        ),
//
//        'fields' => array
//        (
//            'name' => array
//            (
//                'label'         => array('NAME', ''),
//                'exclude'       => true,
//                'inputType'     => true,
//                'eval'          => array('maxlength'=> 255, 'tl_class'=>'long'),
//                'sql'           => "varchar(255) NOT NULL default ''"
//            )
//        )
//    )
//));
IIDO\BasicBundle\Helper\DcaHelper::addNewField( $fieldPrefix . 'headlineStyles', $strFileName, "multiColumnWizard", "", array
(
    'columnFields' => array
    (
        'id'        => array
        (
            'label'     => &$GLOBALS['TL_LANG'][ $strFileName ]['fields']['headlineStyles']['id'],
            'inputType' => 'text',
            'style'     => 'width:50px;'
        ),

        'name'      => array
        (
            'label'     => &$GLOBALS['TL_LANG'][ $strFileName ]['fields']['headlineStyles']['name'],
            'inputType' => 'text',
//            'save_callback' => array(array($tableClass, 'checkHeadlineStylesNameIfUnique')),
            'eval'      => array
            (
                'style'     => 'width:160px;'
            )
        ),

        'tagClasses'  => array
        (
            'label'     => &$GLOBALS['TL_LANG'][ $strFileName ]['fields']['headlineStyles']['tagClasses'],
            'inputType' => 'text',
            'eval'      => array
            (
                'style'     => 'width:180px;'
            )
        ),

        'elementClasses'  => array
        (
            'label'     => &$GLOBALS['TL_LANG'][ $strFileName ]['fields']['headlineStyles']['elementClasses'],
            'inputType' => 'text',
            'eval'      => array
            (
                'style'     => 'width:180px;'
            )
        ),

        'color'  => array
        (
            'label'     => &$GLOBALS['TL_LANG'][ $strFileName ]['fields']['headlineStyles']['color'],
            'inputType' => 'text',
            'eval'      => array
            (
//                'colorpicker'       => TRUE,

                'maxlength'         => 6,
                'isHexColor'        => TRUE,
                'decodeEntities'    => TRUE,

                'tl_class'          => 'field-color'
            )
        ),

        'size'  => array
        (
            'label'     => &$GLOBALS['TL_LANG'][ $strFileName ]['fields']['headlineStyles']['size'],
            'inputType' => 'inputUnit',
            'options'   => $GLOBALS['TL_CSS_UNITS'],
            'eval'      => array
            (
                'includeBlankOption'    => TRUE,
                'rgxp'                  => 'digit_auto_inherit',
                'maxlength'             => 20,

                'tl_class'          => 'field-size'
            )
        ),

        'floating' => array
        (
            'label'     => &$GLOBALS['TL_LANG'][ $strFileName ]['fields']['headlineStyles']['floating'],
            'default'   =>'header_left',
            'inputType' => 'radioTable',
            'options'   => $GLOBALS['TL_LANG']['tl_content']['options']['headlineFloating'],
            'eval'      => array
            (
                'cols'      => 3
            ),
        )
    )
));



// Buttons
\IIDO\BasicBundle\Helper\DcaHelper::addColorField( $fieldPrefix . 'buttonColorPrimary', $strFileName, array('disableSelect'=>true));
\IIDO\BasicBundle\Helper\DcaHelper::addColorField( $fieldPrefix . 'buttonFontColorPrimary', $strFileName, array('disableSelect'=>true));
\IIDO\BasicBundle\Helper\DcaHelper::addColorField( $fieldPrefix . 'buttonHoverColorPrimary', $strFileName, array('disableSelect'=>true));
\IIDO\BasicBundle\Helper\DcaHelper::addColorField( $fieldPrefix . 'buttonHoverFontColorPrimary', $strFileName, array('disableSelect'=>true));

\IIDO\BasicBundle\Helper\DcaHelper::addColorField( $fieldPrefix . 'buttonColorSecondary', $strFileName, array('disableSelect'=>true), 'space-top');
\IIDO\BasicBundle\Helper\DcaHelper::addColorField( $fieldPrefix . 'buttonFontColorSecondary', $strFileName, array('disableSelect'=>true), 'space-top');
\IIDO\BasicBundle\Helper\DcaHelper::addColorField( $fieldPrefix . 'buttonHoverColorSecondary', $strFileName, array('disableSelect'=>true));
\IIDO\BasicBundle\Helper\DcaHelper::addColorField( $fieldPrefix . 'buttonHoverFontColorSecondary', $strFileName, array('disableSelect'=>true));

\IIDO\BasicBundle\Helper\DcaHelper::addColorField( $fieldPrefix . 'buttonColorTertiary', $strFileName, array('disableSelect'=>true), 'space-top');
\IIDO\BasicBundle\Helper\DcaHelper::addColorField( $fieldPrefix . 'buttonFontColorTertiary', $strFileName, array('disableSelect'=>true), 'space-top');
\IIDO\BasicBundle\Helper\DcaHelper::addColorField( $fieldPrefix . 'buttonHoverColorTertiary', $strFileName, array('disableSelect'=>true));
\IIDO\BasicBundle\Helper\DcaHelper::addColorField( $fieldPrefix . 'buttonHoverFontColorTertiary', $strFileName, array('disableSelect'=>true));

\IIDO\BasicBundle\Helper\DcaHelper::addColorField( $fieldPrefix . 'buttonColorQuaternary', $strFileName, array('disableSelect'=>true), 'space-top');
\IIDO\BasicBundle\Helper\DcaHelper::addColorField( $fieldPrefix . 'buttonFontColorQuaternary', $strFileName, array('disableSelect'=>true), 'space-top');
\IIDO\BasicBundle\Helper\DcaHelper::addColorField( $fieldPrefix . 'buttonHoverColorQuaternary', $strFileName, array('disableSelect'=>true));
\IIDO\BasicBundle\Helper\DcaHelper::addColorField( $fieldPrefix . 'buttonHoverFontColorQuaternary', $strFileName, array('disableSelect'=>true));