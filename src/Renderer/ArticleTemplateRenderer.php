<?php
/*******************************************************************
 *
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 *
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *
 *******************************************************************/

namespace IIDO\BasicBundle\Renderer;


use IIDO\BasicBundle\Helper\BasicHelper;
use IIDO\BasicBundle\Helper\ColorHelper;
use IIDO\BasicBundle\Helper\PageHelper;
use IIDO\BasicBundle\Helper\StylesheetHelper;


class ArticleTemplateRenderer
{
    public static function parseTemplate($strContent, $strTemplate)
    {
        global $objPage;

//            $sectionHeaderID      = 3; // TODO: verwaltbar machen!!!
//            $sectionFooterID      = 4;

        $objArticle             = NULL;
        $articleClass           = array();
        $articlePattern         = '/<div([A-Za-z0-9\s\-_=;",:\\.\(\)\'#\/%]{0,})class="mod_article([A-Za-z0-9\s\-_]{0,})"([A-Za-z0-9\s\-_=;",:\\.\(\)\'#\/%]{0,})>/';

        $isFullpage             = PageHelper::isFullPageEnabled( $objPage );
        $objParentPage          = \PageModel::findByPk( $objPage->pid );

        preg_match_all('/id="([A-Za-z0-9\-_]{0,})"/', $strContent, $idMatches);
        preg_match_all('/class="mod_article([A-Za-z0-9\s\-\{\}\/\',;.:\\\(\)_]{0,})"([A-Za-z0-9\s\-,;.:="\'_]{0,})id="([A-Za-z0-9\-_]{0,})"/', $strContent, $arrMatches);

        if( is_array($idMatches) && count($idMatches[0]) > 0 )
        {
            $idOrAlias = $idMatches[1][0];

            if( preg_match('/^article-/', $idOrAlias) )
            {
                $idOrAlias = preg_replace('/^article-/', '', $idOrAlias);
            }

            $objArticle = \ArticleModel::findByIdOrAlias( $idOrAlias );
        }

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

                    $strVideoTag = BasicHelper::renderVideoTag( $objArticle->videoSRC, $objArticle->posterSRC );

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

                    if( $bgSize[2] === "cover" )
                    {
                        $arrArticleClasses[] = 'bg-cover';
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

                    if( $objArticle->enableBackgroundParallax )
                    {
                        $arrArticleClasses[] = 'bg-parallax';
                        $arrArticleClasses[] = 'bg-fixed';

//                            if( $objArticle->backgroundPosition == "center_top" && $objArticle->backgroundMode == "cover" )
//                            {
//                                if( in_array("first", $objArticle->classes) )
//                                {
//                                    $arrAttributes[] = 'data-stellar-vertical-offset="-125"';
//                                }
//                            }

                        $articlePattern = '/<div([A-Za-z0-9\s\-_=;",:\\.\(\)\'#\/%]{0,})class="mod_article([A-Za-z0-9\s\-_]{0,})"([A-Za-z0-9\s\-_=;",:\\.\(\)\'#\/%]{0,})>/';
                        $strContent     = preg_replace($articlePattern, '<div$1class="mod_article $2"$3 data-stellar-offset-parent="true" data-stellar-background-ratio="0.5">', $strContent);
                    }

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

            if( $objArticle->addDivider )
            {
                $arrArticleClasses[] = 'has-article-divider';
                $arrArticleClasses[] = 'divider-' . $objArticle->dividerStyle;
            }
        }

        if( is_array($articleClass) && count($articleClass) > 0 )
        {
            //TODO: doppelt??!? $articleClass == $arrArticleClasses
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
                    //TODO: make size changeable

                    $arrowStyle = $objArticle->toNextArrowStyle;
                    $arrowTitle = '';
                    $arrowType  = 'page';

                    if( $objArticle->toNextArrowAddTitle )
                    {
                        $objNextArticle = \ArticleModel::findOneBy(array('published=?', 'pid=?', 'inColumn=?', 'sorting>?'), array('1', $objArticle->pid, 'main', $objArticle->sorting));

                        $arrowTitle = $objNextArticle->title;
                    }

                    if( $isFullpage )
                    {
                        if( $objPage->fullpageDirection === "horizontal" )
                        {
                            $arrowType = 'section';
                        }
                    }

                    $divTableEnd = '<div class="pos-abs pos-center-bottom arrow arrow-down arrow-' . $arrowStyle . ' big scroll-to-next-' . $arrowType . '"><div class="arrow-inside-container"><div class="arrow-inside">' . $arrowTitle . '</div></div></div>' . $divTableEnd;
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

        if( $objArticle->addDivider && $objArticle->dividerStyle === "style7" )
        {
            $objNextArticle = \ArticleModel::findOneBy(array('published=?', 'pid=?', 'inColumn=?', 'sorting>?'), array('1', $objArticle->pid, $objArticle->inColumn, $objArticle->sorting));
            $bgColor        = ColorHelper::compileColor( \StringUtil::deserialize($objNextArticle->bgColor, TRUE) );

            if( !$bgColor )
            {
                $bgColor = '#fff';
            }

            $strDivider = '<div class="divider-container"><svg xmlns="http://www.w3.org/2000/svg" version="1.1" width="100%" height="100" viewBox="0 0 100 99" preserveAspectRatio="none" shape-rendering="auto">
				<path d="M-5 100 Q 0 20 5 100 Z
						 M0 100 Q 5 0 10 100
						 M5 100 Q 10 30 15 100
						 M10 100 Q 15 10 20 100
						 M15 100 Q 20 30 25 100
						 M20 100 Q 25 -10 30 100
						 M25 100 Q 30 10 35 100
						 M30 100 Q 35 30 40 100
						 M35 100 Q 40 10 45 100
						 M40 100 Q 45 50 50 100
						 M45 100 Q 50 20 55 100
						 M50 100 Q 55 40 60 100
						 M55 100 Q 60 60 65 100
						 M60 100 Q 65 50 70 100
						 M65 100 Q 70 20 75 100
						 M70 100 Q 75 45 80 100
						 M75 100 Q 80 30 85 100
						 M80 100 Q 85 20 90 100
						 M85 100 Q 90 50 95 100
						 M90 100 Q 95 25 100 100
						 M95 100 Q 100 15 105 100 Z" style="fill:' . $bgColor . '">
				</path>
			</svg></div>';

            $strContent = preg_replace('/<\/div>$/', $strDivider . '</div>', $strContent);
        }

        return $strContent;
    }
}