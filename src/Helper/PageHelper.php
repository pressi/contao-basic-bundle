<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\Helper;


use Contao\Database\Result;


/**
 * Description
 *
 */
class PageHelper
{

    /**
     * check
     *
     * @param integer|\PageModel|Result $pageId
     *
     * @return boolean
     * @TODO: add check if removePageLoader active!!
     */
    public static function checkIfParentPagesHasPageLoader( $objPageId )
    {
        $db = \Database::getInstance();

        if( $objPageId instanceof \PageModel || $objPageId instanceof Result)
        {
            $objSearchPage = $objPageId;
        }
        else
        {
            $objSearchPage = $db->prepare("SELECT * FROM tl_page WHERE id=?")->limit(1)->execute( $objPageId );
        }

        if( $objSearchPage && $objSearchPage->pid > 0)
        {
            $objParentPage = $db->prepare("SELECT * FROM tl_page WHERE id=?")->limit(1)->execute( $objSearchPage->pid );

            if( $objParentPage )
            {
                if( !$objParentPage->addPageLoader )
                {
                    return self::checkIfParentPagesHasPageLoader( $objParentPage );
                }
                else
                {
                    return true;
                }

            }
        }

        return false;
    }



    public static function isFullPageEnabled( \PageModel $objPage = NULL, \LayoutModel $objLayout = NULL )
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
            $objLayout  = (($objLayout != NULL) ? $objLayout : BasicHelper::getPageLayout( $objPage ));
            $strClass   = trim($objPage->cssClass) . " " . trim($objLayout->cssClass);

            if( preg_match("/enable-fullpage/", $strClass) )
            {
                $fullpage = true;
            }
        }

        return $fullpage;
    }



    public static function replaceBodyClasses( $strContent, array $arrBodyClasses = array() )
    {
        global $objPage;

        $arrClasses = array();

        if( self::isFullPageEnabled( $objPage ) )
        {
            $arrClasses[] = 'enable-fullpage';
        }

        $colorClass = ColorHelper::getPageColorClass( $objPage );

        if( $colorClass )
        {
            $arrClasses[] = $colorClass;
        }

        $arrClasses[] = 'lang-' . BasicHelper::getLanguage();

        $objRootPage = \PageModel::findByPk( $objPage->rootId );

        if( $objRootPage && strlen(trim($objRootPage->cssClass)) )
        {
            $arrClasses = array_merge($arrClasses, explode(" ", trim($objRootPage->cssClass)));
        }

        $arrBodyClasses = array_merge($arrBodyClasses, $arrClasses);
        $arrBodyClasses = array_values($arrBodyClasses);
        $arrBodyClasses = array_unique($arrBodyClasses);

        $strContent = preg_replace('/<body([^>]+)class="/', '<body$1class="' . implode(" ", $arrBodyClasses) . ' ', $strContent);

        return $strContent;
    }



    public static function hasBodyClass( $className, $strBuffer )
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



    public static function replaceWebsiteTitle( $strContent )
    {
        global $objPage;

        $rootTitle = (($objPage->rootPageTitle) ? $objPage->rootPageTitle : $objPage->rootTitle);

        preg_match_all('/<title>(.*)' . $rootTitle . '<\/title>/', $strContent, $matches);

        if( count($matches[1]) > 0)
        {
            $newTitle   = trim( str_replace("-", "", $matches[1][0]) );
            $strContent = preg_replace('/<title>' . preg_quote($matches[1][0] . $rootTitle, "/") .'<\/title>/', '<title>' . $newTitle . ' - ' . $rootTitle . '</title>', $strContent);
        }

        return $strContent;
    }
}
