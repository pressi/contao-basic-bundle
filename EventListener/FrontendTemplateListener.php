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
        global $objPage;

        $isFullpage = $this->isFullPageEnabled( $objPage );

        if( $strTemplate == "mod_article" )
        {
//            $sectionHeaderID		= 3; // TODO: verwaltbar machen!!!
//            $sectionFooterID		= 4;

            $objArticle             = NULL;
            $articleClass           = array();
            $articlePattern         = '/<div([A-Za-z0-9\s\-_=;",:\\.\(\)\'#\/%]{0,})class="mod_article([A-Za-z0-9\s\-_]{0,})"([A-Za-z0-9\s\-_=;",:\\.\(\)\'#\/%]{0,})>/';

            preg_match_all('/id="([A-Za-z0-9\-_]{0,})"/', $strContent, $idMatches);
            preg_match_all('/class="mod_article([A-Za-z0-9\s\-\{\}\/\',;.:\\\(\)_]{0,})"([A-Za-z0-9\s\-,;.:="\'_]{0,})id="([A-Za-z0-9\-_]{0,})"/', $strContent, $arrMatches);

            if( is_array($idMatches) && count($idMatches[0]) > 0 )
            {
                $objArticle = \ArticleModel::findByIdOrAlias( $idMatches[1][0] );

                if( !$objArticle )
                {
                    $objArticle = \ArticleModel::findByIdOrAlias( preg_replace('/^article_/', '', $idMatches[1][0]) );
                }
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
                            $navNext = '<div class="navigation-top" onClick="DPS.Fullpage.sectionBack(this);"><span>zur vorigen Seite</span></div>';
                            $navPrev = '<div class="navigation-bottom" onClick="DPS.Fullpage.sectionForward(this);"><span>zur nächsten Seite</span></div>';
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

                        $strContent = preg_replace('/<\/div>$/', $strVideoTag . '</div>', $strContent);
                    }

                    if( $objArticle->addBackgroundImage )
                    {
                        $arrArticleClasses[] = 'has-background-image';

                        if( !preg_match('/full-width(\s|")/', $strContent) )
                        {
                            $arrArticleClasses[] = 'full-width';
                        }

                        if( $objArticle->backgroundMode == "cover" )
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

                        $objImage = \FilesModel::findByPk( $objArticle->backgroundSRC );

                        if( $objImage )
                        {
                            if( file_exists(TL_ROOT . '/' . $objImage->path) )
                            {
                                $arrSize = getimagesize($objImage->path);

                                $arrAttributes[] = 'data-img-width="' . $arrSize[0] . '"';
                                $arrAttributes[] = 'data-img-height="' . $arrSize[1] . '"';
                            }
                        }

                        if( $objArticle->enableParallax )
                        {
//                            if( $objArticle->backgroundPosition == "center_top" && $objArticle->backgroundMode == "cover" )
//                            {
//                                if( in_array("first", $objArticle->classes) )
//                                {
//                                    $arrAttributes[] = 'data-stellar-vertical-offset="-125"';
//                                }
//                            }

                            $articlePattern = '/<div([A-Za-z0-9\s\-_=;",:\\.\(\)\'#\/%]{0,})class="mod_article([A-Za-z0-9\s\-_]{0,})"([A-Za-z0-9\s\-_=;",:\\.\(\)\'#\/%]{0,})>/';
                            $strContent     = preg_replace($articlePattern, '<div$1class="mod_article parallax-bg$2"$3 data-stellar-offset-parent="true" data-stellar-background-ratio="0.2">', $strContent);
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
                        if( $objArticle->id == $objArticles->id )
                        {
                            break;
                        }

                        $index++;
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
            }

            if( is_array($articleClass) && count($articleClass) > 0 )
            {
                $strContent = preg_replace('/class="mod_article/', 'class="mod_article ' . implode(" ", $articleClass), $strContent);
            }


            $arrArticleClasses = array_unique($arrArticleClasses);

            $strContent = preg_replace('/class="mod_article/',  ((count($arrAttributes) > 0) ? ' ' . implode(' ', $arrAttributes) : '') . 'class="mod_article' . ((count($arrArticleClasses) > 0) ? ' ' : '') . implode(' ', $arrArticleClasses), $strContent);

            $addAroundDivStart  = "";
            $addAroundDivEnd    = "";
            $articleClasses     = deserialize($objArticle->cssID, true);

//            if( preg_match('/add-bg/', $articleClasses[1]) )
//            {
//                $addAroundDivStart  = '<div class="background-outer bg-image bg-cover bg-scroll" data-0="background-position:0px 0px;"><div class="background-inner bg-image bg-cover bg-scroll" data-0="background-position:0px 0px;">';
//                $addAroundDivEnd    = '</div></div>';
//            }

            $strContent = preg_replace('/<div([A-Za-z0-9\s\-_="\'.,;:\(\)\/#]{0,})class="mod_article([A-Za-z0-9\s\-_\{\}\(\)\']{0,})"([A-Za-z0-9\s\-_="\'.,;:\(\)\/#%]{0,})>/', '<div$1class="mod_article$2"$3>' . $addAroundDivStart . '<div class="article-inside">', $strContent);
//            $strContent = preg_replace('/<\/div>$/', '</div></div>' . $addAroundDivEnd, $strContent);
            $strContent = $strContent . '</div>' . $addAroundDivEnd;
        }


        elseif( $strTemplate == "mod_navigation" )
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
                                            $objLinkPage = \PageModel::findByPk( $this->getPageIdFromUrl() );
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

        $objLayout          = Helper::getPageLayout( $objPage) ;
        $isFullpage         = $this->isFullPageEnabled( $objPage, $objLayout );

        if( $strTemplate == 'fe_page' )
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

            if( $objPage->removeFooter )
            {
                $strBuffer = preg_replace('/<footer([A-Za-z0-9\s="\-:\/\\.,;:_>\n<\{\}]{0,})<\/footer>/', '', $strBuffer);
            }

            if( $objPage->removeHeader )
            {
                $strBuffer = preg_replace('/<header([A-Za-z0-9\s="\-:\/\\.,;:_>\n<\{\}]{0,})<\/header>/', '', $strBuffer);
            }
        }

        return $strBuffer;
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

        $pageTitle = $objPage->rootPageTitle?:$objPage->rootTitle;

        preg_match_all('/<title>(.*)' . preg_quote($pageTitle, "/") . '<\/title>/', $strContent, $matches);

        if( count($matches[1]) > 0)
        {
            $newTitle 	= trim( str_replace("-", "", $matches[1][0]) );
            $strContent = preg_replace('/<title>' . $matches[1][0] . $pageTitle .'<\/title>/', '<title>' . $newTitle . ' :: ' . $pageTitle . '</title>', $strContent);
        }

        return $strContent;
    }

}