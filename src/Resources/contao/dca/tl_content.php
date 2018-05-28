<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

System::loadLanguageFile("tl_module");
$this->loadDataContainer("tl_module");

$objContent     = false;
$boxElement     = false;

$db             = \Database::getInstance();
$do             = Input::get("do");
$act            = Input::get("act");
$id             = (int) Input::get("id");
$theme          = \Backend::getTheme();

$strFileName    = \IIDO\BasicBundle\Config\BundleConfig::getFileTable( __FILE__ );
$strTableClass  = \IIDO\BasicBundle\Config\BundleConfig::getTableClass( $strFileName );

//if ($do == 'iidoPlaceholder')
//{
//    $GLOBALS['TL_DCA']['tl_content']['config']['ptable']                    = 'tl_iido_placeholder';
//    $GLOBALS['TL_DCA']['tl_content']['list']['sorting']['headerFields']     = array('name', 'alias');
//}

if( $act == "edit" && is_numeric($id) && $id > 0 )
{
    $objContent = $db->prepare("SELECT * FROM " . $strFileName . " WHERE id=?")->limit(1)->execute( $id );

    if( $objContent && $objContent->numRows > 0 )
    {
        $objContent = $objContent->first();
    }
}



/**
 * Configuration
 */

//$GLOBALS['TL_DCA']['tl_content']['config']['onload_callback'][]               = array('IIDO\Customize\Access\ContentElements', 'filterContentElements');



/**
 * List
 */

$GLOBALS['TL_DCA']['tl_content']['list']['sorting']['child_record_callback'] = array( $strTableClass, 'addContentTitle');



/**
 * Selectors
 */

//$GLOBALS['TL_DCA']['tl_content']['palettes']['__selector__'][]          = 'showAsButton';
//$GLOBALS['TL_DCA']['tl_content']['palettes']['__selector__'][]          = 'buttonAddon';

//$GLOBALS['TL_DCA']['tl_content']['palettes']['__selector__'][]          = "elementIsBox";
//$GLOBALS['TL_DCA']['tl_content']['palettes']['__selector__'][]          = 'addOrnament';
//$GLOBALS['TL_DCA']['tl_content']['palettes']['__selector__'][]          = 'bgOnOtherColumn';
//$GLOBALS['TL_DCA']['tl_content']['palettes']['__selector__'][]          = "usedTime";
//$GLOBALS['TL_DCA']['tl_content']['palettes']['__selector__'][]          = 'addTopHeadline';
//$GLOBALS['TL_DCA']['tl_content']['palettes']['__selector__'][]          = 'addSubHeadline';
//$GLOBALS['TL_DCA']['tl_content']['palettes']['__selector__'][]          = 'onlyServiceLink';
//$GLOBALS['TL_DCA']['tl_content']['palettes']['__selector__'][]          = 'serviceLinkAfter';
//$GLOBALS['TL_DCA']['tl_content']['palettes']['__selector__'][]          = 'addHeadlineLink';
//$GLOBALS['TL_DCA']['tl_content']['palettes']['__selector__'][]          = "addBoxText";
//$GLOBALS['TL_DCA']['tl_content']['palettes']['__selector__'][]          = "addGalleryText";
//$GLOBALS['TL_DCA']['tl_content']['palettes']['__selector__'][]          = 'blockShowMode';

//$GLOBALS['TL_DCA']['tl_content']['palettes']['__selector__'][]          = 'addBoxLinkButton';


if( $objContent && $objContent->type == "iidoCustomize_newsGalleryDetail" )
{
//    $GLOBALS['TL_DCA']['tl_content']['palettes']['__selector__'][]      = 'news_archives';
//    $GLOBALS['TL_DCA']['tl_content']['subpalettes']['news_archives']    = 'news_item';
}



/**
 * Palettes
 */

\IIDO\BasicBundle\Helper\DcaHelper::addPalette('iido_wrapperStart', '', $strFileName);
\IIDO\BasicBundle\Helper\DcaHelper::addPalette('iido_wrapperSeparator', '', $strFileName);
\IIDO\BasicBundle\Helper\DcaHelper::addPalette('iido_wrapperStop', '', $strFileName);

\IIDO\BasicBundle\Helper\DcaHelper::addPalette('iido_imprint', '{imprint_legend},imprintCompanyName,imprintSubline,imprintStreet,imprintPostal,imprintCity,imprintPhone,imprintFax,imprintEmail,imprintWeb,addImprintContactLabel;{imprintAdd_legend},imprintMitglied,imprintBerufsrecht,imprintBehoerde,imprintBeruf,imprintCountry,imprintObjectOfTheCompany,imprintVATnumber;{imprintBigAdd_legend},imprintCompanyWording,imprintManagingDirector,imprintSection,imprintOccupationalGroup,imprintCompanyRegister,imprintFirmengericht,imprintAddText;{imprintFields_legend},imprintText,privacyPolicyText;{imprintImageCopyright_legend},imprintImageCopyrights;', $strFileName);
\IIDO\BasicBundle\Helper\DcaHelper::addPalette('newslist', '{config_legend},news_archives,numberOfItems,news_featured,perPage,skipFirst;{template_legend:hide},news_metaFields,news_template,customTpl;{image_legend:hide},imgSize;', $strFileName);

\IIDO\BasicBundle\Helper\DcaHelper::addPalette('iido_navigation', '{config_legend},navModule,navPages,navigationTpl;', $strFileName);
\IIDO\BasicBundle\Helper\DcaHelper::addPalette('iido_weather', '{config_legend},addIcon,addSnow,addTemperature,snowUrl;', $strFileName);

if( \IIDO\BasicBundle\Config\BundleConfig::isActiveBundle('codefog/contao-news_categories') )
{
    \IIDO\BasicBundle\Helper\DcaHelper::copyPaletteFromTable('newscategories', 'tl_module', 'newscategories', $strFileName);
}

//$defaultPaletteStart    = '{type_legend},type,headline,subHeadline;';
//$defaultPaletteEnd      = '{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space;{invisible_legend:hide},invisible,start,stop;';


//$GLOBALS['TL_DCA']['tl_content']['palettes']['iido_navigation']         = $defaultPaletteStart . '{config_legend},navModule,navPages,navigationTpl;' . $defaultPaletteEnd;
//$GLOBALS['TL_DCA']['tl_content']['palettes']['iido_weather']            = $defaultPaletteStart . '{config_legend},addIcon,addSnow,addTemperature,snowUrl;' . $defaultPaletteEnd;

//$GLOBALS['TL_DCA']['tl_content']['palettes']['iidoCustomize_divider']           = '{type_legend},type;{divide_legend},dividerSize,dividerColor,dividerStyle,addOrnament;' . $defaultPaletteEnd;
//$GLOBALS['TL_DCA']['tl_content']['palettes']['iidoCustomize_ticker']            = '{type_legend},type;{ticker_legend},tickerMode,usedTime,tickerDate,tickerShowMode;{text_legend},textBefore,textAfter;' . $defaultPaletteEnd;
//$GLOBALS['TL_DCA']['tl_content']['palettes']['iidoCustomize_serviceCalendar']   = $defaultPaletteStart . '{interval_legend},serviceInterval,serviceIntervalLength,startDate,startTime,onlyServiceLink;{text_legend},textBefore,textAfter,serviceLinkAfter;' . $defaultPaletteEnd;

//$GLOBALS['TL_DCA']['tl_content']['palettes']['iidoCustomize_backgroundImage']   = $defaultPaletteStart . '{source_legend},singleSRC,bgOnOtherColumn;{config_legend},bgMode,bgAttachment,bgRepeat,bgPosition,ieFallback,inheritBackground;' . $defaultPaletteEnd;
//$GLOBALS['TL_DCA']['tl_content']['palettes']['iidoCustomize_randomImage']       = $defaultPaletteStart . '{config_legend},imgSize,imageUrl,useCaption,fullsize;{source_legend},multiSRC;' . $defaultPaletteEnd;

//$GLOBALS['TL_DCA']['tl_content']['palettes']['iidoCustomize_websiteStandards']  = $defaultPaletteStart . '{fieldsBox_legend},blockShowMode;' . $defaultPaletteEnd;

//$GLOBALS['TL_DCA']['tl_content']['palettes']['iidoCustomize_newsGalleryDetail'] = $defaultPaletteStart . '{config_legend},news_archive,news_item;{image_legend:hide},imgSize,imagemargin,perRow,fullsize,perPage,numberOfItems;{template_legend:hide},galleryTpl,customTpl;' . $defaultPaletteEnd;

//$GLOBALS['TL_DCA']['tl_content']['palettes']['boxStart']                        = $defaultPaletteStart . '{box_legend},boxWidth,boxHeight,boxBackgroundColor;{slider_legend},sliderDelay,sliderSpeed,sliderStartSlide,sliderContinuous;' . $defaultPaletteEnd;
//$GLOBALS['TL_DCA']['tl_content']['palettes']['boxStop']                         = $GLOBALS['TL_DCA']['tl_content']['palettes']['sliderStop'];


foreach($GLOBALS['TL_DCA']['tl_content']['palettes'] as $strPalette => $strFields)
{
    if( $strPalette === "__selector__" )
    {
        continue;
    }

//    $prefix = 'type';
//
//    if( $strPalette == "dlh_googlemaps" )
//    {
//        $prefix = 'title';
//    }
//
    if( !is_array($strFields) )
    {
//        $headlineFields = 'addTopHeadline,headline,headlineFloating,addHeadlineBorder,addHeadlineLink,addSubHeadline;';
        $headlineFields = 'addTopHeadline,headline,headlineFloating,subHeadline;';
        $strFields      = str_replace( 'headline;', $headlineFields, $strFields );

//        if( !preg_match('/^box/', $strPalette) )
//        {
//            $strFields = preg_replace( '/\{' . $prefix . '_legend([a-z:]{0,})\},([a-zA-Z0-9_\-,]{0,});/', '{' . $prefix . '_legend$1},$2;{box_legend},elementIsBox;', $strFields );
//        }

//        if( $objContent && $objContent->type === "gallery" )
//        {
//            $strFields = str_replace('{template_legend', '{text_legend},addGalleryText;{template_legend', $strFields);
//        }

//		$strFields = $strFields . ';{add_legend},insertClearAfter;';

        if( !in_array($strPalette, ['boxStop','accordionStop','sliderStop','html','code','alias','article']) )
        {
            $strFields = $strFields . ';{animate_legend},addAnimation;';
        }

        if( $strPalette === "gallery" )
        {
            $strFields = preg_replace('/{template_legend/', '{text_legend},text;{template_legend', $strFields);
        }
    }

    $strFields = preg_replace('/{expert_legend/', '{position_legend},position,positionMargin,positionFixed;{expert_legend', $strFields);
    $strFields = preg_replace('/,invisible/', ',invisible,hideOnMobile,showOnMobile', $strFields);

    $GLOBALS['TL_DCA']['tl_content']['palettes'][ $strPalette ] = '{intern_legend:hide},internName;' . $strFields;
}


\IIDO\BasicBundle\Helper\DcaHelper::replacePaletteFields('hyperlink', ',rel', ',rel,showAsButton', $strFileName);
//$GLOBALS['TL_DCA']['tl_content']['palettes']['hyperlink'] = str_replace(',rel', ',rel,showAsButton', $GLOBALS['TL_DCA']['tl_content']['palettes']['hyperlink']);



/**
 * Subpalettes
 */

\IIDO\BasicBundle\Helper\DcaHelper::addSubpalette('showAsButton', 'buttonStyle,buttonType,buttonAddon,buttonLinkMode', $strFileName);
\IIDO\BasicBundle\Helper\DcaHelper::addSubpalette('buttonAddon_arrow', 'buttonAddonPosition,buttonAddonArrow', $strFileName);
\IIDO\BasicBundle\Helper\DcaHelper::addSubpalette('buttonAddon_icon', 'buttonAddonPosition,buttonAddonIcon', $strFileName);
//$GLOBALS['TL_DCA']['tl_content']['subpalettes']['showAsButton']         = 'buttonStyle,buttonType,buttonAddon,buttonLinkMode';

//$GLOBALS['TL_DCA']['tl_content']['subpalettes']['buttonAddon_arrow']     = 'buttonAddonPosition,buttonAddonArrow';
//$GLOBALS['TL_DCA']['tl_content']['subpalettes']['buttonAddon_icon']      = 'buttonAddonPosition,buttonAddonIcon';



//$GLOBALS['TL_DCA']['tl_content']['subpalettes']['elementIsBox']         = "boxWidth,boxHeight,boxLink,boxLinkText,boxBackgroundColor";
//$GLOBALS['TL_DCA']['tl_content']['subpalettes']['usedTime']             = "tickerTime";
//$GLOBALS['TL_DCA']['tl_content']['subpalettes']['addOrnament']          = 'ornamentLight,onlyOrnament,ornament,addOrnamentLinie';
//$GLOBALS['TL_DCA']['tl_content']['subpalettes']['bgOnOtherColumn']      = 'bgToArticle';
//$GLOBALS['TL_DCA']['tl_content']['subpalettes']['addTopHeadline']       = 'topHeadline,topHeadlineFloating,addTopHeadlineBorder';
//$GLOBALS['TL_DCA']['tl_content']['subpalettes']['addSubHeadline']       = 'subHeadline,subHeadlineFloating,addSubHeadlineBorder';
//$GLOBALS['TL_DCA']['tl_content']['subpalettes']['addHeadlineLink']      = 'headlineLink';
//$GLOBALS['TL_DCA']['tl_content']['subpalettes']['onlyServiceLink']      = 'linkPage';
//$GLOBALS['TL_DCA']['tl_content']['subpalettes']['serviceLinkAfter']     = 'url,titleText';
//$GLOBALS['TL_DCA']['tl_content']['subpalettes']['addBoxText']           = "text";
//$GLOBALS['TL_DCA']['tl_content']['subpalettes']['addGalleryText']       = "text";
//$GLOBALS['TL_DCA']['tl_content']['subpalettes']['addBoxLinkButton']     = 'boxLinkButtonText';
//
//$GLOBALS['TL_DCA']['tl_content']['subpalettes']['blockShowMode_field']  = 'websiteField';
//$GLOBALS['TL_DCA']['tl_content']['subpalettes']['blockShowMode_fields'] = 'addressBlock';

\IIDO\BasicBundle\Helper\DcaHelper::addSubpalette("addAnimation", "animationType,animateRun,animationWait,animationOffset", $strFileName);
\IIDO\BasicBundle\Helper\DcaHelper::addSubpalette("addSnow", "snowDepth,snowUnit,snowSubline", $strFileName);
\IIDO\BasicBundle\Helper\DcaHelper::addSubpalette("addTopHeadline", "topHeadline", $strFileName);



foreach($GLOBALS['TL_DCA']['tl_content']['subpalettes'] as $strSubpalette => $strSubfields)
{
    $strSubfields = str_replace(',floating', ',floating,addImageBorder,headlineImagePosition', $strSubfields);

    $GLOBALS['TL_DCA']['tl_content']['subpalettes'][ $strSubpalette ] = $strSubfields;
}

//if( $objContent && $objContent->type == "image" )
//{
//    $GLOBALS['TL_DCA']['tl_content']['subpalettes']['elementIsBox'] = $GLOBALS['TL_DCA']['tl_content']['subpalettes']['elementIsBox'] . ",addBoxText";
//}



/**
 * Operations
 */

//$GLOBALS['TL_DCA']['tl_content']['list']['operations']['edit']['button_callback']		= array('IIDO\Customize\Access\ContentElements', 'hideButton');
//$GLOBALS['TL_DCA']['tl_content']['list']['operations']['copy']['button_callback']		= array('IIDO\Customize\Access\ContentElements', 'hideButton');
//$GLOBALS['TL_DCA']['tl_content']['list']['operations']['cut']['button_callback']		= array('IIDO\Customize\Access\ContentElements', 'hideButton');
//$GLOBALS['TL_DCA']['tl_content']['list']['operations']['delete']['button_callback']		= array('IIDO\Customize\Access\ContentElements', 'deleteButton');
//$GLOBALS['TL_DCA']['tl_content']['list']['operations']['toggle']['button_callback']		= array('IIDO\Customize\Access\ContentElements', 'toggleButton');



/**
 * Fields
 */

if( $objContent && $act === "edit" && $objContent->type === "gallery" )
{
    $GLOBALS['TL_DCA'][ $strFileName ]['fields']['text']['eval']['mandatory'] = false;
}


\IIDO\BasicBundle\Helper\DcaHelper::addTextField("internName", $strFileName);

$GLOBALS['TL_DCA'][ $strFileName ]['fields']['headlineImagePosition'] = array
(
    'label'                 => &$GLOBALS['TL_LANG']['tl_content']['headlineImagePosition'],
    'exclude'               => true,
    'inputType'             => 'select',
    'input_field_callback'  => array($strTableClass, 'parseHeadlineImagePostionField'),
    'eval'                  => array
    (
        'tl_class'              => 'w50'
    ),
    'sql'                   => "varchar(60) NOT NULL default ''"
);


$GLOBALS['TL_DCA']['tl_content']['fields']['navModule'] = array
(
    'label'                 => &$GLOBALS['TL_LANG']['tl_content']['navModule'],
    'exclude'               => true,
    'inputType'             => 'select',
    'options_callback'      => array($strTableClass, 'getNavigationModule'),
    'eval'                  => array
    (
        'tl_class'              => 'w50'
    ),
    'sql'                   => "int(10) unsigned NOT NULL"
);


//if($objContent && \Input::get("act") == "edit")
//{
//	if($objContent->hyperlinkUrlToFiles)
//	{
//		$GLOBALS['TL_DCA']['tl_content']['fields']['url']['eval']['tl_class']			.= ' wizard';
//		$GLOBALS['TL_DCA']['tl_content']['fields']['url']['wizard'] = array
//		(
//			array('IIDO\Customize\Tables', 'filePicker')
//		);
//	}
//}

//if( $objContent && $objContent->type == "gallery" )
//{
//    $GLOBALS['TL_DCA']['tl_content']['fields']['text']['eval']['tl_class']              = trim('clr ' . $GLOBALS['TL_DCA']['tl_content']['fields']['text']['eval']['tl_class']);
//}


$GLOBALS['TL_DCA']['tl_content']['fields']['floating']['eval']['submitOnChange'] 		= true;
//
//$GLOBALS['TL_DCA']['tl_content']['fields']['type']['eval']['tl_class']					= 'w50';

//$GLOBALS['TL_DCA']['tl_content']['fields']['headline']['eval']['allowHTML']				= true;
//$GLOBALS['TL_DCA']['tl_content']['fields']['headline']['eval']['preserveTags']			= true;
//$GLOBALS['TL_DCA']['tl_content']['fields']['headline']['eval']['tl_class'] 				= trim($GLOBALS['TL_DCA']['tl_content']['fields']['headline']['eval']['tl_class'] . " w50 clr");
//
//$GLOBALS['TL_DCA']['tl_content']['fields']['caption']['eval']['allowHtml'] 				= true;
//$GLOBALS['TL_DCA']['tl_content']['fields']['linkTitle']['eval']['allowHtml']			= true;
//
//$GLOBALS['TL_DCA']['tl_content']['fields']['url']['eval']['tl_class']					= trim($GLOBALS['TL_DCA']['tl_content']['fields']['url']['eval']['tl_class'] . ' clr');
//
//$GLOBALS['TL_DCA']['tl_content']['fields']['module']['options_callback']				= array('IIDO\Customize\Access\FrontendModule', 'filterFrontendModule');
//
//$GLOBALS['TL_DCA']['tl_content']['fields']['linkPage']				= $GLOBALS['TL_DCA']['tl_module']['fields']['rootPage'];
//$GLOBALS['TL_DCA']['tl_content']['fields']['linkPage']['label']		= &$GLOBALS['TL_LANG']['tl_content']['linkPage'];


//$GLOBALS['TL_DCA']['tl_content']['fields']['elementIsBox'] = array
//(
//	'label'					=> &$GLOBALS['TL_LANG']['tl_content']['iidoCustomize']['elementIsBox'],
//	'exclude'				=> TRUE,
//	'inputType'				=> 'checkbox',
//	'eval'					=> array
//	(
//		'submitOnChange'		=> TRUE,
//		'tl_class'				=> 'clr w50'
//	),
//	'sql'					=> "char(1) NOT NULL default ''"
//);
//
//$GLOBALS['TL_DCA']['tl_content']['fields']['boxWidth'] = array
//(
//	'label'					=> &$GLOBALS['TL_LANG']['tl_content']['iidoCustomize']['boxWidth'],
//	'exclude'				=> TRUE,
//	'inputType'				=> 'select',
//	'options'				=> $GLOBALS['TL_LANG']['tl_content']['options']['boxWidth'],
//	'eval'					=> array
//	(
//		'tl_class'				=> 'clr w50'
//	),
//	'sql'					=> "varchar(32) NOT NULL default ''"
//);
//
//$GLOBALS['TL_DCA']['tl_content']['fields']['boxHeight']						= $GLOBALS['TL_DCA']['tl_content']['fields']['boxWidth'];
//$GLOBALS['TL_DCA']['tl_content']['fields']['boxHeight']['label'] 			= $GLOBALS['TL_LANG']['tl_content']['iidoCustomize']['boxHeight'];
//$GLOBALS['TL_DCA']['tl_content']['fields']['boxHeight']['options']			= $GLOBALS['TL_LANG']['tl_content']['options']['boxHeight'];
//$GLOBALS['TL_DCA']['tl_content']['fields']['boxHeight']['eval']['tl_class']	= 'w50';
//
//$GLOBALS['TL_DCA']['tl_content']['fields']['boxLink']							= $GLOBALS['TL_DCA']['tl_content']['fields']['url'];
//$GLOBALS['TL_DCA']['tl_content']['fields']['boxLink']['eval']['mandatory']		= false;
//
//$GLOBALS['TL_DCA']['tl_content']['fields']['boxLinkText']						= $GLOBALS['TL_DCA']['tl_content']['fields']['linkTitle'];
//
//$GLOBALS['TL_DCA']['tl_content']['fields']['addBoxText']						= $GLOBALS['TL_DCA']['tl_content']['fields']['elementIsBox'];
//$GLOBALS['TL_DCA']['tl_content']['fields']['addBoxText']['label']				= &$GLOBALS['TL_LANG']['tl_content']['iidoCustomize']['addBoxText'];
////$GLOBALS['TL_DCA']['tl_content']['fields']['addBoxText']['eval']['tl_class']	= "clr";
//
//$GLOBALS['TL_DCA']['tl_content']['fields']['addGalleryText']					= $GLOBALS['TL_DCA']['tl_content']['fields']['addBoxText'];
//$GLOBALS['TL_DCA']['tl_content']['fields']['addGalleryText']['label']			= &$GLOBALS['TL_LANG']['tl_content']['iidoCustomize']['addText'];
//
//$GLOBALS['TL_DCA']['tl_content']['fields']['boxBackgroundColor']				= $GLOBALS['TL_DCA']['tl_content']['fields']['boxWidth'];
//$GLOBALS['TL_DCA']['tl_content']['fields']['boxBackgroundColor']['label']		= $GLOBALS['TL_LANG']['tl_content']['iidoCustomize']['boxBackgroundColor'];
//$GLOBALS['TL_DCA']['tl_content']['fields']['boxBackgroundColor']['options']		= $GLOBALS['TL_LANG']['tl_content']['options']['boxBackgroundColor'];
//$GLOBALS['TL_DCA']['tl_content']['fields']['boxBackgroundColor']['eval']['includeBlankOption']	= TRUE;
//
//$GLOBALS['TL_DCA']['tl_content']['fields']['spaceHorizontal']					= $GLOBALS['TL_DCA']['tl_content']['fields']['space'];
//$GLOBALS['TL_DCA']['tl_content']['fields']['spaceHorizontal']['label'] 			= &$GLOBALS['TL_LANG']['tl_content']['iidoCustomize']['spaceHorizontal'];



$GLOBALS['TL_DCA']['tl_content']['fields']['addImageBorder'] = array
(
	'label'					=> &$GLOBALS['TL_LANG']['tl_content']['addImageBorder'],
	'exclude'				=> TRUE,
	'inputType'				=> 'checkbox',
	'eval'					=> array
	(
		'tl_class'				=> 'w50 m12'
	),
	'sql'					=> "char(1) NOT NULL default ''"
);


//$GLOBALS['TL_DCA']['tl_content']['fields']['skipFirst'] = $GLOBALS['TL_DCA']['tl_module']['fields']['skipFirst'];
//
//$GLOBALS['TL_DCA']['tl_content']['fields']['insertClearAfter'] = array
//(
//	'label'                   => &$GLOBALS['TL_LANG']['tl_content']['insertClearAfter'],
//	'exclude'                 => true,
//	'inputType'               => 'checkbox',
//	'eval'                    => array('tl_class'=>'w50 clr m12'),
//	'sql'                     => "char(1) NOT NULL default ''"
//);
//
//$GLOBALS['TL_DCA']['tl_content']['fields']['insertDividingLineAfter'] = array
//(
//	'label'                   => &$GLOBALS['TL_LANG']['tl_content']['insertDividingLineAfter'],
//	'exclude'                 => true,
//	'inputType'               => 'checkbox',
//	'eval'                    => array('tl_class'=>'w50 m12'),
//	'sql'                     => "char(1) NOT NULL default ''"
//);



// TRENNLINIE (DIVIDER)

$GLOBALS['TL_DCA']['tl_content']['fields']['dividerSize'] = array
(
	'label'						=> &$GLOBALS['TL_LANG']['tl_content']['dividerSize'],
	'inputType'					=> 'inputUnit',
	'options'					=> $GLOBALS['TL_UNITS'], //array('px', '%', 'em', 'ex', 'pt', 'pc', 'in', 'cm', 'mm'),
	'eval'						=> array
	(
		'includeBlankOption'		=> true,
		'rgxp'						=> 'digit_inherit',
		'tl_class'					=> 'w50'
	),
	'sql'						=> "varchar(64) NOT NULL default ''"
);

\IIDO\BasicBundle\Helper\DcaHelper::addField('dividerColor', 'color', $strFileName);
\IIDO\BasicBundle\Helper\DcaHelper::addField('dividerStyle', 'select', $strFileName, array('includeBlankOption' => true));



// Random Image

\IIDO\BasicBundle\Helper\DcaHelper::copyFieldFromTable('imgSize', $strFileName, 'imgSize', 'tl_module');
\IIDO\BasicBundle\Helper\DcaHelper::copyFieldFromTable('useCaption', $strFileName, 'useCaption', 'tl_module');



//// Ornament
//$GLOBALS['TL_DCA']['tl_content']['fields']['addOrnament'] = array
//(
//	'label'						=> &$GLOBALS['TL_LANG']['tl_content']['addOrnament'],
//	'exclude'					=> true,
//	'inputType'					=> 'checkbox',
//	'eval'						=> array
//	(
//		'tl_class'					=> 'clr w50 m12',
//		'submitOnChange'			=> true
//	),
//	'sql'						=> "char(1) NOT NULL default ''"
//);
//$GLOBALS['TL_DCA']['tl_content']['fields']['ornamentLight'] = array
//(
//	'label'						=> &$GLOBALS['TL_LANG']['tl_content']['ornamentLight'],
//	'exclude'					=> true,
//	'inputType'					=> 'checkbox',
//	'eval'						=> array
//	(
//		'tl_class'					=> 'w50 m12',
//		'submitOnChange'			=> true
//	),
//	'sql'						=> "char(1) NOT NULL default ''"
//);
//$GLOBALS['TL_DCA']['tl_content']['fields']['addOrnamentLinie'] = array
//(
//	'label'						=> &$GLOBALS['TL_LANG']['tl_content']['addOrnamentLinie'],
//	'exclude'					=> true,
//	'inputType'					=> 'checkbox',
//	'eval'						=> array
//	(
//		'tl_class'					=> 'clr w50 m12'
//	),
//	'sql'						=> "char(1) NOT NULL default ''"
//);
//$GLOBALS['TL_DCA']['tl_content']['fields']['ornament'] = array
//(
//	'label'						=> &$GLOBALS['TL_LANG']['tl_content']['ornament'],
//	'exclude'					=> true,
//	'inputType'					=> 'text',
//	'input_field_callback'		=> array('IIDO\Customize\Table\Content', 'ornamentField'),
//	'eval'						=> array
//	(
//		'tl_class'					=> 'long'
//	),
//	'sql'						=>"varchar(255) NOT NULL default ''"
//);
//$GLOBALS['TL_DCA']['tl_content']['fields']['onlyOrnament'] = array
//(
//	'label'						=> &$GLOBALS['TL_LANG']['tl_content']['onlyOrnament'],
//	'exclude'					=> true,
//	'inputType'					=> 'checkbox',
//	'eval'						=> array
//	(
//		'tl_class'				=> 'w50 m12'
//	),
//	'sql'						=> "char(1) NOT NULL default ''"
//);


//// News Categories & News List
////$GLOBALS['TL_DCA']['tl_content']['fields']['news_filterCategories']		= $GLOBALS['TL_DCA']['tl_module']['fields']['news_filterCategories'];
////$GLOBALS['TL_DCA']['tl_content']['fields']['news_filterDefault'] 		= $GLOBALS['TL_DCA']['tl_module']['fields']['news_filterDefault'];
////$GLOBALS['TL_DCA']['tl_content']['fields']['news_resetCategories']		= $GLOBALS['TL_DCA']['tl_module']['fields']['news_resetCategories'];
//$GLOBALS['TL_DCA']['tl_content']['fields']['news_archives']				= $GLOBALS['TL_DCA']['tl_module']['fields']['news_archives'];
////$GLOBALS['TL_DCA']['tl_content']['fields']['news_featured']				= $GLOBALS['TL_DCA']['tl_module']['fields']['news_featured'];
////$GLOBALS['TL_DCA']['tl_content']['fields']['news_jumpToCurrent']		= $GLOBALS['TL_DCA']['tl_module']['fields']['news_jumpToCurrent'];
////$GLOBALS['TL_DCA']['tl_content']['fields']['news_readerModule']			= $GLOBALS['TL_DCA']['tl_module']['fields']['news_readerModule'];
////$GLOBALS['TL_DCA']['tl_content']['fields']['news_metaFields']			= $GLOBALS['TL_DCA']['tl_module']['fields']['news_metaFields'];
////$GLOBALS['TL_DCA']['tl_content']['fields']['news_template']				= $GLOBALS['TL_DCA']['tl_module']['fields']['news_template'];
////$GLOBALS['TL_DCA']['tl_content']['fields']['news_format']				= $GLOBALS['TL_DCA']['tl_module']['fields']['news_format'];
////$GLOBALS['TL_DCA']['tl_content']['fields']['news_startDay']				= $GLOBALS['TL_DCA']['tl_module']['fields']['news_startDay'];
////$GLOBALS['TL_DCA']['tl_content']['fields']['news_order']				= $GLOBALS['TL_DCA']['tl_module']['fields']['news_order'];
////$GLOBALS['TL_DCA']['tl_content']['fields']['news_showQuantity']			= $GLOBALS['TL_DCA']['tl_module']['fields']['news_showQuantity'];
//
//$GLOBALS['TL_DCA']['tl_content']['fields']['news_archive']				= $GLOBALS['TL_DCA']['tl_module']['fields']['news_archives'];
//
//if( $objContent && $objContent->type == "iidoCustomize_newsGalleryDetail" )
//{
//	$GLOBALS['TL_DCA']['tl_content']['fields']['news_archive']['inputType']						= "select";
//
//	$GLOBALS['TL_DCA']['tl_content']['fields']['news_archive']['options_callback']				= array('IIDO\Customize\Table\Content', 'getNewsArchives');
//
//	$GLOBALS['TL_DCA']['tl_content']['fields']['news_archive']['eval']['tl_class']				= "w50 hauto";
//	$GLOBALS['TL_DCA']['tl_content']['fields']['news_archive']['eval']['submitOnChange']		= TRUE;
//	$GLOBALS['TL_DCA']['tl_content']['fields']['news_archive']['eval']['includeBlankOption']	= TRUE;
//	$GLOBALS['TL_DCA']['tl_content']['fields']['news_archive']['eval']['multiple']				= FALSE;
//
//	$GLOBALS['TL_DCA']['tl_content']['fields']['news_archive']['wizard'] = array
//	(
//		array('IIDO\Customize\Table\Content', 'editNewsArchive')
//	);
//}
//
//
//$GLOBALS['TL_DCA']['tl_content']['fields']['news_item']				= array
//(
//	'label'						=> &$GLOBALS['TL_LANG']['tl_content']['newsItem'],
//	'exclude'					=> true,
//	'inputType'					=> 'select',
//	'options_callback'			=> array('IIDO\Customize\Table\Content', 'getNewsItems'),
//	'eval'						=> array
//	(
//		'includeBlankOption'		=> true,
//		'tl_class'					=> 'w50',
//		'mandatory'					=> true
//	),
//	'wizard' 					=> array
//	(
//		array('IIDO\Customize\Table\Content', 'editNewsItem')
//	),
//	'sql'						=> "int(10) unsigned NOT NULL default '0'"
//);
//
//
//
//// Background Image
//$GLOBALS['TL_DCA']['tl_content']['fields']['bgOnOtherColumn'] = array
//(
//	'label'						=> &$GLOBALS['TL_LANG']['tl_content']['bgOnOtherColumn'],
//	'exclude'					=> true,
//	'inputType'					=> 'checkbox',
//	'eval'						=> array
//	(
//		'tl_class'					=> 'w50 m12',
//		'submitOnChange'			=> true
//	),
//	'sql'						=> "char(1) NOT NULL default ''"
//);
//
//$GLOBALS['TL_DCA']['tl_content']['fields']['bgToArticle'] = array
//(
//	'label'						=> &$GLOBALS['TL_LANG']['tl_content']['bgToArticle'],
//	'exclude'					=> true,
//	'inputType'					=> 'select',
//	'options'					=> $GLOBALS['TL_LANG']['tl_content']['options']['bgToArticle'],
//	'eval'						=> array
//	(
//		'tl_class'					=> 'w50',
//	),
//	'sql'						=> "varchar(255) NOT NULL default ''"
//);
//
//
//$GLOBALS['TL_DCA']['tl_content']['fields']['bgMode'] = array
//(
//	'label'						=> &$GLOBALS['TL_LANG']['tl_content']['bgMode'],
//	'exclude'					=> true,
//	'inputType'					=> 'select',
//	'options'					=> $GLOBALS['TL_LANG']['tl_content']['options']['bgMode'],
//	'eval'						=> array
//	(
//		'tl_class'					=> 'w50',
//	),
//	'sql'						=> "varchar(255) NOT NULL default ''"
//);
//
//$GLOBALS['TL_DCA']['tl_content']['fields']['bgAttachment'] = array
//(
//	'label'						=> &$GLOBALS['TL_LANG']['tl_content']['bgAttachment'],
//	'exclude'					=> true,
//	'inputType'					=> 'select',
//	'options'					=> $GLOBALS['TL_LANG']['tl_content']['options']['bgAttachment'],
//	'eval'						=> array
//	(
//		'tl_class'					=> 'w50',
//	),
//	'sql'						=> "varchar(255) NOT NULL default ''"
//);
//
//$GLOBALS['TL_DCA']['tl_content']['fields']['bgPosition'] = array
//(
//	'label'						=> &$GLOBALS['TL_LANG']['tl_content']['bgPosition'],
//	'exclude'					=> true,
//	'inputType'					=> 'select',
//	'options'					=> $GLOBALS['TL_LANG']['tl_content']['options']['bgPosition'],
//	'eval'						=> array
//	(
//		'tl_class'					=> 'w50',
//	),
//	'sql'						=> "varchar(255) NOT NULL default ''"
//);
//
//$GLOBALS['TL_DCA']['tl_content']['fields']['bgRepeat'] = array
//(
//	'label'						=> &$GLOBALS['TL_LANG']['tl_content']['bgRepeat'],
//	'exclude'					=> true,
//	'inputType'					=> 'select',
//	'options'					=> $GLOBALS['TL_LANG']['tl_content']['options']['bgRepeat'],
//	'eval'						=> array
//	(
//		'tl_class'					=> 'w50',
//	),
//	'sql'						=> "varchar(255) NOT NULL default ''"
//);
//
//$GLOBALS['TL_DCA']['tl_content']['fields']['ieFallback'] = array
//(
//	'label'						=> &$GLOBALS['TL_LANG']['tl_content']['ieFallback'],
//	'exclude'					=> true,
//	'inputType'					=> 'checkbox',
//	'eval'						=> array
//	(
//		'tl_class'					=> 'w50 m12',
////		'submitOnChange'			=> true
//	),
//	'sql'						=> "char(1) NOT NULL default ''"
//);
//
//$GLOBALS['TL_DCA']['tl_content']['fields']['inheritBackground'] = array
//(
//	'label'						=> &$GLOBALS['TL_LANG']['tl_content']['inheritBackground'],
//	'exclude'					=> true,
//	'inputType'					=> 'checkbox',
//	'eval'						=> array
//	(
//		'tl_class'					=> 'w50 m12',
////		'submitOnChange'			=> true
//	),
//	'sql'						=> "char(1) NOT NULL default ''"
//);



// HEADLINE

\IIDO\BasicBundle\Helper\DcaHelper::addField('topHeadline', 'text', $strFileName);
\IIDO\BasicBundle\Helper\DcaHelper::addField('subHeadline', 'text', $strFileName, array(), 'clr');

\IIDO\BasicBundle\Helper\DcaHelper::addField('addTopHeadline', 'checkbox__selector', $strFileName, array(), 'clr sub-box');

//$GLOBALS['TL_DCA']['tl_content']['fields']['addSubHeadline']			= $GLOBALS['TL_DCA']['tl_content']['fields']['addTopHeadline'];
//$GLOBALS['TL_DCA']['tl_content']['fields']['addSubHeadline']['label']	= &$GLOBALS['TL_LANG']['tl_content']['addSubHeadline'];


$GLOBALS['TL_DCA']['tl_content']['fields']['headlineFloating'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_content']['headlineFloating'],
    'exclude'                 => true,
    'default'                 =>'header_left',
    'inputType'               => 'radioTable',
    'options'                 => $GLOBALS['TL_LANG']['tl_content']['options']['headlineFloating'],
    'eval'                    => array('cols'=>3, 'tl_class'=>'w50'),
    'sql'                     => "varchar(32) NOT NULL default 'header_left'"
);

//$GLOBALS['TL_DCA']['tl_content']['fields']['subHeadlineFloating']			= $GLOBALS['TL_DCA']['tl_content']['fields']['headlineFloating'];
//$GLOBALS['TL_DCA']['tl_content']['fields']['subHeadlineFloating']['label']	= &$GLOBALS['TL_LANG']['tl_content']['subHeadlineFloating'];

//$GLOBALS['TL_DCA']['tl_content']['fields']['topHeadlineFloating']			= $GLOBALS['TL_DCA']['tl_content']['fields']['subHeadlineFloating'];
//$GLOBALS['TL_DCA']['tl_content']['fields']['topHeadlineFloating']['label']	= &$GLOBALS['TL_LANG']['tl_content']['topHeadlineFloating'];


//$GLOBALS['TL_DCA']['tl_content']['fields']['addHeadlineBorder'] = array
//(
//	'label'                   => &$GLOBALS['TL_LANG']['tl_content']['addHeadlineBorder'],
//	'exclude'                 => true,
//	'inputType'               => 'checkbox',
//	'options'                 => $GLOBALS['TL_LANG']['tl_content']['options']['addHeadlineBorder'],
//	'eval'                    => array('tl_class'=>'w50 o50', 'multiple'=>true),
//	'sql'                     => "varchar(255) NOT NULL default ''"
//);

//$GLOBALS['TL_DCA']['tl_content']['fields']['addSubHeadlineBorder']			= $GLOBALS['TL_DCA']['tl_content']['fields']['addHeadlineBorder'];
//$GLOBALS['TL_DCA']['tl_content']['fields']['addSubHeadlineBorder']['label']	= &$GLOBALS['TL_LANG']['tl_content']['addSubHeadlineBorder'];

//$GLOBALS['TL_DCA']['tl_content']['fields']['addTopHeadlineBorder']			= $GLOBALS['TL_DCA']['tl_content']['fields']['addSubHeadlineBorder'];
//$GLOBALS['TL_DCA']['tl_content']['fields']['addTopHeadlineBorder']['label']	= &$GLOBALS['TL_LANG']['tl_content']['addTopHeadlineBorder'];



//$GLOBALS['TL_DCA']['tl_content']['fields']['addHeadlineLink'] = array
//(
//	'label'                   => &$GLOBALS['TL_LANG']['tl_content']['addHeadlineLink'],
//	'exclude'                 => true,
//	'inputType'               => 'checkbox',
//	'eval'                    => array
//	(
//		'tl_class'					=> 'w50 m12',
//		'submitOnChange'			=> true
//	),
//	'sql'                     => "char(1) NOT NULL default ''"
//);

//$GLOBALS['TL_DCA']['tl_content']['fields']['headlineLink'] = array
//(
//	'label'                   => &$GLOBALS['TL_LANG']['tl_content']['headlineLink'],
//	'exclude'                 => true,
//	'inputType'               => 'text',
//	'eval'                    => array
//	(
//		'rgxp'						=> 'url',
//		'decodeEntities'			=> true,
//		'maxlength'					=> 255,
//		'tl_class'					=> 'w50 wizard'
//	),
//	'wizard' => array
//	(
//		array('IIDO\Customize\Tables', 'pagePicker')
//	),
//	'sql'                     => "varchar(255) NOT NULL default ''"
//);



// HYPERLINK BUTTON

\IIDO\BasicBundle\Helper\DcaHelper::addField("showAsButton", "checkbox__selector", $strFileName, array('submitOnChange'=>true), "clr");
\IIDO\BasicBundle\Helper\DcaHelper::addField("buttonStyle", "select", $strFileName);
\IIDO\BasicBundle\Helper\DcaHelper::addField("buttonAddon", "select__selector", $strFileName, array('includeBlankOption' => true));
\IIDO\BasicBundle\Helper\DcaHelper::addField("buttonType", "select", $strFileName);
\IIDO\BasicBundle\Helper\DcaHelper::addField("buttonLinkMode", "select", $strFileName);
\IIDO\BasicBundle\Helper\DcaHelper::addField("buttonAddonPosition", "select", $strFileName);


$GLOBALS['TL_DCA']['tl_content']['fields']['buttonAddonIcon'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_content']['buttonAddonIcon'],
    'exclude'                 => true,
    'inputType'               => 'rocksolid_icon_picker',
    'eval'                    => array
    (
        'tl_class'              => 'w50 hauto',
        'iconFont'              => 'files/master/fonts/icomoon.svg',
    ),
    'sql'                     => "varchar(255) NOT NULL default ''"
);

\IIDO\BasicBundle\Helper\DcaHelper::addField("buttonAddonArrow", "select", $strFileName);



//// Ticker
//
//$GLOBALS['TL_DCA']['tl_content']['fields']['textBefore']			= $GLOBALS['TL_DCA']['tl_content']['fields']['text'];
//$GLOBALS['TL_DCA']['tl_content']['fields']['textBefore']['label']	= &$GLOBALS['TL_LANG']['tl_content']['textBefore'];
//$GLOBALS['TL_DCA']['tl_content']['fields']['textBefore']['eval']['mandatory'] = false;
//
//$GLOBALS['TL_DCA']['tl_content']['fields']['textAfter']				= $GLOBALS['TL_DCA']['tl_content']['fields']['text'];
//$GLOBALS['TL_DCA']['tl_content']['fields']['textAfter']['label']	= &$GLOBALS['TL_LANG']['tl_content']['textAfter'];
//$GLOBALS['TL_DCA']['tl_content']['fields']['textAfter']['eval']['mandatory'] = false;
//
//
//$GLOBALS['TL_DCA']['tl_content']['fields']['tickerMode'] = array
//(
//	'label'                   => &$GLOBALS['TL_LANG']['tl_content']['tickerMode'],
//	'exclude'                 => true,
//	'inputType'               => 'select',
//	'options'                 => $GLOBALS['TL_LANG']['tl_content']['options']['tickerMode'],
//	'eval'                    => array
//	(
//		'tl_class'=>'w50',
//		'includeBlankOption'=>false
//	),
//	'sql'                     => "varchar(50) NOT NULL default ''"
//);
//
//$GLOBALS['TL_DCA']['tl_content']['fields']['tickerShowMode'] = array
//(
//	'label'                   => &$GLOBALS['TL_LANG']['tl_content']['tickerShowMode'],
//	'exclude'                 => true,
//	'inputType'               => 'checkbox',
//	'options'                 => $GLOBALS['TL_LANG']['tl_content']['options']['tickerShowMode'],
//	'eval'                    => array
//	(
//		'tl_class'=>'clr w50',
//		'includeBlankOption'=>false,
//		'multiple'			=> true,
//		'mandatory'			=> true
//	),
//	'sql'                     => "blob NULL"
//);
//
//$GLOBALS['TL_DCA']['tl_content']['fields']['usedTime'] = array
//(
//	'label'                   => &$GLOBALS['TL_LANG']['tl_content']['usedTime'],
//	'exclude'                 => true,
//	'inputType'               => 'checkbox',
//	'eval'                    => array('tl_class'=>'w50 m12', "submitOnChange"=>true),
//	'sql'                     => "char(1) NOT NULL default ''"
//);
//
//$GLOBALS['TL_DCA']['tl_content']['fields']['tickerDate'] = array
//(
//	'label'                   => &$GLOBALS['TL_LANG']['tl_content']['tickerDate'],
//	'default'                 => time(),
//	'exclude'                 => true,
//	'inputType'               => 'text',
//	'eval'                    => array('rgxp'=>'date', 'mandatory'=>true, 'doNotCopy'=>true, 'datepicker'=>true, 'tl_class'=>'w50 wizard'),
//	'sql'                     => "int(10) unsigned NULL"
//);
//
//$GLOBALS['TL_DCA']['tl_content']['fields']['tickerTime'] = array
//(
//	'label'                   => &$GLOBALS['TL_LANG']['tl_content']['tickerTime'],
//	'default'                 => time(),
//	'exclude'                 => true,
////	'filter'                  => true,
////	'sorting'                 => true,
//	'flag'                    => 8,
//	'inputType'               => 'text',
//	'eval'                    => array('rgxp'=>'time', 'mandatory'=>true, 'doNotCopy'=>true, 'tl_class'=>'w50'),
//	'sql'                     => "int(10) unsigned NULL"
//);



// Address Block / Address Field (Website Standards)
//$GLOBALS['TL_DCA']['tl_content']['fields']['blockShowMode'] = array
//(
//	'label'                   => &$GLOBALS['TL_LANG']['tl_content']['blockShowMode'],
//	'exclude'                 => true,
//	'inputType'               => 'select',
//	'options'                 => $GLOBALS['TL_LANG']['tl_content']['options']['blockShowMode'],
//	'eval'                    => array
//	(
//		'tl_class'				=> 'w50',
//		'submitOnChange'		=> true,
//		'includeBlankOption'	=> true
//	),
//	'sql'                     => "varchar(10) NOT NULL default ''"
//);
//
//$GLOBALS['TL_DCA']['tl_content']['fields']['websiteField'] = array
//(
//	'label'                   => &$GLOBALS['TL_LANG']['tl_content']['websiteField'],
//	'exclude'                 => true,
//	'inputType'               => 'select',
//	'options_callback'        => array('IIDO\Customize\Table\Content', 'getWebsiteFields'),
//	'eval'                    => array
//	(
//		'tl_class'				=> 'w50',
//		'includeBlankOption'	=> true,
//		'mandatory'				=> true
//	),
//	'sql'                     => "varchar(20) NOT NULL default ''"
//);
//
//$GLOBALS['TL_DCA']['tl_content']['fields']['addressBlock'] = array
//(
//	'label'                   => &$GLOBALS['TL_LANG']['tl_content']['addressBlock'],
//	'exclude'                 => true,
//	'inputType'               => 'text',
////	'options_callback'        => array('IIDO\Customize\Table\Content', 'getWebsiteFields'),
//	'input_field_callback'		=> array('IIDO\Customize\Table\Content', 'getAddressBlockField'),
//	'eval'                    => array
//	(
//		'tl_class'				=>'clr',
////		'includeBlankOption'	=>false
//	),
//	'sql'                     => "blob NULL"
//);



// SERVICE CALENDAR
//$GLOBALS['TL_DCA']['tl_content']['fields']['serviceInterval'] = array
//(
//	'label'					=> &$GLOBALS['TL_LANG']['tl_content']['serviceInterval'],
//	'exclude'				=> true,
//	'inputType'				=> 'inputUnit',
//	'options'				=> $GLOBALS['TL_LANG']['tl_content']['options']['serviceInterval'],
//	'eval'					=> array
//	(
//		'tl_class'				=> 'w50'
//	),
//	'sql'					=> "varchar(255) NOT NULL default ''"
//);
//
//$GLOBALS['TL_DCA']['tl_content']['fields']['serviceIntervalLength'] = array
//(
//	'label'					=> &$GLOBALS['TL_LANG']['tl_content']['serviceIntervalLength'],
//	'exclude'				=> true,
//	'inputType'				=> 'inputUnit',
//	'options'				=> $GLOBALS['TL_LANG']['tl_content']['options']['serviceInterval'],
//	'eval'					=> array
//	(
//		'tl_class'				=> 'w50'
//	),
//	'sql'					=> "varchar(255) NOT NULL default ''"
//);
//
//$GLOBALS['TL_DCA']['tl_content']['fields']['onlyServiceLink'] = array
//(
//	'label'					=> &$GLOBALS['TL_LANG']['tl_content']['onlyServiceLink'],
//	'exclude'				=> true,
//	'inputType'				=> 'checkbox',
//	'eval'					=> array
//	(
//		'tl_class'				=> 'w50',
//		'submitOnChange'		=> true
//	),
//	'sql'					=> "char(1) NOT NULL default ''"
//);
//
//$GLOBALS['TL_DCA']['tl_content']['fields']['serviceLinkAfter'] = array
//(
//	'label'					=> &$GLOBALS['TL_LANG']['tl_content']['serviceLinkAfter'],
//	'exclude'				=> true,
//	'inputType'				=> 'checkbox',
//	'eval'					=> array
//	(
//		'tl_class'				=> 'w50',
//		'submitOnChange'		=> true
//	),
//	'sql'					=> "char(1) NOT NULL default ''"
//);
//
//
//$GLOBALS['TL_DCA']['tl_content']['fields']['startDate']		= array
//(
//	'label'                   => &$GLOBALS['TL_LANG']['tl_content']['startDate'],
//	'default'                 => time(),
//	'exclude'                 => true,
//	'inputType'               => 'text',
//	'eval'                    => array('rgxp'=>'date', 'doNotCopy'=>true, 'datepicker'=>true, 'tl_class'=>'w50 wizard'),
//	'sql'                     => "int(10) unsigned NULL"
//);
//
//$GLOBALS['TL_DCA']['tl_content']['fields']['startTime']		= array
//(
//	'label'                   => &$GLOBALS['TL_LANG']['tl_content']['startTime'],
//	'default'                 => time(),
//	'exclude'                 => true,
//	'filter'                  => true,
//	'sorting'                 => true,
//	'flag'                    => 8,
//	'inputType'               => 'text',
//	'eval'                    => array('rgxp'=>'time', 'doNotCopy'=>true, 'datepicker'=>true, 'tl_class'=>'w50 wizard'),
//	'sql'                     => "int(10) unsigned NULL"
//);
//,

\IIDO\BasicBundle\Helper\DcaHelper::addField('position', 'select', $strFileName, array('includeBlankOption' => true), 'clr', false, '', array('options' => $GLOBALS['TL_LANG']['RSCE']['positions']));
\IIDO\BasicBundle\Helper\DcaHelper::addField('positionMargin', 'trbl__units', $strFileName);
\IIDO\BasicBundle\Helper\DcaHelper::addField('positionFixed', 'checkbox', $strFileName, array(), 'clr');


$GLOBALS['TL_DCA']['tl_content']['fields']['navPages']                 = $GLOBALS['TL_DCA']['tl_module']['fields']['rootPage'];
$GLOBALS['TL_DCA']['tl_content']['fields']['navPages']['label']        = &$GLOBALS['TL_LANG']['tl_content']['navPages'];
$GLOBALS['TL_DCA']['tl_content']['fields']['navPages']['eval']['tl_class']    = 'clr';
$GLOBALS['TL_DCA']['tl_content']['fields']['navPages']['eval']['fieldType']   = 'checkbox';
$GLOBALS['TL_DCA']['tl_content']['fields']['navPages']['eval']['orderField']  = 'navPagesOrder';
$GLOBALS['TL_DCA']['tl_content']['fields']['navPages']['eval']['mandatory']   = FALSE;
$GLOBALS['TL_DCA']['tl_content']['fields']['navPages']['eval']['multiple']    = TRUE;
$GLOBALS['TL_DCA']['tl_content']['fields']['navPages']['eval']['files']       = FALSE;
$GLOBALS['TL_DCA']['tl_content']['fields']['navPages']['sql']         = "blob NULL";
$GLOBALS['TL_DCA']['tl_content']['fields']['navPages']['relation']    = array('type'=>'hasMany', 'load'=>'lazy');

$GLOBALS['TL_DCA']['tl_content']['fields']['navPagesOrder'] = array
(
    'label'                 => &$GLOBALS['TL_LANG']['tl_content']['navPagesOrder'],
    'sql'                   => "blob NULL"
);


\IIDO\BasicBundle\Helper\DcaHelper::copyFieldFromTable("navigationTpl", $strFileName, "navigationTpl", "tl_module");



// ANIMATION
\IIDO\BasicBundle\Helper\DcaHelper::addField("addAnimation", "checkbox__selector", $strFileName);
\IIDO\BasicBundle\Helper\DcaHelper::addField("animationType", "select__short", $strFileName, array('includeBlankOption'=>true));
\IIDO\BasicBundle\Helper\DcaHelper::addField("animationOffset", "text", $strFileName, array('maxlength'=>80));
\IIDO\BasicBundle\Helper\DcaHelper::addField("animationWait", "checkbox", $strFileName);
\IIDO\BasicBundle\Helper\DcaHelper::addField("animateRun", "select", $strFileName);



// WEATHER DATA
\IIDO\BasicBundle\Helper\DcaHelper::addField("addIcon", "checkbox", $strFileName);
\IIDO\BasicBundle\Helper\DcaHelper::addField("addSnow", "checkbox__selector", $strFileName);
\IIDO\BasicBundle\Helper\DcaHelper::addField("addTemperature", "checkbox", $strFileName);
\IIDO\BasicBundle\Helper\DcaHelper::addField("snowDepth", "text", $strFileName, array('rgxp'=>'digit','maxlength'=>5));
\IIDO\BasicBundle\Helper\DcaHelper::addField("snowUnit", "text", $strFileName, array('maxlength'=>15));
\IIDO\BasicBundle\Helper\DcaHelper::addField("snowSubline", "text", $strFileName, array('maxlength'=>100));

//\IIDO\BasicBundle\Helper\DcaHelper::copyField("snowUrl", $strFileName, 'imageUrl');
$GLOBALS['TL_DCA']['tl_content']['fields']['snowUrl']                 = $GLOBALS['TL_DCA']['tl_content']['fields']['imageUrl'];
$GLOBALS['TL_DCA']['tl_content']['fields']['snowUrl']['label']        = &$GLOBALS['TL_LANG']['tl_content']['snowUrl'];
$GLOBALS['TL_DCA']['tl_content']['fields']['snowUrl']['eval']['tl_class'] = trim($GLOBALS['TL_DCA']['tl_content']['fields']['snowUrl']['eval']['tl_class'] . ' clr');



// MOBILE
\IIDO\BasicBundle\Helper\DcaHelper::addField("showOnMobile", "checkbox", $strFileName);
\IIDO\BasicBundle\Helper\DcaHelper::addField("hideOnMobile", "checkbox", $strFileName);



// IMPRINT
\IIDO\BasicBundle\Helper\DcaHelper::addTextField('imprintCompanyName', $strFileName);
\IIDO\BasicBundle\Helper\DcaHelper::addCheckboxField('imprintText', $strFileName, array('multiple'=>true,'sorting'=>true), '', false, false, '', array('inputType'=>'checkboxWizard'));
\IIDO\BasicBundle\Helper\DcaHelper::addCheckboxField('privacyPolicyText', $strFileName, array('multiple'=>true,'sorting'=>true), '', false, false, '', array('inputType'=>'checkboxWizard'));

\IIDO\BasicBundle\Helper\DcaHelper::addTextField('imprintStreet', $strFileName,array('maxlength'=>100));
\IIDO\BasicBundle\Helper\DcaHelper::addTextField('imprintSubline', $strFileName,array('maxlength'=>100));
\IIDO\BasicBundle\Helper\DcaHelper::addTextField('imprintPostal', $strFileName, array('maxlength'=>8));
\IIDO\BasicBundle\Helper\DcaHelper::addTextField('imprintCity', $strFileName,array('maxlength'=>100));
\IIDO\BasicBundle\Helper\DcaHelper::addTextField('imprintPhone', $strFileName, array('maxlength'=>45));
\IIDO\BasicBundle\Helper\DcaHelper::addTextField('imprintFax', $strFileName, array('maxlength'=>45));
\IIDO\BasicBundle\Helper\DcaHelper::addTextField('imprintEmail', $strFileName, array('maxlength'=>40));
\IIDO\BasicBundle\Helper\DcaHelper::addTextField('imprintWeb', $strFileName, array('maxlength'=>30));
\IIDO\BasicBundle\Helper\DcaHelper::addCheckboxField('addImprintContactLabel', $strFileName);

\IIDO\BasicBundle\Helper\DcaHelper::addTextField('imprintMitglied', $strFileName,array('maxlength'=>100));
\IIDO\BasicBundle\Helper\DcaHelper::addTextField('imprintBerufsrecht', $strFileName,array('maxlength'=>100));
\IIDO\BasicBundle\Helper\DcaHelper::addTextField('imprintBehoerde', $strFileName, array('maxlength'=>100));
\IIDO\BasicBundle\Helper\DcaHelper::addTextField('imprintBeruf', $strFileName,array('maxlength'=>100));
\IIDO\BasicBundle\Helper\DcaHelper::addTextField('imprintCountry', $strFileName,array('maxlength'=>100));
\IIDO\BasicBundle\Helper\DcaHelper::addTextField('imprintObjectOfTheCompany', $strFileName, array('maxlength'=>100));
\IIDO\BasicBundle\Helper\DcaHelper::addTextField('imprintVATnumber', $strFileName, array('maxlength'=>12));

\IIDO\BasicBundle\Helper\DcaHelper::addTextField('imprintCompanyWording', $strFileName,array('maxlength'=>100));
\IIDO\BasicBundle\Helper\DcaHelper::addTextField('imprintManagingDirector', $strFileName,array('maxlength'=>100));
\IIDO\BasicBundle\Helper\DcaHelper::addTextField('imprintSection', $strFileName,array('maxlength'=>100));
\IIDO\BasicBundle\Helper\DcaHelper::addTextField('imprintOccupationalGroup', $strFileName,array('maxlength'=>100));
\IIDO\BasicBundle\Helper\DcaHelper::addTextField('imprintCompanyRegister', $strFileName,array('maxlength'=>100));
\IIDO\BasicBundle\Helper\DcaHelper::addTextField('imprintFirmengericht', $strFileName,array('maxlength'=>100));
\IIDO\BasicBundle\Helper\DcaHelper::addTextareaField('imprintAddText', $strFileName, array(), '', false, true);

$GLOBALS['TL_DCA'][ $strFileName ]['fields']['imprintImageCopyrights'] = array
(
    'label'                   => &$GLOBALS['TL_LANG'][ $strFileName ]['imprintImageCopyrights'],
    'exclude'                 => true,
    'inputType'               => 'listWizard',
    'eval'                    => array
    (
        'size'              => 2,
        'multiple'          => true,
        'addCheckbox'          => true,
        'allowHtml'         => true,
        'tl_class'          => 'clr',
        'labels'            => array
        (
            'Name / Titel',
            'Link',
            'Titel verlinken'
        )
    ),
    'xlabel' => array
    (
        array(\IIDO\BasicBundle\Table\AllTables::class, 'listImportWizard')
    ),
    'sql'                     => "blob NULL"
);



// NEWSLIST
\IIDO\BasicBundle\Helper\DcaHelper::copyFieldFromTable('news_archives', $strFileName, 'news_archives', 'tl_module');
\IIDO\BasicBundle\Helper\DcaHelper::copyFieldFromTable('news_featured', $strFileName, 'news_featured', 'tl_module');
\IIDO\BasicBundle\Helper\DcaHelper::copyFieldFromTable('skipFirst', $strFileName, 'skipFirst', 'tl_module');
//\IIDO\BasicBundle\Helper\DcaHelper::copyFieldFromTable('perPage', $strFileName, 'perPage', 'tl_module');
//\IIDO\BasicBundle\Helper\DcaHelper::copyFieldFromTable('numberOfItems', $strFileName, 'numberOfItems', 'tl_module');

\IIDO\BasicBundle\Helper\DcaHelper::copyFieldFromTable('news_metaFields', $strFileName, 'news_metaFields', 'tl_module');
\IIDO\BasicBundle\Helper\DcaHelper::copyFieldFromTable('news_template', $strFileName, 'news_template', 'tl_module');