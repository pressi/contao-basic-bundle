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

namespace IIDO\BasicBundle\FrontendModule;

use IIDO\BasicBundle\Helper\ContentHelper;


/**
 * Frontend Module: Navigation
 *
 * @author Stephan Preßl <development@prestep.at>
 */
class NavigationModule extends \ModuleNavigation
{


    /**
     * Do not display the module if there are no menu items
     *
     * @return string
     */
    public function generate()
    {
        global $objPage;

        $strBuffer = parent::generate();

//        if( !strlen($strBuffer) && preg_match('/nav-sub/', $this->cssID[1]) )
//        {
//            $objArticle         = \ArticleModel::findPublishedByPidAndColumn( $objPage->id, "main", array("order"=>"sorting"));
//            $objFirstArticle    = $objArticle->first();
//
//            if( $objFirstArticle && $objFirstArticle->bgImage && !preg_match('/homepage/', $objPage->cssClass))
//            {
//                $strBuffer = '<div class="bg-subnav empty-nav">';
//            }
//        }

        return $strBuffer;
    }


    /**
     * Generate the module
     */
    protected function compile()
    {
        parent::compile();
        $this->renderRealMenu();
        return;

        /* @var \PageModel $objPage */
        global $objPage;
        parent::compile();

        $strContent     = "";
        $arrPageItems   = array();
        $arrItems       = preg_split('/<\/li>/', $this->Template->items);
        $level          = 1;
        $prevParentID   = 0;
        $parentID       = 0;
        $language       = \System::getContainer()->get('request_stack')->getCurrentRequest()->getLocale();

        $arrPageItems2  = array();


        if( $this->levelOffset >= 1 && $objPage->submenuNoPages )
        {
            $arrCurrentSubPages = array();

            switch( $objPage->submenuSRC )
            {
                case "articles":
                    $arrCurrentSubPages = \ArticleModel::findBy( array('pid=?', 'published=?', 'hideInMenu=?'), array($objPage->id, '1', '') , array("order"=>"sorting") )->fetchAll();
                    break;
            }

            if( count($arrCurrentSubPages) )
            {
                $langPartName   = 'artikel';

                if( $language == "en" )
                {
                    $langPartName = 'article';
                }

                foreach( $arrCurrentSubPages as $arrSubPage )
                {
                    $objItem    = \ArticleModel::findByPk( $arrSubPage['id'] );
                    $href       = $objPage->getFrontendUrl('/' . $langPartName . '/' . $objItem->alias);
                    $class      = '';
                    $strTitle   = ContentHelper::renderText((strlen($objItem->navTitle)) ? $objItem->navTitle : $objItem->title );

                    if( $objItem->submenuSRC )
                    {
                        $arrSubPage[ 'submenuNoPages' ] = TRUE;
                    }

                    if( preg_match('/(<br>|{{br}})/', $strTitle) )
                    {
                        $class = ' double-line';
                    }

                    $arrItem = array
                    (
                        'object'    => (object) $arrSubPage,
                        'item'      => '<li class="sibling' . $class . '"><a href="' . $href . '"><span itemprop="name">' . $strTitle . '</span></a>',
                        'subitems'  => array()
                    );

                    if( $objItem->submenuSRC )
                    {
                        $arrItem['subitems'] = $this->renderSubNavigation( $arrItem, ($level + 1) );
                    }

                    if( ($objPage->submenuPageCombination && $objPage->submenuPageOrder == "pagesAfter") || !$objPage->submenuPageCombination )
                    {
                        $arrPageItems[] = $arrItem;
                    }
                    elseif( $objPage->submenuPageCombination && $objPage->submenuPageOrder == "pagesBefore" )
                    {
                        $arrPageItems2[] = $arrItem;
                    }
                }
            }
        }

        foreach( $arrItems as $strItem)
        {
            $strItem = trim(preg_replace('/<ul class="level_1">/', '', $strItem));

            if( preg_match('/<\/ul>/', $strItem) )
            {
                $level--;
                $parentID = $prevParentID;
            }
            else
            {
                if( preg_match('/<ul/', $strItem) && preg_match('/level_2/', $strItem) )
                {
                    $arrCurrentItems = explode('<ul class="level_2">', $strItem);

                    preg_match_all('/href="([a-z]+\/{0,2})([A-Za-z0-9\/.\{\}:\-_]{0,})"/', $arrCurrentItems[0], $arrUrl);

                    $strUrl = $arrUrl[2][0];

                    if( !$strUrl )
                    {
                        preg_match_all('/data-alias="([A-Za-z0-9\/.\{\}:\-_]{0,})"/', $arrCurrentItems[0], $arrUrl);

                        $strUrl = $arrUrl[1][0];
                    }

                    if( $strUrl )
                    {
                        $objItemPage = \PageModel::findByIdOrAlias( preg_replace('/.html$/', '', $strUrl) );

                        $arrPageItems[ $objItemPage->id ] = array
                        (
                            'object'    => $objItemPage,
                            'item'      => $arrCurrentItems[0],
                            'subitems'  => array()
                        );

                        $level++;
                        $parentID = $objItemPage->id;
                    }

                    preg_match_all('/href="([a-z]+\/{0,2})([A-Za-z0-9\/.\{\}:\-_]{0,})"/', $arrCurrentItems[1], $arrSubUrl);

                    $strSubUrl = $arrSubUrl[2][0];

                    if( !$strSubUrl )
                    {
                        preg_match_all('/data-alias="([A-Za-z0-9\/.\{\}:\-_]{0,})"/', $arrCurrentItems[1], $arrUrl);

                        $strSubUrl = $arrSubUrl[1][0];
                    }

                    if( $strSubUrl )
                    {
                        $objItemPage = \PageModel::findByIdOrAlias( preg_replace('/.html$/', '', $strSubUrl) );

                        $arrPageItems[ $parentID ]['subitems'][ $objItemPage->id ] = array
                        (
                            'object'    => $objItemPage,
                            'item'      => $arrCurrentItems[1],
                            'subitems'  => array()
                        );
                    }
                }
                else
                {
                    preg_match_all('/href="([a-z]+\/{0,2})([A-Za-z0-9\/.\{\}:\-_]{0,})"/', $strItem, $arrUrl);

                    $strUrl = $arrUrl[2][0];

                    if( !$strUrl || $arrUrl[1][0] == "http" )
                    {
                        preg_match_all('/data-alias="([A-Za-z0-9\/.\{\}:\-_]{0,})"/', $strItem, $arrUrl);
                        $strUrl = $arrUrl[1][0];
                    }

                    if( $strUrl )
                    {
                        $objItemPage = \PageModel::findByIdOrAlias( preg_replace('/.html$/', '', $strUrl) );

                        if( $objItemPage )
                        {
                            if( $level == 2 )
                            {
                                $arrPageItems[ $parentID ]['subitems'][ $objItemPage->id ] = array
                                (
                                    'object'    => $objItemPage,
                                    'item'      => $strItem,
                                    'subitems'  => array()
                                );
                            }
                            else
                            {
                                $arrPageItems[ $objItemPage->id ] = array
                                (
                                    'object'    => $objItemPage,
                                    'item'      => $strItem,
                                    'subitems'  => array()
                                );
                            }
                        }
                    }
                }
            }
        }

        if( count($arrPageItems2) )
        {
            $arrPageItems = array_merge($arrPageItems, $arrPageItems2);
        }

        if( count($arrPageItems) )
        {
            $className = '';

            if( $this->levelOffset == 2 && $objPage->submenuSRC == "articles" )
            {
                $className = ' article-submenu';
            }

            $strContent = '<ul class="level_1' . $className . '">';

            foreach( $arrPageItems as $arrPageItem )
            {
                $strItem        = $arrPageItem['item'];

                if( $arrPageItem['object']->submenuNoPages )
                {
                    if( !preg_match('/submenu/', $strItem) )
                    {
                        $strItem = preg_replace('/<li([0-9A-Za-z\s\-="_]{0,})class="/', '<li$1class="submenu ', $strItem);
                    }
                }

                if( $arrPageItem['object']->type == "redirect" )
                {
                    $strItem = preg_replace('/<li([0-9A-Za-z\s\-="_]{0,})class="/', '<li$1class="external-link ', $strItem);
                }

                $strSubItems = $this->renderSubNavigation( $arrPageItem, 2 );

                if( $arrPageItem['object']->id == $objPage->id )
                {
                    if( (preg_match('/<strong/', $strSubItems) || preg_match('/active/', $strSubItems)) && !preg_match('/<strong/', $strItem) )
                    {
                        $strItem = preg_replace(array('/<a/', '/<\/a>/', '/href="/'), array('<strong', '</strong>', 'data-href="'), $strItem);
                    }
                }

                $strContent .= $strItem . $strSubItems . '</li>';
            }

            $strContent .= '</ul>';
        }

        $this->Template->items = $strContent;
    }


    /**
     * @param array     $arrParentPage
     * @param integer   $level
     *
     * @return string
     */
    protected function renderSubNavigation( $arrParentPage, $level )
    {
        global $objPage;

        /* @var \PageModel $objParentPage */
        $objParentPage  = $arrParentPage['object'];
        $arrSubitems    = $arrParentPage['subitems'];
        $from           = "array";
        $strContent     = '';
        $language       = \System::getContainer()->get('request_stack')->getCurrentRequest()->getLocale();

        if( $objParentPage->submenuNoPages )
        {
            switch( $objParentPage->submenuSRC )
            {
                case "articles":
                    $arrSubitems    = \ArticleModel::findBy( array('pid=?', 'published=?', 'hideInMenu=?'), array($objParentPage->id, '1', '') , array("order"=>"sorting"))->fetchAll();
                    $from           = "articles";
                    break;

                case "news":
                    $arrSubitems    = \NewsModel::findBy( array('pid=?', 'published=?'), array($objParentPage->submenuNewsArchive, '1'), array("order"=>"sorting") )->fetchAll();
                    $from           = "news";
                    break;
            }
        }

        if( count($arrSubitems) )
        {
            if( $from == "array"  )
            {
                $strSubItems = "";
                foreach( $arrSubitems as $arrSubitem )
                {
                    $strSubItems .= $arrSubitem['item'];
                }

                if( strlen($strSubItems) )
                {
                    $strContent = '<ul class="level_' . $level . '">' . $strSubItems . '</ul>';
                }
            }
            else
            {
                $langPartName   = 'artikel';
                $items          = array();

                if( $language == "en" )
                {
                    $langPartName = 'article';
                }

                $activeUrl      = \Input::get($langPartName);

                /** @var \FrontendTemplate|object $objTemplate */
                $objTemplate = new \FrontendTemplate( $this->navigationTpl );

                $objTemplate->level = 'level_' . $level;

                if( $objParentPage->id == $objPage->id )
                {
                    $objTemplate->level = $objTemplate->level . ' article-menu';
                }

                $num = 0;
                foreach($arrSubitems as $arrSubitem)
                {
                    $row        = $arrSubitem;
                    $isActive   = FALSE;
//                echo "<pre>"; print_r( $from ); echo "<br>"; print_r( $objParentPage->getFrontendUrl('/item/' . $arrSubitem['alias']) ); exit;
                    $strClass = "";

                    if( $num == 0 )
                    {
                        $strClass .= " first";
                    }

                    if( $num == (count($arrSubitems) - 1) )
                    {
                        $strClass .= " last";
                    }

                    if( $from == "articles" )
                    {
                        if( $arrSubitem['submenuSRC'] )
                        {
                            $arrSubitem['submenuNoPages']   = TRUE;
                            $arrSubitem['parent']           = $objParentPage;
                        }

                        if( $arrSubitem['alias'] == $activeUrl )
                        {
//                            $isActive = TRUE;
                            $strClass .= ' active';
                        }
                    }
                    elseif( $from == "news" )
                    {
                        $arrSubitem['title'] = $arrSubitem['headline'];
                    }

                    $href = "";

                    if( $level == 2 )
                    {
                        if( $objParentPage instanceof \PageModel )
                        {
                            $href = $objParentPage->getFrontendUrl('/' . $langPartName . '/' . $arrSubitem['alias']);
                        }
                        else
                        {
                            if( $from == "news" )
                            {
                                $objNews            = \NewsModel::findByPk( $arrSubitem['id'] );
                                $href               = \News::generateNewsUrl( $objNews, false);
                            }
                        }
                    }
                    elseif( $level == 3 )
                    {
//                        $objArchive         = \NewsArchiveModel::findByPk( $arrSubitem['pid'] );
                        $objNews            = \NewsModel::findByPk( $arrSubitem['id'] );

//                        $objJumpTo          = $objArchive->getRelated("jumpTo");
//                        $objUrlGenerator    = \System::getContainer()->get('contao.routing.url_generator');

                        $href               = \News::generateNewsUrl( $objNews, false); //$objUrlGenerator->generate(($objJumpTo->alias ?: $objJumpTo->id) . '/' . $arrSubitem['alias']);
                    }

                    $strSubItems = $this->renderSubNavigation( array('object'=> (object) $arrSubitem, 'item'=>'', 'subitems'=>array()), ($level + 1) );

                    if( strlen($strSubItems) )
                    {
                        $strClass .= ' submenu';
                    }

                    if( $arrSubitem['type'] == "external" )
                    {
                        $strClass .= ' external-link';
                    }

                    $row['isActive']    = $isActive;
                    $row['isTrail']     = false;
                    $row['subitems']    = $strSubItems;
                    $row['class']       = "sibling" . $strClass;
                    $row['pageTitle']   = \StringUtil::specialchars($arrSubitem['title'], true);
                    $row['link']        = $arrSubitem['title'];
                    $row['href']        = $href;
//                $row['nofollow']    = (strncmp($objSubpage->robots, 'noindex,nofollow', 16) === 0);
                    $row['target']      = '';
                    $row['description'] = '';

                    $items[] = $row;
                    $num++;
                }

                $objTemplate->items = $items;

                $strContent = !empty($items) ? $objTemplate->parse() : '';
            }
        }

        return $strContent;
    }



    public function renderRealMenu()
    {
        /* @var \PageModel $objPage */
        global $objPage;

        $arrItems       = array();
        $arrNavPages    = \StringUtil::deserialize( $this->navPagesOrder, TRUE );
        $useCustomNav   = FALSE;

        if( count($arrNavPages) > 0 && $arrNavPages[0] !== '' )
        {
            if( count( $arrNavPages ) > 1 )
            {
                $useCustomNav   = TRUE;
            }
            else
            {
                $useCustomNav   = TRUE;
                $objSubPages    = \PageModel::findPublishedSubpagesWithoutGuestsByPid( $arrNavPages[0] );

                if( !$objSubPages )
                {
                    $useCustomNav       = FALSE;

                    $this->defineRoot   = TRUE;
                    $this->rootPage     = $arrNavPages[0];
                }
            }
        }

        // Set the trail and level
        if ($this->defineRoot && $this->rootPage > 0)
        {
            $trail = array($this->rootPage);
            $level = 0;
        }
        else
        {
            $trail = $objPage->trail;
            $level = ($this->levelOffset > 0) ? $this->levelOffset : 0;
        }

        $lang = null;
        $host = null;

        // Overwrite the domain and language if the reference page belongs to a differnt root page (see #3765)
        if ($this->defineRoot && $this->rootPage > 0)
        {
            $objRootPage = \PageModel::findWithDetails($this->rootPage);

            // Set the language
            if (\Config::get('addLanguageToUrl') && $objRootPage->rootLanguage != $objPage->rootLanguage)
            {
                $lang = $objRootPage->rootLanguage;
            }

            // Set the domain
            if ($objRootPage->rootId != $objPage->rootId && $objRootPage->domain != '' && $objRootPage->domain != $objPage->domain)
            {
                $host = $objRootPage->domain;
            }
        }

        if( $useCustomNav )
        {
            $arrPages = array();

            foreach($arrNavPages as $navPageID)
            {
                $objNavPage = \PageModel::findByPk( $navPageID );

                if( $objNavPage->published )
                {
                    $arrPages[] = $objNavPage;
                }
            }

            $strItems = $this->getCustomPages( $arrPages, 1, $host, $lang);
        }
        else
        {
            if( $level > count($trail) )
            {
                $strItems = $this->getPages( $objPage->id, 1, $host, $lang );
            }
            else
            {
                if( $this->name == "Navigation Sub" && $level == 1 && count($trail) > 2 )
                {
                    $level++;
                }

                $strItems = $this->getPages( $trail[ $level ], 1, $host, $lang );
            }
        }

        $this->Template->items = $strItems;
    }


    protected function getPages( $pid, $level=1, $host=null, $language=null, $type = '' )
    {
        /* @var \PageModel $objPage */
        global $objPage;

        $objParentPage = false;

        if( $pid > 0 )
        {
            $objParentPage = \PageModel::findByPk( $pid );

            if( $type == "article" )
            {
                $objParentPage = \ArticleModel::findByPk( $pid );

                if( $objParentPage->submenuSRC )
                {
                    $objParentPage->submenuNoPages = true;
                }
            }
        }

        // Layout template fallback
        if ($this->navigationTpl == '')
        {
            $this->navigationTpl = 'nav_default';
        }

        /** @var \FrontendTemplate|object $objTemplate */
        $objTemplate = new \FrontendTemplate( $this->navigationTpl );

        $objTemplate->pid   = $pid;
        $objTemplate->type  = get_class($this);
        $objTemplate->cssID = $this->cssID;
        $objTemplate->level = 'level_' . $level++;

        if( preg_match('/article-menu/', $this->cssID[1]) )
        {
            $objTemplate->level .=' article-submenu';
        }

        if( $objParentPage->submenuSRC == "articles" && !preg_match('/article-submenu/', $objTemplate->level) && $objPage->id == $objParentPage->id )
        {
            $objTemplate->level .=' article-menu';
        }

        $arrItems   = array();
        $groups     = array();

        if( $objParentPage && $objParentPage->submenuNoPages && (!$objParentPage->submenuPageCombination || ($objParentPage->submenuPageCombination && $objParentPage->submenuPageOrder === "pagesAfter")) )
        {
            switch( $objParentPage->submenuSRC )
            {
                case "articles":
                    $arrItems = $this->getArticlePages( $objParentPage, $level, $host, $language );
                    break;

                case "news":
                    $arrItems = $this->getNewsPages( $objParentPage, $level, $host, $language );
                    break;
            }
        }

        if( !$objParentPage->submenuNoPages || ($objParentPage->submenuNoPages && $objParentPage->submenuPageCombination) )
        {
            $objPages = \PageModel::findPublishedSubpagesWithoutGuestsByPid( $pid, $this->showHidden );

            if( $objPages )
            {
                while( $objPages->next() )
                {
                    $objItem    = $objPages->current();

                    $trail      = in_array($objItem->id, $objPage->trail);
                    $href       = null;
                    $subitems   = '';
                    $_groups    = \StringUtil::deserialize( $objItem->groups );

                    // Override the domain (see #3765)
                    if ($host !== null)
                    {
                        $objItem->domain = $host;
                    }

                    // Do not show protected pages unless a front end user is logged in
                    if (!$objItem->protected || (is_array($_groups) && count(array_intersect($_groups, $groups))) || $this->showProtected || ($this instanceof \ModuleSitemap && $objItem->sitemap == 'map_always'))
                    {
                        // Check whether there will be subpages
                        if( (!$this->showLevel || $this->showLevel >= $level || (!$this->hardLimit && ($objPage->id == $objItem->id || in_array($objPage->id, $this->Database->getChildRecords($objItem->id, 'tl_page'))))) )
                        {
                            $subitems = $this->getPages($objItem->id, $level, $host, $language);
                        }

                        $arrItem    = $this->setItemRow($objPages->row(), $objItem, "page", $subitems);

                        $arrItems[] = $arrItem;
                    }
                }
            }
        }


        if( $objParentPage && $objParentPage->submenuNoPages && $objParentPage->submenuPageCombination && $objParentPage->submenuPageOrder === "pagesBefore" )
        {
            switch( $objParentPage->submenuSRC )
            {
                case "articles":
                    $arrItems = array_merge($arrItems, $this->getArticlePages( $objParentPage, $level, $host, $language ) );
                    break;

                case "news":
                    $arrItems = array_merge($arrItems, $this->getNewsPages( $objParentPage, $level, $host, $language ) );
                    break;
            }
        }

        if( count($arrItems) === 1 )
        {
            if( $arrItems[0]['id'] == $objPage->id && $this->name !== "Navigation Main")
            {
                return '';
            }
        }

//        if( $objPage->hide && $level > 0)
//        {
//            if( $objPage->trail[ $level ] == $objPage->id )
//            if( $objPage )
//            {
//                return '';
//            }
//        }


        // Add classes first and last
        if (!empty($arrItems))
        {
            $last = count($arrItems) - 1;

            $arrItems[0]['class'] = trim($arrItems[0]['class'] . ' first');
            $arrItems[$last]['class'] = trim($arrItems[$last]['class'] . ' last');
        }

        $objTemplate->items = $arrItems;

        $hasSubitems = false;
        foreach( $arrItems as $arrItem)
        {
            if( strlen($arrItem['subitems']) )
            {
                $hasSubitems = true;
            }
        }

        if( !$hasSubitems )
        {
            $objTemplate->level = trim( $objTemplate->level . ' last-level');
        }

        return !empty($arrItems) ? $objTemplate->parse() : '';
    }



    protected function getArticlePages( \PageModel $objParentPage, $level, $host=null, $language=null )
    {
        $arrItems       = array();
        $objArticles    = \ArticleModel::findPublishedByPidAndColumn($objParentPage->id, "main");

        if( $objArticles )
        {
            while( $objArticles->next() )
            {
                $objItem    = $objArticles->current();

                if( $objItem->hideInMenu || !$objItem->published )
                {
                    continue;
                }

                $subitems   = '';

                if( $objItem->submenuSRC )
                {
                    $subitems = $this->getPages( $objItem->id, $level, $host, $language, "article" );
                }

                $arrItem    = $this->setItemRow($objArticles->row(), $objItem, "article", $subitems);

                if( !empty($arrItem) )
                {
                    $arrItems[] = $arrItem;
                }
            }
        }

        return $arrItems;
    }



    protected function getNewsPages( $objParent, $level, $host=null, $language=null)
    {
        $arrItems = array();

        $objNews = \NewsModel::findPublishedByPid( $objParent->submenuNewsArchive, 0, array('order'=>'sorting') );

        if( $objNews )
        {
            while( $objNews->next() )
            {
                $objItem = $objNews->current();

                if( !$objItem->published )
                {
                    continue;
                }

                $arrItem = $this->setItemRow($objNews->row(), $objItem, 'news');

                $arrItems[] = $arrItem;
            }
        }


        return $arrItems;
    }



//    protected function getSubPages( $objItem )
//    {
//        echo "<pre>"; print_r( $arrItem ); exit;
//    }



    protected function setItemRow( $arrItem, $objItem, $type, $subitems = '', $arrVars = array() )
    {
        global $objPage;

        $language       = \System::getContainer()->get('request_stack')->getCurrentRequest()->getLocale();

        $strTitle       = ContentHelper::renderText( $objItem->title );
        $strPageTitle   = ContentHelper::renderText( $objItem->pageTitle?:$objItem->title );
        $isActive       = false;
        $isTrail        = false;
        $href           = '';
        $strClass       = '';

        if( $type == "page" )
        {
            $trail      = in_array($objItem->id, $objPage->trail);

            // Get href
            switch ($objItem->type)
            {
                case 'redirect':
                    $href = $objItem->url;

                    if (strncasecmp($href, 'mailto:', 7) === 0)
                    {
                        $href = \StringUtil::encodeEmail($href);
                    }
                    break;

                case 'forward':
                    if ($objItem->jumpTo)
                    {
                        /** @var \PageModel $objNext */
                        $objNext = $objItem->getRelated('jumpTo');
                    }
                    else
                    {
                        $objNext = \PageModel::findFirstPublishedRegularByPid($objItem->id);
                    }

                    // Hide the link if the target page is invisible
                    if (!($objNext instanceof \PageModel) || !$objNext->published || ($objNext->start != '' && $objNext->start > time()) || ($objNext->stop != '' && $objNext->stop < time()))
                    {
                        return array();
                    }

                    $href = $objNext->getFrontendUrl();
                    break;

                default:
                    $href = $objItem->getFrontendUrl();
                    break;
            }

            // Active page
            if (($objPage->id == $objItem->id || $objItem->type == 'forward' && $objPage->id == $objItem->jumpTo) && !($this instanceof \ModuleSitemap) && $href == \Environment::get('request'))
            {
                // Mark active forward pages (see #4822)
                $strClass = (($objItem->type == 'forward' && $objPage->id == $objItem->jumpTo) ? 'forward' . ($trail ? ' trail' : '') : 'active') . (($subitems != '') ? ' submenu' : '') . ($objItem->protected ? ' protected' : '') . (($objItem->cssClass != '') ? ' ' . $objItem->cssClass : '');

                $isActive = true;
                $isTrail = false;
            }

            // Regular page
            else
            {
                $strClass = (($subitems != '') ? 'submenu' : '') . ($objItem->protected ? ' protected' : '') . ($trail ? ' trail' : '') . (($objItem->cssClass != '') ? ' ' . $objItem->cssClass : '');

                // Mark pages on the same level (see #2419)
                if ($objItem->pid == $objPage->pid)
                {
                    $strClass .= ' sibling';
                }

                $isActive = false;
                $isTrail = $trail;
            }
        }
        elseif( $type == "article" )
        {
            $langPartName   = 'artikel';

            if( $language == "en" )
            {
                $langPartName = 'article';
            }

            $activeArticle  = \Input::get( $langPartName );

            $strTitle       = ContentHelper::renderText($objItem->navTitle?:$objItem->title );
            $strPageTitle   = $strTitle;

            $objParentPage  = \PageModel::findByPk( $objItem->pid );
            $href           = $objParentPage->getFrontendUrl('/' . $langPartName . '/' . $objItem->alias);
            $strClass       = deserialize($objItem->cssID, true)[1] . ' article-link';

            if( $activeArticle == $objItem->alias )
            {
//                $isActive = true;
                $strClass .= ' active';
            }

            if( strlen($subitems) )
            {
                if( $objItem->submenuSRC === "news" )
                {
                    $activeAlias = \Input::get("auto_item");

                    if( $activeAlias )
                    {
                        $objActiveNews = \NewsModel::findByIdOrAlias( $activeAlias );

                        if( $objActiveNews && $objActiveNews->pid == $objItem->submenuNewsArchive )
                        {
                            $isTrail = true;
                            $strClass .= ' trail';
                        }
                    }
                }
            }
        }
        elseif( $type == "news" )
        {
            $strTitle       = ContentHelper::renderText( $objItem->headline );
            $strPageTitle   = $strTitle;

            $activeAlias = \Input::get("auto_item");

            if( $activeAlias == $objItem->alias )
            {
                $isActive = true;
            }

            $href           = \News::generateNewsUrl( $objItem, false);
            $strClass       = $objItem->cssClass . ' news-link';
        }

        if( strlen($subitems) )
        {
            if( !preg_match('/submenu/', $strClass) )
            {
                $strClass .= ' submenu';
            }
        }

        if( preg_match('/(<br>|{{br}})/', $strTitle) )
        {
            $strClass .= ' double-line';
        }


        $arrItem['isActive']    = $isActive;
        $arrItem['isTrail']     = $isTrail;
        $arrItem['subitems']    = $subitems;
        $arrItem['class']       = trim($strClass);
        $arrItem['title']       = \StringUtil::specialchars($strTitle, true);
        $arrItem['pageTitle']   = \StringUtil::specialchars($strPageTitle, true);
        $arrItem['link']        = $strTitle;
        $arrItem['href']        = $href;
        $arrItem['nofollow']    = (strncmp($objItem->robots, 'noindex,nofollow', 16) === 0);
        $arrItem['target']      = '';
        $arrItem['description'] = str_replace(array("\n", "\r"), array(' ' , ''), $objItem->description);

        // Override the link target
        if ($objItem->type == 'redirect' && $objItem->target)
        {
            $arrItem['target'] = ' target="_blank"';
        }

        if( count($arrVars) )
        {
            foreach($arrVars as $varName => $varValue )
            {
                $arrItem[ $varName ] = $varValue;
            }
        }

        return $arrItem;
    }


    protected function getCustomPages( $arrPages, $level, $host, $language)
    {
        /** @var \PageModel $objPage */
        global $objPage;

        $arrItems   = array();
        $groups     = array();

        // Get all groups of the current front end user
        if (FE_USER_LOGGED_IN)
        {
            $this->import('FrontendUser', 'User');
            $groups = $this->User->groups;
        }

        // Set default template
        if ($this->navigationTpl == '')
        {
            $this->navigationTpl = 'nav_default';
        }

        /** @var \FrontendTemplate|object $objTemplate */
        $objTemplate = new \FrontendTemplate($this->navigationTpl);

        $objTemplate->type = get_class($this);
        $objTemplate->cssID = $this->cssID; // see #4897 and 6129
        $objTemplate->level = 'level_1';

        /** @var \PageModel[] $arrPages */
        foreach ($arrPages as $objModel)
        {
            $_groups = \StringUtil::deserialize($objModel->groups);

            // Do not show protected pages unless a front end user is logged in
            if (!$objModel->protected || (is_array($_groups) && count(array_intersect($_groups, $groups))) || $this->showProtected)
            {
                $subitems   = $this->getPages( $objModel->id, $level, $host, $language);

                $arrRow     = $objModel->row();
                $arrRow     = $this->setItemRow($arrRow, $objModel, 'page', $subitems);

               $arrItems[]  = $arrRow;
            }
        }

        // Add classes first and last
        $arrItems[0]['class'] = trim($arrItems[0]['class'] . ' first');
        $last = count($arrItems) - 1;
        $arrItems[$last]['class'] = trim($arrItems[$last]['class'] . ' last');

        $objTemplate->items = $arrItems;

        return !empty($arrItems) ? $objTemplate->parse() : '';;
    }
}
