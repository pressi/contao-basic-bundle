<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\Table;


use \Contao\CoreBundle\Exception\AccessDeniedException;


/**
 * Description
 *
 * @author ZOMEDIA <dialog@zomedia.at>
 */
class NewsTable
{

	protected $strTable				= 'tl_news';



	/**
	 * Check permissions to edit table tl_news
	 */
	public function checkPermission()
	{
        $this->User = \Backend::importStatic("BackendUser");

        $bundles = \ModuleLoader::getActive();

		// HOOK: comments extension required
        if (!isset($bundles['ContaoCommentsBundle']))
		{
			$key = array_search('allowComments', $GLOBALS['TL_DCA']['tl_news']['list']['sorting']['headerFields']);
			unset($GLOBALS['TL_DCA']['tl_news']['list']['sorting']['headerFields'][$key]);
		}

		if ($this->User->isAdmin)
		{
			return;
		}

		// Set the root IDs
		if (!is_array($this->User->news) || empty($this->User->news))
		{
			$root = array(0);
		}
		else
		{
			$root = $this->User->news;
		}

		$id 			= strlen(\Input::get('id')) ? \Input::get('id') : CURRENT_ID;
		$objNews 		= \Database::getInstance()->prepare("SELECT * FROM tl_news WHERE id=?")->limit(1)->execute($id);
		$objArchive		= \Database::getInstance()->prepare("SELECT * FROM tl_news_archive WHERE id=?")->limit(1)->execute($objNews->pid);

		// Check current action
		switch (\Input::get('act'))
		{
            case 'paste':
                // Allow
                break;

            case 'create':
                if (!strlen(\Input::get('pid')) || !in_array(\Input::get('pid'), $root))
                {
                    throw new AccessDeniedException('Not enough permissions to create news items in news archive ID ' . \Input::get('pid') . '.');
                }
                break;

            case 'cut':
            case 'copy':
                if( !$objArchive || ($objArchive && !$objArchive->manualSorting) )
                {
                    if (!in_array(\Input::get('pid'), $root))
                    {
                        throw new AccessDeniedException('Not enough permissions to ' . \Input::get('act') . ' news item ID ' . $id . ' to news archive ID ' . \Input::get('pid') . '.');
                    }
                }
                // NO BREAK STATEMENT HERE

            case 'edit':
            case 'show':
            case 'delete':
            case 'toggle':
            case 'feature':
                $objArchive = \Database::getInstance()->prepare("SELECT pid FROM tl_news WHERE id=?")
                    ->limit(1)
                    ->execute($id);

                if ($objArchive->numRows < 1)
                {
                    throw new AccessDeniedException('Invalid news item ID ' . $id . '.');
                }

                if (!in_array($objArchive->pid, $root))
                {
                    throw new AccessDeniedException('Not enough permissions to ' . \Input::get('act') . ' news item ID ' . $id . ' of news archive ID ' . $objArchive->pid . '.');
                }
                break;

            case 'select':
            case 'editAll':
            case 'deleteAll':
            case 'overrideAll':
            case 'cutAll':
            case 'copyAll':
                if (!in_array($id, $root))
                {
                    throw new AccessDeniedException('Not enough permissions to access news archive ID ' . $id . '.');
                }

                $objArchive = \Database::getInstance()->prepare("SELECT id FROM tl_news WHERE pid=?")->execute($id);

                if ($objArchive->numRows < 1)
                {
                    throw new AccessDeniedException('Invalid news archive ID ' . $id . '.');
                }

                /** @var \Symfony\Component\HttpFoundation\Session\SessionInterface $objSession */
                $objSession = \System::getContainer()->get('session');

                $session = $objSession->all();
                $session['CURRENT']['IDS'] = array_intersect($session['CURRENT']['IDS'], $objArchive->fetchEach('id'));
                $objSession->replace($session);
                break;

            default:
                if (strlen(\Input::get('act')))
                {
                    throw new AccessDeniedException('Invalid command "' . \Input::get('act') . '".');
                }
                elseif (!in_array($id, $root))
                {
                    throw new AccessDeniedException('Not enough permissions to access news archive ID ' . $id . '.');
                }
                break;
		}
	}



	public static function checkTableForSorting(&$globalArray, $objArchive, $do, $table, $id)
	{
		$self = new self();

		if($objArchive && $do == "news" && $table == $self->strTable && $objArchive->manualSorting)
		{
			$globalArray['list']['sorting']['fields']					= array('sorting');
			$globalArray['list']['sorting']['flag']						= 12;
			$globalArray['list']['sorting']['disableGrouping']			= true;
			$globalArray['list']['sorting']['panelLayout']				= 'filter;search,limit';
			$globalArray['list']['sorting']['child_record_callback']	= array('IIDO\BasicBundle\Table\NewsTable', 'listNewsArticles');

//			unset( $globalArray['fields']['date']['flag'] );

			foreach($globalArray['fields'] as $field => $arrConfig)
			{
				if($arrConfig['sorting'])
				{
					$globalArray['fields'][ $field ]['sorting'] = false;
				}
			}
		}
	}



	public function listNewsArticles(array $arrRow)
	{
		$strTitle		= $arrRow['headline'];

		if( in_array("news_categories", \ModuleLoader::getActive()) )
		{
			$arrCategories	= array();
			$categories		= deserialize( $arrRow['categories'] );

			if( is_array($categories) )
			{
				foreach($categories as $category)
				{
					$objCategory = \NewsCategoryModel::findByPk( $category );

					if( $objCategory )
					{
						$arrCategories[] = $objCategory->title;
					}
				}
			}

			if( count($arrCategories) > 0 )
			{
				$strTitle .= ' <span style="color:#b3b3b3;">[' . implode(",", $arrCategories) . ']</span>';
			}
		}

		return '<div class="tl_content_left">' . $strTitle . '</div>';
	}



	public function getMainNewsCategories(\DataContainer $dc)
	{
		$arrCategories		= array();
		$objNewsCategories 	= \NewsCategoryModel::findBy("pid", 0);

		while( $objNewsCategories->next() )
		{
			$arrCategories[ $objNewsCategories->id ] = $objNewsCategories->title;
		}

		return $arrCategories;
	}



	public function getSubNewsCategories(\DataContainer $dc)
	{
		$arrCategories		= array();
		$objNewsCategories 	= \NewsCategoryModel::findBy("pid", 0);
		$activeCategories	= deserialize( $dc->activeRecord->categories );

		if( !is_array($activeCategories) )
		{
			$activeCategories = array();
		}

		while( $objNewsCategories->next() )
		{
			if( in_array($objNewsCategories->id, $activeCategories) )
			{
				$arrCategories[ $objNewsCategories->title ] = $this->findNewsSubCategories( $objNewsCategories->id );
			}
		}

		return $arrCategories;
	}



	protected function findNewsSubCategories( $pid, $level = 1 )
	{
		$arrCategories 		= array();
		$objNewsCategories 	= \NewsCategoryModel::findBy("pid", $pid);

		if( $objNewsCategories && $objNewsCategories->count() > 0)
		{
			while( $objNewsCategories->next() )
			{
				if($objNewsCategories->linkPage)
				{
					continue;
				}

				$strLevel		= '';
				$nextLevel		= ($level + 1);

				for($i=1; $i<$level;$i++)
				{
					$strLevel .= '&#9492;';
				}

				if( strlen($strLevel) )
				{
					$strLevel = $strLevel . ' ';
				}

				$subCategories	= $this->findNewsSubCategories( $objNewsCategories->id, $nextLevel );

				if( count($subCategories) > 0)
				{
					$arrCategories[ $objNewsCategories->id ] = $strLevel . $objNewsCategories->title;

					foreach($subCategories as $catId => $catName)
					{
						$arrCategories[ $catId ] = $catName;
					}

				}
				else
				{
					$arrCategories[ $objNewsCategories->id ] = $strLevel . $objNewsCategories->title;
				}
			}
		}

		return $arrCategories;
	}



    /**
     * Extract the YouTube ID from an URL
     *
     * @param mixed          $varValue
     * @param \DataContainer $dc
     *
     * @return mixed
     */
    public function extractYouTubeId($varValue, \DataContainer $dc)
    {
        if ($dc->activeRecord->singleSRC != $varValue)
        {
            $matches = array();

            if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $varValue, $matches))
            {
                $varValue = $matches[1];
            }
        }

        return $varValue;
    }

    /**
     * Extract the Vimeo ID from an URL
     *
     * @param mixed          $varValue
     * @param \DataContainer $dc
     *
     * @return mixed
     */
    public function extractVimeoId($varValue, \DataContainer $dc)
    {
        if ($dc->activeRecord->singleSRC != $varValue)
        {
            $matches = array();

            if (preg_match('%vimeo\.com/(?:channels/(?:\w+/)?|groups/(?:[^/]+)/videos/|album/(?:\d+)/video/)?(\d+)(?:$|/|\?)%i', $varValue, $matches))
            {
                $varValue = $matches[1];
            }
        }

        return $varValue;
    }
}
