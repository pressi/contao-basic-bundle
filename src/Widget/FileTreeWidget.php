<?php
/*******************************************************************
 * (c) 2020 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\Widget;


use Contao\FileTree;


class FileTreeWidget extends FileTree
{


	/**
	 * Generate the widget and return it as string
	 *
	 * @return string
	 */
	public function generate()
	{
		$return = parent::generate();

		if( $this->alternativeFileTree )
        {
            $join = '\t';

            if( $this->fieldType === 'checkbox' )
            {
                $join = ',';
            }

            $newFunction = '$("ctrl_' . $this->strId . '").value = value.join("' . $join . '");
            
            $("sort_' . $this->strId . '").set("html", \'<li data-id="\' + value.join("\t") + \'"></li>\');
            
            Backend.autoSubmit("' . $this->strTable . '");';


            $return = preg_replace('/new Request.Contao\(\{([A-Za-z0-9\s\n\-\\\\,;.:_\(\)\{\}>$"\'&]{0,})REQUEST_TOKEN":"([A-Za-z0-9\-_]+)"\}\);/', $newFunction, $return);
        }

		return $return;
	}
}