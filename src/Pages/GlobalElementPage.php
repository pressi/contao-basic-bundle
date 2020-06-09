<?php
/*******************************************************************
 * (c) 2020 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 *
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\Pages;


use Contao\Frontend;
use Contao\PageModel;
use Contao\PageRedirect;
use Symfony\Component\HttpFoundation\RedirectResponse;


/**
 * Provide methods to handle a global elements page.
 *
 * @author Stephan Preßl <development@prestep.at>
 */
class GlobalElementPage extends Frontend
{
	/**
	 * Redirect to an external page
	 *
	 * @param PageModel $objPage
	 */
	public function generate($objPage)
	{
        $objRootPage = PageModel::findByPk( $objPage->rootId );
		$this->redirect($objRootPage->getFrontendUrl(), 301);
	}



	/**
	 * Return a response object
	 *
	 * @param PageModel $objPage
	 *
	 * @return RedirectResponse
	 */
	public function getResponse($objPage)
	{
        $objRootPage = PageModel::findByPk( $objPage->rootId );
		return new RedirectResponse($objRootPage->getFrontendUrl(), 301);
	}
}