<?php
/*******************************************************************
 * (c) 2020 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\Helper;


use Contao\ArticleModel;
use Contao\ContentModel;
use Contao\StringUtil;
use IIDO\BasicBundle\Config\BundleConfig;


/**
 * Class Script Helper
 * @package IIDO\Configundle
 */
class ScriptHelper
{
    protected static $scriptPath        = '/Resources/public/scripts/';
    protected static $internScriptPath  = 'files/%s/scripts/';


    protected static $scriptPathPublic  = '/scripts/';


    protected static $scriptModeCombine = '|static';



    public static function addDefaultScripts()
    {
        $jsPathCustom   = sprintf(self::$internScriptPath, PageHelper::getRootPageAlias());

        if( file_exists(BasicHelper::getRootDir( true ) . $jsPathCustom . 'functions.js') )
        {
            $GLOBALS['TL_JAVASCRIPT'][] = $jsPathCustom . 'functions.js|static';
        }
    }



    /**
     * Check if current page has any animations
     *
     * @return boolean
     */
    public static function hasPageAnimation()
    {
        global $objPage;

        $objArticles    = \ArticleModel::findBy( array('published=?', 'pid=?'), array('1', $objPage->id) );
        $hasAnimation   = false;

        if( $objArticles )
        {
            while( $objArticles->next() )
            {
                if( $objArticles->addAnimation )
                {
                    $hasAnimation = true;
                    break;
                }

                $objElements = \ContentModel::findPublishedByPidAndTable( $objArticles->id, \ArticleModel::getTable());

                if( $objElements )
                {
                    while( $objElements->next() )
                    {
                        if( $objElements->addAnimation )
                        {
                            $hasAnimation = true;
                            break;
                        }
                    }
                }

                if( $hasAnimation )
                {
                    break;
                }
            }
        }

        if( !$hasAnimation && (HeaderHelper::isHeaderIsSticky() ||  HeaderHelper::isTopHeaderIsSticky()) )
        {
            $hasAnimation = true;
        }

        if( !$hasAnimation )
        {
            $strTable           = \ContentModel::getTable();
            $objContentElements = \ContentModel::findBy(array($strTable . ".invisible=?", $strTable . ".type=?"), array("", "rsce_feature-box"));

            if( $objContentElements )
            {
                while( $objContentElements->next() )
                {
                    $cssID = \StringUtil::deserialize($objContentElements->cssID, TRUE);

                    if( preg_match('/bg-parallax/', $cssID[1]) )
                    {
                        $hasAnimation = true;
                    }
                }
            }
        }

        return $hasAnimation;
    }



    /**
     * Check if current page has active isotope script
     *
     * @return boolean
     */
    public static function hasPageIsotope()
    {
        global $objPage;

        $hasIsotope = false;

        if( $objPage->addIsotope )
        {
            $hasIsotope = true;
        }
        else
        {
            $objRootPage = PageHelper::getRootPage();

            if( $objRootPage && $objRootPage->addIsotope )
            {
                $hasIsotope = true;
            }
        }

        if( !$hasIsotope )
        {
            $objArticles = ArticleModel::findBy(['published=?', 'pid=?'], ['1', $objPage->id]);

            if( $objArticles )
            {
                while( $objArticles->next() )
                {
                    $objElements = ContentModel::findPublishedByPidAndTable( $objArticles->pid, 'tl_article');

                    if( $objElements )
                    {
                        while( $objElements->next() )
                        {
                            $cssID = StringUtil::deserialize( $objElements->cssID, true );

                            if( false !== strpos($cssID[0], 'listMasonry') )
                            {
                                $hasIsotope = true;
                                break;
                            }
                        }

                        if( $hasIsotope )
                        {
                            break;
                        }
                    }
                }
            }
        }

        return $hasIsotope;
    }



    /**
     * Add script
     *
     * @param string|array $scriptName
     * @param bool         $addStylesheet
     * @param bool         $includeAdd
     */
    public static function addScript( $scriptName, $addStylesheet = false, $includeAdd = false )
    {
        if( !is_array($scriptName) )
        {
            $scriptName = array( $scriptName );
        }

        foreach($scriptName as $fileKey => $fileName)
        {
            if( is_numeric($fileKey) )
            {
                $fileKey = $fileName;
            }

            $filePath       = self::getScriptSource( $fileName, false, true );
            $filePathIntern = self::getScriptSource( $fileName );

            if( file_exists(BasicHelper::getRootDir( true ) . $filePathIntern) )
            {
                $arrAddAfter = array();

                if( $includeAdd )
                {
                    if( is_dir( BasicHelper::getRootDir( true ) . $filePath . '/add' ) )
                    {

                        $arrFiles = scan( BasicHelper::getRootDir( true ) . $filePath . '/add' );

                        if( is_array($arrFiles) && count($arrFiles) )
                        {
                            foreach($arrFiles as $strFile )
                            {
                                $fileParts = explode("-", $strFile);

                                if( preg_match('/^b/', $strFile) )
                                {
                                    $num = preg_replace('/^b/', '', $fileParts[0]);

                                    $GLOBALS['TL_JAVASCRIPT'][ $fileKey . '-before-' . $num] = $filePath . '/add/' . $strFile . self::getScriptMode();
                                }
                                elseif( preg_match('/^a/', $strFile) )
                                {
                                    $arrAddAfter[] = $strFile;
                                }
                            }
                        }
                    }
                }

                $GLOBALS['TL_JAVASCRIPT'][ $fileKey ] = $filePathIntern . self::getScriptMode();

                if( $includeAdd && count($arrAddAfter) )
                {
                    foreach($arrAddAfter as $strFile )
                    {
                        $fileParts  = explode("-", $strFile);
                        $num        = preg_replace('/^a/', '', $fileParts[0]);

                        $GLOBALS['TL_JAVASCRIPT'][ $fileKey . '-after-' . $num] = $filePath . '/add/' . $strFile . self::getScriptMode();
                    }
                }

//                if( $addStylesheet ) TODO: implement!!
//                {
//                    StylesheetHelper::addStylesheet( $fileName );
//                }
            }
        }
    }



    public static function addTranslateScript( $scriptName, $language )
    {
        if( !is_array($scriptName) )
        {
            $scriptName = array( $scriptName );
        }

        foreach($scriptName as $fileKey => $fileName)
        {
            if( is_numeric($fileKey) )
            {
                $fileKey = $fileName;
            }

            $filePath       = self::getScriptSource( $fileName, false, true );

            if( is_dir(BasicHelper::getRootDir( true ) . $filePath . '/translate') )
            {
                if( file_exists(BasicHelper::getRootDir( true ) . $filePath . '/translate/' . $language . '.js') )
                {
                    $GLOBALS['TL_JAVASCRIPT'][ $fileKey . '-trans-' . $language] = $filePath . '/translate/' . $language . '.js' . self::getScriptMode();
                }
                elseif( file_exists(BasicHelper::getRootDir( true ) . $filePath . '/translate/' . $language . '_' . strtoupper($language) . '.js') )
                {
                    $GLOBALS['TL_JAVASCRIPT'][ $fileKey . '-trans-' . $language] = $filePath . '/translate/' . $language . '_' . strtoupper($language) . '.js' . self::getScriptMode();
                }
            }
        }
    }



    public static function insertScript( $scriptName, $addStylesheet = false )
    {
        if( !is_array($scriptName) )
        {
            $scriptName = array( $scriptName );
        }

        foreach($scriptName as $fileKey => $fileName)
        {
            if( is_numeric($fileKey) )
            {
                $fileKey = $fileName;
            }

            $filePathPublic = self::getScriptSource( $fileName, true, false );

            if( file_exists(BasicHelper::getRootDir( true ) . $filePathPublic) )
            {
                $filePathPublicSRC = self::getScriptSource( $fileName, true, false, true );

                echo '<script src=' . $filePathPublicSRC . '></script>';

                if( $addStylesheet )
                {
                    StyleSheetHelper::addStylesheet( $fileName );
                }
            }
        }
    }



    /**
     * Add intern script
     *
     * @param string $scriptName
     */
    public static function addInternScript( $scriptName )
    {
        $GLOBALS['TL_JAVASCRIPT']['iido_' . $scriptName ] = BundleConfig::getBundlePath( true ) . self::$scriptPathPublic . self::getActiveJavascriptLibrary() . '/iido/IIDO.' . ucfirst( $scriptName ) . '.js' . self::getScriptMode();
    }



    /**
     * Add source script
     *
     * @param string $scriptName
     * @param string|array $sourceScriptName
     */
    public static function addSourceScript( $scriptName, $sourceScriptName )
    {
        if( !is_array($sourceScriptName) )
        {
            $sourceScriptName = array( $sourceScriptName );
        }

        $filePathIntern = self::getScriptSource( $scriptName, false, true ) . '/src/';
        $filePath       = self::getScriptSource( $scriptName, true, true ) . '/src/';

        foreach( $sourceScriptName as $srcKey => $srcFileName )
        {
            if( file_exists(BasicHelper::getRootDir(true) . $filePathIntern . $srcFileName . '.min.js') )
            {
                if( is_numeric($srcKey) )
                {
                    $srcKey = $scriptName . '_' . $srcFileName;
                }

                $GLOBALS['TL_JAVASCRIPT'][ $srcKey ] = $filePath. $srcFileName . '.min.js' . self::getScriptMode();
            }
        }
    }



    /**
     * Get script source
     *
     * @param string $scriptName
     * @param bool   $public
     * @param bool   $withoutFile
     * @param bool   $publicWithoutWeb
     *
     * @return string
     */
    public static function getScriptSource( $scriptName, $public = false, $withoutFile = false, $publicWithoutWeb = false )
    {
        $strPath    = BundleConfig::getBundlePath() . self::$scriptPath;
        $subFolder  = 'lib';

        $folderVersion = self::getScriptVersion( $scriptName );

        if( !is_dir( BasicHelper::getRootDir( true ) . $strPath . $subFolder . '/' . $scriptName . '/' . $folderVersion) )
        {
            $subFolder = self::getActiveJavascriptLibrary();
        }

        $arrFiles = scan( BasicHelper::getRootDir( true ) . $strPath . $subFolder . '/' . $scriptName . '/' . $folderVersion );
        $fileName = '';

        foreach($arrFiles as $strFile)
        {
            if( preg_match('/.min.js$/', $strFile) && preg_match('/' . $scriptName . '/', $strFile) )
            {
                $fileName = $strFile;
                break;
            }
        }

        return BundleConfig::getBundlePath( $public, !$publicWithoutWeb ) . ($public ? self::$scriptPathPublic : self::$scriptPath) . $subFolder . '/' . $scriptName . '/' . $folderVersion . ($withoutFile ? '' : '/' . $fileName);
//        return BundleConfig::getBundlePath( $public ) . ($public ? self::$scriptPathPublic : self::$scriptPath) . $subFolder . '/' . $scriptName . '/' . $folderVersion . ($withoutFile ? '' : '/' . $fileName);
    }



    /**
     * Get script version
     *
     * @param string $scriptName
     *
     * @return mixed|null
     */
    public static function getScriptVersion( $scriptName )
    {
        $tableFieldPrefix   = BundleConfig::getTableFieldPrefix();
        $scriptVersion      = \Config::get( $tableFieldPrefix . 'script' . ucfirst($scriptName) );

        if( !$scriptVersion )
        {
            $strPath    = BundleConfig::getBundlePath() . self::$scriptPath;
            $subFolder  = 'lib';

            if( !is_dir( BasicHelper::getRootDir( true ) . $strPath . $subFolder . '/' . $scriptName . '/' . $scriptVersion) )
            {
                $subFolder = self::getActiveJavascriptLibrary();
            }

            $arrFolders = scan( BasicHelper::getRootDir( true ) . $strPath . $subFolder . '/' . $scriptName );

            if( in_array('addons', $arrFolders) )
            {
                unset( $arrFolders[ array_search('addons', $arrFolders ) ] );

                $arrFolders = array_values($arrFolders);
            }

            if( count($arrFolders) > 1 )
            {
                // TODO:
            }
            else
            {
                $scriptVersion = $arrFolders[0];
            }
        }

        return $scriptVersion;
    }



    /**
     * Get active javascript library
     *
     * @return bool|string
     */
    public static function getActiveJavascriptLibrary()
    {
        global $objPage;

        $objLayout  = PageHelper::getPageLayout( $objPage );

        $jquery     = ($objLayout->addJQuery)   ? TRUE : FALSE;
        $mootools   = ($objLayout->addMooTools) ? TRUE : FALSE;

        if( $jquery )
        {
            return 'jquery';
        }

        if( $mootools )
        {
            return 'mootools';
        }

        return FALSE;
    }



    /**
     * Get Script Mode
     *
     * @return string
     */
    public static function getScriptMode()
    {
        global $objPage;

        $objLayout = PageHelper::getPageLayout( $objPage );

        return ($objLayout->combineScripts ? self::$scriptModeCombine : '');
    }

}
