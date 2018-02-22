<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\Table;


/**
 * Class Layout
 * @package IIDO\Customize\Table
 */
class LayoutTable extends \Backend
{

	protected $strTable				= 'tl_layout';

	protected $mootoolsField		= 'mootoolsRedirect';
	protected $jqueryField			= 'jqueryRedirect';



	public function layoutMootoolsRedirect(\DC_Table $dc)
	{
		$this->loadDataContainer('tl_layout');

		return $this->layoutRedirect($dc, "mootools");
	}


	public function layoutJQueryRedirect(\DC_Table $dc)
	{
		$this->loadDataContainer('tl_layout');

		return $this->layoutRedirect($dc, "jquery");
	}


	protected function layoutRedirect(\DC_Table $dc, $mode = "mootools")
	{
		//Variables
		$activeRecord 		= $dc->activeRecord;
		$strTemplates		= deserialize( (($mode == "mootools") ? $activeRecord->mootools : $activeRecord->jquery) );
		$strField			= (($mode == "mootools") ? $this->mootoolsField : $this->jqueryField);
		$content 			= '<div class="clr w50 hauto">';

		if( !is_array($strTemplates) )
		{
			$strTemplates = array();
		}

		if ($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			$rawValue 	= $this->saveData($_POST[ $strField ] ?: '', $dc, $mode);
		}
		else
		{
			$field 		= (($mode == "mootools") ? $this->mootoolsField : $this->jqueryField);
			$rawValue 	= $activeRecord->$field;
		}

		$value 		= $rawValue;
		$inArray	= (($mode == "mootools") ? 'moo_redirect' : 'j_redirect');


		if(in_array($inArray, $strTemplates))
		{
			$content .= '<h3><label for="ctrl_' . $mode . 'Redirect">Weiterleitung (Redirect) Ziel</label></h3>';

			$arrData 			= $GLOBALS['TL_DCA'][ $this->strTable ]['fields'][ $strField ];
			$arrData['name']	= $inArray;

			$arrWidget 			= \Widget::getAttributesFromDca($arrData, $strField, $value, $strField, $this->strTable);
			$objWidget 			= new $GLOBALS['BE_FFL']['pageTree']( $arrWidget );

			$content .= $objWidget->generate();
		}
		return $content . '</div>';
	}



	public function saveData($sent, \DC_Table $dc, $mode = "mootools")
	{
		$ceId 	= (int) $dc->activeRecord->id;
		$db		= \Database::getInstance();

		$field 	= (($mode == "mootools") ? $this->mootoolsField : $this->jqueryField);

		$fields	= array
		(
			$field		=> $sent
		);

		$db->prepare('UPDATE ' . $this->strTable . ' %s WHERE id=?')->set($fields)->execute($ceId);

		return $sent;
	}
}
