<?php
/******************************************************************
 *
 * (c) 2016 Stephan Preßl <development@prestep.at>
 * All rights reserved
 *
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *
 ******************************************************************/

namespace IIDO\BasicBundle\EventListener;

use IIDO\BasicBundle\Helper\BasicHelper as Helper;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\Environment;
use Contao\PageModel;
use Contao\LayoutModel;
use Contao\Model\Collection;
use Contao\NewsFeedModel;
use Contao\StringUtil;
use Contao\Template;
use Contao\Frontend;
use IIDO\BasicBundle\Helper\ColorHelper;


/**
 * Class Backend Template Hook
 * @package IIDO\Customize\Hook
 */
class BackendTemplateListener
{

    /**
     * @var ContaoFrameworkInterface
     */
    private $framework;



    /**
     * Constructor.
     *
     * @param ContaoFrameworkInterface $framework
     */
    public function __construct(ContaoFrameworkInterface $framework)
    {
        $this->framework = $framework;
    }


    /**
     * Edit the Frontend Template
     *
     * @param string $strContent
     * @param string $strTemplate
     *
     * @return string
     */
    public function outputCustomizeBackendTemplate($strContent, $strTemplate)
    {
        $config = \Config::getInstance();

        if( $config->isComplete() )
        {
            if( $strTemplate == "be_main" )
            {
                $strErrorMessage = "";

//				if( $config->get("dps_setDefaultSettings") && $config->get("dps_websiteStatusIsDevelopment") )
//				{
//					$strErrorMessage	.= '<p class="tl_error tl_permalert">' . (($this->User->isAdmin) ? '<a href="' . $this->addToUrl('websiteIsLive=1') . '" class="tl_submit">' . $GLOBALS['TL_LANG']['MSC']['websiteToLive'] . '</a>' : '') . $GLOBALS['TL_LANG']['MSC']['websiteInDevelopment'] . '</p>';
//				}

//				if( !in_array("multicolumnwizard", \ModuleLoader::getActive()) )
//				{
//					$strErrorMessage	.= '<p class="tl_info tl_permalert">' . (($this->User->isAdmin) ? '<a href="' . $this->addToUrl('do=composer&amp;install=menatwork/contao-multicolumnwizard') . '" class="tl_submit">Module installieren</a>' : '') . 'Bitte installieren Sie das Module "multicolumnwizard" um alle Funktionen verwenden zu können!</p>';
//				}

//				if( strlen($strErrorMessage) )
//				{
//					$strContent			= preg_replace('/<\/div>([\s\n]{0,})<\/div>([\s\n]{0,})<div id="container"/', $strErrorMessage . '</div></div><div id="container"', $strContent);
//				}
            }

            $scripts = '<script src="assets/jquery/js/jquery.min.js"></script>
<script>jQuery = jQuery.noConflict();</script>
<script src="bundles/iidobasic/javascript/jquery/jquery.hc-sticky.min.js"></script>
<script>setTimeout(function(){var fromTop = jQuery("#header").height(); jQuery("#left").hcSticky({top:fromTop});jQuery("#container").css("padding-top", fromTop);}, 500);</script>';


            $strContent = preg_replace('/<\/body>/', $scripts . '</body>', $strContent);
        }

        return $strContent;
    }

}
