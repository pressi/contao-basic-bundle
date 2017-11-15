<?php
/*******************************************************************
 *
 * (c) 2017 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 *
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *
 *******************************************************************/

namespace IIDO\BasicBundle\Ajax;



/**
 * IIDO Ajax Manager
 *
 * @author Stephan Preßl <https://github.com/pressi>
 * @deprecated NOT IN USE!
 */
class ManageAjax extends \Frontend
{


	public function parseAjaxRequest()
	{
		$className		= str_replace('_', '\\', \Input::post("c") );
		$function		= \Input::post("f");
		$value			= \Input::post("v");

		if( !strlen($className) && !strlen($function) )
		{
			$className		= str_replace('_', '\\', \Input::get("c") );
			$function		= \Input::get("f");
			$value			= \Input::get("v");
		}

		if( preg_match('/^([A-Za-z]{1})/', $className) )
		{
			$className = '\\' . $className;
		}

		if( class_exists($className) )
		{
			if( method_exists($this, $function) )
			{
				$this->$function( $value );
			}
			else
			{
				$objClass = new $className();

				if( method_exists($objClass, $function) )
				{
					$objClass->$function( $value );
				}
			}
		}
	}
}
