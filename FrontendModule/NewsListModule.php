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

namespace IIDO\BasicBundle\FrontendModule;

use \Contao\CoreBundle\Exception\PageNotFoundException;
use \Patchwork\Utf8;


/**
 * Frontend Module: News List
 *
 * @author Stephan Preßl <development@prestep.at>
 */
class NewsListModule extends \ModuleNewsList
{

	/**
	 * Display a wildcard in the back end
	 *
	 * @return string
	 */
	public function generate()
	{
		return parent::generate();
	}



	/**
	 * Generate the module
	 */
	protected function compile()
	{
		$limit      = null;
		$offset     = intval($this->skipFirst);
        $options    = array();

		// Maximum number of items
		if ($this->numberOfItems > 0)
		{
			$limit = $this->numberOfItems;
		}

		// Handle featured news
		if ($this->news_featured == 'featured')
		{
			$blnFeatured = true;
		}
		elseif ($this->news_featured == 'unfeatured')
		{
			$blnFeatured = false;
		}
		else
		{
			$blnFeatured = null;
		}

		$this->Template->articles = array();
		$this->Template->empty = $GLOBALS['TL_LANG']['MSC']['emptyList'];

		// Get the total number of items
		$intTotal = $this->countItems($this->news_archives, $blnFeatured);

		if ($intTotal < 1)
		{
			return;
		}

		$total = $intTotal - $offset;

		// Split the results
		if ($this->perPage > 0 && (!isset($limit) || $this->numberOfItems > $this->perPage))
		{
			// Adjust the overall limit
			if (isset($limit))
			{
				$total = min($limit, $total);
			}

			// Get the current page
			$id = 'page_n' . $this->id;
			$page = (\Input::get($id) !== null) ? \Input::get($id) : 1;

			// Do not index or cache the page if the page number is outside the range
			if ($page < 1 || $page > max(ceil($total/$this->perPage), 1))
			{
				throw new PageNotFoundException('Page not found: ' . \Environment::get('uri'));
			}

			// Set limit and offset
			$limit = $this->perPage;
			$offset += (max($page, 1) - 1) * $this->perPage;
			$skip = intval($this->skipFirst);

			// Overall limit
			if ($offset + $limit > $total + $skip)
			{
				$limit = $total + $skip - $offset;
			}

			// Add the pagination menu
			$objPagination = new \Pagination($total, $this->perPage, \Config::get('maxPaginationLinks'), $id);
			$this->Template->pagination = $objPagination->generate("\n  ");
		}

		foreach( $this->news_archives as $newsArchiveID )
        {
            $objNewsArchive = \NewsArchiveModel::findByPk( $newsArchiveID );

            if( $objNewsArchive && $objNewsArchive->manualSorting )
            {
                $options['order'] = 'sorting';
                break;
            }
        }

		$objArticles = $this->fetchItems($this->news_archives, $blnFeatured, ($limit ?: 0), $offset, $options);

		// Add the articles
		if ($objArticles !== null)
		{
			$this->Template->articles = $this->parseArticles($objArticles);
		}

		$this->Template->archives = $this->news_archives;
	}


	/**
	 * Fetch the matching items
	 *
	 * @param  array   $newsArchives
	 * @param  boolean $blnFeatured
	 * @param  integer $limit
	 * @param  integer $offset
     * @param  array   $options
	 *
	 * @return \Model\Collection|\NewsModel|null
	 */
	protected function fetchItems($newsArchives, $blnFeatured, $limit, $offset, array $options = array())
	{
		// HOOK: add custom logic
		if (isset($GLOBALS['TL_HOOKS']['newsListFetchItems']) && is_array($GLOBALS['TL_HOOKS']['newsListFetchItems']))
		{
			foreach ($GLOBALS['TL_HOOKS']['newsListFetchItems'] as $callback)
			{
				if (($objCollection = \System::importStatic($callback[0])->{$callback[1]}($newsArchives, $blnFeatured, $limit, $offset, $this, $options)) === false)
				{
					continue;
				}

				if ($objCollection === null || $objCollection instanceof \Model\Collection)
				{
					return $objCollection;
				}
			}
		}

		return \NewsModel::findPublishedByPids($newsArchives, $blnFeatured, $limit, $offset, $options);
	}
}
