<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\EventListener;


use IIDO\BasicBundle\Config\BundleConfig;

use IIDO\BasicBundle\Helper\ColorHelper;
use IIDO\BasicBundle\Helper\HeaderHelper;
use IIDO\BasicBundle\Helper\PageHelper;
use IIDO\BasicBundle\Helper\ScriptHelper;
use IIDO\BasicBundle\Helper\StylesheetHelper;
use IIDO\BasicBundle\Helper\BasicHelper;

use IIDO\BasicBundle\Renderer\MobileRenderer;
use IIDO\BasicBundle\Renderer\SearchRenderer;


/**
 * Class Page Hook
 *
 * @package IIDO\BasicBundle
 * @author Stephan Preßl <development@prestep.at>
 */
class PageListener extends DefaultListener
{

    /**
     * Get Page Status Icon > Regular Redirect
     * 
     * @param $objCurrentPage
     * @param $strImage
     *
     * @return string
     */
    public function getCustomizePageStatusIcon( $objCurrentPage, $strImage )
    {
        if( $objCurrentPage->type === "regular_redirect" )
        {
            $strImage = preg_replace('/^web\//', '', $this->bundlePathPublic) . '/images/pages/' . $strImage;
        }

        return $strImage;
    }



    /**
     * generate customize page //TODO: LADEZEITEN OPTIMIEREN!! SCRIPTE NUR DANN LADEN WENN SIE BENÖTIGT WERDEN!!
     *
     * @param \PageModel   $objPage
     * @param \LayoutModel $objLayout
     * @param \PageRegular $objPageRegular
     */
    public function generateCustomizePage( \PageModel $objPage, \LayoutModel $objLayout, \PageRegular $objPageRegular )
    {
        if( $objLayout->master_ID === 0 || $objLayout->master_ID === "" || !$objLayout->master_ID || $objLayout->master_ID === "0" )
        {
            return;
        }

        BasicHelper::replaceOtherDefaultScripts();
        BasicHelper::checkForUniqueScripts();

        $arrBodyClasses     = array();
        $strStyles          = '';

        $objRootPage        = \PageModel::findByPk( $objPage->rootId );
        $bundles            = $this->bundles;

        $jsPrefix           = 'mootools';
        $ua                 = \Environment::get( "agent" );

        $jquery             = ($objLayout->addJQuery)   ? TRUE : FALSE;
        $mootools           = ($objLayout->addMooTools) ? TRUE : FALSE;
        $tableFieldPrefix   = BundleConfig::getTableFieldPrefix();

        if( $jquery )
        {
            $jsPrefix       = 'jquery';
        }

        $layoutHasFooter    = $objLayout->rows;
        $footerMode         = (($objLayout->footerAtBottom && ($layoutHasFooter == "2rwf" || $layoutHasFooter == "3rw")) ? TRUE : FALSE);

//        if( $objLayout->loadJQueryUI )
//        {
//            $GLOBALS['TL_JAVASCRIPT']['jquery_ui'] = $this->bundlePathPublic . '/javascript/' . $jsPrefix . '/jquery-ui.min.js|static';
//            $GLOBALS['TL_CSS']['jquery_ui']        = $this->bundlePathPublic . '/css/frontend/jquery-ui.css||static';
//        }
//        $GLOBALS['TL_JAVASCRIPT']['jquery_ui']        = $this->bundlePathPublic . '/javascript/' . $jsPrefix . '/jquery-ui.1.12.1.min.js|static';
//        $GLOBALS['TL_JAVASCRIPT']['easing']            = $this->bundlePathPublic . '/javascript/' . $jsPrefix . '/jquery.easing.min.js|static';

        if( $objPage->enableFullpage && $jsPrefix === "jquery" )
        {
            // TODO: fullpage versioning / easings / scrolloverflow

            $GLOBALS['TL_JAVASCRIPT']['easings']            = $this->bundlePathPublic . '/javascript/' . $jsPrefix . '/jquery.easings.min.js|static';
            ScriptHelper::addScript("scrolloverflow");
            ScriptHelper::addScript("fullpage");

            ScriptHelper::addInternScript("fullpage");
        }

        if( preg_match('/page-is-onepage/', $objPage->cssClass)  && $jsPrefix === "jquery" )
        {
            ScriptHelper::addScript("nav");
        }

        if( $footerMode )
        {
            $GLOBALS['TL_CSS']['footer'] = $this->bundlePathPublic . '/css/footer.css||static';
        }

        $objArticles = \ArticleModel::findBy(array('pid=?', 'published=?'), array($objPage->id, "1") );

        if( $objArticles )
        {
            while( $objArticles->next() )
            {
                if( $objArticles->addDivider )
                {
                    StylesheetHelper::addMasterStylesheet("article-divider");
                    break;
                }
            }
        }


        // TODO: import js wenn es gebraucht wird!! wann werden diese bibliotheken benötigt??
        $GLOBALS['TL_JAVASCRIPT']['j_gsap']                 = $this->bundlePathPublic . '/javascript/greensock/jquery.gsap.min.js|static';
        $GLOBALS['TL_JAVASCRIPT']['tweenlite']              = $this->bundlePathPublic . '/javascript/greensock/TweenMax.min.js|static';


        //TODO: das brauchen wir nur bei barba js!! damit es auf jeder seite vorhanden ist!! bzw. methode finden um es nachzuladen wenn nötig??!
        if( in_array('RockSolidSliderBundle', $bundles) && $objRootPage->enablePageFadeEffect )
        {
            $assetsDir = 'web/bundles/rocksolidslider';

            $GLOBALS['TL_JAVASCRIPT']['rocksolid_slider']       = $assetsDir . '/js/rocksolid-slider.min.js|static';
            $GLOBALS['TL_CSS']['rocksolid_slider']              = $assetsDir . '/css/rocksolid-slider.min.css||static';

            $skinPath = $assetsDir . '/css/default-skin.min.css';
            if (file_exists(TL_ROOT . '/' . $skinPath))
            {
                $GLOBALS['TL_CSS']['rocksolid_slider_default'] = $skinPath . '||static';
            }
        }

        if( !$objPage->removePageLoader && ($objPage->addPageLoader || PageHelper::checkIfParentPagesHasPageLoader( $objPage )) )
        {
            $GLOBALS['TL_CSS']['fakeloader']                = $this->bundlePathPublic . '/css/fakeloader.css||static';
            $GLOBALS['TL_JAVASCRIPT']['fakeloader']         = $this->bundlePathPublic . '/javascript/fakeloader.min.js|static';
        }

//        $GLOBALS['TL_JAVASCRIPT']['hdpi_canvas']         = $this->bundlePathPublic . '/javascript/hidpi-canvas.min.js|static';

        StylesheetHelper::addDefaultStylesheets();

        if( $jsPrefix == "jquery" )
        {
            // TODO: smoothscroll

            // TODO: waypoints => nur bei animationen laden!! inview & sticky nur wenn benötitgt!!
            // TODO: isotope => nur wenn benötigt, filter usw.
            // TODO: number & count_to nur wenn nötig!

            // TODO: iido script nur wenn nötig!!


//            $GLOBALS['TL_JAVASCRIPT']['easings']            = $this->bundlePathPublic . '/javascript/' . $jsPrefix . '/jquery.easings.min.js|static';
//            $GLOBALS['TL_JAVASCRIPT'][] = $this->bundlePathPublic . '/javascript/' . $jsPrefix . '/jquery.scrollTo.min.js|static';
            $GLOBALS['TL_JAVASCRIPT']['smoothscroll']       = $this->bundlePathPublic . '/javascript/' . $jsPrefix . '/jquery.smooth-scroll.min.js|static';


            // parallax background
            $GLOBALS['TL_JAVASCRIPT']['stellar']            = $this->bundlePathPublic . '/javascript/' . $jsPrefix . '/jquery.stellar.min.js|static';


            if( ScriptHelper::hasPageAnimation() )
            {
                ScriptHelper::addScript('waypoints');
                ScriptHelper::addSourceScript('waypoints', array('wp_inview' => 'inview', 'wp_sticky'=>'sticky'));
//                ScriptHelper::addSourceScript('waypoints', array('wp_inview' => 'inview', 'wp_sticky'=>'sticky', 'wp_infinite'=>'infinite'));
            }

//            $GLOBALS['TL_JAVASCRIPT'][] = $this->bundlePathPublic . '/javascript/' . $jsPrefix . '/jquery.sticky-kit.min.js|static';

            if( ScriptHelper::hasPageIsotope() )
            {
                ScriptHelper::addScript('isotope');

//                $isotopeVersion = \Config::get( $tableFieldPrefix . 'scriptsIsotope');
//                $GLOBALS['TL_JAVASCRIPT']['isotope']            = $this->bundlePathPublic . '/javascript/' . $jsPrefix . '/isotope/' . $isotopeVersion . '/isotope.pkgd.min.js|static';
//            $GLOBALS['TL_JAVASCRIPT']['iso_fit-columns']    = $this->bundlePathPublic . '/javascript/' . $jsPrefix . '/isotope/fit-columns.js|static';
            }


            // TODO: check if we need hc sticky js?!
            $GLOBALS['TL_JAVASCRIPT']['hc_sticky']          = $this->bundlePathPublic . '/javascript/' . $jsPrefix . '/jquery.hc-sticky.min.js|static';

            // TODO: if we need the number js?!
            $GLOBALS['TL_JAVASCRIPT']['number']             = $this->bundlePathPublic . '/javascript/' . $jsPrefix . '/jquery.number.min.js|static';

            // TODO: if count to element is active!
            $GLOBALS['TL_JAVASCRIPT']['count_to']           = $this->bundlePathPublic . '/javascript/' . $jsPrefix . '/count-to.min.js|static';


            $GLOBALS['TL_JAVASCRIPT']['iido_base']          = $this->bundlePathPublic . '/javascript/' . $jsPrefix . '/iido/IIDO.Base.js|static';




//TODO: edit iido script and check if we need !?!
//            $GLOBALS['TL_JAVASCRIPT'][] = $this->bundlePathPublic . '/javascript/' . $jsPrefix . '/iido/IIDO.Functions.js|static';
            $GLOBALS['TL_JAVASCRIPT']['iido_page']          = $this->bundlePathPublic . '/javascript/' . $jsPrefix . '/iido/IIDO.Page.js|static';
            $GLOBALS['TL_JAVASCRIPT']['iido_content']       = $this->bundlePathPublic . '/javascript/' . $jsPrefix . '/iido/IIDO.Content.js|static';
            $GLOBALS['TL_JAVASCRIPT']['iido_filter']        = $this->bundlePathPublic . '/javascript/' . $jsPrefix . '/iido/IIDO.Filter.js|static';
            $GLOBALS['TL_JAVASCRIPT']['iido_project']       = $this->bundlePathPublic . '/javascript/' . $jsPrefix . '/iido/IIDO.Project.js|static';
            $GLOBALS['TL_JAVASCRIPT']['iido_form']          = $this->bundlePathPublic . '/javascript/' . $jsPrefix . '/iido/IIDO.Form.js|static';
        }

        // TODO: script nur laden wenn nötig && version!!
//        $GLOBALS['TL_JAVASCRIPT']['scrollmagic'] = $this->bundlePathPublic . '/javascript/ScrollMagic.min.js|static';
//        $GLOBALS['TL_JAVASCRIPT']['scrollmagic_gsap'] = $this->bundlePathPublic . '/javascript/scrollmagic/animation.gsap.min.js|static';

//        $GLOBALS['TL_JAVASCRIPT']['scrollmagic_debug'] = $this->bundlePathPublic . '/javascript/scrollmagic/debug.addIndicators.min.js|static';
//        $GLOBALS['TL_JAVASCRIPT']['scrollmagic_gsap'] = $this->bundlePathPublic . '/javascript/scrollmagic/animation.velocity.min.js|static';

        if( $objRootPage->enablePageFadeEffect )
        {
            //TODO: barab version!!
            $arrBodyClasses[] = 'page-fade-animation';
//            $GLOBALS['TL_JAVASCRIPT']['barba']              = $this->bundlePathPublic . '/javascript/barba.min.js|static';

            ScriptHelper::addScript('barba');
        }

        if( $objLayout->loadDomainCSS )
        {
            $objRootPage = \PageModel::findByPk( $objPage->rootId );

            if( $objRootPage )
            {
                $domainFile	= str_replace('.', '-', $objRootPage->dns) . '.css';

                if( strlen($objRootPage->dns) && file_exists( $this->rootDir . '/' . \Config::get("uploadPath") . '/' . $objRootPage->alias . '/css/' . $domainFile) )
                {
                    $GLOBALS['TL_USER_CSS'][] = \Config::get("uploadPath") . '/' . $objRootPage->alias . '/css/' . $domainFile . '||static';
                }
            }
        }

        if($jquery && $mootools)
        {
            $mootools = false;
        }
        elseif(!$jquery && !$mootools)
        {
            $mootools = true;
        }

        if( $objRootPage->enableCookie || $objPage->enableCookie )
        {
            //TODO: cooke version!!
//            $GLOBALS['TL_JAVASCRIPT']['cookie'] = $this->bundlePathPublic . '/javascript/cookie.min.js|static';
            ScriptHelper::addScript('cookie');
        }


        if( $objRootPage->enableLazyLoad || $objPage->enableLazyLoad )
        {
            //TODO: lazyload script!!
            if($jquery)
            {
//                $GLOBALS['TL_JAVASCRIPT']['lazyload'] = $this->bundlePathPublic . '/javascript/jquery/jquery.lazyload.min.js|static';
                ScriptHelper::addScript('lazyload');
            }
            elseif($mootools)
            {
                // TODO: add lazyload script for mootools!!
            }
        }

//        if($ua->browser == "ie" && $ua->version < 9 && $config->get($prefix . 'activePlaceholderFallback') )
//        {
//            if($jquery)
//            {
//                $GLOBALS['TL_JAVASCRIPT'][] = 'system/modules/ziido_customize/assets/javascript/jquery/jquery.placeholder.min.js|static';
//                $GLOBALS['TL_HEAD'][]		= '<script type="text/javascript">$(function() { $("input, textarea").placeholder(); });</script>';
//            }
//            elseif($mootools)
//            {
//                // TODO: add placeholder fallback script for mootools!!
//            }
//        }

        $arrBodyClasses = StylesheetHelper::createDefaultStylesheet( $arrBodyClasses );

        $this->addDefaultScripts();

        if( count($arrBodyClasses) )
        {
            $objPage->cssClass = $objPage->cssClass . ((strlen($objPage->cssClass)) ? ' ' : '') . implode(" ", $arrBodyClasses);
        }

        BasicHelper::checkForUniqueScripts();
    }



    public function modifyCustomizeFrontendPage($strBuffer, $templateName)
    {
        /* @var \PageModel $objPage */
        global $objPage;

        $objLayout      = BasicHelper::getPageLayout( $objPage );

        if( $objLayout->master_ID === 0 || $objLayout->master_ID === "" || !$objLayout->master_ID || $objLayout->master_ID === "0" )
        {
            return $strBuffer;
        }

        if ('fe_page' === $templateName)
        {
            $strBuffer = SearchRenderer::renderSearchTemplate( $strBuffer );
            $strBuffer = MobileRenderer::renderMobileMenuTemplate( $strBuffer );

            if( !$objPage->removePageLoader && ($objPage->addPageLoader || PageHelper::checkIfParentPagesHasPageLoader( $objPage )) )
            {
                $tableFieldPrefix   = BundleConfig::getTableFieldPrefix();
                $pageLoaderColor    = ColorHelper::compileColor( \StringUtil::deserialize(\Config::get($tableFieldPrefix . 'pageLoaderBackgroundColor'), TRUE)  );

                $loaderTag      = '<div id="fakeLoader"></div>';
                $loaderScript   = '<script type="text/javascript">$("#fakeLoader").fakeLoader({timeToHide:1600,zIndex:"9999",spinner:"' . (\Config::get($tableFieldPrefix . 'pageLoaderStyle') ?: "spinner1") . '",bgColor:"' . (($pageLoaderColor !== "transparent") ? $pageLoaderColor : '#545454')  . '"})</script>';

                $strBuffer = preg_replace('/<\/body>/',  $loaderTag . $loaderScript . '</body>', $strBuffer);
            }
        }

        return $strBuffer;
    }



    protected function addDefaultScripts()
    {
        $rootAlias = BasicHelper::getRootPageAlias();

        $jsPathCustom  = 'files/' . $rootAlias . '/js/';

        if( file_exists($this->rootDir . '/' . $jsPathCustom . 'functions.js') )
        {
            $GLOBALS['TL_JAVASCRIPT'][] = $jsPathCustom . 'functions.js|static';
        }
    }

}
