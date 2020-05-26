<?php


namespace IIDO\BasicBundle\Helper;


use Contao\PageModel;


class PageHelper
{
    /**
     * Get a page layout and return it as database result object
     *
     * @param \Model
     *
     * @return \Model|boolean
     */
    public static function getPageLayout($objPage)
    {
        if($objPage === NULL)
        {
            return false;
        }

        $blnMobile  = ($objPage->mobileLayout && \Environment::get('agent')->mobile);

        // Override the autodetected value
        if( \Input::cookie('TL_VIEW') === 'mobile' && $objPage->mobileLayout )
        {
            $blnMobile = true;
        }
        elseif( \Input::cookie('TL_VIEW') === 'desktop' )
        {
            $blnMobile = false;
        }

        $intId      = $blnMobile ? $objPage->mobileLayout : $objPage->layout;
        $objLayout  = \LayoutModel::findByPk( $intId );

        // Die if there is no layout
        if ($objLayout === null)
        {
            $objLayout = false;

            if($objPage->pid > 0)
            {
                $objParentPage  = self::getPage( $objPage->pid );
                $objLayout      = self::getPageLayout( $objParentPage );
            }
        }

        return $objLayout;
    }



    public static function getCurrentPageLayout()
    {
        global $objPage;

        return self::getPageLayout( $objPage );
    }



    /**
     * @param $id
     *
     * @return \Contao\PageModel|null|static
     * @deprecated USE getPage instead!!
     */
    public static function getParentPage( $id )
    {
        return self::getPage( $id );
    }



    public static function getPage( $id )
    {
        return \PageModel::findByPk( $id );
    }



    public static function getRootPageAlias( $deRoot = false )
    {
        global $objPage;

        $strLang = BasicHelper::getLanguage();

        if( $strLang !== "de" && $deRoot )
        {
            //TODO: Verbindung zwischen root pages herstellen!!
            $objRooPage = PageModel::findOneBy("language", "de");
            $rootAlias  =  $objRooPage->alias;
        }
        else
        {
            $rootAlias  = $objPage->rootAlias;
            $objRooPage = PageModel::findByPk( $objPage->rootId );

            if( $objRooPage && !$objRooPage->rootIsFallback )
            {
                $objFallbackPage = PageModel::findByPk( $objPage->languageMain );

                if( $objFallbackPage )
                {
                    $objFallbackPage->loadDetails();

                    $rootAlias = $objFallbackPage->rootAlias;
                }
            }
        }

        return $rootAlias;
    }



    public static function getRootPage( $page = false )
    {
        $objRootPage = false;

        if( $page )
        {
            if( $page instanceof PageModel )
            {
                $objRootPage = PageModel::findByPk( $page->rootId );
            }
            else
            {
                $objCurPage = PageModel::findByPk( $page );

                if( $objCurPage )
                {
                    $objCurPage = $objCurPage->loadDetails();

                    $objRootPage = PageModel::findByPk( $objCurPage->rootId );
                }
            }
        }
        else
        {
            global $objPage;

            $objRootPage = PageModel::findByPk( $objPage->rootId );
        }

        return $objRootPage;
    }

}