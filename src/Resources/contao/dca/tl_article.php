<?php

$strArticleFileName = \IIDO\BasicBundle\Config\BundleConfig::getFileTable( __FILE__ );
$objArticleTable    = new \IIDO\BasicBundle\Dca\ExistTable( $strArticleFileName );

$objArticleTable->setTableListener( 'iido.basic.dca.article' );



/**
 * Palettes
 */

Contao\CoreBundle\DataContainer\PaletteManipulator::create()
    ->addLegend('design_legend', 'layout_legend', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER)
    ->addLegend('dimensions_legend', 'design_legend', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_BEFORE)
    ->addLegend('animation_legend', 'expert_legend', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER, true)

    ->addField(['bgColor', 'gradientColors', 'gradientAngle', 'bgImage', 'bgPosition', 'bgRepeat', 'bgAttachment', 'bgSize'], 'design_legend', \Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_PREPEND)
    ->addField(['width', 'height'], 'dimensions_legend', \Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_PREPEND)

    ->addField('addAnimation', 'animation_legend', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_PREPEND)

    ->applyToPalette('default', $strArticleFileName);



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



$objArticleTable->updateDca();