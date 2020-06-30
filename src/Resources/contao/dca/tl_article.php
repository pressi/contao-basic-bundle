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
    }

    if( in_array('bg_gradient', $arrFields) )
    {
        $arrDesign[] = 'gradientColors';
        $arrDesign[] = 'gradientAngle';
    }

    if( in_array('bg_image', $arrFields) )
    {
        $arrDesign[] = 'bgImage';
        $arrDesign[] = 'bgPosition';
        $arrDesign[] = 'bgRepeat';
        $arrDesign[] = 'bgAttachment';
        $arrDesign[] = 'bgSize';
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


    $paletteManipulator->applyToPalette('default', $strArticleFileName);
}



/**
 * Subpalettes
 */

//Contao\CoreBundle\DataContainer\PaletteManipulator::create()
//    ->applyToSubpalette();

$objArticleTable->addSubpalette("addAnimation", "animationType,animateRun,animationWait,animationOffset");



/**
 * Fields
 */


// DESIGN

\IIDO\BasicBundle\Dca\Field::create('bgColor', 'color')->addToTable( $objArticleTable );

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
    ->addDefault('default')
    ->addToTable( $objArticleTable );



// GLOBAL ELEMENTS

//\IIDO\BasicBundle\Dca\Field::create('articleType', 'select')
//    ->addDefault('default')
//    ->addToTable( $objArticleTable );



$objArticleTable->updateDca();