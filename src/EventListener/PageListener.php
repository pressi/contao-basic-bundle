<?php
declare(strict_types=1);

/*******************************************************************
 * (c) 2020 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\EventListener;


use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\StringUtil;
use IIDO\BasicBundle\Config\BundleConfig;
use IIDO\BasicBundle\Helper\PageHelper;
use IIDO\BasicBundle\Helper\ScriptHelper;
use IIDO\BasicBundle\Renderer\MobileRenderer;
use IIDO\BasicBundle\Renderer\SearchRenderer;
use IIDO\BasicBundle\Renderer\SectionRenderer;
use Terminal42\ServiceAnnotationBundle\ServiceAnnotationInterface;
use Contao\ArticleModel;
use Contao\LayoutModel;
use Contao\PageModel;
use Contao\PageRegular;
use IIDO\BasicBundle\Helper\BasicHelper;
use IIDO\BasicBundle\Helper\StyleSheetHelper;


class PageListener implements ServiceAnnotationInterface
{
    /**
     * @Hook("generatePage")
     */
    public function onGeneratePage(PageModel $pageModel, LayoutModel $layout, PageRegular $pageRegular): void
    {
        $publicBundlePath = BundleConfig::getBundlePath(true);

//        $GLOBALS['TL_JAVASCRIPT']['j_mask'] = 'files/bestpreisagrar/js/jquery.mask.min.js|static';

        if( false === strpos($pageModel->cssClass, 'ext-blankpage') && false === strpos($layout->cssClass, 'ext-blankpage') )
        {
            StyleSheetHelper::addDefaultPageStyleSheets();
        }

        ScriptHelper::addDefaultScripts();

        ScriptHelper::addInternScript('base');
        ScriptHelper::addInternScript('form');
        ScriptHelper::addInternScript('content');
        ScriptHelper::addInternScript('page');

        if( ScriptHelper::hasPageAnimation() )
        {
            ScriptHelper::addScript('waypoints');
            ScriptHelper::addSourceScript('waypoints', array('wp_inview' => 'inview', 'wp_sticky'=>'sticky'));
//                ScriptHelper::addSourceScript('waypoints', array('wp_inview' => 'inview', 'wp_sticky'=>'sticky', 'wp_infinite'=>'infinite'));
        }

        if( ScriptHelper::hasPageIsotope() )
        {
            ScriptHelper::addScript('isotope');
        }

        if( ScriptHelper::hasPageFullPage() )
        {
            ScriptHelper::addScript('fullpage', true, true);
        }

        //TODO: selectable in backend!!! wenn seite in lightbox öffnet!?
//        StyleSheetHelper::addThemeStyle('pickdate', 'default,default.date');
//        ScriptHelper::addScript('pickdate', false, true);

//        if( BasicHelper::getLanguage() !== 'en' )
//        {
//            ScriptHelper::addTranslateScript('pickdate', BasicHelper::getLanguage());
//        }


        //TODO add wenn needed && create function ScriptHelper::addLibSrcipt || addLibraryScript
        $GLOBALS['TL_JAVASCRIPT']['j_gsap']                 = $publicBundlePath . '/scripts/lib/greensock/jquery.gsap.min.js|static';
        $GLOBALS['TL_JAVASCRIPT']['tweenlite']              = $publicBundlePath . '/scripts/lib/greensock/TweenMax.min.js|static';


        //TODO: include when it will be needed && -||-
        $GLOBALS['TL_JAVASCRIPT']['scrollmagic']            = $publicBundlePath . '/scripts/lib/scrollMagic/2.0.7/ScrollMagic.min.js|static';
        $GLOBALS['TL_JAVASCRIPT']['scrollmagic_gsap']       = $publicBundlePath . '/scripts/lib/scrollMagic/2.0.7/animation.gsap.min.js|static';
//        $GLOBALS['TL_JAVASCRIPT']['scrollmagic_debug']      = $publicBundlePath . '/scripts/lib/scrollMagic/2.0.7/debug.addIndicators.min.js|static';
        $GLOBALS['TL_JAVASCRIPT']['scrollmagic_velocity']   = $publicBundlePath . '/scripts/lib/scrollMagic/2.0.7/animation.velocity.min.js|static';
//        ScriptHelper::addScript('scrollMagic');
//        ScriptHelper::addSourceScript('scrollMagic', 'animation.gsap');
//        ScriptHelper::addSourceScript('scrollMagic', 'debug.addIndicators');
//        ScriptHelper::addSourceScript('scrollMagic', 'animation.velocity');

//        echo "<pre>"; print_r( $GLOBALS['TL_JAVASCRIPT'] ); exit;
//        echo "<pre>"; print_r( $GLOBALS['TL_CSS'] ); exit;
//        echo "<pre>"; print_r( $GLOBALS['TL_USER_CSS'] ); exit;
    }


    /**
     * @Hook("modifyFrontendPage")
     */
    public function onModifyFrontendPage($strBuffer, $templateName): string
    {
        global $objPage;

        if( 0 === strpos($templateName, 'fe_page') )
        {
            $objFooter = ArticleModel::findByAlias('ge_footer_' . $objPage->rootAlias );
            $objHeader = ArticleModel::findByAlias('ge_header_' . $objPage->rootAlias );

//            $objFooter = ArticleModel::findByAlias('ge_footer_' . $objPage->rootAlias );
//            $objHeader = ArticleModel::findByAlias('ge_header_' . $objPage->rootAlias );

            if( $objHeader )
            {
                $headerOnLeft   = false;
                $headerClasses  = StringUtil::deserialize( $objHeader->cssID, true );

                if( $objHeader->layout === 'left' || false !== strpos($headerClasses[1], 'layout-02') )
                {
                    $headerOnLeft = true;

                    if( false === strpos($headerClasses[1], 'layout-02') )
                    {
                        $headerClasses[1] = trim($headerClasses[1] . ' layout-02');

                        $objHeader->cssID = $headerClasses;
                    }
                }

                $strBuffer = $this->renderSection('header', $objHeader, $strBuffer);

                if( false !== strpos($headerClasses[1], 'layout-01') )
                {
                    $objOffsetNavigation = ArticleModel::findBy(['articleType=?', 'published=?'], ['navigationCont', '1'] );

                    if( $objOffsetNavigation )
                    {
                        $strBuffer = SectionRenderer::renderOffsetNavigation( $strBuffer, true, $objOffsetNavigation );

                        $offsetNavToggler = SectionRenderer::getOffsetNavigationToggler( $objHeader );

                        $strBuffer = preg_replace('/<\/div>([\n\s]{0,})<\/div>([\n\s]{0,})<\/div>([\n\s]{0,})<\/header>/', $offsetNavToggler . '</div></div></div></header>', $strBuffer, 1, $count);

                        if( !$count )
                        {
                            $strBuffer = preg_replace('/<\/div>([\n\s]{0,})<\/header>/', $offsetNavToggler . '</div></header>', $strBuffer);
                        }
                    }
                }

                if( $headerOnLeft )
                {
                    $strBuffer = PageHelper::addBodyClasses('header-on-left', $strBuffer );
                }
            }

            if( $objFooter )
            {
                $strBuffer = $this->renderSection('footer', $objFooter, $strBuffer);
            }

            $strBuffer = SearchRenderer::renderSearchTemplate( $strBuffer );
            $strBuffer = MobileRenderer::renderMobileMenuTemplate( $strBuffer );

//            $strBuffer = SectionRenderer::renderStickyHeader( $strBuffer, $offsetNavToggler );
            $strBuffer = SectionRenderer::renderStickyHeader( $strBuffer );
//            $strBuffer = SectionRenderer::renderFixedButtons( $strBuffer );

            $strBuffer = preg_replace('/<!-- REMOVE:([A-Za-z0-9\n\s\-,;.:_\{\}><]{0,}) -->/', '', $strBuffer);

            $strBuffer = preg_replace('/<div id="fixedContainer">([\s\n]{0,})<div class="inside">([\s\n]{0,})<\/div>([\s\n]{0,})<\/div>/', '', $strBuffer);
            $strBuffer = preg_replace('/<div id="stickyHeader">([\s\n]{0,})<div class="inside">([\s\n]{0,})<\/div>([\s\n]{0,})<\/div>/', '', $strBuffer);
            $strBuffer = preg_replace('/<div class="custom">([\s\n]{0,})<\/div>/', '', $strBuffer);
        }

        return $strBuffer;
    }



    /**
     * @Hook("getPageStatusIcon")
     */
    public function onGetPageStatusIcon( $objPage, string $image): string
    {
        if( $objPage->type === 'global_element' )
        {
            $image = str_replace('global_element', 'redirect', $image);
        }

        return $image;
    }



    protected function renderSection( string $tag, $model, string $strContent ): string
    {
        $cssID          = \StringUtil::deserialize( $model->cssID, TRUE );
        $showGrid       = (FALSE !== strpos($cssID[1], 'show-grid'));
        $gridJustify    = (FALSE !== strpos($cssID[1], 'justify'));
        $gridCenter     = (FALSE !== strpos($cssID[1], 'valign-middle'));

        $cssID[1]       = trim( preg_replace(['/show-grid/', '/justify/', '/valign-middle/'], ['', '', ''], $cssID[1]) );

        if( $showGrid )
        {
            $strContent = preg_replace('/<' . $tag . '([A-Za-z0-9\s\-="]{0,})>([\s\n]{0,})<div class="inside">/', '<' . $tag . '$1>$2<div class="inside"><div class="grid-container' . ($gridJustify ? ' justify' : '') . ($gridCenter ? ' valign-middle' : '') . '">', $strContent, -1, $count);

            if( $count )
            {
                $strContent = preg_replace('/<\/' . $tag . '>/', '</div></' . $tag . '>', $strContent);
            }
        }

        if( $cssID[1] )
        {
            $strContent = preg_replace('/<' . $tag . '/', '<' . $tag . ' class="' . $cssID[1] . '"', $strContent);
        }

        return $strContent;
    }
}