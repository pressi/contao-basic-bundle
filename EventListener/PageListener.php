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

    private $rootDir;



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

        $this->rootDir          = dirname(\System::getContainer()->getParameter('kernel.root_dir'));

    }

    public function getCustomizePageStatusIcon( $objCurrentPage, $strImage )
    {
        if( $objCurrentPage->type == "regular_redirect" )
        {
            $strImage = preg_replace('/^web\//', '', $this->bundlePathPublic) . '/images/pages/' . $strImage;
        }

        return $strImage;
    }



    public function generateCustomizePage( \PageModel $objPage, \LayoutModel $objLayout, \PageRegular $objPageRegular )
    {
        $arrBodyClasses     = array();

        Helper::replaceOtherDefaultScripts();
        Helper::checkForUniqueScripts();

        $objRootPage        = \PageModel::findByPk( $objPage->rootId );
        $strStyles          = '';
        $objArticle         = \ArticleModel::findPublishedByPidAndColumn($objPage->id, "main");
        $objTheme           = \ThemeModel::findByPk( $objLayout->pid );
        $bundles            = array_keys(\System::getContainer()->getParameter('kernel.bundles'));

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

//        if( $objLayout->loadJQueryUI )
//        {
//            $GLOBALS['TL_JAVASCRIPT']['jquery_ui'] = $this->bundlePathPublic . '/javascript/' . $jsPrefix . '/jquery-ui.min.js|static';;
//            $GLOBALS['TL_CSS']['jquery_ui']        = $this->bundlePathPublic . '/css/frontend/jquery-ui.css||static';
//        }

        if( $objPage->enableFullpage && $jsPrefix == "jquery" )
        {
            $GLOBALS['TL_JAVASCRIPT']['easings']            = $this->bundlePathPublic . '/javascript/' . $jsPrefix . '/jquery.easings.min.js|static';
            $GLOBALS['TL_JAVASCRIPT']['scrolloverflow']     = $this->bundlePathPublic . '/javascript/' . $jsPrefix . '/scrolloverflow.min.js|static';
            $GLOBALS['TL_JAVASCRIPT']['fullpage']           = $this->bundlePathPublic . '/javascript/' . $jsPrefix . '/jquery.fullPage.min.js|static';
        }

        if( $footerMode )
        {
            $GLOBALS['TL_CSS']['footer'] = $this->bundlePathPublic . '/css/footer.css||static';
        }

        $GLOBALS['TL_JAVASCRIPT']['j_gsap']                 = $this->bundlePathPublic . '/javascript/greensock/jquery.gsap.min.js|static';
        $GLOBALS['TL_JAVASCRIPT']['tweenlite']              = $this->bundlePathPublic . '/javascript/greensock/TweenMax.min.js|static';

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

        $this->addDefaultStylesheets();

        if( $jsPrefix == "jquery" )
        {
//            $GLOBALS['TL_JAVASCRIPT']['easings']            = $this->bundlePathPublic . '/javascript/' . $jsPrefix . '/jquery.easings.min.js|static';
//            $GLOBALS['TL_JAVASCRIPT'][] = $this->bundlePathPublic . '/javascript/' . $jsPrefix . '/jquery.scrollTo.min.js|static';
            $GLOBALS['TL_JAVASCRIPT']['smoothscroll']       = $this->bundlePathPublic . '/javascript/' . $jsPrefix . '/jquery.smooth-scroll.min.js|static';
            $GLOBALS['TL_JAVASCRIPT']['stellar']            = $this->bundlePathPublic . '/javascript/' . $jsPrefix . '/jquery.stellar.min.js|static';
            $GLOBALS['TL_JAVASCRIPT']['waypoints']          = $this->bundlePathPublic . '/javascript/' . $jsPrefix . '/jquery.waypoints.min.js|static';
//            $GLOBALS['TL_JAVASCRIPT']['iido_wp_infinite']   = $this->bundlePathPublic . '/javascript/' . $jsPrefix . '/waypoints/infinite.min.js|static';
            $GLOBALS['TL_JAVASCRIPT']['wp_inview']          = $this->bundlePathPublic . '/javascript/' . $jsPrefix . '/waypoints/inview.min.js|static';
            $GLOBALS['TL_JAVASCRIPT']['wp_sticky']          = $this->bundlePathPublic . '/javascript/' . $jsPrefix . '/waypoints/sticky.min.js|static';
//            $GLOBALS['TL_JAVASCRIPT'][] = $this->bundlePathPublic . '/javascript/' . $jsPrefix . '/jquery.sticky-kit.min.js|static';
            $GLOBALS['TL_JAVASCRIPT']['isotope']            = $this->bundlePathPublic . '/javascript/' . $jsPrefix . '/isotope.pkgd.min.js|static';
//            $GLOBALS['TL_JAVASCRIPT']['iso_fit-columns']    = $this->bundlePathPublic . '/javascript/' . $jsPrefix . '/isotope/fit-columns.js|static';

            $GLOBALS['TL_JAVASCRIPT']['iido_base']          = $this->bundlePathPublic . '/javascript/' . $jsPrefix . '/iido/IIDO.Base.js|static';
//            $GLOBALS['TL_JAVASCRIPT'][] = $this->bundlePathPublic . '/javascript/' . $jsPrefix . '/iido/IIDO.Functions.js|static';
            $GLOBALS['TL_JAVASCRIPT']['iido_page']          = $this->bundlePathPublic . '/javascript/' . $jsPrefix . '/iido/IIDO.Page.js|static';
//            $GLOBALS['TL_JAVASCRIPT'][] = $this->bundlePathPublic . '/javascript/' . $jsPrefix . '/iido/IIDO.Content.js|static';
        }

        if( $objRootPage->enablePageFadeEffect )
        {
            $arrBodyClasses[] = 'page-fade-animation';
            $GLOBALS['TL_JAVASCRIPT']['barba']              = $this->bundlePathPublic . '/javascript/barba.min.js|static';
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



//        if( $objArticle )
//        {
//            while( $objArticle->next() )
//            {
//                $cssID = deserialize($objArticle->cssID, true);
//
//                if( $objArticle->addBackgroundImage )
//                {
//                    $addToTag = '';
//                    if( preg_match('/bg-image-height/', $cssID[1]) )
//                    {
//                        $addToTag = ' .article-inside';
//                    }
//
//                    $objImage = \FilesModel::findByPk( $objArticle->backgroundSRC );
//
//                    if( $objImage )
//                    {
//                        $strStyles .= '#main .mod_article#' . $objArticle->alias . $addToTag . '{background-image:url("' . $objImage->path . '");';
//
//                        if( $objArticle->backgroundPosition )
//                        {
//                            $setPos = false;
//
////                            if( $objArticle->backgroundPosition == "center_top" && $objArticle->backgroundMode == "cover" )
////                            {
////                                if( in_array("first", $objArticle->classes) )
////                                {
////                                    $setPos = true;
////                                    $strStyles .= 'background-position:center 125px;';
////                                }
////                            }
//
//                            if( !$setPos )
//                            {
//                                $strStyles .= 'background-position:' . str_replace('_', ' ', $objArticle->backgroundPosition) . ';';
//                            }
//                        }
//
//                        if( $objArticle->backgroundMode )
//                        {
//                            if( preg_match('/repeat/', $objArticle->backgroundMode) )
//                            {
//                                $strStyles .= 'background-repeat:' . $objArticle->backgroundMode . ';';
//                            }
//                            else
//                            {
//                                $strStyles .= 'background-repeat:no-repeat;';
////                                $strStyles .= '-webkit-background-size:cover;-moz-background-size:cover;-o-background-size:cover;background-size:cover;';
//                            }
//                        }
//
//                        if( $objArticle->backgroundAttachment )
//                        {
//                            if( $objArticle->backgroundAttachment == "scrol" )
//                            {
//                                $objArticle->backgroundAttachment = "scroll";
//                            }
//                            $strStyles .= 'background-attachment:' . $objArticle->backgroundAttachment . ';';
//                        }
//
//                        $strStyles .= '}';
//                    }
//                }
//            }
//        }
//
//        if( strlen($strStyles) )
//        {
//            $GLOBALS['TL_HEAD'][] = '<style>' . $strStyles . '</style>';
//        }

        $arrBodyClasses = $this->createDefaultStylesheet( $arrBodyClasses );

        if ( is_array( $externalJavascript ) && count( $externalJavascript ) > 0 )
        {
            foreach ( $externalJavascript as $jsFile )
            {
                $objFile = \FilesModel::findByPk( $jsFile );

                if( file_exists($this->rootDir . '/' . $objFile->path ) && strlen($objFile->path) )
                {
                    $GLOBALS[ 'TL_JAVASCRIPT' ][ ] = $objFile->path . '|static';
                }
            }
        }

        if( count($arrBodyClasses) )
        {
            $objPage->cssClass = $objPage->cssClass . ((strlen($objPage->cssClass)) ? ' ' : '') . implode(" ", $arrBodyClasses);
        }

        Helper::checkForUniqueScripts();
    }



    public function modifyCustomizeFrontendPage($strBuffer, $templateName)
    {
        /* @var \PageModel $objPage */
        global $objPage;

        $objRootPage    = \PageModel::findByPk( $objPage->rootId );
        $objLayout      = Helper::getPageLayout( $objPage );
        $objTheme       = \ThemeModel::findByPk( $objLayout->pid );

        if ('fe_page' === $templateName)
        {
            if( preg_match('/open-fullscreen-search/', $strBuffer) )
            {
                $objModule  = \ModuleModel::findOneBy("type", "search");

                if( $objModule )
                {
                    $strModule = \Controller::getFrontendModule( $objModule->id ); //6
                    $pregMatch = '([A-Za-z0-9\s\-=",;.:_]{0,})';

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
            }

            // Mobile Menü
            $menuOpen   = '<a href="javascript:void(0)" class="main-navigation-mobile-open hamburger hamburger--squeeze js-hamburger"><div class="hamburger-box"><div class="hamburger-inner"></div></div></a>';
            $menuClose  = '<button class=" main-navigation-mobile-close">close</button>';

            $strModuleTable = \ModuleModel::getTable();
            $objNavModule   = \ModuleModel::findOneBy(array($strModuleTable . ".type=?", $strModuleTable . ".pid=?"), array("navigation", $objTheme->id));

//            //TODO: set module ID flexible, make a change posible
            $modSearch  = ''; //\Controller::getFrontendModule( 10 );
            $modNavi    = ''; //\Controller::getFrontendModule( $objNavModule->id ); // 11

            if ($objNavModule )
            {
                $strClass = \Module::findClass( $objNavModule->type );

                if (class_exists($strClass))
                {
                    $objNavModule->typePrefix = 'ce_';

                    /** @var \Module $objNavModule */
                    $objNavModule = new $strClass($objNavModule, "main");

                    $objNavModule->cssID = array('', 'nav-mobile-main');
                    $objNavModule->navigationTpl = 'nav_mobile';

                    $modNavi = $objNavModule->generate();
                }
            }

            $modSocial  = ''; //TODO: Add Socialmedia links

            $menuMobile = '<div class="main-navigation-mobile">' . $modSearch . $modNavi . $modSocial . $menuClose . '</div>';

//            $strBuffer = preg_replace('/<body([A-Za-z0-9\s\-_,;.:\{\}\(\)="\'<>%\/]{0,})>/',  '<body$1>' . $menuOpen . $menuMobile, $strBuffer);
            $strBuffer = preg_replace('/<\/body>/',  $menuOpen . $menuMobile . '</body>', $strBuffer);
        }

        return $strBuffer;
    }



    protected function createDefaultStylesheet( $arrBodyClasses )
    {
        global $objPage;

        $objRootPage        = \PageModel::findByPk( $objPage->rootId );

        $arrPageStyles      = array();
        $objAllPages        = \PageModel::findAll(); //\PageModel::findPublishedByPid( $objPage->rootId, array("order"=>"sorting") );
        $createTime         = 0;
        $createFile         = FALSE;
        $objFile            = new \File('assets/css/page-styles.css');

        if( $objFile->exists() )
        {
            $createTime = $objFile->mtime;
        }

        if( $objAllPages )
        {
            while( $objAllPages->next() )
            {
                $objArticles = \ArticleModel::findPublishedByPidAndColumn( $objAllPages->id, "main");

                if( $objArticles )
                {
                    while( $objArticles->next() )
                    {
                        if( $objArticles->tstamp > $createTime || $this->getArticleLastSave( $objArticles->id ) > $createTime )
                        {
                            $createFile     = TRUE;
                        }

                        if( $objArticles->fullWidth )
                        {
                            if( !preg_match('/content-width/', $objAllPages->cssClass) && !in_array('content-width', $arrBodyClasses))
                            {
                                $arrBodyClasses[] = 'content-width';
                            }
                        }

                        $cssID      = deserialize($objArticles->cssID, TRUE);
                        $strImage   = '';
                        $objImage   = \FilesModel::findByUuid( $objArticles->bgImage );

                        if( $objImage && file_exists($this->rootDir . '/' . $objImage->path) )
                        {
                            $strImage = $objImage->path;
                        }

                        $arrPageStyles[ $objArticles->id ] = array
                        (
                            'selector'          => '#main .mod_article#' . (empty($cssID[0])? 'article-' . $objArticles->id : $cssID[0]),

                            'background'        => TRUE,
                            'bgcolor'           => $objArticles->bgColor,
                            'bgimage'           => $strImage,
                            'bgrepeat'          => $objArticles->bgRepeat,
                            'bgposition'        => $objArticles->bgPosition,
                            'gradientAngle'     => $objArticles->gradientAngle,
                            'gradientColors'    => $objArticles->gradientColors
                        );

                        $bgColor        = deserialize($objArticles->bgColor, TRUE);
                        $arrOwnStyles   = array();

//                if( !empty($bgColor[0]) )
//                {
//                    $rgb = ColorHelper::HTMLToRGB( $bgColor[0] );
//                    $hsl = ColorHelper::RGBToHSL( $rgb );
//
//                    if( $hsl->lightness < 200 )
//                    {
//                        $arrPageStyles[ $objArticles->id ]['font']      = TRUE;
//                        $arrPageStyles[ $objArticles->id ]['fontcolor'] = serialize(array('fff', ''));
//                    }
//                }

                        $arrBackgroundSize = deserialize($objArticles->bgSize, true);

                        if( is_array($arrBackgroundSize) && strlen($arrBackgroundSize[2]) && $arrBackgroundSize[2] != '-' )
                        {
                            $bgSize = $arrBackgroundSize[2];

                            if( $arrBackgroundSize[2] == 'own' )
                            {
                                unset($arrBackgroundSize[2]);
                                $bgSize = implode(" ", $arrBackgroundSize);
                            }

                            $arrOwnStyles[] = '-webkit-background-size:' . $bgSize . ';-moz-background-size:' . $bgSize . ';-o-background-size:' . $bgSize . ';background-size:' . $bgSize . ';';
                        }

                        if( $objArticles->bgAttachment )
                        {
                            $arrOwnStyles[] = 'background-attachment:' . $objArticles->bgAttachment . ';';
                        }

                        if( count($arrOwnStyles) )
                        {
                            $arrPageStyles[ $objArticles->id ]['own'] = implode("", $arrOwnStyles);
                        }
                    }
                }
            }
        }

        if( count($arrPageStyles) && $createFile )
        {
            if( $objFile->exists() )
            {
                $objFile->delete();
            }

            $objStyleSheets     = new \StyleSheets();
            $arrStyles          = array();

            foreach($arrPageStyles as $arrPageStyle)
            {
                $arrStyles[] = $objStyleSheets->compileDefinition($arrPageStyle, true);
            }

            if( count($arrStyles) )
            {
                $objFile            = new \File('assets/css/page-styles.css');

                foreach($arrStyles as $strStyle)
                {
                    $strOnlyStyles = preg_replace('/#main .mod_article#([A-Za-z0-9\-_]{0,})\{([A-Za-z0-9\s\-\(\)\"\'\\,;.:\/_@]{0,})\}/', '$2', $strStyle);

                    if( strlen(trim($strOnlyStyles)) )
                    {
                        $objFile->append($strStyle, '');
                    }
                }

                $objFile->close();
            }
        }

        if( file_exists($this->rootDir . '/assets/css/page-styles.css') )
        {
            $GLOBALS['TL_CSS']['custom_page-styles'] = 'assets/css/page-styles.css||static';
        }




//
////echo "<pre>";
//// print_r( $objFile->exists() );
//        if( !$objFile->exists() )
//        {
//            $objFile->write('.');
//            $objFile->close();
////echo "<br> NO FILE";
//            $createTime     = $objFile->ctime;
//            $createFile     = FALSE;
//        }
//        else
//        {
//            $createTime     = $objFile->mtime;
//        }
////echo "<br>"; print_r( $createTime ); echo "<br>"; print_r( $createFile );
//        if( $objAllPages )
//        {
//            while( $objAllPages->next() )
//            {
//                $objArticles = \ArticleModel::findPublishedByPidAndColumn( $objAllPages->id, "main");
//
//                if( $objArticles )
//                {
//                    while( $objArticles->next() )
//                    {
////                        echo "<br>Ar: ";
////                        print_r( $objArticles->tstamp );
////                        echo "<br>Time: ";
////                        print_r( $createTime );
////                        echo " ==> ";
////                        print_r( $objArticles->tstamp > $createTime );
////                        echo " ==> ";
////                        print_r( $this->getArticleLastSave( $objArticles->id ) > $createTime );
////                        echo "<br>AS: ";
////                        print_r( $this->getArticleLastSave( $objArticles->id ) );
//
//                        if( $objArticles->tstamp > $createTime || $this->getArticleLastSave( $objArticles->id ) > $createTime )
//                        {
////                            echo "<br> YES CREATE IT";
//                            $createFile     = TRUE;
//                        }
//
//                        if( $objArticles->fullWidth )
//                        {
//                            if( !preg_match('/content-width/', $objAllPages->cssClass) && !in_array('content-width', $arrBodyClasses))
//                            {
//                                $arrBodyClasses[] = 'content-width';
//                            }
//                        }
//
//                        $cssID      = deserialize($objArticles->cssID, TRUE);
//                        $strImage   = '';
//                        $objImage   = \FilesModel::findByUuid( $objArticles->bgImage );
//
//                        if( $objImage && file_exists($this->rootDir . '/' . $objImage->path) )
//                        {
//                            $strImage = $objImage->path;
//                        }
//
//                        $arrPageStyles[ $objArticles->id ] = array
//                        (
//                            'selector'          => '#main .mod_article#' . (empty($cssID[0])? 'article-' . $objArticles->id : $cssID[0]),
//
//                            'background'        => TRUE,
//                            'bgcolor'           => $objArticles->bgColor,
//                            'bgimage'           => $strImage,
//                            'bgrepeat'          => $objArticles->bgRepeat,
//                            'bgposition'        => $objArticles->bgPosition,
//                            'gradientAngle'     => $objArticles->gradientAngle,
//                            'gradientColors'    => $objArticles->gradientColors
//                        );
//
//                        $bgColor        = deserialize($objArticles->bgColor, TRUE);
//                        $arrOwnStyles   = array();
//
////                if( !empty($bgColor[0]) )
////                {
////                    $rgb = ColorHelper::HTMLToRGB( $bgColor[0] );
////                    $hsl = ColorHelper::RGBToHSL( $rgb );
////
////                    if( $hsl->lightness < 200 )
////                    {
////                        $arrPageStyles[ $objArticles->id ]['font']      = TRUE;
////                        $arrPageStyles[ $objArticles->id ]['fontcolor'] = serialize(array('fff', ''));
////                    }
////                }
//
//                        $arrBackgroundSize = deserialize($objArticles->bgSize, true);
//
//                        if( is_array($arrBackgroundSize) && strlen($arrBackgroundSize[2]) && $arrBackgroundSize[2] != '-' )
//                        {
//                            $bgSize = $arrBackgroundSize[2];
//
//                            if( $arrBackgroundSize[2] == 'own' )
//                            {
//                                unset($arrBackgroundSize[2]);
//                                $bgSize = implode(" ", $arrBackgroundSize);
//                            }
//
//                            $arrOwnStyles[] = '-webkit-background-size:' . $bgSize . ';-moz-background-size:' . $bgSize . ';-o-background-size:' . $bgSize . ';background-size:' . $bgSize . ';';
//                        }
//
//                        if( $objArticles->bgAttachment )
//                        {
//                            $arrOwnStyles[] = 'background-attachment:' . $objArticles->bgAttachment . ';';
//                        }
//
//                        if( count($arrOwnStyles) )
//                        {
//                            $arrPageStyles[ $objArticles->id ]['own'] = implode("", $arrOwnStyles);
//                        }
//                    }
//                }
//            }
//        }
////        echo "<pre>";
////echo "<br>"; print_r( $arrPageStyles ); exit;
//        if( count($arrPageStyles) )
//        {
//            if( $createFile )
//            {
////                echo "<br>CREATE FILE";
////                exit;
//                if( $objFile->exists() )
//                {
//                    $objFile->delete();
//                }
//
//                $objStyleSheets     = new \StyleSheets();
//                $arrStyles          = array();
//
//                foreach($arrPageStyles as $arrPageStyle)
//                {
//                    $arrStyles[] = $objStyleSheets->compileDefinition($arrPageStyle, true);
//                }
//
//                if( count($arrStyles) )
//                {
//                    $objFile            = new \File('assets/css/page-styles.css');
//
//                    foreach($arrStyles as $strStyle)
//                    {
//                        $strOnlyStyles = preg_replace('/#main .mod_article#([A-Za-z0-9\-_]{0,})\{([A-Za-z0-9\s\-\(\)\"\'\\,;.:\/_@]{0,})\}/', '$2', $strStyle);
//
//                        if( strlen(trim($strOnlyStyles)) )
//                        {
//                            $objFile->append($strStyle, '');
//                        }
//                    }
//
//                    $objFile->close();
//                }
//            }
//            else
//            {
//                if( $objFile->exists() )
//                {
//                    $objFile->delete();
//                }
//            }
//        }
//        else
//        {
//            if( $objFile->exists() )
//            {
//                $objFile->delete();
//            }
//        }
////echo "<br>"; print_r( file_exists($this->rootDir . '/assets/css/page-styles.css') ); exit;
//        if( file_exists($this->rootDir . '/assets/css/page-styles.css') )
//        {
//            $GLOBALS['TL_CSS']['custom_page-styles'] = 'assets/css/page-styles.css||static';
//        }

        if( file_exists($this->rootDir . '/files/' . $objRootPage->alias . '/css/theme.css') )
        {
            $GLOBALS['TL_CSS']['custom_theme'] = 'files/' . $objRootPage->alias . '/css/theme.css||static';
        }
//exit;
        return $arrBodyClasses;
    }



    protected function getArticleLastSave( $articleID )
    {
        $objResult = \Database::getInstance()->prepare("SELECT * FROM tl_version WHERE fromTable=? AND pid=? ORDER BY tstamp DESC LIMIT 1")->execute("tl_article", $articleID);

        if( $objResult->numRows > 0 )
        {
            $objResult = $objResult->first();

            return $objResult->tstamp;
        }

        return 0;
    }



    protected function addDefaultStylesheets()
    {
        global $objPage;

        $cssPath        = $this->bundlePathPublic . '/css/';
        $cssPathStd     = $cssPath . 'frontend/iido/';
        $cssPathMaster  = 'files/master/css/';
        $cssPathCustom  = 'files/' . $objPage->rootAlias . '/css/';

        $arrFiles       = array
        (
            'reset.css',
            'animate.css',
//            'grid16.css',
            'styles.css',
            'standards.css',
            'page.css'
        );

        $arrMasterFiles = array
        (
            'reset.css',
            'animate.css',
            'core.css',
            'buttons.css',
            'layout.css',
            'navigation.css',
            'bulma/columns.css',
            'content.css',
            'responsive.css'
        );

        $arrCustomFiles = array
        (
            'fonts.css',
            'icons.css',
            'animate.css',
            'core.css',
            'buttons.css',
            'layout.css',
            'navigation.css',
            'content.css',
            'style.css',
            'styles.css',
            'page.css',
            'responsive.css'
        );

        foreach($arrFiles as $strFile)
        {
            if( file_exists($this->rootDir . '/' . $cssPathStd . $strFile) )
            {
                $GLOBALS['TL_CSS'][ 'std_' . $strFile ] =  $cssPathStd . $strFile . '||static';
            }
        }

        foreach($arrMasterFiles as $strFile)
        {
            if( file_exists($this->rootDir . '/' . $cssPathMaster  . $strFile) )
            {
                $GLOBALS['TL_CSS'][ 'master_' . $strFile ] =  $cssPathMaster . $strFile . '||static';
            }
        }

        foreach($arrCustomFiles as $strFile)
        {
            if( file_exists($this->rootDir . '/' . $cssPathCustom  . $strFile) )
            {
                $GLOBALS['TL_CSS'][ 'custom_' . $strFile ] =  $cssPathCustom . $strFile . '||static';
            }
        }
    }

}
