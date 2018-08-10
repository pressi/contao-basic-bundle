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

namespace IIDO\BasicBundle\ContentElement;


use IIDO\BasicBundle\Helper\TwigHelper;


/**
 *
 *
 * @package IIDO\BasicBundle
 * @author Stephan Preßl <development@prestep.at>
 */
class LoginElement extends \ContentElement
{

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'ce_iido_login';



    /**
     * Generate configurator element
     *
     * @return string
     */
    public function generate()
    {
        if( TL_MODE === "BE" )
        {
            /** @var \BackendTemplate|object $objTemplate */
            $objTemplate = new \BackendTemplate('be_wildcard');

            $objTemplate->wildcard  = '### LOGIN ###';
            $objTemplate->title     = $this->headline;
            $objTemplate->id        = $this->id;
            $objTemplate->link      = $this->name;
            $objTemplate->href      = '';

            return $objTemplate->parse();
        }

        return parent::generate();
    }



    /**
     * Generate the content element
     */
    protected function compile()
    {
        \Controller::loadLanguageFile("modules");

        $arrLabels = array
        (
            'logout'            => $GLOBALS['TL_LANG']['MSC']['logout'],
            'register'          => $GLOBALS['TL_LANG']['MSC']['register'],
            'lostPassword'      => $GLOBALS['TL_LANG']['FMD']['lostPassword'][0]
        );

        $objUrlGenerator = \System::getContainer()->get("contao.routing.url_generator");
        /* @var $objUrlGerator \Contao\CoreBundle\Routing\UrlGenerator */

        $arrUrls = array
        (
            'logout'            => $this->logoutPage        ? \PageModel::findByPk( $this->logoutPage )->getFrontendUrl()       : '',
            'register'          => $this->registerPage      ? \PageModel::findByPk( $this->registerPage )->getFrontendUrl()     : '',
            'lostPassword'      => $this->lostPasswordPage  ? \PageModel::findByPk( $this->lostPasswordPage )->getFrontendUrl() : ''
        );

//        $this->Template->loginModuleId  = $this->loginModuleId;
        $this->Template->labels         = $arrLabels;
        $this->Template->urls           = $arrUrls;
    }

}
