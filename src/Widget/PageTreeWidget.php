<?php


namespace IIDO\BasicBundle\Widget;


use Contao\PageTree;


class PageTreeWidget extends PageTree
{


	/**
	 * Generate the widget and return it as string
	 *
	 * @return string
	 */
	public function generate()
	{
		$return = parent::generate();

		if( $this->alternativePageTree )
        {
            $newFunction = '$("ctrl_' . $this->strId . '").value = value.join("\t");
            
            $("sort_' . $this->strId . '").set("html", \'<li data-id="\' + value.join("\t") + \'"></li>\');
            
            Backend.autoSubmit("' . $this->strTable . '");';


            $return = preg_replace('/new Request.Contao\(\{([A-Za-z0-9\s\n\-\\\\,;.:_\(\)\{\}>$"\'&]{0,})REQUEST_TOKEN":"([A-Za-z0-9]+)"\}\);/', $newFunction, $return);
        }

		return $return;
	}
}