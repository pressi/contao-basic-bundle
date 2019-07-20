<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

System::loadLanguageFile( 'tl_content' );
Controller::loadDataContainer( 'tl_content' );

$objElement     = false;
$objArchive     = false;

$db             = Database::getInstance();
$do             = Input::get( 'do' );
$act            = Input::get( 'act' );
$table          = Input::get( 'table' );
$id             = (int) Input::get( 'id' );
$theme          = Backend::getTheme();

//$strFileName    = \NewsModel::getTable();
$strFileName    = \IIDO\BasicBundle\Config\BundleConfig::getTableName( __FILE__ );
$strFileClass   = \IIDO\BasicBundle\Config\BundleConfig::getTableClass( $strFileName );

$enableCarouFredSel     = false; //in_array("dk_caroufredsel", \ModuleLoader::getActive());

if( count( $db->listTables() ) > 0 )
{
	if( $id )
	{
		if( $act && $act === 'edit' )
		{
//			$objNews = \NewsModel::findByPk($id);
			$objNews = $db->prepare( 'SELECT * FROM ' . $strFileName . ' WHERE id=?' )->limit(1)->execute( $id );

			if( $objNews )
			{
//				$objArchive = \NewsArchiveModel::findByPk( $objNews->pid );
				$objArchive = $db->prepare( 'SELECT * FROM tl_news_archive WHERE id=?' )->limit(1)->execute( $objNews->pid );
			}
		}
		else
		{
//            $objArchive = \NewsArchiveModel::findByPk( $id );
            $objArchive = $db->prepare( 'SELECT * FROM tl_news_archive WHERE id=?' )->limit(1)->execute( $id );
		}
	}
}

\IIDO\BasicBundle\Table\NewsTable::checkTableForSorting($GLOBALS['TL_DCA'][ $strFileName ], $objArchive, $do, $table, $id);


if( $objArchive )
{
	if( $objArchive->hideContentElements || in_array($objArchive->newsTyps,  array("job")) )
	{
		unset( $GLOBALS['TL_DCA'][ $strFileName ]['list']['operations']['edit'] );
	}
}

if( isset($GLOBALS['TL_DCA'][ $strFileName ]['config']['onload_callback']) && is_array($GLOBALS['TL_DCA'][ $strFileName ]['config']['onload_callback']) && count($GLOBALS['TL_DCA'][ $strFileName ]['config']['onload_callback']) )
{
    foreach($GLOBALS['TL_DCA'][ $strFileName ]['config']['onload_callback'] as $i => $callback)
    {
        if($callback[1] === "checkPermission")
        {
            unset($GLOBALS['TL_DCA'][ $strFileName ]['config']['onload_callback'][ $i ]);
            $GLOBALS['TL_DCA'][ $strFileName ]['config']['onload_callback'][ $i ] = array($strFileClass, 'checkPermission');
        }
    }
}



/**
 * Palettes
 */

$GLOBALS['TL_DCA'][ $strFileName ]['palettes']['__selector__'][]           = 'addTable';
$GLOBALS['TL_DCA'][ $strFileName ]['palettes']['__selector__'][]           = 'addGallery';
$GLOBALS['TL_DCA'][ $strFileName ]['palettes']['__selector__'][]           = 'addGallery_lb';
$GLOBALS['TL_DCA'][ $strFileName ]['palettes']['__selector__'][]           = 'useCarouFredSel';
$GLOBALS['TL_DCA'][ $strFileName ]['palettes']['__selector__'][]           = 'useCarouFredSelThumbnails';
$GLOBALS['TL_DCA'][ $strFileName ]['palettes']['__selector__'][]           = 'addVideo';


//$GLOBALS['TL_DCA'][ $strFileName ]['palettes']['default'] = str_replace('addImage;', '{source_legend},multiSRC,sortBy,metaIgnore;{image_legend},gal_size,gal_imagemargin,gal_perRow,gal_fullsize,gal_perPage,gal_numberOfItems;{template_legend:hide},galleryTpl,customTpl;', $GLOBALS['TL_DCA'][ $strFileName ]['palettes']['default']);
foreach($GLOBALS['TL_DCA'][ $strFileName ]['palettes'] as $palette => $fields)
{
	if( $palette === '__selector__' )
	{
		continue;
	}

	if( $objArchive )
	{
		if( $objArchive->hideContentElements || in_array($objArchive->newsTyps, array("job", "pressReport")) )
		{
			$fields = str_replace('teaser;', 'teaser;{text_legend},text;', $fields); //textBig,textMiddle,textLeft,textRight
		}

        if( $objArchive->newsTyps === 'job' )
        {
            $fields = preg_replace('/text;/', 'text;{addText_legend},contactPerson,contactLink,contactLinkTitle;', $fields);
        }
        elseif( $objArchive->newsTyps === 'pressReport' )
        {
            $fields = preg_replace('/text;/', 'text;{blockquotes_legend},blockquotes;', $fields);
        }
	}

    $fields = str_replace('addImage;', 'addImage;{gallery_legend},addGallery;{gallery_lb_legend},addGallery_lb;{video_legend},addVideo;', $fields);
    $fields = preg_replace('/{image_legend}/', '{table_legend},addTable;{image_legend}', $fields);


	$GLOBALS['TL_DCA'][ $strFileName ]['palettes'][ $palette ] = $fields;
}


//if( $objArchive && $objArchive->newsTyps === "job" )
//{
//    foreach($GLOBALS['TL_DCA'][ $strFileName ]['palettes'] as $palette => $fields)
//    {
//        if( $palette == "__selector__" )
//        {
//            continue;
//        }
//
//        $fields = str_replace('teaser;', 'teaser;{text_legend},text;', $fields);;
//
//        $GLOBALS['TL_DCA'][ $strFileName ]['palettes'][ $palette ] = $fields;
//    }
//}

if( $objArchive && $objArchive->newsTyps === 'project' )
{
    foreach($GLOBALS['TL_DCA'][ $strFileName ]['palettes'] as $palette => $fields)
    {
        if( $palette === '__selector__' )
        {
            continue;
        }

        $fields = str_replace('{date_legend', '{detail_legend},location,year,projectStatus,projectLeistung,projectPhotos;{slogan_legend},sloganTextBig,sloganTextSmall,sloganTextSmallFloating,sloganTextSmallMargin,sloganClass;{date_legend', $fields);
        $fields = preg_replace('/\{date_legend([a-zA-Z0-9\s\-,_\{\}]{0,});/', '', $fields);
        $fields = preg_replace('/\{teaser_legend([a-zA-Z0-9\s\-,_\{\}]{0,});/', '{addText_legend},text2Headline,text3Headline,text2,text3;{teaser_legend$1;', $fields);

        $GLOBALS['TL_DCA'][ $strFileName ]['palettes'][ $palette ] = $fields;
    }
}



/**
 * Subpalettes
 */

$GLOBALS['TL_DCA'][ $strFileName ]['subpalettes']['addTable']					= 'tableitems,tableFirstColBold';
$GLOBALS['TL_DCA'][ $strFileName ]['subpalettes']['addGallery']			        = 'multiSRC,sortBy,metaIgnore,gal_size,gal_imagemargin,gal_perRow,gal_fullsize,gal_perPage,gal_numberOfItems,galleryTpl,customTpl' . (($enableCarouFredSel) ? ',useCarouFredSel' : '');
$GLOBALS['TL_DCA'][ $strFileName ]['subpalettes']['addGallery_lb']			    = 'multiSRC_lb';
$GLOBALS['TL_DCA'][ $strFileName ]['subpalettes']['useCarouFredSel']			= 'carouFredSelConfig,useCarouFredSelThumbnails';
$GLOBALS['TL_DCA'][ $strFileName ]['subpalettes']['useCarouFredSelThumbnails']	= 'cfsThumbnailSize,cfsThumbnailsPosition,cfsThumbnailsAlign,cfsThumbnailsWidth,cfsThumbnailsHeight';

$GLOBALS['TL_DCA'][ $strFileName ]['subpalettes']['addVideo']					= 'videoMode';

$GLOBALS['TL_DCA'][ $strFileName ]['subpalettes']['videoMode_youtube']			= 'youtube';
$GLOBALS['TL_DCA'][ $strFileName ]['subpalettes']['videoMode_vimeo']			= 'vimeo';
$GLOBALS['TL_DCA'][ $strFileName ]['subpalettes']['videoMode_player']			= 'playerSRC,posterSRC,playerSize,autoplay';



/**
 * Fields
 */

//$GLOBALS['TL_DCA'][ $strFileName ]['fields']['headline']['eval']['allowHTML']		= true;
//$GLOBALS['TL_DCA'][ $strFileName ]['fields']['headline']['eval']['preserveTags']		= true;

$GLOBALS['TL_DCA'][ $strFileName ]['fields']['sorting'] = array
(
	'sql'						=> "int(10) unsigned NOT NULL default '0'"
);

//$GLOBALS['TL_DCA'][ $strFileName ]['fields']['text'] = $GLOBALS['TL_DCA']['tl_content']['fields']['text'];
\IIDO\BasicBundle\Helper\DcaHelper::addTextareaField( 'text', $strFileName, array(), '', false, true);
//\IIDO\BasicBundle\Helper\DcaHelper::addTextareaField( 'textBig', $strFileNameTable, array(), '', false, true);
//\IIDO\BasicBundle\Helper\DcaHelper::addTextareaField( 'textMiddle', $strFileNameTable, array(), '', false, true);

//\IIDO\BasicBundle\Helper\DcaHelper::addTextareaField( 'textLeft', $strFileNameTable, array(), 'clr w50 hauto', false, true);
//\IIDO\BasicBundle\Helper\DcaHelper::addTextareaField( 'textRight', $strFileNameTable, array(), 'w50 hauto', true, true);


//$GLOBALS['TL_DCA'][ $strFileName ]['fields']['categories']['options_callback']			= array('DPS\Customize\Table\News', 'getMainNewsCategories');
//$GLOBALS['TL_DCA'][ $strFileName ]['fields']['categories']['eval']['tl_class']			= 'w50 hauto';
//$GLOBALS['TL_DCA'][ $strFileName ]['fields']['categories']['eval']['submitOnChange']		= true;

//$GLOBALS['TL_DCA'][ $strFileName ]['fields']['subCategories'] 							= $GLOBALS['TL_DCA'][ $strFileName ]['fields']['categories'];
//$GLOBALS['TL_DCA'][ $strFileName ]['fields']['subCategories']['label']					= &$GLOBALS['TL_LANG'][ $strFileName ]['subCategories'];
//$GLOBALS['TL_DCA'][ $strFileName ]['fields']['subCategories']['options_callback'] 		= array('DPS\Customize\Table\News', 'getSubNewsCategories');
//$GLOBALS['TL_DCA'][ $strFileName ]['fields']['subCategories']['eval']['submitOnChange']	= false;



// -- GALLERY

$GLOBALS['TL_DCA'][ $strFileName ]['fields']['addGallery']           = $GLOBALS['TL_DCA'][ $strFileName ]['fields']['addImage'];
$GLOBALS['TL_DCA'][ $strFileName ]['fields']['addGallery']['label']  = &$GLOBALS['TL_LANG'][ $strFileName ]['addGallery'];

$GLOBALS['TL_DCA'][ $strFileName ]['fields']['addGallery_lb']       = $GLOBALS['TL_DCA'][ $strFileName ]['fields']['addGallery'];


$GLOBALS['TL_DCA'][ $strFileName ]['fields']['multiSRC']				= $GLOBALS['TL_DCA']['tl_content']['fields']['multiSRC'];
$GLOBALS['TL_DCA'][ $strFileName ]['fields']['multiSRC']['eval']['tl_class']		= trim($GLOBALS['TL_DCA'][ $strFileName ]['fields']['multiSRC']['eval']['tl_class'] . " clr");
$GLOBALS['TL_DCA'][ $strFileName ]['fields']['multiSRC']['eval']['orderField'] 	= 'orderGallerySRC';
$GLOBALS['TL_DCA'][ $strFileName ]['fields']['multiSRC']['eval']['isGallery'] 	= TRUE;
$GLOBALS['TL_DCA'][ $strFileName ]['fields']['multiSRC']['eval']['mandatory'] 	= TRUE;
$GLOBALS['TL_DCA'][ $strFileName ]['fields']['multiSRC']['eval']['extensions'] 	= Config::get('validImageTypes');

$GLOBALS['TL_DCA'][ $strFileName ]['fields']['orderGallerySRC'] = array
(
	'label'					=> &$GLOBALS['TL_LANG']['tl_content']['orderSRC'],
	'sql'					=> "blob NULL"
);

$GLOBALS['TL_DCA'][ $strFileName ]['fields']['sortBy']				= $GLOBALS['TL_DCA']['tl_content']['fields']['sortBy'];
$GLOBALS['TL_DCA'][ $strFileName ]['fields']['metaIgnore']			= $GLOBALS['TL_DCA']['tl_content']['fields']['metaIgnore'];

$GLOBALS['TL_DCA'][ $strFileName ]['fields']['gal_size']				= $GLOBALS['TL_DCA']['tl_content']['fields']['size'];
$GLOBALS['TL_DCA'][ $strFileName ]['fields']['gal_imagemargin']		= $GLOBALS['TL_DCA']['tl_content']['fields']['imagemargin'];
$GLOBALS['TL_DCA'][ $strFileName ]['fields']['gal_perRow']			= $GLOBALS['TL_DCA']['tl_content']['fields']['perRow'];
$GLOBALS['TL_DCA'][ $strFileName ]['fields']['gal_fullsize']			= $GLOBALS['TL_DCA']['tl_content']['fields']['fullsize'];
$GLOBALS['TL_DCA'][ $strFileName ]['fields']['gal_perPage']			= $GLOBALS['TL_DCA']['tl_content']['fields']['perPage'];
$GLOBALS['TL_DCA'][ $strFileName ]['fields']['gal_numberOfItems']	= $GLOBALS['TL_DCA']['tl_content']['fields']['numberOfItems'];

$GLOBALS['TL_DCA'][ $strFileName ]['fields']['galleryTpl']			= $GLOBALS['TL_DCA']['tl_content']['fields']['galleryTpl'];
$GLOBALS['TL_DCA'][ $strFileName ]['fields']['customTpl']			= $GLOBALS['TL_DCA']['tl_content']['fields']['customTpl'];



$GLOBALS['TL_DCA'][ $strFileName ]['fields']['multiSRC_lb'] = $GLOBALS['TL_DCA'][ $strFileName ]['fields']['multiSRC'];
$GLOBALS['TL_DCA'][ $strFileName ]['fields']['multiSRC_lb']['eval']['orderField'] = 'orderGallerySRC_lb';

$GLOBALS['TL_DCA'][ $strFileName ]['fields']['orderGallerySRC_lb'] = $GLOBALS['TL_DCA'][ $strFileName ]['fields']['orderGallerySRC'];


// -- TABLE

$GLOBALS['TL_DCA'][ $strFileName ]['fields']['addTable'] = array
(
	'label'					=> &$GLOBALS['TL_LANG'][ $strFileName ]['addTable'],
	'exclude'				=> true,
	'inputType'				=> 'checkbox',
	'eval'					=> array
	(
		'submitOnChange'		=> true
	),
	'sql'					=> "char(1) NOT NULL default ''"
);

$GLOBALS['TL_DCA'][ $strFileName ]['fields']['tableitems'] = $GLOBALS['TL_DCA']['tl_content']['fields']['tableitems'];


$GLOBALS['TL_DCA'][ $strFileName ]['fields']['tableFirstColBold'] = array
(
	'label'					=> &$GLOBALS['TL_LANG'][ $strFileName ]['tableFirstColBold'],
	'exclude'				=> true,
	'inputType'				=> 'checkbox',
	'eval'					=> array
	(
		'tl_class'				=> 'clr w50'
	),
	'sql'					=> "char(1) NOT NULL default ''"
);



// -- CarouFredSel
$GLOBALS['TL_DCA'][ $strFileName ]['fields']['useCarouFredSel'] = array
(
	'label'					=> &$GLOBALS['TL_LANG'][ $strFileName ]['useCarouFredSel'],
	'exclude'				=> true,
	'inputType'				=> 'checkbox',
	'eval'					=> array
	(
		'submitOnChange'		=> true,
		'tl_class'				=> 'w50 m12'
	),
	'sql'					=> "char(1) NOT NULL default ''"
);

//$GLOBALS['TL_DCA'][ $strFileName ]['fields']['carouFredSelConfig'] = $GLOBALS['TL_DCA']['tl_content']['fields']['dk_cfsCarouFredSel'];



// -- CarouFredSel Thumbnails
$GLOBALS['TL_DCA'][ $strFileName ]['fields']['useCarouFredSelThumbnails'] = array
(
	'label'					=> &$GLOBALS['TL_LANG'][ $strFileName ]['useCarouFredSelThumbnails'],
	'exclude'				=> true,
	'inputType'				=> 'checkbox',
	'eval'					=> array
	(
		'submitOnChange'		=> true,
		'tl_class'				=> 'w50 m12'
	),
	'sql'					=> "char(1) NOT NULL default ''"
);

//$GLOBALS['TL_DCA'][ $strFileName ]['fields']['cfsThumbnailSize']			= $GLOBALS['TL_DCA']['tl_content']['fields']['dk_cfsThumbnailSize'];
//$GLOBALS['TL_DCA'][ $strFileName ]['fields']['cfsThumbnailsPosition'] 	= $GLOBALS['TL_DCA']['tl_content']['fields']['dk_cfsThumbnailsPosition'];
//$GLOBALS['TL_DCA'][ $strFileName ]['fields']['cfsThumbnailsAlign']		= $GLOBALS['TL_DCA']['tl_content']['fields']['dk_cfsThumbnailsAlign'];
//$GLOBALS['TL_DCA'][ $strFileName ]['fields']['cfsThumbnailsWidth']		= $GLOBALS['TL_DCA']['tl_content']['fields']['dk_cfsThumbnailsWidth'];
//$GLOBALS['TL_DCA'][ $strFileName ]['fields']['cfsThumbnailsHeight']		= $GLOBALS['TL_DCA']['tl_content']['fields']['dk_cfsThumbnailsHeight'];



// -- VIDEO
$GLOBALS['TL_DCA'][ $strFileName ]['fields']['addVideo']             = $GLOBALS['TL_DCA'][ $strFileName ]['fields']['addImage'];
$GLOBALS['TL_DCA'][ $strFileName ]['fields']['addVideo']['label']    = &$GLOBALS['TL_LANG'][ $strFileName ]['addVideo'];

//$GLOBALS['TL_DCA'][ $strFileName ]['fields']['youtube']              = $GLOBALS['TL_DCA']['tl_content']['fields']['youtube'];
\IIDO\BasicBundle\Helper\DcaHelper::copyFieldFromTable('youtube', $strFileName, 'youtube', 'tl_content', false, '', array('save_callback'=>array($strFileClass, 'extractYouTubeId')));
\IIDO\BasicBundle\Helper\DcaHelper::copyFieldFromTable('vimeo', $strFileName, 'vimeo', 'tl_content', false, '', array('save_callback'=>array($strFileClass, 'extractVimeoId')));

\IIDO\BasicBundle\Helper\DcaHelper::addSelectField('videoMode', $strFileName, array('includeBlankOption'=>true,'mandatory'=>true), '', false, '', false,true);

\IIDO\BasicBundle\Helper\DcaHelper::copyFieldFromTable('playerSRC', $strFileName, 'playerSRC', 'tl_content');
\IIDO\BasicBundle\Helper\DcaHelper::copyFieldFromTable('posterSRC', $strFileName, 'posterSRC', 'tl_content');
\IIDO\BasicBundle\Helper\DcaHelper::copyFieldFromTable('playerSize', $strFileName, 'playerSize', 'tl_content');
\IIDO\BasicBundle\Helper\DcaHelper::copyFieldFromTable('autoplay', $strFileName, 'autoplay', 'tl_content');



// -- Project
\IIDO\BasicBundle\Helper\DcaHelper::addTextField('location', $strFileName);
\IIDO\BasicBundle\Helper\DcaHelper::addTextField('year', $strFileName, array('rgxp'=>'digit'));



// - ADD TEXT
\IIDO\BasicBundle\Helper\DcaHelper::addTextareaField( 'contactPerson', $strFileName, array(), '', false, true);


$GLOBALS['TL_DCA'][ $strFileName ]['fields']['contactLink'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_content']['contactLink'],
    'exclude'                 => true,
    'search'                  => true,
    'inputType'               => 'text',
    'eval'                    => array('rgxp'=>'url', 'decodeEntities'=>true, 'maxlength'=>255, 'dcaPicker'=>true, 'tl_class'=>'clr w50 wizard'),
    'sql'                     => "varchar(255) NOT NULL default ''"
);

\IIDO\BasicBundle\Helper\DcaHelper::addTextField('contactLinkTitle', $strFileName );



// - BLOCKQUOTE
$GLOBALS['TL_DCA'][ $strFileName ]['fields']['blockquotes'] = array
(
    'label'                   => &$GLOBALS['TL_LANG'][ $strFileName ]['blockquotes'],
    'exclude'                 => true,
    'inputType'               => 'multiColumnWizard',
    'eval'                    => array
    (
        'tl_class'          => 'clr',
        'columnFields'      => array
        (
            'blockquote' =>
                [
                    'label'     => array('Zitat / Text'),
                    'exclude'   => true,
                    'inputType' => 'textarea',
                    'eval'      => [ 'style' => 'width:600px' ],
                ],

            'author' =>
                [
                    'label'     => array('Author / Quelle'),
                    'exclude'   => true,
                    'inputType' => 'text',
                    'eval'      => [ 'style' => 'width:250px' ],
                ]
        )
    ),
    'sql'                     => "blob NULL"
);



// - Project Infos

\IIDO\BasicBundle\Helper\DcaHelper::addTextField('projectStatus', $strFileName);
\IIDO\BasicBundle\Helper\DcaHelper::addTextField('projectLeistung', $strFileName);
\IIDO\BasicBundle\Helper\DcaHelper::addTextField('projectPhotos', $strFileName);



// - Slogan

\IIDO\BasicBundle\Helper\DcaHelper::addTextareaField('sloganTextBig', $strFileName, [], '', false, true );
\IIDO\BasicBundle\Helper\DcaHelper::addTextField('sloganTextSmall', $strFileName);
\IIDO\BasicBundle\Helper\DcaHelper::addTextField('sloganClass', $strFileName);
//\IIDO\BasicBundle\Helper\DcaHelper::copyFieldFromTable('sloganTextSmallFloating', $strFileName, 'textSmallFloating', 'tl_content');
//\IIDO\BasicBundle\Helper\DcaHelper::copyFieldFromTable('sloganTextSmallMargin', $strFileName, 'textSmallMargin', 'tl_content');
\IIDO\BasicBundle\Helper\DcaHelper::addPositionField('sloganTextSmallMargin', $strFileName);

$GLOBALS['TL_DCA'][ $strFileName ]['fields']['sloganTextSmallFloating'] = array
(
    'label'                 => array('Text Ausrichtung', ''),
    'default'               =>'header_left',
    'inputType'             => 'radioTable',
    'options'               => $GLOBALS['TL_LANG']['tl_content']['options']['headlineFloating'],
    'eval'                  => array('cols'=>3, 'tl_class'=>'w50'),
    'sql'                   => "varchar(32) NOT NULL default ''"
);

//        'textSmallFloating' => array
//(
//    'label'                 => array('Text Ausrichtung', ''),
//    'default'                 =>'header_left',
//    'inputType'               => 'radioTable',
//    'options'                 => $GLOBALS['TL_LANG']['tl_content']['options']['headlineFloating'],
//    'eval'                    => array('cols'=>3, 'tl_class'=>'w50'),
//),

//        'textSmallMargin' => array
//(
//    'label'     => array('Text Verschiebung', ''),
//    'inputType'         => 'trbl',
//    'options'           => $GLOBALS['TL_CSS_UNITS'],
//    'eval'              => array('tl_class'=>'w50'),
//),



// add texts
\IIDO\BasicBundle\Helper\DcaHelper::addTextField('text2Headline', $strFileName );
\IIDO\BasicBundle\Helper\DcaHelper::addTextField('text3Headline', $strFileName );

\IIDO\BasicBundle\Helper\DcaHelper::addTextareaField('text2', $strFileName, [], 'w50 hauto', false, true );
\IIDO\BasicBundle\Helper\DcaHelper::addTextareaField('text3', $strFileName, [], 'w50 hauto', true, true );