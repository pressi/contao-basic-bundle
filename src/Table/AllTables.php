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
 * General Table Class
 *
 * @package IIDO\BasicBundle\Table
 * @author Stephan Preßl <https://github.com/pressi>
 */
class AllTables
{

    /**
     * Strip all not allowed tags
     *
     * @param string $varValue
     * @param \DataContainer $dc
     *
     * @return string
     */
    public function strip_all_tags($varValue, \DataContainer $dc)
    {
        $allowedTags = '<strong></strong><em></em><span></span><light></light><br>';

        if(is_array(\StringUtil::deserialize($varValue)))
        {
            $varValue           = \StringUtil::deserialize($varValue);
            $varValue['value']  = strip_tags($varValue['value'], $allowedTags);
            $varValue           = serialize($varValue);
        }
        else
        {
            $varValue = strip_tags($varValue, $allowedTags);
        }

        return $varValue;
    }


    /**
     * Return the link picker wizard
     *
     * @param \DataContainer|array $dc
     *
     * @return string
     */
    public function pagePicker($dc, $objWidget)
    {
        $id         = 0;
        $table      = "";
        $field      = "";
        $value      = "";

        if($dc instanceof \DataContainer)
        {
            $id     = $dc->id;
            $value  = $dc->value;
            $table  = $dc->table;
            $field  = $dc->field;
        }
        elseif( is_array($dc) )
        {
            $id     = $dc['id'];
            $value  = $dc['value'];
            $table  = $dc['table'];
            $field  = $dc['field'];
        }

        $title      = \StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['pagepicker']);
        $modalTitle = \StringUtil::specialchars(str_replace("'", "\\'", $GLOBALS['TL_DCA'][ $table ]['fields'][ $field ]['label'][0]));
        $href       = (($value == '' || strpos($value, '{{link_url::') !== false) ? 'contao/page' : 'contao/file') . '?do=' . \Input::get('do') . '&amp;table=' . $table . '&amp;field=' . $field . '&amp;value=' . rawurlencode(str_replace(array('{{link_url::', '}}'), '', $value)) . '&amp;switch=1';

        if( !strlen($modalTitle) )
        {
            preg_match_all('/_row([0-9]{0,10})_([A-Za-z0-9\-_]{0,})$/', $field, $arrMatches);

            $strField   = preg_replace('/_row([0-9]{0,10})_([A-Za-z0-9\-_]{0,})$/', '', $field);
            $modalTitle =  \StringUtil::specialchars(str_replace("'", "\\'",  $GLOBALS['TL_DCA'][ $table ]['fields'][ $strField ]['label'][0] . ' - ' . $GLOBALS['TL_DCA'][ $table ]['fields'][ $strField ]['eval']['columnFields'][ $arrMatches[2][0] ]['label']));
        }

        return ' <a href="' . $href . '" title="' . $title . '" onclick="Backend.getScrollOffset();Backend.openModalSelector({\'width\':768,\'title\':\'' . $modalTitle . '\',\'url\':this.href,\'id\':\'' . $field . '\',\'tag\':\'ctrl_'. $field . ((\Input::get('act') == 'editAll') ? '_' . $id : '') . '\',\'self\':this});return false">' . \Image::getHtml('pickpage.svg', $GLOBALS['TL_LANG']['MSC']['pagepicker']) . '</a>';
    }



    /**
     * Return the file picker wizard
     *
     * @param \DataContainer|array $dc
     *
     * @return string
     */
    public function filePicker($dc)
    {
        $strId      = 0;
        $strTable   = "";
        $strField   = "";
        $strValue   = "";
        $strIField  = "";

        if( is_array($dc) )
        {
            $strId      = $dc['id'];
            $strField   = $dc['field'];
            $strTable   = $dc['table'];
            $strValue   = $dc['value'];

            $strIField  = (($dc['ifield']) ? $dc['ifield'] : $strField);
        }
        elseif($dc instanceof \DataContainer)
        {
            $strId      = $dc->id;
            $strField   = $dc->field;
            $strTable   = $dc->table;
            $strValue   = $dc->value;

            $strIField  = (($dc->ifield) ? $dc->iFfeld : $strField);
        }

        $strTitle   = specialchars(str_replace("'", "\\'", $GLOBALS['TL_LANG']['MSC']['filepicker']));
        $onClick    = "Backend.getScrollOffset();Backend.openModalSelector({'width':765,'title':'" . specialchars($GLOBALS['TL_LANG']['MOD']['files'][0]) . "','url':this.href,'id':'" . $strField. "','tag':'ctrl_" . $strIField . ((\Input::get('act') == 'editAll') ? '_' . $strId : '') . "','self':this});return false";
        $strImage   = \Image::getHtml('pickfile.gif', $GLOBALS['TL_LANG']['MSC']['filepicker'], 'style="vertical-align:top;cursor:pointer"');

        return ' <a href="contao/file.php?do=' . \Input::get('do') . '&amp;table=' . $strTable . '&amp;field=' . $strIField . '&amp;value=' . $strValue . '" title="' . $strTitle . '" onclick="' . $onClick . '">' . $strImage . '</a>';
    }



//	public static function staticPagePicker( array $arrData )
//	{
//		$strTitle	= specialchars($GLOBALS['TL_LANG']['MSC']['pagepicker']);
//		$onClick	= "Backend.getScrollOffset();Backend.openModalSelector({'width':765,'title':'" . specialchars(str_replace("'", "\\'", $GLOBALS['TL_LANG']['MOD']['page'][0])) . "','url':this.href,'id':'" . $arrData['field'] . "','tag':'ctrl_" . $arrData['field'] . ((\Input::get('act') == 'editAll') ? '_' . $arrData['id'] : '') . "','self':this});return false";
//		$strImage	= \Image::getHtml('pickpage.gif', $GLOBALS['TL_LANG']['MSC']['pagepicker'], 'style="vertical-align:top;cursor:pointer"');
//
//		return ' <a href="contao/page.php?do=' . \Input::get('do') . '&amp;table=' . $arrData['table'] . '&amp;field=' . $arrData['field'] . '&amp;value=' . str_replace(array('{{link_url::', '}}'), '', $arrData['value']) . '" title="' . $strTitle . '" onclick="' . $onClick . '">' . $strImage . '</a>';
//	}



    /**
     * Pre-fill the "alt" and "caption" fields with the file meta data
     *
     * @param mixed $varValue
     * @param \DataContainer $dc
     *
     * @return mixed
     */
    public function storeFileMetaInformation($varValue, \DataContainer $dc)
    {
        if ($dc->activeRecord->singleSRC === $varValue)
        {
            return $varValue;
        }

        $objFile = \FilesModel::findByUuid($varValue);

        if ($objFile !== null)
        {
            $arrMeta = deserialize($objFile->meta);

            if (!empty($arrMeta))
            {
                $strLanguage = \System::getContainer()->get('request_stack')->getCurrentRequest()->getLocale();

                if ( isset($arrMeta[$strLanguage]) )
                {
                    \Input::setPost('alt', $arrMeta[$strLanguage]['title']);
                    \Input::setPost('caption', $arrMeta[$strLanguage]['caption']);
                }
            }
        }

        return $varValue;
    }



    /**
     * Generates a filePicker icon for Contao Version > 3.1
     *
     * @param \DataContainer $dc
     *
     * @return string
     */
    public function rewFilePicker(\DataContainer $dc)
    {
        return ' <a href="contao/file.php?do='.\Input::get('do').'&amp;table='.$dc->table.'&amp;field='.preg_replace('/_row[0-9]*_/i', '__', $dc->field).'&amp;value='.$dc->value.'" title="'.specialchars(str_replace("'", "\\'", $GLOBALS['TL_LANG']['MSC']['filepicker'])).'" onclick="Backend.getScrollOffset();Backend.openModalSelector({\'width\':765,\'title\':\''.specialchars($GLOBALS['TL_LANG']['MOD']['files'][0]).'\',\'url\':this.href,\'id\':\''.$dc->field.'\',\'tag\':\'ctrl_'.$dc->field . ((\Input::get('act') == 'editAll') ? '_' . $dc->id : '').'\',\'self\':this});return false">' . \Image::getHtml('pickfile.gif', $GLOBALS['TL_LANG']['MSC']['filepicker'], 'style="vertical-align:top;cursor:pointer"') . '</a>';
    }



    /**
     * Auto-generate an article alias if it has not been set yet
     *
     * @param mixed         $varValue
     * @param \DataContainer $dc
     *
     * @return string
     *
     * @throws \Exception
     */
    public function generateAlias($varValue, \DataContainer $dc)
    {
        echo "<pre>"; print_r( $dc ); exit;
        $prefix     = 'article-';
        $strTable   = '';

        $autoAlias = false;

        // Generate an alias if there is none
        if ($varValue == '')
        {
            $autoAlias = true;
            $slugOptions = array();

            // Read the slug options from the associated page
            if (($objPage = \PageModel::findWithDetails($dc->activeRecord->pid)) !== null)
            {
                $slugOptions['locale'] = $objPage->language;

                if ($objPage->validAliasCharacters)
                {
                    $slugOptions['validChars'] = $objPage->validAliasCharacters;
                }
            }

            $varValue = \System::getContainer()->get('contao.slug.generator')->generate(\StringUtil::stripInsertTags($dc->activeRecord->title), $slugOptions);
        }

        // Add a prefix to reserved names (see #6066)
        if ($strTable === "tl_article" && \in_array($varValue, array('top', 'wrapper', 'header', 'container', 'main', 'left', 'right', 'footer')))
        {
            $varValue = $prefix . $varValue;
        }

        $objAlias = \Database::getInstance()->prepare("SELECT id FROM " . $strTable . " WHERE id=? OR alias=?")->execute($dc->id, $varValue);

        // Check whether the page alias exists
        if ($objAlias->numRows > 1)
        {
            if (!$autoAlias)
            {
                throw new \Exception(sprintf($GLOBALS['TL_LANG']['ERR']['aliasExists'], $varValue));
            }

            $varValue .= '-' . $dc->id;
        }

        return $varValue;
    }
}
