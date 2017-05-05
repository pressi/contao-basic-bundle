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
        return parent::generate();
    }


    /**
     * Generate the module
     */
    protected function compile()
    {
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


        if( $this->levelOffset == 2 && $objPage->submenuNoPages )
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
                    $arrSubitems    = \NewsModel::findBy( array('pid=?', 'published=?'), array($objParentPage->submenuNewsArchive, '1'), array("order"=>"headline ASC") )->fetchAll();
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
}
