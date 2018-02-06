<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

$config         = \Config::getInstance();
$db             = \Database::getInstance();

$act            = \Input::get("act");
$id             = \Input::get("id");

$objLayout      = null;


if( $act == "edit" )
{
    $objBeLayout = $db->prepare("SELECT * FROM tl_layout WHERE id=?")->limit(1)->execute($id);

    if($objBeLayout->numRows > 0)
    {
        $objLayout = $objBeLayout->first();
    }
}



/**
 * Table tl_layout
 */

$GLOBALS['TL_DCA']['tl_layout']['palettes']['default']                  = str_replace(',combineScripts', ',combineScripts,loadDomainCSS;', $GLOBALS['TL_DCA']['tl_layout']['palettes']['default']);
$GLOBALS['TL_DCA']['tl_layout']['palettes']['default']                  = str_replace(',analytics', ',analytics,externalJavascript', $GLOBALS['TL_DCA']['tl_layout']['palettes']['default']);


if($objLayout != null && !$objLayout->footerAtBottom)
{
    $GLOBALS['TL_DCA']['tl_layout']['palettes']['default']              = str_replace(';{sections_legend', ',addPageWrapperOuter,addPageWrapperPage,addPageWrapperOuterPageWrapper;{sections_legend', $GLOBALS['TL_DCA']['tl_layout']['palettes']['default']);
}


$GLOBALS['TL_DCA']['tl_layout']['subpalettes']['rows_2rwf']             = str_replace('footerHeight', 'footerHeight,footerAtBottom', $GLOBALS['TL_DCA']['tl_layout']['subpalettes']['rows_2rwf']);
$GLOBALS['TL_DCA']['tl_layout']['subpalettes']['rows_3rw']              = str_replace(',footerHeight', ',footerHeight,footerAtBottom', $GLOBALS['TL_DCA']['tl_layout']['subpalettes']['rows_3rw']);

// TODO: Redirect Page Type!!
//$GLOBALS['TL_DCA']['tl_layout']['subpalettes']['addMooTools']           = str_replace(',mootools', ',mootools,mootoolsRedirect', $GLOBALS['TL_DCA']['tl_layout']['subpalettes']['addMooTools']);
//$GLOBALS['TL_DCA']['tl_layout']['subpalettes']['addJQuery']             = str_replace(',jquery', ',jquery,jqueryRedirect', $GLOBALS['TL_DCA']['tl_layout']['subpalettes']['addJQuery']);



/**
 * Fields
 */

//$GLOBALS['TL_DCA']['tl_layout']['fields']['mootools']['eval']['submitOnChange']     = true;
//$GLOBALS['TL_DCA']['tl_layout']['fields']['jquery']['eval']['submitOnChange']       = true;


//$GLOBALS['TL_DCA']['tl_layout']['fields']['mootoolsRedirect'] = array
//(
//	'label'						=> &$GLOBALS['TL_LANG']['tl_layout']['mootoolsRedirect'],
//	'exlude'					=> true,
//	'inputType'					=> 'pageTree',
//	'foreignKey'				=> 'tl_page.title',
//	'eval'						=> array
//	(
//		'fieldType'					=> 'radio'
//	),
//	'input_field_callback'		=> array('IIDO\BasicBundle\Table\LayoutTable', 'layoutMootoolsRedirect'),
//	'sql'						=> "int(10) unsigned NOT NULL default '0'",
//	'relation'					=> array('type'=>'hasOne', 'load'=>'eager')
//);
//
//$GLOBALS['TL_DCA']['tl_layout']['fields']['jqueryRedirect']             = $GLOBALS['TL_DCA']['tl_layout']['fields']['mootoolsRedirect'];
//$GLOBALS['TL_DCA']['tl_layout']['fields']['jqueryRedirect']['label']    = &$GLOBALS['TL_LANG']['tl_layout']['jqueryRedirect'];
//$GLOBALS['TL_DCA']['tl_layout']['fields']['jqueryRedirect']['input_field_callback'][1] = 'layoutJQueryRedirect';



//$GLOBALS['TL_DCA']['tl_layout']['fields']['redirectTimeout'] = array
//(
//	'label'						=> &$GLOBALS['TL_LANG']['tl_layout']['redirectTimeout'],
//	'exclude'					=> true,
//	'inputType'					=> 'text',
//	'eval'						=> array
//	(
//		'rgxp'						=> 'natural',
//		'tl_class'					=> 'w50'
//	),
//	'sql'						=> "smallint(5) unsigned NOT NULL default '0'"
//);

$GLOBALS['TL_DCA']['tl_layout']['fields']['master_ID'] = array
(
    'sql'                       => "int(10) unsigned NOT NULL"
);


$GLOBALS['TL_DCA']['tl_layout']['fields']['footerAtBottom'] = array
(
	'label'						=> &$GLOBALS['TL_LANG']['tl_layout']['footerAtBottom'],
	'inputType'					=> 'checkbox',
	'eval'						=> array
	(
		'tl_class'					=> 'clr w50 m12',
		'submitOnChange'			=> true
	),
	'sql'						=> "char(1) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_layout']['fields']['addPageWrapperOuter'] = array
(
	'label'						=> &$GLOBALS['TL_LANG']['tl_layout']['addPageWrapperOuter'],
	'inputType'					=> 'checkbox',
	'eval'						=> array
	(
		'tl_class'					=> 'clr w50 m12'
	),
	'sql'						=> "char(1) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_layout']['fields']['addPageWrapperPage'] = array
(
	'label'						=> &$GLOBALS['TL_LANG']['tl_layout']['addPageWrapperPage'],
	'inputType'					=> 'checkbox',
	'eval'						=> array
	(
		'tl_class'					=> 'w50 m12'
	),
	'sql'						=> "char(1) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_layout']['fields']['addPageWrapperOuterPageWrapper'] = array
(
	'label'						=> &$GLOBALS['TL_LANG']['tl_layout']['addPageWrapperOuterPageWrapper'],
	'inputType'					=> 'checkbox',
	'eval'						=> array
	(
		'tl_class'					=> 'w50 m12'
	),
	'sql'						=> "char(1) NOT NULL default ''"
);


$GLOBALS['TL_DCA']['tl_layout']['fields']['externalJavascript'] = array
(
	'label'						=> &$GLOBALS['TL_LANG']['tl_layout']['externalJavascript'],
	'exlude'					=> true,
	'inputType'					=> 'fileTree',
	'eval'						=> array
	(
		'multiple'					=> true,
		'orderField'				=> 'orderExternalJavascript',
		'fieldType'					=> 'checkbox',
		'filesOnly'					=> true,
		'extensions'				=> 'js'
	),
	'sql'						=> "blob NULL"
);

$GLOBALS['TL_DCA']['tl_layout']['fields']['orderExternalJavascript'] = array
(
	'label'						=> &$GLOBALS['TL_LANG']['tl_layout']['orderExternalJavascript'],
	'sql'						=> "blob NULL"
);

$GLOBALS['TL_DCA']['tl_layout']['fields']['loadDomainCSS'] = array
(
	'label'						=> &$GLOBALS['TL_LANG']['tl_layout']['loadDomainCSS'],
	'inputType'					=> 'checkbox',
	'eval'						=> array
	(
		'tl_class'					=> 'w50 m12'
	),
	'sql'						=> "char(1) NOT NULL default ''"
);