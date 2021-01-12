<?php

$strArticleFileName = \IIDO\BasicBundle\Config\BundleConfig::getFileTable( __FILE__ );
$objArticleTable    = new \IIDO\BasicBundle\Dca\ExistTable( $strArticleFileName );

$objArticleTable->setTableListener( 'iido.basic.dca.article' );

//$objConfig  = System::getContainer()->get('iido.basic.config');
$objArticle = false;

if( Input::get('act') === 'edit' )
{
    $objArticle = \Contao\ArticleModel::findByPk( Input::get('id') );
}



/**
 * Palettes
 */
$arrBGColor = [];

$paletteManipulator = Contao\CoreBundle\DataContainer\PaletteManipulator::create();

//if( $objConfig->get('includeArticleFields') )
if( \IIDO\BasicBundle\Config\IIDOConfig::get('includeArticleFields') )
{
//    $arrFields      = StringUtil::deserialize( $objConfig->get('articleFields'), true);
    $arrFields      = StringUtil::deserialize( \IIDO\BasicBundle\Config\IIDOConfig::get('articleFields'), true);
    $arrDesign      = [];
    $arrDimensions  = [];

    // Design

    if( in_array('bg_color', $arrFields) )
    {
        $arrDesign[] = 'bgColor';
        $arrDesign[] = 'addOwnBGColor';
    }

//    if( in_array('bg_gradient', $arrFields) )
//    {
//        $arrDesign[] = 'gradientColors';
//        $arrDesign[] = 'gradientAngle';
//    }

    if( in_array('bg_image', $arrFields) )
    {
        $arrDesign[] = 'bgImage';
        $arrDesign[] = 'extBGImage';

//        $arrDesign[] = 'bgPosition';
//        $arrDesign[] = 'bgRepeat';
//        $arrDesign[] = 'bgAttachment';
//        $arrDesign[] = 'bgSize';
    }

    if( count($arrDesign) )
    {
        $paletteManipulator->addLegend('design_legend', 'layout_legend', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER);
        $paletteManipulator->addField($arrDesign, 'design_legend', \Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_PREPEND);
    }


    // Dimensions

    if( in_array('width', $arrFields) )
    {
        $arrDimensions[] = 'width';
    }

    if( in_array('height', $arrFields) )
    {
        $arrDimensions[] = 'height';
    }

    if( count($arrDimensions) )
    {
        $paletteManipulator->addLegend('dimensions_legend', 'layout_legend', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER);
        $paletteManipulator->addField($arrDimensions, 'dimensions_legend', \Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_PREPEND);
    }


    // Animation

    if( in_array('animation', $arrFields) )
    {
        $paletteManipulator->addLegend('animation_legend', 'expert_legend', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER, true);
        $paletteManipulator->addField('addAnimation', 'animation_legend', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_PREPEND);
    }


    // Title
    if( in_array('frontendTitle', $arrFields) )
    {
        $paletteManipulator->addField('frontendTitle', 'author', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER);
    }

    if( in_array('navTitle', $arrFields) )
    {
        $parentField = 'author';

        if( in_array('frontendTitle', $arrFields) )
        {
            $parentField = 'frontendTitle';
        }

        $paletteManipulator->addField('navTitle', $parentField, Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER);
    }


    // Type

//    if( in_array('type', $arrFields) )
//    {
        if( $objArticle )
        {
            $objArticlePage = \Contao\PageModel::findByPk( $objArticle->pid );

            if( $objArticlePage )
            {
                if( $objArticlePage->type === 'global_element' )
                {
                    $paletteManipulator->addField('articleType', 'title', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER);
                }
            }
        }
//    }

    $paletteManipulator->addField('hideInNav', 'cssID', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER);


    $paletteManipulator->applyToPalette('default', $strArticleFileName);

    $arrBGColor = ['ownBGColor'];

    if( in_array('bg_gradient', $arrFields) )
    {
        $arrBGColor[] = 'gradientColors';
        $arrBGColor[] = 'gradientAngle';
    }

    $arrArticleTypeHeader =
    [
        'title'     => ['title', 'articleType', 'alias'],
        'layout'    => ['inColumn', 'layout'],
    ];

    if( count($arrDesign) )
    {
        $arrArticleTypeHeader['design'] = $arrDesign;
    }

    $arrArticleTypeHeader['template'] = ['customTpl'];
    $arrArticleTypeHeader['expert'] = ['cssStyleSelector', 'cssID'];

//    if( in_array('animation', $arrFields) )
//    {
//        $arrArticleTypeHeader['animation'] = ['addAnimation'];
//    }

    $arrArticleTypeHeader['publish'] = ['published'];

    $objArticleTable->addPalette('header', $arrArticleTypeHeader);
}



/**
 * Subpalettes
 */

//Contao\CoreBundle\DataContainer\PaletteManipulator::create()
//    ->applyToSubpalette();

$objArticleTable->addSubpalette("addAnimation", "animationType,animateRun,animationWait,animationOffset");
$objArticleTable->addSubpalette("extBGImage", "bgPosition,bgRepeat,bgAttachment,bgSize");
$objArticleTable->addSubpalette("addOwnBGColor", implode(',', $arrBGColor));



/**
 * Fields
 */

\IIDO\BasicBundle\Dca\Field::create('hideInNav', 'checkbox')
    ->addToTable( $objArticleTable );

\IIDO\BasicBundle\Dca\Field::create('frontendTitle')
    ->addToTable( $objArticleTable );

\IIDO\BasicBundle\Dca\Field::create('navTitle')
    ->addToTable( $objArticleTable );



// DESIGN

\IIDO\BasicBundle\Dca\Field::create('bgColor', 'select')
    ->addEval('includeBlankOption', true)
    ->addEval('tl_class', 'w50', true)
    ->addToTable( $objArticleTable );


\IIDO\BasicBundle\Dca\Field::create('ownBGColor', 'color')->addToTable( $objArticleTable );

\IIDO\BasicBundle\Dca\Field::create('addOwnBGColor', 'checkbox')
    ->setAsSelector()
    ->addEval('tl_class', 'w50 m12', true)
    ->addToTable( $objArticleTable );


\IIDO\BasicBundle\Dca\Field::create('gradientAngle')
    ->addEval('maxlength', 32)
    ->addToTable( $objArticleTable );

\IIDO\BasicBundle\Dca\Field::create('gradientColors')
    ->addEval('maxlength', 128)
    ->addEval('multiple', true)
    ->addEval('size', 4)
    ->addEval('decodeEntities', true)
    ->addEval('tl_class', 'clr')
    ->addToTable( $objArticleTable );




\IIDO\BasicBundle\Dca\Field::create('bgImage', 'image')
    ->addEval('tl_class', 'clr hauto w50')
    ->addToTable( $objArticleTable );

\IIDO\BasicBundle\Dca\Field::create('extBGImage', 'checkbox')
    ->setAsSelector()
    ->addEval('tl_class', 'w50 m12', true)
    ->addToTable( $objArticleTable );


\IIDO\BasicBundle\Dca\Field::create('bgMode', 'select')
    ->addEval('includeBlankOption', true)
    ->addEval('tl_class', 'w25', true)
    ->addToTable( $objArticleTable );

\IIDO\BasicBundle\Dca\Field::create('bgPosition', 'select')
    ->addEval('includeBlankOption', true)
    ->addEval('tl_class', 'clr')
    ->addToTable( $objArticleTable );

\IIDO\BasicBundle\Dca\Field::create('bgRepeat', 'select')
    ->addEval('includeBlankOption', true)
    ->addToTable( $objArticleTable );

\IIDO\BasicBundle\Dca\Field::create('bgAttachment', 'select')
    ->addEval('includeBlankOption', true)
    ->addToTable( $objArticleTable );

\IIDO\BasicBundle\Dca\Field::create('bgSize', 'imageSize')
    ->addEval('includeBlankOption', true)
    ->addEval('tl_class', 'bg-size')
    ->addToTable( $objArticleTable );



// DIMENSIONS

//\IIDO\BasicBundle\Dca\Field::create('fullWidth', 'checkbox')->addToTable( $objArticleTable );
//\IIDO\BasicBundle\Dca\Field::create('fullHeight', 'checkbox')->addToTable( $objArticleTable );

\IIDO\BasicBundle\Dca\Field::create('width', 'select')
    ->addEval('includeBlankOption', true)
    ->addToTable( $objArticleTable );

\IIDO\BasicBundle\Dca\Field::create('height', 'select')
    ->addEval('includeBlankOption', true)
    ->addToTable( $objArticleTable );



// ANIMATION

\IIDO\BasicBundle\Dca\Field::create('addAnimation', 'checkbox')
    ->setAsSelector()
    ->setLangTable( 'content' )
    ->addToTable( $objArticleTable );

\IIDO\BasicBundle\Dca\Field::create('animationType', 'select')
    ->addEval('includeBlankOption', true)
    ->setLangTable( 'content' )
    ->addToTable( $objArticleTable );

\IIDO\BasicBundle\Dca\Field::create('animationOffset')
    ->addEval('maxlength', 80)
    ->setLangTable( 'content' )
    ->addToTable( $objArticleTable );

\IIDO\BasicBundle\Dca\Field::create('animationWait', 'checkbox')
    ->setLangTable( 'content' )
    ->addToTable( $objArticleTable );

\IIDO\BasicBundle\Dca\Field::create('animateRun', 'select')
    ->setLangTable( 'content' )
    ->addToTable( $objArticleTable );



// TYPE

\IIDO\BasicBundle\Dca\Field::create('articleType', 'select')
    ->setSelector()
    ->addDefault('default')
    ->addEval('tl_class', 'w50', true)
    ->addToTable( $objArticleTable );



// LAYOUT

\IIDO\BasicBundle\Dca\Field::create('layout', 'layoutWizard')
    ->addDefault('top')
    ->addEval('helpwizard', true)
    ->addEval('imagePath', 'bundles/iidobasic/images/layout/header/')
    ->addEval('tl_class', 'clr')
    ->addSQL("varchar(8) NOT NULL default 'top'")
    ->addToTable( $objArticleTable );

//if( $objArticle->articleType === 'footer' )
//{
//
//}



// GLOBAL ELEMENTS

//\IIDO\BasicBundle\Dca\Field::create('articleType', 'select')
//    ->addDefault('default')
//    ->addToTable( $objArticleTable );



$objArticleTable->updateDca();

//echo "<pre>"; print_r( $GLOBALS['TL_DCA'][ $strArticleFileName ] ); exit;