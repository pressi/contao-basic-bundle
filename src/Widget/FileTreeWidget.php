<?php
/*******************************************************************
 * (c) 2019 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\Widget;



/**
 * Provide methods to handle input field "file tree".
 *
 * @property string  $orderField
 * @property boolean $multiple
 * @property boolean $isGallery
 * @property boolean $isDownloads
 * @property boolean $files
 * @property boolean $filesOnly
 * @property string  $path
 * @property string  $extensions
 * @property string  $fieldType
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class FileTreeWidget extends \FileTree
{

	/**
	 * Generate the widget and return it as string
	 *
	 * @return string
	 */
	public function generate()
	{
        $strField = parent::generate();

        if( $this->isMetaField )
        {
            $strNewName = $this->metaPrefix . '[' . $this->metaLang . '][' . $this->metaField . ']';
            $strField   = preg_replace('/name="' . $this->strField . '"/', 'name="' . $strNewName . '"', $strField);

//            $strField   = preg_replace('/"name":"' . $this->metaField . '_([\d]{0,})"/', '"name":"' . $this->metaField . '"', $strField);
        }

        /*
        $("ft_previewImage_0") . addEvent( "click", function(e)
        {
            e.preventDefault();
            Backend.openModalSelector({"id":"tl_listing","title":"","url":this . href + document . getElementById( "ctrl_previewImage_0" ) . value,"callback":function(table,value)
        {
            new Request . Contao({evalScripts:!1,onSuccess:function(txt,json)
        {
                $("ctrl_previewImage_0") . getParent( "div" ) . set( "html", json . content );
                json . javascript && Browser . exec( json . javascript )}}).post({"action":"reloadFiletree","name":"previewImage_0","value":value . join( "\t" ),"REQUEST_TOKEN":"_kpbzQn5a9R406Wtj_MsMobarzg46rO9tpEwcZ2jn2g"})}})} )

        */

		return $strField;
	}
}
