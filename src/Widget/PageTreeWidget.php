<?php

namespace IIDO\BasicBundle\Widget;

/**
 * Provide methods to handle input field "page tree".
 *
 * @property string  $orderField
 * @property boolean $multiple
 * @property array   $rootNodes
 * @property string  $fieldType
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class PageTreeWidget extends \PageTree
{
	/**
	 * Generate the widget and return it as string
	 *
	 * @return string
	 */
	public function generate()
	{
		$return = parent::generate();

        if( $this->isMetaField )
        {
            $strNewName = $this->metaPrefix . '[' . $this->metaLang . '][' . $this->metaField . ']';
            $return     = preg_replace('/name="' . $this->strField . '"/', 'name="' . $strNewName . '"', $return);
        }

		return $return;
	}
}
