<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\EventListener;


use IIDO\BasicBundle\Helper\BasicHelper;
use IIDO\BasicBundle\Helper\ColorHelper;
use IIDO\BasicBundle\Helper\HeaderHelper;
use IIDO\BasicBundle\Helper\PageHelper;
use IIDO\BasicBundle\Renderer\ArticleTemplateRenderer;


/**
 * Class Frontend Template Hook
 * @package IIDO\Customize\Hook
 */
class FrontendTemplateListener extends DefaultListener
{

    /**
     * Edit the Frontend Template
     */
    public function parseCustomizeFrontendTemplate($strContent, $strTemplate)
    {
        /* @var \PageModel $objPage */
        global $objPage;

        if( $strTemplate === "mod_article" )
        {
            $strContent = ArticleTemplateRenderer::parseTemplate( $strContent, $strTemplate );
        }


        elseif( $strTemplate === "mod_navigation" )
        {
            preg_match_all('/id="skipNavigation([0-9]{0,})"/', $strContent, $navMatches);

            if( is_array($navMatches[0]) && count($navMatches[0]) > 0 )
            {
                $objModule = \ModuleModel::findByPk( $navMatches[1][0] );

                if( $objModule )
                {
                    if( !$objModule->hideSubtitles )
                    {
                        preg_match_all('/<li([A-Za-z0-9\s\-_:;.,\(\)#"\'=\/]{0,})><(a|strong)([A-Za-z0-9\s\-_:;.,\/\(\)#"\'=]{0,})>([A-Za-z0-9\s\-.:;,#+&\[\]\(\)=ßöäüÖÄÜ?!%$"\']{0,})<\/(a|strong)><\/li>/', $strContent, $navListMatches);

                        if( is_array($navListMatches[0]) && count($navListMatches[0]) > 0 )
                        {
                            foreach( $navListMatches[3] as $index => $strLink )
                            {
                                if( $navListMatches[2][ $index ] == "strong" )
                                {
                                    $linkTitle      = $navListMatches[4][ $index ];
                                    $objLinkPage    = \PageModel::findBy("title", $linkTitle);

                                    if( $objLinkPage )
                                    {
                                        if( $objLinkPage->count() > 1 )
                                        {
                                            $objLinkPage = \PageModel::findByPk( \Frontend::getPageIdFromUrl() );
                                        }

                                        $subtitle = '<span class="subtitle">' . (($objLinkPage->subtitlePosition == "after") ? ' ' : '') . trim($objLinkPage->subtitle) . (($objLinkPage->subtitlePosition == "before") ? ' ' : '') . '</span>';

                                        if( strlen(trim($objLinkPage->subtitle)) )
                                        {
                                            if( $objLinkPage->subtitlePosition == "before" )
                                            {
                                                $strContent = preg_replace('/' .  preg_quote($navListMatches[0][ $index ], "/") .'/', '<li' . $navListMatches[1][ $index ] . '><' . $navListMatches[2][ $index ] . $navListMatches[3][ $index ] .'>' . $subtitle . $navListMatches[4][ $index ] . '</' . $navListMatches[5][ $index ] . '></li>', $strContent);
                                            }
                                            elseif( $objLinkPage->subtitlePosition == "after" )
                                            {
                                                $strContent = preg_replace('/' .  preg_quote($navListMatches[0][ $index ], "/") .'/', '<li' . $navListMatches[1][ $index ] . '><' . $navListMatches[2][ $index ] . $navListMatches[3][ $index ] .'>' . $navListMatches[4][ $index ] . $subtitle . '</' . $navListMatches[5][ $index ] . '></li>', $strContent);
                                            }
                                        }
                                    }
                                }
                                else
                                {
                                    preg_match_all('/href="([A-Za-z0-9\-\/._#&]{0,})"/', $strLink, $linkMatches);

                                    if( is_array($linkMatches[0]) && count($linkMatches[0]) > 0 )
                                    {
                                        $linkParts	= explode("/", $linkMatches[1][0]);
                                        $linkPart	= str_replace('.html', '', array_pop($linkParts));

                                        $objLinkPage    = \PageModel::findByIdOrAlias($linkPart);
                                        $subtitle       = '<span class="subtitle">' . (($objLinkPage->subtitlePosition == "after") ? ' ' : '') . trim($objLinkPage->subtitle) . (($objLinkPage->subtitlePosition == "before") ? ' ' : '') . '</span>';

                                        if( $objLinkPage )
                                        {
                                            if( strlen(trim($objLinkPage->subtitle)) )
                                            {
                                                if( $objLinkPage->subtitlePosition == "before" )
                                                {
                                                    $strContent = preg_replace('/' .  preg_quote($navListMatches[0][ $index ], "/") .'/', '<li' . $navListMatches[1][ $index ] . '><' . $navListMatches[2][ $index ] . $navListMatches[3][ $index ] .'>' . $subtitle . $navListMatches[4][ $index ] . '</' . $navListMatches[5][ $index ] . '></li>', $strContent);
                                                }
                                                elseif( $objLinkPage->subtitlePosition == "after" )
                                                {
                                                    $strContent = preg_replace('/' .  preg_quote($navListMatches[0][ $index ], "/") .'/', '<li' . $navListMatches[1][ $index ] . '><' . $navListMatches[2][ $index ] . $navListMatches[3][ $index ] .'>' . $navListMatches[4][ $index ] . $subtitle . '</' . $navListMatches[5][ $index ] . '></li>', $strContent);
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        return $strContent;
    }



    /**
     * output frontend template
     *
     * @param string $strBuffer
     * @param string $strTemplate
     *
     * @return string
     */
    public function outputCustomizeFrontendTemplate($strBuffer, $strTemplate)
    {
        /** @var \PageModel $objPage */
        global $objPage;

        $arrBodyClasses     = array();
        $objRootPage        = \PageModel::findByPk( $objPage->rootId );
        $objLayout          = BasicHelper::getPageLayout( $objPage) ;
        $isFullpage         = PageHelper::isFullPageEnabled( $objPage, $objLayout );

        if( $strTemplate === 'fe_page' )
        {
            $strBuffer          = PageHelper::replaceBodyClasses( $strBuffer );
            $strBuffer          = PageHelper::replaceWebsiteTitle( $strBuffer );

            $layoutHasFooter    = $objLayout->rows;
            $footerMode         = (($objLayout->footerAtBottom && ($layoutHasFooter == "2rwf" || $layoutHasFooter == "3rw")) ? true : false);

            if( $isFullpage )
            {
                if( $objPage->fullpageDirection == "horizontal" )
                {
                    $strBuffer = preg_replace('/<main([A-Za-z0-9\s\-="\(\):;\{\}\/%.]{0,})id="main"([A-Za-z0-9\s\-="\(\):;\{\}\/%.]{0,})>([A-Za-z0-9\s\n]{0,})<div class="inside">/', '<main$1id="main"$2>$3<div class="inside section">', $strBuffer);
                }

                $strBuffer = preg_replace('/<html/', '<html class="enable-fullpage"', $strBuffer);
            }

            if($footerMode)
            {
                if( preg_match("/<footer/", $strBuffer) )
                {
//                    $strBuffer = preg_replace('/<body([^>]+)class="/', '<body$1class="footerpage ', $strBuffer);
                    $arrBodyClasses[] = 'footerpage';

                    $divsOpen   = '<div id="page"><div id="outer">';
                    $divsClose  = '</div></div></div>';

                    $strBuffer  = str_replace('<div id="wrapper">', $divsOpen . '<div id="wrapper">', $strBuffer);
                    $strBuffer  = str_replace('<footer', $divsClose . '<footer', $strBuffer);

                    $strBuffer  = preg_replace('/<\/footer>(\s){0,}<\/div>/im', '</footer>', $strBuffer);
                }
            }
            else
            {
                if($objLayout->addPageWrapperOuter)
                {
                    $strBuffer = str_replace('<div id="wrapper">', '<div id="outer"><div id="wrapper">', $strBuffer);

                    if( preg_match('/<footer/', $strBuffer) )
                    {
                        $strBuffer = str_replace('</footer>',  '</footer></div>', $strBuffer);
                    }
                    else
                    {
                        $strBuffer = str_replace('</body>', '</div>' . "\n" . '</body>', $strBuffer);
                    }
                }

                if($objLayout->addPageWrapperPage)
                {
                    $replaceID = "wrapper";

                    if($objLayout->addPageWrapperOuter)
                    {
                        $replaceID = "outer";
                    }

                    $strBuffer = str_replace('<div id="' . $replaceID . '">', '<div id="page"><div id="' . $replaceID . '">', $strBuffer);

                    if( preg_match('/<footer/', $strBuffer) )
                    {
                        $strBuffer = str_replace('</footer>',  '</footer></div>', $strBuffer);
                    }
                    else
                    {
                        $strBuffer = str_replace('</body>', '</div>' . "\n" . '</body>', $strBuffer);
                    }
                }
            }

            if( $objRootPage->enablePageFadeEffect )
            {
                $outerID    = 'container';
                $strBuffer  = str_replace('<div id="' . $outerID . '">', '<div id="barba-wrapper"><div class="barba-container"><div id="' . $outerID . '">', $strBuffer);

                if( preg_match('/<footer/', $strBuffer) )
                {
                    $strBuffer = str_replace('<footer',  '</div></div><footer', $strBuffer);
                }
                else
                {
                    $strBuffer = str_replace('</body>', '</div></div>' . "\n" . '</body>', $strBuffer);
                }
            }

            if( $objPage->removeFooter )
            {
                $strBuffer = preg_replace('/<footer([A-Za-z0-9öäüÖÄÜß\s="\-:\/\\.,;:_>\n<\{\}]{0,})<\/footer>/', '', $strBuffer);
            }
            else
            {
                $objFooterArticle   = \ArticleModel::findByAlias("ge_footer_" . $objRootPage->alias);
                $footerClass        = \StringUtil::deserialize($objFooterArticle->cssID, true)[1];
                $arrFooterAttribute = array();

                if( $objFooterArticle->isFixed )
                {
                    if( !$objFooterArticle->isAbsolute )
                    {
                        $footerClass = trim($footerClass . ' is-fixed');
                    }
                    else
                    {
                        $footerClass = trim($footerClass . ' pos-abs');
                    }

                    if( $objFooterArticle->position === "top" )
                    {
                        $footerClass = trim($footerClass . ' pos-top');
                    }
                    elseif( $objFooterArticle->position === "right" )
                    {
                        $footerClass = trim($footerClass . ' pos-right');
                    }
                    elseif( $objFooterArticle->position === "bottom" )
                    {
                        $footerClass = trim($footerClass . ' pos-bottom');
                    }
                    elseif( $objFooterArticle->position === "left" )
                    {
                        $footerClass = trim($footerClass . ' pos-left');
                    }

                    $arrFooterWidth = \StringUtil::deserialize($objFooterArticle->articleWidth, TRUE);

                    if( $arrFooterWidth['value'] )
                    {
                        $arrFooterAttribute['style'] = trim($arrFooterAttribute['style'] . ' width:' . $arrFooterWidth['value'] . ($arrFooterWidth['unit'] . ';' ? : 'px;'));
                    }

                    $arrFooterHeight = \StringUtil::deserialize($objFooterArticle->articleHeight, TRUE);

                    if( $arrFooterHeight['value'] )
                    {
                        $arrFooterAttribute['style'] = trim($arrFooterAttribute['style'] . ' height:' . $arrFooterHeight['value'] . ($arrFooterHeight['unit'] . ';' ? : 'px;'));
                    }
                }

                if( strlen($footerClass) )
                {
                    $strAttributes = '';

                    if( count($arrFooterAttribute) )
                    {
                        foreach($arrFooterAttribute as $key => $value)
                        {
                            $strAttributes .= ' ' . $key . '="' . $value . '"';
                        }
                    }

                    $strBuffer = preg_replace('/<footer/', '<footer class="' . $footerClass . '"' . $strAttributes, $strBuffer);
                }
            }

            if( $objPage->removeHeader )
            {
                $strBuffer = preg_replace('/<header([A-Za-z0-9öäüÖÄÜß\s="\-:\/\\.,;:_>\n<\{\}]{0,})<\/header>/', '', $strBuffer);
            }
            else
            {
                $strBuffer = HeaderHelper::renderHeader( $strBuffer );
            }

            if( $objPage->removeLeft )
            { //TODO: check DIV tags!
                $strBuffer = preg_replace('/<aside id="left"([A-Za-z0-9öäüÖÄÜß\s\-,:.;_\/\\="\n\>\<\{\}]{0,})<\/aside>/', '', $strBuffer);
            }

            if( preg_match('/<footer/', $strBuffer) && PageHelper::hasBodyClass("homepage", $strBuffer) )
            {
                if( preg_match('/<footer([A-Za-z0-9\s\-=",;.:_\(\)\{\}]{0,})class/', $strBuffer) )
                {
                    $strBuffer = preg_replace('/<footer([A-Za-z0-9\s\-=",;.:_\(\)\{\}]{0,})class="/', '<footer$1class="home ', $strBuffer);
                }
                else
                {
                    $strBuffer = preg_replace('/<footer/', '<footer class="home"', $strBuffer);
                }
            }

            if( preg_match('/nav-sub/', $strBuffer) && !preg_match('/(ce_backlink|mod_newsearder)/', $strBuffer ))
            {
                $strBuffer = preg_replace('/nav-sub/', 'nav-sub has-bg-left', $strBuffer);
                $strBuffer = preg_replace('/<nav([A-Za-z0-9\s\-=\",;.:_\{\}\/\(\)]{0,})class="mod_navigation([A-Za-z0-9\s\-\'\",;.:_\{\}\/\(\)]{0,})nav-sub([A-Za-z0-9\s\-\'\",;.:_\{\}\/\(\)]{0,})"([A-Za-z0-9\s\-=\",;.:_\{\}\/\(\)]{0,})>/', '<nav$1class="mod_navigation$2nav-sub$3"$4><div class="bg-subnav"></div>', $strBuffer);
            }

            if( preg_match('/homepage/', $objPage->cssClass) )
            {
                $strBuffer = preg_replace('/<div class="page-title-container">([A-Za-z0-9öäüÖÄÜß&!?\-\n\s_.,;:<>="\{\}\(\)\/]{0,})<\/div>([\s]{0,})<div([A-Za-z0-9\-\s="]{0,})class="mod_article/', '<div$3class="mod_article', $strBuffer);
                $strBuffer = preg_replace('/<div class="custom">([\s]{0,})<div id="main_menu_container">([A-Za-z0-9öäüÖÄÜß&!?@#\-\n\s_.,;:<>="\{\}\(\)\/]{0,})<footer/', '</div></div><footer', $strBuffer);
            }

            $arrContainerClasses    = array();
            $arrMainClasses         = array();

            if( preg_match('/id="left"/', $strBuffer) )
            {
                $arrBodyClasses[] = 'has-left-col';

                $arrContainerClasses[] = 'has-left-col';
            }

            if( preg_match('/id="right"/', $strBuffer) )
            {
                $arrBodyClasses[] = 'has-right-col';
            }

            if( !preg_match('/id="bg_image"/', $strBuffer) )
            {
                $arrContainerClasses[]  = 'has-no-bgimage-col';
                $arrMainClasses[]       = 'has-no-bgimage-col';
            }
            else
            {
                $arrContainerClasses[]  = 'has-bgimage-col';
                $arrMainClasses[]       = 'has-bgimage-col';
            }

            if( count($arrBodyClasses) )
            {
                $strBuffer = PageHelper::replaceBodyClasses($strBuffer, $arrBodyClasses);
            }

            if( PageHelper::hasBodyClass("homepage", $strBuffer) )
            {
                $arrContainerClasses[]  = 'home-container';
                $arrMainClasses[]       = 'home-main';
            }

            if( PageHelper::hasBodyClass("projectpage", $strBuffer) )
            {
                $arrContainerClasses[]  = 'container-projectpage';
                $arrMainClasses[]       = 'main-projectpage';
            }

            if( PageHelper::hasBodyClass("teampage", $strBuffer) )
            {
                $arrContainerClasses[]  = 'container-teampage';
                $arrMainClasses[]       = 'main-teampage';
            }

            if( PageHelper::hasBodyClass("projectdetailpage", $strBuffer) )
            {
                $arrContainerClasses[]  = 'container-projectdetailpage';
                $arrMainClasses[]       = 'main-projectdetailpage';
            }

            if( count($arrContainerClasses) )
            {
                $strBuffer = preg_replace('/id="container"/', 'id="container" class="' . implode(' ', $arrContainerClasses) . '"', $strBuffer);
            }

            if( count($arrMainClasses) )
            {
                $strBuffer = preg_replace('/id="main"/', 'id="main" class="' . implode(' ', $arrMainClasses) . '"', $strBuffer);
            }

            if( $objPage->addPageLoader )
            {
                preg_match_all('/<html([A-Za-z0-9\s\-_:.;,="%]{0,})>/', $strBuffer, $arrHtmlMatches);

                if( count($arrHtmlMatches[0]) )
                {
                    if( preg_match('/class="/', $arrHtmlMatches[1][0]) )
                    {
                        $strBuffer = preg_replace('/<html([A-Za-z0-9\s\-_:.;,="%]{0,})class="/', '<html$1class="enable-pageloader ', $strBuffer);
                    }
                    else
                    {
                        $strBuffer = preg_replace('/<html/', '<html class="enable-pageloader"', $strBuffer);
                    }
                }
            }

            // Remove empty canvasTop && pitLane
//            $objCanvasTop = \ArticleModel::findByAlias("ge_canvas-top_" . $objRootPage->alias);

//            if( !$objCanvasTop )
//            {
//                $strBuffer = preg_replace('/<div class="custom">([\s\n]{0,})<div id="canvasTop">([\s\n]{0,})<div class="inside">([A-Za-z0-9öäüÖÄÜß\s="\-:\/\\.,;:_\n\{\}<>]{0,})<\/div>([\s\n]{0,})<\/div>([\s\n]{0,})<\/div>/', '', $strBuffer);
//            }

//            $objPitLane = \ArticleModel::findByAlias("ge_pitlane_" . $objRootPage->alias);

//            if( !$objPitLane )
//            {
//                $strBuffer = preg_replace('/<div class="custom">([\s\n]{0,})<div id="pitLane">([\s\n]{0,})<div class="inside">([A-Za-z0-9öäüÖÄÜß\s="\-:\/\\.,;:_\n\{\}<>]{0,})<\/div>([\s\n]{0,})<\/div>([\s\n]{0,})<\/div>/', '', $strBuffer);
//            }

            if( preg_match('/nav-cont-left-outside/', $strBuffer) )
            {
                $strBuffer = preg_replace('/<\/body>/', '<div class="open-left-side-navigation"><div class="olsn-inside"></div></div></body>', $strBuffer);
            }

        }

        return $strBuffer;
    }

}
