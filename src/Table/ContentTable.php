<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\Table;


use IIDO\BasicBundle\Helper\BasicHelper;


/**
 * Class Content Table
 * @package IIDO\BasicBundle\Table
 */
class ContentTable extends \Backend
{

    protected $strTable             = 'tl_content';

    protected $ornamentePath        = "files/Library/Images/Ornamente/"; //TODO: wartbar machen?!



    public function getNavigationModule( $dc )
    {
        $activeRecord   = $dc->activeRecord;

        $arrModules     = array(''=>'-');
//        $objModules     = \ModuleModel::findByType('navigation');
        $objModules     = \Database::getInstance()
                                ->prepare("SELECT * FROM tl_module WHERE type=? OR type=? OR type=? OR type=?")
                                ->execute('navigation', 'booknav', 'articlenav', 'customnav');

        $objArticle     = \ArticleModel::findByPk( $activeRecord->pid );
        $objPage        = \PageModel::findByPk( $objArticle->pid );
        $objLayout      = BasicHelper::getPageLayout( $objPage );
        $objTheme       = \ThemeModel::findByPk( $objLayout->pid );

        if( $objModules )
        {
            while( $objModules->next() )
            {
                if( $objModules->pid === $objTheme->id )
                {
                    $arrModules[ $objModules->id ] = $objModules->name;
                }
            }
        }

        return $arrModules;
    }


    public function parseHeadlineImagePostionField(\DC_Table $dc, $strLabel)
    {
        $strContent		= "";
        $activeRecord	= $dc->activeRecord;
        $strFieldName	= $dc->field;
        $varValue		= $activeRecord->$strFieldName;
        $floating		= $activeRecord->floating;
        $arrOptions		= array
        (
            'top'			=> array
            (
                'value'			=> 'top',
                'label'			=> $GLOBALS['TL_LANG']['tl_content']['reference'][ $strFieldName ]['top']
            ),
            'bottom'		=> array
            (
                'value'			=> 'bottom',
                'label'			=> $GLOBALS['TL_LANG']['tl_content']['reference'][ $strFieldName ]['bottom']
            ),
        );

        if( \Input::post("FORM_SUBMIT") == $this->strTable)
        {
            $varValue = $this->saveData($strFieldName, $dc);
        }

        if( strlen($floating) )
        {
            switch( $floating )
            {
                case "above":
                case "below":
                    // top and bottom are always shown
                    break;

                case "left":
                case "right":
                    $arrOptions['nextTo'] = array
                    (
                        'value'	=> 'nextTo',
                        'label'	=> $GLOBALS['TL_LANG']['tl_content']['reference'][ $strFieldName ]['nextTo']
                    );
                    break;
            }

            $arrField	= $GLOBALS['TL_DCA']['tl_content']['fields'][ $strFieldName ];
            $inputType	= $arrField['inputType'];
            $strClass	= $GLOBALS['BE_FFL'][ $inputType ];
            /* @var $strClass \SelectMenu */

            $objWidget = new $strClass( $strClass::getAttributesFromDca($arrField, $dc->inputName, $varValue, $strFieldName, $dc->table, $dc) );
            $objWidget->options		= $arrOptions;
            $objWidget->xlabel		= $strLabel;

            $strContent = '<div class="' . $arrField['eval']['tl_class'] . ' widget">' . $objWidget->parse() . '</div>';
        }

        return $strContent;
    }



    public function saveData($fieldName, \DC_Table $dc )
    {
        $ceId 	= (int) $dc->activeRecord->id;
        $db		= \Database::getInstance();
        $sent	= \Input::post( $fieldName );

        if( !$sent )
        {
            $sent = $_POST[ $fieldName ];
        }

        $fields	= array
        (
            $fieldName		=> $sent
        );

        $db->prepare('UPDATE ' . $this->strTable . ' %s WHERE id=?')->set($fields)->execute($ceId);

        return $sent;
    }



    public function ornamentField(\DC_Table $dc)
    {
        $config = \Config::getInstance();

        $pathToOrnamente		= $config->get('iidoCustomize_pathToOrnamente');

        if ($_SERVER['REQUEST_METHOD'] == 'POST')
        {
            $rawValue = $this->saveData('ornament', $dc);
        }
        else
        {
            $rawValue = $dc->activeRecord->ornament;
        }

        $value = array(
            'element' => !empty($rawValue) ? $rawValue : ''
        );

        $path = (($pathToOrnamente && strlen($pathToOrnamente)) ? $pathToOrnamente : $this->ornamentePath);

        $items = $this->getOrnamentItems($path);
        $light = $dc->activeRecord->ornamentLight;

        $shownItems = (($light) ? $items['light'] : $items['normal']);

        $renteredItems = '';
        $i = 0;

        if( count($shownItems) > 0)
        {
            foreach($shownItems as $item)
            {
                if( !file_exists(TL_ROOT . '/' . $item) )
                {
                    continue;
                }

                $image 		= \Image::get($item, 180, 90, 'proportional');
                $imageSize	= getimagesize(TL_ROOT . '/' . $image);
                $height		= $imageSize[1];
                $imgSRC		= $image;

                $marginTop	= (100 - $height) / 2;

                $checked = '';

                if($item == $value['element'])
                {
                    $checked = ' checked';
                }

                $img		= '<label for="ornament_image_' . $i . '"><img src="' . $imgSRC . '"' . (($marginTop > 0) ? ' style="margin-top:' . $marginTop . 'px;"' : '') . '></label>';
                $input 		= '<input type="radio" name="ornament" id="ornament_image_' . $i . '" value="' . $item . '"' . $checked . '>';

                $renteredItems .= 	'<div class="item' . (($light) ? ' item-light' : '') . '">'
                    .	'<div class="tl_left">' . $input . '</div>'
                    . 	'<div class="tl_right">' . $img . '</div>'
                    .	'<div class="clear"></div>'
                    .	'</div>';

                $i++;
            }
        }
        else
        {
            $renteredItems = "Keine Ornamente vorhanden!";
        }


        $content =
            '<div class="clr">' .
            '<h3>Ornament auswählen</h3>' .
            '<div class="w100 ornament-liste" style="width:100%;height:auto;">' . $renteredItems . '</div>' .
            '<div class="clear"></div>' .
            '</div>';

        return $content;
    }



    protected function getOrnamentItems($path)
    {
        $elements = array
        (
            'normal'	=> array(),
            'light'		=> array()
        );

        $objFiles = \FilesModel::findMultipleByBasepath( $path );

        if($objFiles && $objFiles->count() > 0)
        {
            while( $objFiles->next() )
            {
                $objFile = $objFiles->current();
                /* @var $objFile \Contao\FilesModel */

                if(!preg_match('/light/', $objFile->name))
                {
                    $elements['normal'][] = $objFile->path;
                }
                else
                {
                    $elements['light'][] = $objFile->path;
                }
            }
        }

        return $elements;
    }



    /**
     * Add the type of content element
     * @param array
     * @return string
     */
    public function addContentTitle( $arrRow )
    {
        $objTableContent    = new \tl_content();
        $strContent         = $objTableContent->addCteType( $arrRow );
        $addContentTitle    = "";

        if( $arrRow['elementIsBox'] )
        {
            $addContentTitle = "Box-Element " . str_replace('w', '', $arrRow['boxWidth']) . "x" . str_replace('h', '', $arrRow['boxHeight']);
        }

        if( strlen($addContentTitle) )
        {
            $addContentTitle = " - " . $addContentTitle;
        }

        if( $arrRow['type'] === "newslist" )
        {
            $newsConfig     = '';
            $arrArchives    = \StringUtil::deserialize($arrRow['news_archives'], TRUE);

            if( count($arrArchives) )
            {
                $newsConfig = 'Archiv' . (count($arrArchives) > 1 ? 'e: ' : ': ');

                foreach($arrArchives as $num => $archiveID)
                {
                    $objArchive = \NewsArchiveModel::findByPk( $archiveID );

                    if( $objArchive )
                    {
                        if( $num > 0 )
                        {
                            $newsConfig .= ', ';
                        }

                        $newsConfig .= $objArchive->title;
                    }
                }
            }

            $newsConfig .= (strlen($newsConfig) ? ' / ' : '') . 'Anzahl: ' . $arrRow['numberOfItems'];
            $newsConfig .= (strlen($newsConfig) ? ' / ' : '') . 'Anzeige: ' . $GLOBALS['TL_LANG']['tl_module'][ $arrRow['news_featured'] ];

            $strContent = preg_replace('/<div([A-Za-z0-9\s\-=":;,._]{0,})class="tl_gray([A-Za-z0-9\s\-_]{0,})"([A-Za-z0-9\s\-=":;,.]{0,})>([A-Za-z0-9\s\-#;,:._]{0,})<\/div>/', '<div$1class="tl_gray$2"$3>$4<br>' . $newsConfig . '</div>', $strContent);
        }

        if( $arrRow['internName'] )
        {
            $addContentTitle .= ' (' . $arrRow['internName'] . ')';
        }

        $strContent = preg_replace('/<div class="cte_type([A-Za-z0-9\s\-_]{0,})">([A-Za-z0-9\s\-_öäüÖÄÜß@:;,.+#*&%!?\/\\\(\)\]\[\{\}\'\"]{0,})<\/div>/', '<div class="cte_type$1">$2' . $addContentTitle . '</div>', $strContent);

        return $strContent;
    }



    public function getWebsiteFields(\DC_Table $dc, $addEmpty = false, $lowercase = true)
    {
        $this->loadDataContainer("tl_iido_website");
        $this->loadLanguageFile("tl_iido_website");

        $arrOptions	= array();
        $savedField	= "";

        foreach($GLOBALS['TL_DCA']['tl_iido_website']['fields'] as $strField => $arrConfig)
        {
            if( (strlen($savedField) && !preg_match('/' . $savedField . '/', $strField)) || !strlen($savedField) )
            {
                $fieldName	= preg_replace('/^iido_website/', '', $strField);
                $labelName	= $arrConfig['label'][0];

                if( $lowercase )
                {
                    $fieldName = strtolower( $fieldName );
                }

                if( \Config::get($strField) )
                {
                    $arrOptions[ $fieldName ] = $labelName;
                }
                else
                {
                    if( $addEmpty )
                    {
                        $arrOptions[ $fieldName ] = $labelName;
                    }
                }

                $savedField = $strField;
            }
        }

        return $arrOptions;
    }


    public function getAddressBlockField(\DC_Table $dc, $xlabel)
    {
        $this->loadLanguageFile("tl_iido_website");

        $db						= \Database::getInstance();
        $ceId					= (int) $dc->activeRecord->id;

        $arrValueFields			= array();
        $arrRealValueFields		= array();

        if ($_SERVER['REQUEST_METHOD'] == 'POST')
        {
            $varValue = $_POST['addressBlock'];

            $arrFields = array
            (
                'addressBlock'		=> $varValue
            );

            $db->prepare('UPDATE ' . $this->strTable . ' %s WHERE id=?')->set($arrFields)->execute($ceId);
        }
        else
        {
            $varValue = $dc->activeRecord->addressBlock;
        }

        foreach( explode(",", $varValue) as $strField )
        {
            $fieldName = explode(";", $strField);

            $arrValueFields[] 		= $strField;
            $arrRealValueFields[]	= $fieldName[0];
        }

        $objTemplate = new \BackendTemplate("be_iido_field_addressBlock");

        $objTemplate->divider				= $GLOBALS['TL_LANG']['tl_iido_website']['divider'];
        $objTemplate->label					= $xlabel;
        $objTemplate->arrFields				= $this->getWebsiteFields($dc, true);
        $objTemplate->fieldValue			= $varValue;
        $objTemplate->arrValueFields		= $arrValueFields;
        $objTemplate->arrRealValueFields	= $arrRealValueFields;

        return $objTemplate->parse();
    }



    /**
     * Get all news archives and return them as array
     * @return array
     */
    public function getNewsArchives()
    {
        $moduleNews		= new \tl_module_news();
        $arrArchives	= $moduleNews->getNewsArchives();

        if( is_array($arrArchives) && count($arrArchives) > 0 )
        {
            foreach($arrArchives as $id => $title )
            {
                $objNewsArchive = \NewsArchiveModel::findByPk( $id );

                if( $objNewsArchive )
                {
                    if( $objNewsArchive->newsTyps != "gallery" )
                    {
                        unset( $arrArchives[ $id ] );
                    }
                }
            }
        }

        return $arrArchives;
    }


    public function getNewsItems(\DC_Table $dc)
    {
        $arrOptions		= array();
        $activeRecord	= $dc->activeRecord;
        $arrArchives	= $activeRecord->news_archive; //deserialize($activeRecord->news_archives, true);
//		$addLabelHeader	= FALSE;

//		if( count($arrArchives) > 1 )
//		{
//			$addLabelHeader = TRUE;
//		}

//		foreach($arrArchives as $archiveID)
//		{
        $objNewsItems = \NewsModel::findPublishedByPid( $arrArchives );

        if( $objNewsItems )
        {
//				if( $addLabelHeader )
//				{
//					$objNewsArchive = \NewsArchiveModel::findByPk( $archiveID );
//
//					if( $objNewsArchive )
//					{
//						while( $objNewsItems->next() )
//						{
//							$arrOptions[ $objNewsArchive->title ][ $objNewsItems->id ] = $objNewsItems->headline;
//						}
//					}
//				}
//				else
//				{
            while( $objNewsItems->next() )
            {
                $arrOptions[ $objNewsItems->id ] = $objNewsItems->headline;
            }
//				}
        }
//		}


        return $arrOptions;
    }



    /**
     * Return the edit news archive wizard
     * @param \DataContainer
     * @return string
     */
    public function editNewsArchive(\DataContainer $dc)
    {
        return ($dc->value < 1) ? '' : ' <a href="contao/main.php?do=news&amp;act=edit&amp;id=' . $dc->value . '&amp;popup=1&amp;nb=1&amp;rt=' . REQUEST_TOKEN . '" title="' . sprintf(specialchars($GLOBALS['TL_LANG']['tl_content']['editalias'][1]), $dc->value) . '" style="padding-left:3px" onclick="Backend.openModalIframe({\'width\':768,\'title\':\'' . specialchars(str_replace("'", "\\'", sprintf($GLOBALS['TL_LANG']['tl_content']['editalias'][1], $dc->value))) . '\',\'url\':this.href});return false">' . \Image::getHtml('alias.gif', $GLOBALS['TL_LANG']['tl_content']['editalias'][0], 'style="vertical-align:top"') . '</a>';
    }



    /**
     * Return the edit news item wizard
     * @param \DataContainer
     * @return string
     */
    public function editNewsItem(\DataContainer $dc)
    {
        return ($dc->value < 1) ? '' : ' <a href="contao/main.php?do=news&amp;table=tl_news&amp;act=edit&amp;id=' . $dc->value . '&amp;popup=1&amp;nb=1&amp;rt=' . REQUEST_TOKEN . '" title="' . sprintf(specialchars($GLOBALS['TL_LANG']['tl_content']['editalias'][1]), $dc->value) . '" style="padding-left:3px" onclick="Backend.openModalIframe({\'width\':768,\'title\':\'' . specialchars(str_replace("'", "\\'", sprintf($GLOBALS['TL_LANG']['tl_content']['editalias'][1], $dc->value))) . '\',\'url\':this.href});return false">' . \Image::getHtml('alias.gif', $GLOBALS['TL_LANG']['tl_content']['editalias'][0], 'style="vertical-align:top"') . '</a>';
    }



    public function getFontAwsomeIcons(\DataContainer $dc)
    {
        $arrIcons = array();

        $objFile		= new \File("files/theme_files/font_awesome/less/icons.less");
        $arrContent		= $objFile->getContentAsArray();

        foreach($arrContent as $strRow)
        {
            if( preg_match('/^.icon/', $strRow) )
            {
                $strIcon = preg_replace('/^.icon-/', '', $strRow);

                if( preg_match('/{/', $strRow) )
                {
                    $strIcon = preg_replace('/:before \{ content: ([A-Za-z0-9\-@]{0,}); \}/', '', $strIcon);
                }
                else
                {
                    $strIcon = preg_replace('/:before,/', '', $strIcon);
                }

                $arrIcons[ $strIcon ] = ucfirst( str_replace('-', ' ', $strIcon) );
            }
        }

        asort($arrIcons);

        return $arrIcons;
    }
}
