<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\Model;



use IIDO\BasicBundle\Helper\BasicHelper;


/**
 * Class WebsiteStyleModel - Fake Model
 *
 * @package IIDO\BasicBundle\Model
 */
class WebsiteStyleModel
{
    static $excludeFilesFolders     = ['master'];

    static $configFile              = '/scss/config/variables.scss';

    static $configFiles             = ['ext-variables', 'functions'];



    public static function findAll()
    {
        $arrCollection  = array();
        $arrFolders     = scan('files');
        $rootDir        = BasicHelper::getRootDir( true );

        if( count($arrFolders) )
        {
            foreach( $arrFolders as $strFolder )
            {
                if( in_array($strFolder, self::$excludeFilesFolders) )
                {
                    continue;
                }

                if( file_exists($rootDir . 'files/' . $strFolder . self::$configFile ) )
                {
                    $objRootPage = \PageModel::findOneByAlias( $strFolder );

                    if( $objRootPage )
                    {
                        $arrCollection[] = self::getWebsiteStyleObject( $objRootPage, $strFolder );
                    }
                }
            }
        }

        return $arrCollection;
    }



    public static function checkIfStylesIsConfigured( $rootAlias )
    {
        $rootDir    = BasicHelper::getRootDir( true );
        $strPath    = $rootDir . 'files/' . $rootAlias . self::$configFile;

        if( is_file($strPath) )
        {
            return true;
        }

        return false;
    }



    public static function addNewConfigFile( $rootAlias )
    {
        $rootDir    = BasicHelper::getRootDir( true );
        $arrParts   = explode("/", self::$configFile);
        $strPath    = $rootDir . 'files/' . $rootAlias;

        array_pop( $arrParts );

        foreach( $arrParts as $strPart )
        {
            $strPath = $strPath . '/' . $strPart;

            if( !is_dir( $strPath ) )
            {
                mkdir( $strPath );
            }
        }

        copy($rootDir . 'files/master' . self::$configFile, $rootDir . 'files/' . $rootAlias . self::$configFile );

        foreach( self::$configFiles as $configFile )
        {
            $configFileName = preg_replace('/variables.scss$/', $configFile . '.scss', self::$configFile);

            if( !file_exists($rootDir . 'files/' . $rootAlias . $configFileName) && file_exists($rootDir . 'files/master'. $configFileName) )
            {
                copy($rootDir . 'files/master' . $configFileName, $rootDir . 'files/' . $rootAlias . $configFileName );
            }
        }
    }



    public static function deleteConfigFile( $rootAlias )
    {
        $rootDir    = BasicHelper::getRootDir( true );

        unlink( self::getConfigFilePath( $rootAlias ) );

        foreach( self::$configFiles as $configFile )
        {
            $configFileName = preg_replace('/variables.scss$/', $configFile . '.scss', self::$configFile);

            if( file_exists($rootDir . 'files/' . $rootAlias . $configFileName) )
            {
                unlink($rootDir . 'files/' . $rootAlias . $configFileName );
            }
        }
    }



    public static function getConfigFilePath( $rootAlias, $fileName = '' )
    {
        $filePath = BasicHelper::getRootDir( true ) . 'files/' . $rootAlias . self::$configFile;

        if( $fileName )
        {
            $filePath = preg_replace('/\/([a-z]{1,}).scss$/', '/' . $fileName . '.scss', $filePath);
        }

        return $filePath;
    }



    /**
     * @param \PageModel $objRootPage
     * @param string     $strFolder
     *
     * @return \stdClass
     */
    protected static function getWebsiteStyleObject( $objRootPage, $strFolder )
    {
        $object = new \stdClass();

        $object->id         = $objRootPage->id;
        $object->name       = $objRootPage->title;
        $object->language   = $objRootPage->language;

        return $object;
    }
}