<?php
/******************************************************************
 *
 * (c) 2018 Stephan Preßl <mail@stephanpressl.at>
 * All rights reserved
 *
 * Modification, distribution or any other action on or with 
 * this file is permitted unless explicitly granted by DesProSeo
 * www.desproseo.at <development@desproseo.at>
 *
 ******************************************************************/

System::loadLanguageFile("tl_content");
Controller::loadDataContainer("tl_content");

$objElement     = false;
$objArchive     = false;

$db             = \Database::getInstance();
$do             = Input::get("do");
$act            = Input::get("act");
$table          = Input::get("table");
$id             = (int) Input::get("id");
$theme          = \Backend::getTheme();
$strTable       = \NewsModel::getTable();

$enableCarouFredSel		= false; //in_array("dk_caroufredsel", \ModuleLoader::getActive());


if( count( $db->listTables() ) > 0 )
{
	if( $id )
	{
		if( $act && $act == "edit")
		{
			$objNews            = \NewsModel::findByPk($id);

			if($objNews)
			{
				$objArchive     = \NewsArchiveModel::findByPk( $objNews->pid );
			}
		}
		else
		{
			$objArchive         = \NewsArchiveModel::findByPk( $id );
		}
	}
}

\IIDO\BasicBundle\Table\NewsTable::checkTableForSorting($GLOBALS['TL_DCA'][ $strTable ], $objArchive, $do, $table, $id);


if( $objArchive )
{

	if( $objArchive->hideContentElements )
	{
		unset( $GLOBALS['TL_DCA'][ $strTable ]['list']['operations']['edit'] );
	}
}

if( is_array($GLOBALS['TL_DCA'][ $strTable ]['config']['onload_callback']) && count($GLOBALS['TL_DCA'][ $strTable ]['config']['onload_callback']) )
{
    foreach($GLOBALS['TL_DCA'][ $strTable ]['config']['onload_callback'] as $i => $callback)
    {
        if($callback[1] == "checkPermission")
        {
            unset($GLOBALS['TL_DCA'][ $strTable ]['config']['onload_callback'][ $i ]);
            $GLOBALS['TL_DCA'][ $strTable ]['config']['onload_callback'][ $i ] = array('IIDO\BasicBundle\Table\NewsTable', 'checkPermission');
        }
    }
}



/**
 * Palettes
 */

$GLOBALS['TL_DCA'][ $strTable ]['palettes']['__selector__'][]           = 'addTable';
$GLOBALS['TL_DCA'][ $strTable ]['palettes']['__selector__'][]           = 'addGallery';
$GLOBALS['TL_DCA'][ $strTable ]['palettes']['__selector__'][]           = 'useCarouFredSel';
$GLOBALS['TL_DCA'][ $strTable ]['palettes']['__selector__'][]           = 'useCarouFredSelThumbnails';
$GLOBALS['TL_DCA'][ $strTable ]['palettes']['__selector__'][]           = 'addVideo';


//$GLOBALS['TL_DCA'][ $strTable ]['palettes']['default'] = str_replace('addImage;', '{source_legend},multiSRC,sortBy,metaIgnore;{image_legend},gal_size,gal_imagemargin,gal_perRow,gal_fullsize,gal_perPage,gal_numberOfItems;{template_legend:hide},galleryTpl,customTpl;', $GLOBALS['TL_DCA'][ $strTable ]['palettes']['default']);
foreach($GLOBALS['TL_DCA'][ $strTable ]['palettes'] as $palette => $fields)
{
	if( $palette == "__selector__" )
	{
		continue;
	}

	if( $objArchive )
	{
		if( $objArchive->hideContentElements )
		{
			$fields = str_replace('teaser;', 'teaser;{text_legend},text;', $fields);
		}
	}

    $fields     = str_replace('addImage;', 'addImage;{gallery_legend},addGallery;{video_legend},addVideo;', $fields);
	$fields     = preg_replace('/{image_legend}/', '{table_legend},addTable;{image_legend}', $fields);


	$GLOBALS['TL_DCA'][ $strTable ]['palettes'][ $palette ] = $fields;
}



/**
 * Subpalettes
 */

$GLOBALS['TL_DCA'][ $strTable ]['subpalettes']['addTable']					= 'tableitems,tableFirstColBold';
$GLOBALS['TL_DCA'][ $strTable ]['subpalettes']['addGallery']			    = 'multiSRC,sortBy,metaIgnore,gal_size,gal_imagemargin,gal_perRow,gal_fullsize,gal_perPage,gal_numberOfItems,galleryTpl,customTpl' . (($enableCarouFredSel) ? ',useCarouFredSel' : '');
$GLOBALS['TL_DCA'][ $strTable ]['subpalettes']['useCarouFredSel']			= 'carouFredSelConfig,useCarouFredSelThumbnails';
$GLOBALS['TL_DCA'][ $strTable ]['subpalettes']['useCarouFredSelThumbnails']	= 'cfsThumbnailSize,cfsThumbnailsPosition,cfsThumbnailsAlign,cfsThumbnailsWidth,cfsThumbnailsHeight';
$GLOBALS['TL_DCA'][ $strTable ]['subpalettes']['addVideo']					= 'youtube';



/**
 * Fields
 */

//$GLOBALS['TL_DCA'][ $strTable ]['fields']['headline']['eval']['allowHTML']		= true;
//$GLOBALS['TL_DCA'][ $strTable ]['fields']['headline']['eval']['preserveTags']		= true;

$GLOBALS['TL_DCA'][ $strTable ]['fields']['sorting'] = array
(
	'sql'						=> "int(10) unsigned NOT NULL default '0'"
);

$GLOBALS['TL_DCA'][ $strTable ]['fields']['text'] = $GLOBALS['TL_DCA']['tl_content']['fields']['text'];



//$GLOBALS['TL_DCA'][ $strTable ]['fields']['categories']['options_callback']			= array('DPS\Customize\Table\News', 'getMainNewsCategories');
//$GLOBALS['TL_DCA'][ $strTable ]['fields']['categories']['eval']['tl_class']			= 'w50 hauto';
//$GLOBALS['TL_DCA'][ $strTable ]['fields']['categories']['eval']['submitOnChange']		= true;

//$GLOBALS['TL_DCA'][ $strTable ]['fields']['subCategories'] 							= $GLOBALS['TL_DCA'][ $strTable ]['fields']['categories'];
//$GLOBALS['TL_DCA'][ $strTable ]['fields']['subCategories']['label']					= &$GLOBALS['TL_LANG'][ $strTable ]['subCategories'];
//$GLOBALS['TL_DCA'][ $strTable ]['fields']['subCategories']['options_callback'] 		= array('DPS\Customize\Table\News', 'getSubNewsCategories');
//$GLOBALS['TL_DCA'][ $strTable ]['fields']['subCategories']['eval']['submitOnChange']	= false;



// -- GALLERY

$GLOBALS['TL_DCA'][ $strTable ]['fields']['addGallery']           = $GLOBALS['TL_DCA'][ $strTable ]['fields']['addImage'];
$GLOBALS['TL_DCA'][ $strTable ]['fields']['addGallery']['label']  = &$GLOBALS['TL_LANG'][ $strTable ]['addGallery'];


$GLOBALS['TL_DCA'][ $strTable ]['fields']['multiSRC']				= $GLOBALS['TL_DCA']['tl_content']['fields']['multiSRC'];
$GLOBALS['TL_DCA'][ $strTable ]['fields']['multiSRC']['eval']['tl_class']		= trim($GLOBALS['TL_DCA'][ $strTable ]['fields']['multiSRC']['eval']['tl_class'] . " clr");
$GLOBALS['TL_DCA'][ $strTable ]['fields']['multiSRC']['eval']['orderField'] 	= 'orderGallerySRC';
$GLOBALS['TL_DCA'][ $strTable ]['fields']['multiSRC']['eval']['isGallery'] 	= TRUE;
$GLOBALS['TL_DCA'][ $strTable ]['fields']['multiSRC']['eval']['extensions'] 	= Config::get('validImageTypes');

$GLOBALS['TL_DCA'][ $strTable ]['fields']['orderGallerySRC'] = array
(
	'label'					=> &$GLOBALS['TL_LANG']['tl_content']['orderSRC'],
	'sql'					=> "blob NULL"
);

$GLOBALS['TL_DCA'][ $strTable ]['fields']['sortBy']				= $GLOBALS['TL_DCA']['tl_content']['fields']['sortBy'];
$GLOBALS['TL_DCA'][ $strTable ]['fields']['metaIgnore']			= $GLOBALS['TL_DCA']['tl_content']['fields']['metaIgnore'];

$GLOBALS['TL_DCA'][ $strTable ]['fields']['gal_size']				= $GLOBALS['TL_DCA']['tl_content']['fields']['size'];
$GLOBALS['TL_DCA'][ $strTable ]['fields']['gal_imagemargin']		= $GLOBALS['TL_DCA']['tl_content']['fields']['imagemargin'];
$GLOBALS['TL_DCA'][ $strTable ]['fields']['gal_perRow']			= $GLOBALS['TL_DCA']['tl_content']['fields']['perRow'];
$GLOBALS['TL_DCA'][ $strTable ]['fields']['gal_fullsize']			= $GLOBALS['TL_DCA']['tl_content']['fields']['fullsize'];
$GLOBALS['TL_DCA'][ $strTable ]['fields']['gal_perPage']			= $GLOBALS['TL_DCA']['tl_content']['fields']['perPage'];
$GLOBALS['TL_DCA'][ $strTable ]['fields']['gal_numberOfItems']	= $GLOBALS['TL_DCA']['tl_content']['fields']['numberOfItems'];

$GLOBALS['TL_DCA'][ $strTable ]['fields']['galleryTpl']			= $GLOBALS['TL_DCA']['tl_content']['fields']['galleryTpl'];
$GLOBALS['TL_DCA'][ $strTable ]['fields']['customTpl']			= $GLOBALS['TL_DCA']['tl_content']['fields']['customTpl'];



// -- TABLE

$GLOBALS['TL_DCA'][ $strTable ]['fields']['addTable'] = array
(
	'label'					=> &$GLOBALS['TL_LANG'][ $strTable ]['addTable'],
	'exclude'				=> true,
	'inputType'				=> 'checkbox',
	'eval'					=> array
	(
		'submitOnChange'		=> true
	),
	'sql'					=> "char(1) NOT NULL default ''"
);

$GLOBALS['TL_DCA'][ $strTable ]['fields']['tableitems'] = $GLOBALS['TL_DCA']['tl_content']['fields']['tableitems'];


$GLOBALS['TL_DCA'][ $strTable ]['fields']['tableFirstColBold'] = array
(
	'label'					=> &$GLOBALS['TL_LANG'][ $strTable ]['tableFirstColBold'],
	'exclude'				=> true,
	'inputType'				=> 'checkbox',
	'eval'					=> array
	(
		'tl_class'				=> 'clr w50'
	),
	'sql'					=> "char(1) NOT NULL default ''"
);



// -- CarouFredSel

$GLOBALS['TL_DCA'][ $strTable ]['fields']['useCarouFredSel'] = array
(
	'label'					=> &$GLOBALS['TL_LANG'][ $strTable ]['useCarouFredSel'],
	'exclude'				=> true,
	'inputType'				=> 'checkbox',
	'eval'					=> array
	(
		'submitOnChange'		=> true,
		'tl_class'				=> 'w50 m12'
	),
	'sql'					=> "char(1) NOT NULL default ''"
);

//$GLOBALS['TL_DCA'][ $strTable ]['fields']['carouFredSelConfig'] = $GLOBALS['TL_DCA']['tl_content']['fields']['dk_cfsCarouFredSel'];



// -- CarouFredSel Thumbnails

$GLOBALS['TL_DCA'][ $strTable ]['fields']['useCarouFredSelThumbnails'] = array
(
	'label'					=> &$GLOBALS['TL_LANG'][ $strTable ]['useCarouFredSelThumbnails'],
	'exclude'				=> true,
	'inputType'				=> 'checkbox',
	'eval'					=> array
	(
		'submitOnChange'		=> true,
		'tl_class'				=> 'w50 m12'
	),
	'sql'					=> "char(1) NOT NULL default ''"
);

//$GLOBALS['TL_DCA'][ $strTable ]['fields']['cfsThumbnailSize']			= $GLOBALS['TL_DCA']['tl_content']['fields']['dk_cfsThumbnailSize'];
//$GLOBALS['TL_DCA'][ $strTable ]['fields']['cfsThumbnailsPosition'] 	= $GLOBALS['TL_DCA']['tl_content']['fields']['dk_cfsThumbnailsPosition'];
//$GLOBALS['TL_DCA'][ $strTable ]['fields']['cfsThumbnailsAlign']		= $GLOBALS['TL_DCA']['tl_content']['fields']['dk_cfsThumbnailsAlign'];
//$GLOBALS['TL_DCA'][ $strTable ]['fields']['cfsThumbnailsWidth']		= $GLOBALS['TL_DCA']['tl_content']['fields']['dk_cfsThumbnailsWidth'];
//$GLOBALS['TL_DCA'][ $strTable ]['fields']['cfsThumbnailsHeight']		= $GLOBALS['TL_DCA']['tl_content']['fields']['dk_cfsThumbnailsHeight'];



// -- VIDEO

$GLOBALS['TL_DCA'][ $strTable ]['fields']['addVideo']             = $GLOBALS['TL_DCA'][ $strTable ]['fields']['addImage'];
$GLOBALS['TL_DCA'][ $strTable ]['fields']['addVideo']['label']    = &$GLOBALS['TL_LANG'][ $strTable ]['addVideo'];

$GLOBALS['TL_DCA'][ $strTable ]['fields']['youtube']              = $GLOBALS['TL_DCA']['tl_content']['fields']['youtube'];