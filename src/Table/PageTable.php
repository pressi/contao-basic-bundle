<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\Table;


use Contao\Versions;
use IIDO\BasicBundle\Helper\BasicHelper as Helper;


/**
 * Class Page
 * @package IIDO\BasicBundle\Table
 */
class PageTable extends \Backend
{

    protected $strTable             = 'tl_page';



    /**
     * Auto-generate a page alias if it has not been set yet
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
        $objClassPage = new \tl_page();

        $varValue = $objClassPage->generateAlias($varValue, $dc);

        // Generate folder URL aliases (see #4933)
        if (\Config::get('folderUrl'))
        {
            $objCurrentPage    = \PageModel::findWithDetails($dc->activeRecord->id);
            $varValue           = $this->replaceExceptPages($varValue, $objCurrentPage);
        }

        $objAlias = $this->Database->prepare("SELECT id FROM tl_page WHERE alias=?")
                                   ->execute($varValue);

        if( $objAlias && $objAlias->numRows > 0 )
        {
            $objAlias = $objAlias->first();

            if( $objAlias->id != $dc->id )
            {
                if( !preg_match('/-' . $dc->id . '$/', $varValue) )
                {
                    $varValue = $varValue . '-' . $dc->id;
                }
            }
        }

        return $varValue;
    }



    /**
     * Add an image to each page in the tree
     *
     * @param array          $row
     * @param string         $label
     * @param \DataContainer $dc
     * @param string         $imageAttribute
     * @param boolean        $blnReturnImage
     * @param boolean        $blnProtected
     *
     * @return string
     */
    public function addIcon($row, $label, \DataContainer $dc=null, $imageAttribute='', $blnReturnImage=false, $blnProtected=false)
    {
        if( $row['hideTitle'] )
        {
            $label              = '<span class="gray">' . $label. '</span>';
            $imageAttribute     = trim($imageAttribute . ' class="gray-image"');
        }

        return \Backend::addPageIcon($row, $label, $dc, $imageAttribute, $blnReturnImage, $blnProtected);
    }



    protected function replaceExceptPages($varValue, $objCurrentPage)
    {
        if( $objCurrentPage->pid > 0 && $objCurrentPage->pid != $objCurrentPage->rootId )
        {
            $objParentPage = \PageModel::findWithDetails( $objCurrentPage->pid );

            if( $objParentPage && $objParentPage->excludeFromFolderUrl)
            {
                $varValue = str_replace($objParentPage->alias . '/', '', $varValue);
            }

            $varValue = $this->replaceExceptPages($varValue, $objParentPage);
        }

        return $varValue;
    }



    //TODO: change function!! error with fullpage script!!
    public function renameArticle($varValue, \DataContainer $dc)
    {
        $db = \Database::getInstance();

        $objDbArticle = $db->prepare('SELECT id FROM tl_article WHERE pid=? AND title=? AND inColumn=?')
            ->execute($dc->activeRecord->id, $dc->activeRecord->title, 'main');

        if ($objDbArticle->numRows >= 1)
        {
            foreach ($objDbArticle->fetchEach('id') as $v)
            {
                $strAlias = standardize($varValue);

                // check if the alias exists
                $objAlias = $db->prepare('SELECT id FROM tl_article WHERE alias=?')
                    ->limit(1) // limit because we only need one to generate a new alias
                    ->execute($strAlias);

                if ($objAlias->numRows >= 1)
                {
                    $strAlias .= '-' . $v;
                }

                $this->Database->prepare('UPDATE tl_article %s WHERE id=?')
                    ->set(array('title'=>$varValue,'alias'=>$strAlias))
                    ->execute($v);
            }
        }
        return $varValue;
    }



    /**
     * Automatically create an article in the main column of a new page
     * @param \DataContainer
     */
    public function generateExtraArticle(\DataContainer $dc)
    {
//        $this->checkThemeStylesheet( $dc );

        if( !$this->insertExtraArticle )
        {
            return;
        }

        // Return if there is no active record (override all)
        if ( !$dc->activeRecord || $dc->activeRecord->title == "Standard" )
        {
            return;
        }

        // Existing or not a regular page
        if ( $dc->activeRecord->tstamp > 0 || !in_array($dc->activeRecord->type, array('regular', 'error_403', 'error_404')) )
        {
            return;
        }

        $new_records = $this->Session->get( 'new_records' );

        // Not a new page
        if ( !$new_records || (is_array( $new_records[ $dc->table ] ) && !in_array( $dc->id, $new_records[ $dc->table ] )) )
        {
            return;
        }

        // Check whether there are articles (e.g. on copied pages)
        $objTotal = $this->Database->prepare( "SELECT COUNT(*) AS count FROM tl_article WHERE pid=?" )->execute( $dc->id );

        if ( $objTotal->count > 1 )
        {
            return;
        }

        $objLayout = Helper::getPageLayout( $dc->activeRecord );

        if ( $objLayout )
        {
            $modules = \StringUtil::deserialize( $objLayout->modules );

            foreach ( $modules as $module )
            {
                if( $module['mod'] == 0 && $module['enable'] == 1 && $module['col'] != "main" )
                {
                    $artTitle = (($module['col'] == "left") ? "Linke Spalte" : "Rechte Spalte");


                    // Create article
                    $arrSet[ 'pid' ]       = $dc->id;
                    $arrSet[ 'sorting' ]   = (($module['col'] == "left") ? 64 : 256);
                    $arrSet[ 'tstamp' ]    = time();
                    $arrSet[ 'author' ]    = $this->User->id;
                    $arrSet[ 'inColumn' ]  = $module['col'];
                    $arrSet[ 'title' ]     = $artTitle;
                    $arrSet[ 'alias' ]     = str_replace( '/', '-', $dc->activeRecord->alias ) . "-" . $module['col'] . "column"; // see #5168
                    $arrSet[ 'published' ] = $dc->activeRecord->published;

                    $this->Database->prepare( "INSERT INTO tl_article %s" )->set( $arrSet )->execute();
                }
            }
        }
    }



    public function checkThemeStylesheet( $strTable, $strId, $intVersion, $newRecord )
    {
        $touchFile = TRUE;

        if( $intVersion > 1 )
        {
            $objVersion = $this->Database->prepare("SELECT * FROM tl_version WHERE pid=? AND fromTable=? AND version=?")
                                         ->execute($strId, $strTable, ($intVersion-1));

            if( $objVersion )
            {
                $arrData = \StringUtil::deserialize( $objVersion->data, TRUE );

                if( $arrData['pageColor'] === $newRecord['pageColor'])
                {
                    $touchFile = FALSE;
                }
            }
        }

        if( $touchFile )
        {
            $objLayout = Helper::getPageLayout(\PageModel::findByPk($strId));
            $objTheme  = \ThemeModel::findByPk($objLayout->pid);

            $folders    = \StringUtil::deserialize($objTheme->folders, TRUE);
            $vars       = \StringUtil::deserialize($objTheme->vars, TRUE);
            $folderName = '';

            if( count($vars) )
            {
                foreach( $folders as $strFolder )
                {
                    $objFolder = \FilesModel::findByUuid($strFolder);
                    if( $objFolder && !preg_match('/master/', $objFolder->path) )
                    {
                        $folderName = $objFolder->name;
                    }
                }

                $rootDir  = dirname(\System::getContainer()->getParameter('kernel.root_dir'));
                $filePath = 'files/' . $folderName . '/css/theme.css';

                if( file_exists($rootDir . '/' . $filePath) )
                {
                    touch($rootDir . '/' . $filePath);
                }
                else
                {
                    touch($rootDir . '/files/master/css/core.css');

                }
            }
        }
    }



    /**
     * Add an image to each page in the tree
     * @param array
     * @param string
     * @param \DataContainer
     * @param string
     * @param boolean
     * @param boolean
     * @return string
     */
    public function pageLabel($row, $label, \DataContainer $dc=null, $imageAttribute='', $blnReturnImage=false, $blnProtected=false)
    {
        if( strlen($row['subtitle']) || strlen($row['navSubtitle']) )
        {
            if( !strlen($row['subtitlePosition']) )
            {
                $row['subtitlePosition'] = "before";
            }

            $subTitle = '<span class="subtitle ' . $row['subtitlePosition'] . '">' . trim( strlen(trim($row['navSubtitle']))?$row['navSubtitle']:$row['subtitle'] ) . '</span>';

            $label = (($row['subtitlePosition'] == "before") ? $subTitle : '') . $label . (($row['subtitlePosition'] == "after") ? $subTitle : '');
        }

        if( strlen($row['navTitle']) )
        {
            $label = $row['navTitle'] . ' <span class="grey">(' . $label . ')</span>';
        }

        return \Backend::addPageIcon($row, $label, $dc, $imageAttribute, $blnReturnImage, $blnProtected);
    }


}