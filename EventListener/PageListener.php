<?php
/******************************************************************
 *
 * (c) 2015 Stephan Preßl <development@prestep.at>
 * All rights reserved
 *
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *
 ******************************************************************/

namespace IIDO\BasicBundle\EventListener;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\CoreBundle\Framework\ScopeAwareTrait;

use IIDO\BasicBundle\Config\BundleConfig;
use IIDO\BasicBundle\Helper\BasicHelper as Helper;


/**
 * Class Page Hook
 * @package IIDO\Customize\Hook
 */
class PageListener
{
    use ScopeAwareTrait;


    /**
     * @var ContaoFrameworkInterface
     */
    private $framework;


    protected $bundlePathPublic;
    protected $bundlePath;



    /**
     * Constructor.
     *
     * @param ContaoFrameworkInterface $framework
     */
    public function __construct(ContaoFrameworkInterface $framework)
    {
        $this->framework = $framework;

        $this->bundlePathPublic = BundleConfig::getBundlePath(true );
        $this->bundlePath       = BundleConfig::getBundlePath();

    }

    public function getCustomizePageStatusIcon( $objPage, $strImage )
    {
        if( $objPage->type == "regular_redirect" )
        {
            $strImage = preg_replace('/^web\//', '', $this->bundlePathPublic) . '/images/pages/' . $strImage;

        }

        return $strImage;
    }



    public function generateCustomizePage( \PageModel $objPage, \LayoutModel $objLayout, \PageRegular $objPageRegular )
    {
        Helper::replaceOtherDefaultScripts();
        Helper::checkForUniqueScripts();

        $objRootPage        = \PageModel::findByPk( $objPage->rootId );
        $strStyles          = '';
        $objArticle         = \ArticleModel::findPublishedByPidAndColumn($objPage->id, "main");

        $config             = \Config::getInstance();
        $jsPrefix           = 'mootools';

        $ua                 = \Environment::get( "agent" );

        $jquery             = ($objLayout->addJQuery) ? TRUE : FALSE;
        $mootools           = ($objLayout->addMooTools) ? TRUE : FALSE;

//        $externalJavascript = deserialize( $objLayout->externalJavascript, TRUE );
        $externalJavascript = deserialize( $objLayout->orderExternalJavascript, TRUE );

        if( $jquery )
        {
            $jsPrefix       = 'jquery';
        }

        $layoutHasFooter    = $objLayout->rows;
        $footerMode         = (($objLayout->footerAtBottom && ($layoutHasFooter == "2rwf" || $layoutHasFooter == "3rw")) ? TRUE : FALSE);

        if( $objPage->enableFullpage && $jsPrefix == "jquery" )
        {
            $GLOBALS['TL_JAVASCRIPT']['easings']    = $this->bundlePathPublic . '/javascript/' . $jsPrefix . '/jquery.easings.min.js|static';
            $GLOBALS['TL_JAVASCRIPT']['easings']    = $this->bundlePathPublic . '/javascript/' . $jsPrefix . '/scrolloverflow.min.js|static';
            $GLOBALS['TL_JAVASCRIPT']['fullpage']   = $this->bundlePathPublic . '/javascript/' . $jsPrefix . '/jquery.fullPage.min.js|static';
        }

        if( $jsPrefix == "jquery" )
        {
//            $GLOBALS['TL_JAVASCRIPT'][] = 'web/bundles/' . $folderName . '/javascript/' . $jsPrefix . '/jquery.easings.min.js|static';
//            $GLOBALS['TL_JAVASCRIPT'][] = 'web/bundles/' . $folderName . '/javascript/' . $jsPrefix . '/jquery.scrollTo.min.js|static';
//            $GLOBALS['TL_JAVASCRIPT'][] = 'web/bundles/' . $folderName . '/javascript/' . $jsPrefix . '/jquery.slimscroll.min.js|static';
//            $GLOBALS['TL_JAVASCRIPT'][] = 'web/bundles/' . $folderName . '/javascript/' . $jsPrefix . '/jquery.stellar.min.js|static';
//            $GLOBALS['TL_JAVASCRIPT'][] = 'web/bundles/' . $folderName . '/javascript/' . $jsPrefix . '/jquery.waypoints.min.js|static';
//            $GLOBALS['TL_JAVASCRIPT'][] = 'web/bundles/' . $folderName . '/javascript/' . $jsPrefix . '/waypoints/infinite.min.js|static';
//            $GLOBALS['TL_JAVASCRIPT'][] = 'web/bundles/' . $folderName . '/javascript/' . $jsPrefix . '/waypoints/inview.min.js|static';
//            $GLOBALS['TL_JAVASCRIPT'][] = 'web/bundles/' . $folderName . '/javascript/' . $jsPrefix . '/waypoints/sticky.min.js|static';

            $GLOBALS['TL_JAVASCRIPT'][] = $this->bundlePathPublic . '/javascript/' . $jsPrefix . '/iido/IIDO.Base.js|static';
//            $GLOBALS['TL_JAVASCRIPT'][] = 'web/bundles/' . $folderName . '/javascript/' . $jsPrefix . '/iido/IIDO.Functions.js|static';
            $GLOBALS['TL_JAVASCRIPT'][] = $this->bundlePathPublic . '/javascript/' . $jsPrefix . '/iido/IIDO.Page.js|static';
//            $GLOBALS['TL_JAVASCRIPT'][] = 'web/bundles/' . $folderName . '/javascript/' . $jsPrefix . '/iido/IIDO.Content.js|static';

//            if( $objLayout->loadJQueryUI )
//            {
//                $GLOBALS['TL_JAVASCRIPT'][] = 'web/bundles/' . $folderName . '/javascript/' . $jsPrefix . '/jquery-ui.min.js|static';;
//                $GLOBALS['TL_CSS'][]        = 'web/bundles/' . $folderName . '/css/frontend/jquery-ui.css||static';
//            }
        }

        if( $footerMode )
        {
            $GLOBALS['TL_CSS'][] = $this->bundlePathPublic . '/css/footer.css||static';
        }

        if( $objLayout->loadDomainCSS )
        {
            $objRootPage	= \PageModel::findByPk( $objPage->rootId );

            if( $objRootPage )
            {
                $domainFile	= str_replace('.', '-', $objRootPage->dns) . '.css';

                if( strlen($objRootPage->dns) && file_exists( TL_ROOT . '/' . \Config::get("uploadPath") . '/' . $objRootPage->alias . '/css/' . $domainFile) )
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
            if($jquery)
            {
                $GLOBALS['TL_JAVASCRIPT']['cookie'] = $this->bundlePathPublic . '/javascript/jquery/jquery.cookie.min.js|static';
            }
            elseif($mootools)
            {
                // TODO: add cookie script for mootools!!
            }
        }


        if( $objRootPage->enableLazyLoad || $objPage->enableLazyLoad )
        {
            if($jquery)
            {
                $GLOBALS['TL_JAVASCRIPT']['lazyload'] = $this->bundlePathPublic . '/javascript/jquery/jquery.lazyload.min.js|static';
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



        if( $objArticle )
        {
            while( $objArticle->next() )
            {
                $cssID = deserialize($objArticle->cssID, true);

                if( $objArticle->addBackgroundImage )
                {
                    $addToTag = '';
                    if( preg_match('/bg-image-height/', $cssID[1]) )
                    {
                        $addToTag = ' .article-inside';
                    }

                    $objImage = \FilesModel::findByPk( $objArticle->backgroundSRC );

                    if( $objImage )
                    {
                        $strStyles .= '#main .mod_article#' . $objArticle->alias . $addToTag . '{background-image:url("' . $objImage->path . '");';

                        if( $objArticle->backgroundPosition )
                        {
                            $setPos = false;

//                            if( $objArticle->backgroundPosition == "center_top" && $objArticle->backgroundMode == "cover" )
//                            {
//                                if( in_array("first", $objArticle->classes) )
//                                {
//                                    $setPos = true;
//                                    $strStyles .= 'background-position:center 125px;';
//                                }
//                            }

                            if( !$setPos )
                            {
                                $strStyles .= 'background-position:' . str_replace('_', ' ', $objArticle->backgroundPosition) . ';';
                            }
                        }

                        if( $objArticle->backgroundMode )
                        {
                            if( preg_match('/repeat/', $objArticle->backgroundMode) )
                            {
                                $strStyles .= 'background-repeat:' . $objArticle->backgroundMode . ';';
                            }
                            else
                            {
                                $strStyles .= 'background-repeat:no-repeat;';
//                                $strStyles .= '-webkit-background-size:cover;-moz-background-size:cover;-o-background-size:cover;background-size:cover;';
                            }
                        }

                        if( $objArticle->backgroundAttachment )
                        {
                            if( $objArticle->backgroundAttachment == "scrol" )
                            {
                                $objArticle->backgroundAttachment = "scroll";
                            }
                            $strStyles .= 'background-attachment:' . $objArticle->backgroundAttachment . ';';
                        }

                        $strStyles .= '}';
                    }
                }
            }
        }

        if( strlen($strStyles) )
        {
            $GLOBALS['TL_HEAD'][] = '<style>' . $strStyles . '</style>';
        }

        if ( is_array( $externalJavascript ) && count( $externalJavascript ) > 0 )
        {
            foreach ( $externalJavascript as $jsFile )
            {
                $objFile = \FilesModel::findByPk( $jsFile );

                if( file_exists(TL_ROOT . '/' . $objFile->path ) && strlen($objFile->path) )
                {
                    $GLOBALS[ 'TL_JAVASCRIPT' ][ ] = $objFile->path . '|static';
                }
            }
        }
    }



    public function modifyCustomizeFrontendPage($strBuffer, $templateName)
    {
        if ('fe_page' === $templateName)
        {
            if( preg_match('/open-fullscreen-search/', $strBuffer) )
            {
                $strModule  = \Controller::getFrontendModule( 3 );
                $pregMatch  = '([A-Za-z0-9\s\-=",;.:_]{0,})';

//                if( preg_match('/<div' . $pregMatch . 'class="mod_search([A-Za-z0-9\s\-_]{0,})"' . $pregMatch . '>/', $strModule, $arrMatches) )
//                {
//                    $strModule = preg_replace('/' . preg_quote($arrMatches[0],  '/') . '/', '', $strModule);
//                    $strModule = preg_replace('/<\div>$/', '', trim($strModule));

//                    $strModule = preg_replace('/<form/', '<form class="' . trim(preg_replace('/block/', '', $arrMatches[2])) . '"', trim($strModule));
//                }

//                $strModule = preg_replace('/<div class="formbody">/', '', $strModule);
//                $strModule = preg_replace('/<\/div>([\s\n]{0,})<\/form>/', '</form>', $strModule);

                $strModule = preg_replace('/<input' . $pregMatch . 'type="submit"' . $pregMatch . 'value="([A-Za-z0-9öäüÖÄÜß]{0,})"' . $pregMatch . '>/', '<button$1type="submit"$2$4>$3</button>', $strModule);

                $strModule = preg_replace('/<\/form>/', '<a href="" class="fullscreen-search-form-close">close</a></form>', $strModule);

                $strBuffer = preg_replace('/<\/body>/', $strModule . '</body>', $strBuffer);
            }

            // Mobile Menü
            $menuOpen   = '<a href="javascript:void(0)" class="main-navigation-mobile-open">navigation</a>';
            $menuClose  = '<button class=" main-navigation-mobile-close">close</button>';

//            //TODO: set module ID flexible, make a change posible
//            $modSearch  = \Controller::getFrontendModule( 10 );
//            $modNavi    = \Controller::getFrontendModule( 11 );
//            $modSocial  = ''; //TODO: Add Socialmedia links

//            $menuMobile = '<div class="main-navigation-mobile">' . $modSearch . $modNavi . $modSocial . $menuClose . '</div>';

//            $strBuffer = preg_replace('/<body([A-Za-z0-9\s\-_,;.:\{\}\(\)="\']{0,})>/',  '<body$1>' . $menuOpen . $menuMobile, $strBuffer);
        }

        return $strBuffer;
    }

}
