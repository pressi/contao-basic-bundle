<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\Helper;


use IIDO\BasicBundle\Config\BundleConfig;


/**
 * Class Script Helper
 * @package IIDO\BasicBundle
 */
class ScriptHelper
{
    protected static $scriptPath        = '/src/Resources/public/javascript/';


    protected static $scriptPathPublic  = '/javascript/';


    protected static $scriptModeCombine = '|static';



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

        return $hasAnimation;
    }



    public static function hasPageIsotope()
    {
        global $objPage;

        $hasIsotope = false;

        if( $objPage->addIsotope )
        {
            $hasIsotope = true;
        }

        return $hasIsotope;
    }



    /**
     * @param string|array $scriptName
     */
    public static function addScript( $scriptName, $addStylesheet = false )
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

            $filePath       = self::getScriptSource( $fileName, true );
            $filePathIntern = self::getScriptSource( $fileName );

            if( file_exists(BasicHelper::getRootDir( true ) . $filePathIntern) )
            {
                $GLOBALS['TL_JAVASCRIPT'][ $fileKey ] = $filePath . self::getScriptMode();

                if( $addStylesheet )
                {
                    StylesheetHelper::addStylesheet( $fileName );
                }
            }
        }
    }



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
            if( file_exists(BasicHelper::getRootDir(true) . $filePathIntern . $srcFileName) )
            {
                if( is_numeric($srcKey) )
                {
                    $srcKey = $scriptName . '_' . $srcFileName;
                }

                $GLOBALS['TL_JAVASCRIPT'][ $srcKey ] = $filePath . $srcFileName . '.min.js' . self::getScriptMode();
            }
        }
    }



    public static function getScriptSource( $scriptName, $public = false, $withoutFile = false )
    {
        $strPath    = BundleConfig::getBundlePath() . self::$scriptPath;
        $subFolder  = 'lib';

        if( !is_dir( $strPath . $subFolder . '/' . $scriptName) )
        {
            $subFolder  = self::getActiveJavascriptLibrary();
        }

        $folderVersion = self::getScriptVersion( $scriptName );

        $arrFiles = scan( BasicHelper::getRootDir( true ) . $strPath . $subFolder . '/' . $scriptName . '/' . $folderVersion );
        $fileName = '';

        foreach($arrFiles as $strFile)
        {
            if( preg_match('/.min.js$/', $strFile) )
            {
                $fileName = $strFile;
                break;
            }
        }

        return BundleConfig::getBundlePath( $public ) . ($public ? self::$scriptPathPublic : self::$scriptPath) . $subFolder . '/' . $scriptName . '/' . $folderVersion . ($withoutFile ? '' : '/' . $fileName);
    }



    public static function getScriptVersion( $scriptName )
    {
        $tableFieldPrefix = BundleConfig::getTableFieldPrefix();
        return \Config::get( $tableFieldPrefix . 'script' . ucfirst($scriptName) );
    }



    public static function getActiveJavascriptLibrary()
    {
        global $objPage;

        $objLayout  = BasicHelper::getPageLayout( $objPage );

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



    public static function getScriptMode()
    {
        global $objPage;

        $objLayout = BasicHelper::getPageLayout( $objPage );

        return ($objLayout->combineScripts ? self::$scriptModeCombine : '');
    }

}
