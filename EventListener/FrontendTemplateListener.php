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
 * Class Frontend Template Hook
 * @package IIDO\Customize\Hook
 */
class FrontendTemplateListener
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
     */
    public function parseCustomizeFrontendTemplate($strContent, $strTemplate)
    {
        /* @var \PageModel $objPage */
        global $objPage;

        $objParentPage  = \PageModel::findByPk( $objPage->pid );

        $isFullpage = $this->isFullPageEnabled( $objPage );

        if( $strTemplate === "mod_article" )
        {
//            $sectionHeaderID		= 3; // TODO: verwaltbar machen!!!
//            $sectionFooterID		= 4;

            $objArticle             = NULL;
            $articleClass           = array();
            $articlePattern         = '/<div([A-Za-z0-9\s\-_=;",:\\.\(\)\'#\/%]{0,})class="mod_article([A-Za-z0-9\s\-_]{0,})"([A-Za-z0-9\s\-_=;",:\\.\(\)\'#\/%]{0,})>/';

            preg_match_all('/id="([A-Za-z0-9\-_]{0,})"/', $strContent, $idMatches);
            preg_match_all('/class="mod_article([A-Za-z0-9\s\-\{\}\/\',;.:\\\(\)_]{0,})"([A-Za-z0-9\s\-,;.:="\'_]{0,})id="([A-Za-z0-9\-_]{0,})"/', $strContent, $arrMatches);

//            $articleID = "";
            if( is_array($idMatches) && count($idMatches[0]) > 0 )
            {
//                $idOrAlias = $articleID = $idMatches[1][0];
                $idOrAlias = $idMatches[1][0];

                if( preg_match('/^article-/', $idOrAlias) )
                {
                    $idOrAlias = preg_replace('/^article-/', '', $idOrAlias);
                }

                $objArticle = \ArticleModel::findByIdOrAlias( $idOrAlias );
            }

//            if( strlen($articleID) )
//            {
//                $strContent = preg_replace('/id="'. $articleID . '"/', 'id="' . $objArticle->alias . '"', $strContent);
//            }


            if( $objArticle && $objArticle->noContent )
            {
                return '';
            }

            if( $isFullpage )
            {
                if( $objArticle )
                {
//                    if( $objArticle->enableSectionHeader )
//                    {
//                        $sectionHeaderID	= $objArticle->sectionHeaderModule;
//
//                        $strContent = preg_replace($articlePattern, '<div$1class="mod_article$2"$3><div class="header section-header">{{insert_module::' . $sectionHeaderID . '}}</div>', $strContent);
//                    }
//
//                    if( $objArticle->enableSectionFooter )
//                    {
//                        $sectionFooterID	= $objArticle->sectionFooterModule;
//
//                        $strContent = preg_replace('/<\/div>$/', '<div class="footer section-footer">{{insert_module::' . $sectionFooterID . '}}</div></div>', $strContent);
//                    }

                    if( $objArticle->enableOverlay )
                    {
                        $strOverlayContent	= '';

                        if( $objArticle->showTitleInOverlay )
                        {
                            $strArticleTitle = $objArticle->title;

                            if( strlen($objArticle->alt_title) )
                            {
                                $strArticleTitle = $objArticle->alt_title;
                            }

                            $strOverlayContent = '<div class="overlay-title"><div class="title-inner">' . $strArticleTitle . '</div></div>';
                        }

                        $strClass = "";

                        if( $objArticle->overlayTransparent )
                        {
                            $strClass = " overlay-trans";
                        }

                        $strOverlayTags     = '<div class="show-overlay"></div><div class="overlay article-overlay' . $strClass . ' shown"><div class="close"></div>' . $strOverlayContent . '</div>';

                        $strContent         = preg_replace($articlePattern, '<div$1class="mod_article has-overlay$2"$3>', $strContent);
                        $strContent         = preg_replace('/<\/div>$/', $strOverlayTags . '</div>', $strContent);
                    }

                    if( $objArticle->enableFullpageNavigation )
                    {
                        $navNext = "";
                        $navPrev = "";

                        if( $objPage->fullpageDirection == "horizontal" )
                        {
                            // TODO: NAVI RIGHT UND LEFT!!
                            // TODO: buttons or pagination!!

//							$navNext		= '<div class="navigation-top" onClick="DPS.Fullpage.sectionBack(this);"></div>';
//							$navPrev		= '<div class="navigation-bottom" onClick="DPS.Fullpage.sectionForward(this);"></div>';
                        }
                        else
                        {
                            $navNext = '<div class="navigation-top" onClick="IIDO.FullPage.sectionBack(this);"><span>zur vorigen Seite</span></div>';
                            $navPrev = '<div class="navigation-bottom" onClick="IIDO.FullPage.sectionForward(this);"><span>zur nächsten Seite</span></div>';
                        }

                        $navigationTags = '<div class="fullpage-navigation">' . $navNext . $navPrev .'</div>';
//						$strContent     = preg_replace($articlePattern, '', $strContent);

                        $strContent     = preg_replace('/<\/div>$/', $navigationTags . '</div>', $strContent);
                    }
                }

                if( $objPage->fullpageDirection == "horizontal" )
                {
                    $articleClass[] = 'slide';
                }
                else
                {
                    $articleClass[] = 'section';
                }

//				$strContent = preg_replace('/<div([A-Za-z0-9\s\-_=;",:\\.\(\)\'#\/%]{0,})class="mod_article([A-Za-z0-9\s\-_]{0,})"([A-Za-z0-9\s\-_=;",:\\.\(\)\'#\/%]{0,})>/', '<div$1class="mod_article$2"$3><div class="article-inner">', $strContent);
//				$strContent = $strContent . '</div>';
            }

            $arrArticleClasses  = array();
            $arrAttributes      = array();

            $cssID          = deserialize($objArticle->cssID, true);
            $objArticles    = \ArticleModel::findPublishedByPidAndColumn($objPage->id, "main");

            if( is_array($arrMatches) && is_array($arrMatches[0]) && count($arrMatches[0]) > 0 )
            {
                $strClasses     = $arrMatches[1][0];
                $strID          = $cssID[0]?:'article_' . $objArticle->id;
                $articleNum     = 0;

                if( $objArticles )
                {
                    while( $objArticles->next() )
                    {
                        if( $objArticles->id == $objArticle->id )
                        {
                            break;
                        }

                        $articleNum++;
                    }
                }

                if( $objArticle )
                {
                    if( $objArticle->addBackgroundVideo )
                    {
                        $arrArticleClasses[] = 'has-background-video';

                        $strVideoTag = Helper::renderVideoTag( $objArticle->videoSRC, $objArticle->posterSRC );

                        $strContent = preg_replace('/<\/div>$/', '</div>' . $strVideoTag, trim($strContent));
                    }

                    if( $objArticle->bgImage )
                    {
                        $arrArticleClasses[] = 'has-background-image';

                        if( !preg_match('/full-width(\s|")/', $strContent) )
                        {
                            $arrArticleClasses[] = 'full-width';
                        }

                        $bgSize = deserialize($objArticle->bgSize, TRUE);

                        if( $bgSize[2] == "cover" )
                        {
//                            if( $objArticle->enableParallax )
//                            {
//                                $arrArticleClasses[] = 'bg-size-125';
//                            }
//                            else
//                            {
                                $arrArticleClasses[] = 'bg-cover';
//                            }
                        }

                        $objImage = \FilesModel::findByPk( $objArticle->bgImage );

                        if( $objImage )
                        {
                            if( file_exists(TL_ROOT . '/' . $objImage->path) )
                            {
                                $arrSize = getimagesize($objImage->path);

                                $arrAttributes[] = 'data-img-width="' . $arrSize[0] . '"';
                                $arrAttributes[] = 'data-img-height="' . $arrSize[1] . '"';
                            }
                        }

//                        if( $objArticle->enableParallax )
//                        {
////                            if( $objArticle->backgroundPosition == "center_top" && $objArticle->backgroundMode == "cover" )
////                            {
////                                if( in_array("first", $objArticle->classes) )
////                                {
////                                    $arrAttributes[] = 'data-stellar-vertical-offset="-125"';
////                                }
////                            }
//
//                            $articlePattern = '/<div([A-Za-z0-9\s\-_=;",:\\.\(\)\'#\/%]{0,})class="mod_article([A-Za-z0-9\s\-_]{0,})"([A-Za-z0-9\s\-_=;",:\\.\(\)\'#\/%]{0,})>/';
//                            $strContent     = preg_replace($articlePattern, '<div$1class="mod_article parallax-bg$2"$3 data-stellar-offset-parent="true" data-stellar-background-ratio="0.2">', $strContent);
//                        }

                        if( preg_match('/bg-in-container/', $cssID[1]) )
                        {
                            $strBGContainer = '<div class="background-container"></div>';

                            $strContent = preg_replace('/<\/div>$/', '</div>' . $strBGContainer, trim($strContent));
                        }
                    }
                }
            }

            if( $objArticle )
            {
                $objArticles = \ArticleModel::findPublishedByPidAndColumn($objPage->id, "main");

                if( $objArticles )
                {
                    $index = 0;

                    while($objArticles->next())
                    {
                        if( $objArticle->id === $objArticles->id )
                        {
                            break;
                        }

                        if( $objArticles->noContent )
                        {
                            continue;
                        }

                        $index++;
                    }

                    if( $objArticle->fullWidth )
                    {
                        $arrArticleClasses[] = 'full-width';

                        if( $objArticle->fullWidthInside )
                        {
                            $arrArticleClasses[] = 'full-width-inside';
                        }
                    }

                    if( $objArticle->fullHeight )
                    {
                        $arrArticleClasses[] = 'full-height';

                        if( $objArticle->opticalHeight )
                        {
                            $arrArticleClasses[] = 'full-height-optical';
                        }
                    }

                    if( $objArticle->textMiddle )
                    {
                        $arrArticleClasses[] = 'text-valign-middle';

                        if( $objArticle->textMiddleOptical )
                        {
                            $arrArticleClasses[] = 'text-valign-middle-optical';
                        }
                    }

                    if( $objArticle->hiddenArea )
                    {
                        $arrArticleClasses[] = 'hidden-area';

//                        foreach($articleClass as $classIndex => $className)
//                        {
//                            if( $className === "section" )
//                            {
//                                unset($articleClass[ $classIndex ]);
//                                $articleClass = array_values($articleClass);
//                                break;
//                            }
//                        }
                    }

                    $bgColor        = ColorHelper::getBackgroundColor( $objArticle );

                    if( $bgColor )
                    {
                        if( $bgColor->lightness < 140 )
                        {
                            if( $bgColor->lightness < 90)
                            {
                                $arrArticleClasses[] = 'lighter-color';
                            }
                            else
                            {
                                $arrArticleClasses[] = 'light-color';
                            }
                        }
                    }

                    if( $index == 1 )
                    {
                        $arrArticleClasses[] = 'secondArticle';
                    }

                    $arrArticleClasses[] = 'article-' . ($index + 1);

                    if( $objArticles->count() > 1 )
                    {
                        $strContent = preg_replace($articlePattern, '<div$1class="mod_article$2"$3 data-index="' . $index . '">', $strContent);
                    }
                }

//				if( preg_match('/scroll-inner/', $cssID[1]) )
//				{
//					$articleWrapPattern = str_replace(')>/', ')>(\s\n)<div class="wrap">/', $articlePattern);
//
//					$strContent = preg_replace($articleWrapPattern, '<div$1class="mod_article$2"$3><div class="wrap"><div class="scroll-container">', $strContent);
//					$strContent = preg_replace('/<\/div>$/',  '</div></div>', $strContent);
//				}

                $inMenu = false;

                if( $objArticle->inColumn == "main" )
                {
                    if( $objPage->submenuNoPages && $objPage->submenuSRC == "articles" )
                    {
                        $dataAnker = ($cssID[1]?:'article-' . $objArticle->id);

                        $arrAttributes[] = 'data-alias="' . $objArticle->alias . '"';
                        $arrAttributes[] = 'data-anker="' . $dataAnker . '"';

                        if( $objArticle->published && !$objArticle->hideInMenu )
                        {
                            $inMenu = true;
                        }
                    }
                }

                $arrAttributes[] = 'data-menu="' . $inMenu . '"';
                $arrAttributes[] = 'data-anchor="' . $objArticle->alias . '"';

                if( $objArticle->inColumn === "bg_image" )
                {
                    $strContent = preg_replace('/<\/div>$/', '<div class="hover-article-image"><div class="hover-inside"></div><div class="hover-close"></div></div></div>', trim($strContent));
                }

                if( $objArticle->hideInMenu )
                {
                    $articleClass[] = 'hide-in-menu';
                }
            }

            if( is_array($articleClass) && count($articleClass) > 0 )
            {
                $strContent = preg_replace('/class="mod_article/', 'class="mod_article ' . implode(" ", $articleClass), $strContent);
            }

            $arrArticleClasses = array_unique($arrArticleClasses);

            if( count($arrArticleClasses) )
            {
                $strContent = preg_replace('/class="mod_article/',  ((count($arrAttributes) > 0) ? ' ' . implode(' ', $arrAttributes) : '') . 'class="mod_article' . ((count($arrArticleClasses) > 0) ? ' ' : '') . implode(' ', $arrArticleClasses), $strContent);
            }

//            $addAroundDivStart  = "";
//            $addAroundDivEnd    = "";
//            $articleClasses     = deserialize($objArticle->cssID, true);

//            if( preg_match('/add-bg/', $articleClasses[1]) )
//            {
//                $addAroundDivStart  = '<div class="background-outer bg-image bg-cover bg-scroll" data-0="background-position:0px 0px;"><div class="background-inner bg-image bg-cover bg-scroll" data-0="background-position:0px 0px;">';
//                $addAroundDivEnd    = '</div></div>';
//            }

//            $strContent = preg_replace('/<div([A-Za-z0-9\s\-_="\'.,;:\(\)\/#]{0,})class="mod_article([A-Za-z0-9\s\-_\{\}\(\)\']{0,})"([A-Za-z0-9\s\-_="\'.,;:\(\)\/#%]{0,})>/', '<div$1class="mod_article$2"$3>' . $addAroundDivStart . '<div class="article-inside">', $strContent);
//            $strContent = preg_replace('/<\/div>$/', '</div></div>' . $addAroundDivEnd, $strContent);
//            $strContent = $strContent . $addAroundDivEnd . '</div>';

            if( $objArticle->inColumn === "main" )
            {
                $divTableStart  = "";
                $divTableEnd    = "";
                $divOverlay     = "";

//                if( $objArticle->textMiddle && $isFullpage )
                if( $objArticle->textMiddle )
                {
                    $divTableStart = '<div class="article-table">';
                    $divTableEnd   = '</div>';
                }

                if( $objArticle->addBackgroundOverlay )
                {
                    $divOverlay = '<div class="bg-container-overlay"></div>';
                }

                $strContent = preg_replace('/<div([A-Za-z0-9öäüÖÄÜß\s\-_="\'.,;:\(\)\/#]{0,})class="mod_article([A-Za-z0-9öäüÖÄÜß\s\-_\{\}\(\)\']{0,})"([A-Za-z0-9öäüÖÄÜß\s\-_="\'.,;:\(\)\/#%]{0,})>/', '<div$1class="mod_article$2"$3>' . $divOverlay . '<div class="article-inside">' . $divTableStart, $strContent, -1, $count);

                if( $count > 0 )
                {
                    if( $objArticle->toNextArrow )
                    {
                        $divTableEnd = '<div class="pos-abs pos-center-bottom arrow arrow-down arrow-style3 scroll-to-next-page"><div class="arrow-inside-container"><div class="arrow-inside"></div></div></div>' . $divTableEnd;
                    }

                    if( preg_match('/add-footer/', $cssID[1]) )
                    {
                        $lang       = \System::getContainer()->get('request_stack')->getCurrentRequest()->getLocale();
                        $strFooter  = \Frontend::replaceInsertTags( '{{insert_article::ge_footer_' . $objPage->rootAlias . '_' . $lang . '}}' );

                        if( strlen($strFooter) )
                        {
                            $strFooter = '<div class="article-footer"><div class="inside">' . $strFooter . '</div></div>';
                        }

                        $divTableEnd = $strFooter . $divTableEnd;
                    }

                    $strContent = $strContent . $divTableEnd . '</div>';
                }

//                $strContent = preg_replace('/<div([A-Za-z0-9\s\-_="\'.,;:\(\)\/#]{0,})class="mod_article([A-Za-z0-9\s\-_\{\}\(\)\']{0,})"([A-Za-z0-9\s\-_="\'.,;:\(\)\/#%]{0,})>/', '<div$1class="mod_article$2"$3><div class="article-inside">', $strContent);
//                $strContent = $strContent . '</div>';

                if( $objParentPage->subPagesHasBacklink && !$objPage->thisPageHasNoBacklink && preg_match('/last/', $arrMatches[1][0]) )
                {
                    $linkText       = 'Zur Übersicht';
                    $link           = '<a href="{{link_url::' . $objParentPage->id . '}}">' . $linkText . '</a>';
                    $backLinkDivs   = '<div class="ce_backlink block"><div class="inner">' . $link . '</div></div>';

//                    $strContent = preg_replace('/<\/div>$/', $backLinkDivs, $strContent);
//                    $strContent .= $backLinkDivs;
                    $strContent = $strContent . $backLinkDivs;
                }

                if( $objParentPage->subPagesHasRequestLink && !$objPage->thisPageHasNoReuqestLink && preg_match('/last/', $arrMatches[1][0]) )
                {
                    $linkText       = 'Anfragen';
                    $linkUrl        = '{{link_url::' . $objParentPage->requestLinkPage . '}}';

                    $productName    = $objParentPage->title . ' - ' . $objPage->title;

                    $form           = '<div class="ce_requestForm"><form action="' . $linkUrl . '" method="post" id="request-form">
    <input type="hidden" name="product_name" value="' . $productName . '">
    <input type="hidden" name="REQUEST_TOKEN" value="' . REQUEST_TOKEN . '">
    <input type="submit" value="' . $linkText . '">
</form></div>';

//                    $strContent = preg_replace('/<\/div>$/', $form, $strContent);
                    $strContent = $strContent . $form;
                }
            }
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
     *
     *
     * @param string $strBuffer
     * @param string $strTemplate
     */
    public function outputCustomizeFrontendTemplate($strBuffer, $strTemplate)
    {
        /** @var \PageModel $objPage */
        global $objPage;

        $arrBodyClasses     = array();
        $objRootPage        = \PageModel::findByPk( $objPage->rootId );
        $objLayout          = Helper::getPageLayout( $objPage) ;
        $isFullpage         = $this->isFullPageEnabled( $objPage, $objLayout );
        $strLanguage        = \System::getContainer()->get('request_stack')->getCurrentRequest()->getLocale();
//        $outerID            = 'wrapper';

        if( $strTemplate === 'fe_page' )
        {
            $strBuffer          = $this->replaceBodyClasses( $strBuffer );
            $strBuffer          = $this->replaceWebsiteTitle( $strBuffer );

            $layoutHasFooter    = $objLayout->rows;
            $footerMode         = (($objLayout->footerAtBottom && ($layoutHasFooter == "2rwf" || $layoutHasFooter == "3rw")) ? true : false);

            if( $isFullpage )
            {
                if( $objPage->fullpageDirection == "horizontal" )
                {
                    $strBuffer = preg_replace('/<div id="main">([A-Za-z0-9\s\n]{0,})<div class="inside">/', '<div id="main">$1<div class="inside section">', $strBuffer);
                }

                $strBuffer = preg_replace('/<html/', '<html class="enable-fullpage"', $strBuffer);
            }

            if($footerMode)
            {
                if( preg_match("/<footer/", $strBuffer) )
                {
                    $strBuffer = preg_replace('/<body([^>]+)class="/', '<body$1class="footerpage ', $strBuffer);

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
                    $outerID    = 'outer';
                    $strBuffer  = str_replace('<div id="wrapper">', '<div id="outer"><div id="wrapper">', $strBuffer);

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
                    $outerID    = 'page';
                    $replaceID  = "wrapper";

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

            if( $objPage->removeHeader )
            {
                $strBuffer = preg_replace('/<header([A-Za-z0-9öäüÖÄÜß\s="\-:\/\\.,;:_>\n<\{\}]{0,})<\/header>/', '', $strBuffer);
            }
            else
            {
                $objHeaderArticle   = \ArticleModel::findByAlias("ge_header_" . $objRootPage->alias . '_' . $strLanguage);
                $headerClass        = \StringUtil::deserialize($objHeaderArticle->cssID, true)[1];

                if( $objHeaderArticle->isFixed )
                {
                    $headerClass = trim($headerClass . ' is-fixed');

                    if( $objHeaderArticle->position === "top" )
                    {
                        $headerClass = trim($headerClass . ' pos-top');
                    }
                    elseif( $objHeaderArticle->position === "right" )
                    {
                        $headerClass = trim($headerClass . ' pos-right');
                    }
                    elseif( $objHeaderArticle->position === "bottom" )
                    {
                        $headerClass = trim($headerClass . ' pos-bottom');
                    }
                    elseif( $objHeaderArticle->position === "left" )
                    {
                        $headerClass = trim($headerClass . ' pos-left');
                    }
                }

                if( strlen($headerClass) )
                {
                    $strBuffer = preg_replace('/<header/', '<header class="' . $headerClass . '"', $strBuffer);
                }
            }

            if( $objPage->removeLeft )
            { //TODO: check DIV tags!
                $strBuffer = preg_replace('/<aside id="left"([A-Za-z0-9öäüÖÄÜß\s="\-:\/\\.,;:_>\n<\{\}]{0,})<\/aside>/', '', $strBuffer);
            }

            if( preg_match('/<footer/', $strBuffer) && $this->hasBodyClass("homepage", $strBuffer) )
            {
                $strBuffer = preg_replace('/<footer/', '<footer class="home"', $strBuffer);
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
                $strBuffer = $this->replaceBodyClasses($strBuffer, $arrBodyClasses);
            }

            if( $this->hasBodyClass("homepage", $strBuffer) )
            {
                $arrContainerClasses[]  = 'home-container';
                $arrMainClasses[]       = 'home-main';
            }

            if( $this->hasBodyClass("projectpage", $strBuffer) )
            {
                $arrContainerClasses[]  = 'container-projectpage';
                $arrMainClasses[]       = 'main-projectpage';
            }

            if( $this->hasBodyClass("teampage", $strBuffer) )
            {
                $arrContainerClasses[]  = 'container-teampage';
                $arrMainClasses[]       = 'main-teampage';
            }

            if( $this->hasBodyClass("projectdetailpage", $strBuffer) )
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
        }

        return $strBuffer;
    }



    protected function hasBodyClass( $className, $strBuffer )
    {
        preg_match_all('/<body([A-Za-z0-9\s\-_=",;.:]{0,})class="([A-Za-z0-9\s\-_\{\}:]{0,})"/', $strBuffer, $arrMatches);

        if( is_array($arrMatches) && count($arrMatches) && count($arrMatches[0]) )
        {
            if( preg_match('/' . preg_quote($className, '/') . '/', $arrMatches[2][0]) )
            {
                return true;
            }
        }

        return false;
    }




    protected function isFullPageEnabled( \PageModel $objPage = NULL, \LayoutModel $objLayout = NULL )
    {
        $fullpage = false;

        if( $objPage == NULL )
        {
            global $objPage;
        }

        if( $objPage->enableFullpage )
        {
            $fullpage = true;
        }

        if( !$fullpage )
        {
            $objLayout  = (($objLayout != NULL) ? $objLayout : Helper::getPageLayout( $objPage ));
            $strClass   = trim($objPage->cssClass) . " " . trim($objLayout->cssClass);

            if( preg_match("/enable-fullpage/", $strClass) )
            {
                $fullpage = true;
            }
        }

        return $fullpage;
    }



    protected function replaceBodyClasses( $strContent, array $arrBodyClasses = array() )
    {
        global $objPage;

        $arrClasses = array();

        if( $this->isFullPageEnabled() )
        {
            $arrClasses[] = 'enable-fullpage';
        }

        $colorClass = ColorHelper::getPageColorClass( $objPage );

        if( $colorClass )
        {
            $arrClasses[] = $colorClass;
        }

        $arrClasses[] = 'lang-' . \System::getContainer()->get('request_stack')->getCurrentRequest()->getLocale();

        $arrBodyClasses = array_merge($arrBodyClasses, $arrClasses);
        $arrBodyClasses = array_values($arrBodyClasses);
        $arrBodyClasses = array_unique($arrBodyClasses);

        $strContent = preg_replace('/<body([^>]+)class="/', '<body$1class="' . implode(" ", $arrBodyClasses) . ' ', $strContent);

        return $strContent;
    }



    protected function replaceWebsiteTitle( $strContent )
    {
        global $objPage;

        $rootTitle = (($objPage->rootPageTitle) ? $objPage->rootPageTitle : $objPage->rootTitle);

        preg_match_all('/<title>(.*)' . $rootTitle . '<\/title>/', $strContent, $matches);

        if( count($matches[1]) > 0)
        {
            $newTitle   = trim( str_replace("-", "", $matches[1][0]) );
            $strContent = preg_replace('/<title>' . preg_quote($matches[1][0] . $rootTitle, "/") .'<\/title>/', '<title>' . $newTitle . ' :: ' . $rootTitle . '</title>', $strContent);
        }

        $strContent = str_replace(":: ::", "::", $strContent);

        return $strContent;
    }

}
