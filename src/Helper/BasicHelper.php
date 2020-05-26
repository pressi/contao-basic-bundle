<?php
declare(strict_types=1);

/*******************************************************************
 * (c) 2020 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\Helper;


use Contao\Input;
use Contao\Model;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\System;
use IIDO\BasicBundle\Config\BundleConfig;


class BasicHelper
{
    public static function getRootDir( $includeSlash = false ): string
    {
        return BundleConfig::getRootDir( $includeSlash );
    }



    public static function getLanguage(): string
    {
        return System::getContainer()->get('request_stack')->getCurrentRequest()->getLocale();
    }



    public static function getContaoVersion(): string
    {
        return BundleConfig::getContaoVersion();
    }


    public static function isActiveBundle( $bundleName )
    {
        return BundleConfig::isActiveBundle( $bundleName );
    }



    public static function replacePlaceholder( $strContent, $fromBackend = false ): string
    {
        preg_match_all('/\{[^=#]+\}/', $strContent, $arrChunks);

        if( is_array($arrChunks) && count($arrChunks) && count($arrChunks[0]) )
        {
            foreach ($arrChunks[0] as $strChunk)
            {
                $strKey = substr($strChunk, 1, -1);
                $arrKey = explode('::', $strKey);

                if( $fromBackend )
                {
                    if( count($arrKey) > 1 )
                    {
                        $doMode     = \Input::get('do');
                        $modelKey   = $arrKey[1];

                        if( $arrKey[0] === 'page' && ($doMode === 'article' || $doMode === 'page') )
                        {
                            $objRootPage = false;

                            if( $doMode === 'article' )
                            {
                                if( \Input::get("table") )
                                {
                                    $objContent     = \ContentModel::findByPk( \Input::get('id') );
                                    $objArticle     = \ArticleModel::findByPk( $objContent->pid );
                                    $objRootPage    = PageModel::findByPk( $objArticle->pid );
                                }
                            }
                            else
                            {
                                $objRootPage    = PageModel::findByPk( \Input::get('id') );
                            }

                            if( $objRootPage )
                            {
                                $objRootPage = $objRootPage->loadDetails();

                                $strContent = preg_replace('/' . $strChunk . '/', $objRootPage->$modelKey, $strContent);
                            }
                        }
                    }
                }
            }
        }

        return $strContent;
    }



    public static function getFilesCustomerDir(): string
    {
        if( TL_MODE === 'FE' )
        {
            global $objPage;

            return $objPage->rootAlias;
        }
        else
        {
            return 'bestpreisagrar';
        }
    }



    /**
     * Get a page layout and return it as database result object
     *
     * @param Model
     *
     * @return Model|boolean
     */
    public static function getPageLayout( Model $objPage )
    {
        if($objPage === NULL)
        {
            return false;
        }

        $blnMobile  = ($objPage->mobileLayout && \Environment::get('agent')->mobile);

        // Override the autodetected value
        if( Input::cookie('TL_VIEW') === 'mobile' && $objPage->mobileLayout )
        {
            $blnMobile = true;
        }
        elseif( Input::cookie('TL_VIEW') === 'desktop' )
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



    public static function getPage( $id ): PageModel
    {
        return PageModel::findByPk( $id );
    }



    public static function isBackend(): bool
    {
        $scopeMatcher   = System::getContainer()->get('contao.routing.scope_matcher');
        $requestStack   = System::getContainer()->get('request_stack');

        return $scopeMatcher->isBackendRequest( $requestStack->getCurrentRequest() );
    }



    public static function isFrontend(): bool
    {
        $scopeMatcher   = System::getContainer()->get('contao.routing.scope_matcher');
        $requestStack   = System::getContainer()->get('request_stack');

        return $scopeMatcher->isFrontendRequest( $requestStack->getCurrentRequest() );
    }



    public static function renderPosition( $objElement, $positionMarginName = 'positionMargin', $returnStyles = true, $returnStylesAsArray = false, $returnClasses = false, $positionName = 'position', $posFixedName = 'positionFixed', $reverseFixed = false )
    {
        $arrClasses = array();
        $arrStyles  = array();
        $strStyles  = '';

        if( $returnClasses )
        {
            $arrClasses[] = 'pos-' . (($objElement->$posFixedName && !$reverseFixed) ? 'fixed' : 'abs');
            $arrClasses[] = 'pos-' . str_replace('_', '-', $objElement->$positionName);
        }

        $arrPosMargin = StringUtil::deserialize($objElement->$positionMarginName, TRUE);

        if( $arrPosMargin['top'] || $arrPosMargin['right'] || $arrPosMargin['bottom'] || $arrPosMargin['left'] )
        {
            $unit       = $arrPosMargin['unit']?:'px';
            $useUnit    = true;

            if( $arrPosMargin['top'] )
            {
                if( preg_match('/(' . implode('|', $GLOBALS['TL_CSS_UNITS']) . ')$/', $arrPosMargin['top']) )
                {
                    $useUnit = false;
                }

                if( preg_match('/' . $unit . '$/', $arrPosMargin['top']) )
                {
                    $useUnit = false;
                }

                $strStyles .= " margin-top:" . $arrPosMargin['top'] . (($useUnit)?$unit:'') . ";";
                $arrStyles['margin-top'] = $arrPosMargin['top'] . (($useUnit)?$unit:'');

                $useUnit    = true;
            }

            if( $arrPosMargin['right'] )
            {
                if( preg_match('/(' . implode('|', $GLOBALS['TL_CSS_UNITS']) . ')$/', $arrPosMargin['right']) )
                {
                    $useUnit = false;
                }

                if( preg_match('/' . $unit . '$/', $arrPosMargin['right']) )
                {
                    $useUnit = false;
                }

                $strStyles .= " margin-right:" . $arrPosMargin['right'] . (($useUnit)?$unit:'') . ";";
                $arrStyles['margin-right'] = $arrPosMargin['right'] . (($useUnit)?$unit:'');

                $useUnit    = true;
            }

            if( $arrPosMargin['bottom'] )
            {
                if( preg_match('/(' . implode('|', $GLOBALS['TL_CSS_UNITS']) . ')$/', $arrPosMargin['bottom']) )
                {
                    $useUnit = false;
                }

                if( preg_match('/' . $unit . '$/', $arrPosMargin['bottom']) )
                {
                    $useUnit = false;
                }

                $strStyles .= " margin-bottom:" . $arrPosMargin['bottom'] . (($useUnit)?$unit:'') . ";";
                $arrStyles['margin-bottom'] = $arrPosMargin['bottom'] . (($useUnit)?$unit:'');

                $useUnit    = true;
            }

            if( $arrPosMargin['left'] )
            {
                if( preg_match('/(' . implode('|', $GLOBALS['TL_CSS_UNITS']) . ')$/', $arrPosMargin['left']) )
                {
                    $useUnit = false;
                }

                if( preg_match('/' . $unit . '$/', $arrPosMargin['left']) )
                {
                    $useUnit = false;
                }

                $strStyles .= " margin-left:" . $arrPosMargin['left'] . (($useUnit)?$unit:'') . ";";
                $arrStyles['margin-left'] = $arrPosMargin['left'] . (($useUnit)?$unit:'');

                $useUnit    = true;
            }
        }

        $arrReturn = array();

        if( $returnStyles )
        {
            $arrReturn[] = ($returnStylesAsArray ? $arrStyles : $strStyles);
        }

        if( $returnClasses )
        {
            $arrReturn[] = $arrClasses;
        }

        return (count($arrReturn) === 1 ? $arrReturn[0] : $arrReturn);
    }



    public static function getRootPageAlias( $deRoot = false )
    {
        global $objPage;

        $strLang    = self::getLanguage();

        if( $strLang !== "de" && $deRoot )
        {
            $objRooPage = PageModel::findOneBy("language", "de");//TODO: verbindung zwischen root pages herstellen!!
            $rootAlias  =  $objRooPage->alias;
        }
        else
        {
            $rootAlias = $objPage->rootAlias;
        }

        return $rootAlias;
    }



    /**
     * Deserialize all data recursively
     *
     * @param  array|object $data data array or object
     * @return array|object       data passed in with deserialized values
     */
    public static function deserializeDataRecursive($data)
    {
        foreach ($data as $key => $value)
        {
            if (is_string($value) && trim($value))
            {
                if (is_object($data))
                {
                    $data->$key = deserialize($value);
                }
                else
                {
                    $data[$key] = deserialize($value);
                }
            }
            else if (is_array($value) || is_object($value))
            {
                if (is_object($data))
                {
                    $data->$key = self::deserializeDataRecursive($value);
                }
                else
                {
                    $data[$key] = self::deserializeDataRecursive($value);
                }
            }
        }

        return $data;
    }
}