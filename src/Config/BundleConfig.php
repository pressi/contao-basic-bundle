<?php
/*******************************************************************
 * (c) 2020 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\Config;


use Contao\System;


/**
 * Bundle Config Class
 *
 * @author Stephan Preßl <development@prestep.at>
 */
class BundleConfig
{
    static $namespace           = 'IIDO';
    static $subNamespace        = 'BasicBundle';

    static $bundleName          = "contao-basic-bundle";
    static $bundleGroup         = "2do";



    /**
     * Get all Bundle Data in one array
     *
     * @param bool $includeListener
     *
     * @return array
     * @TODO custom return order?!
     */
    public static function getBundleConfigArray( $includeListener = true ): array
    {
        return array(self::getNamespace(), self::getSubNamespace(), self::getSubName(), self::getPrefix(), self::getTablePrefix( true ), self::getListenerName( $includeListener ));
    }



    /**
     * Get Bundle Config Data from function name
     *
     * @param string       $funcName  Name of the get function
     * @param null|string  $funcVar   function variable
     *
     * @return string
     */
    public static function getBundleConfig( $funcName, $funcVar = null ): string
    {
        $functionName = 'get' . $funcName;

        if( function_exists($functionName) )
        {
            return self::$functionName();
        }
        else
        {
            $funcName = preg_replace('/_/', '', $funcName);
            switch( $funcName )
            {
                case "namespace":
                    $return = self::getNamespace();
                    break;

                case "subnamespace":
                    $return = self::getSubNamespace();
                    break;

                case "subname":
                    $return = self::getSubName();
                    break;

                case "prefix":
                    $return = self::getPrefix();
                    break;

                case "table":
                case "tableprefix":
                    $return = self::getTablePrefix();
                    break;

                case "field":
                case "fieldprefix":
                case "tablefield":
                case "tablefieldprefix":
                    $return = self::getTableFieldPrefix();
                    break;

                case "listener":
                case "listenername":
                    $funcVar    = (($funcVar === null) ? false : $funcVar);
                    $return     = self::getListenerName( $funcVar );
                    break;

                case "bundle":
                case "bundlename":
                    $return = self::getBundleName();
                    break;

                case "group":
                case "groupName":
                    $return = self::getBundleGroup();
                    break;

                case "path":
                case "bundlepath":
                    $return = self::getBundlePath();
                    break;

                case "bundlepathpublic":
                case "publicbundlepath":
                case "bundlePathPublic":
                case "publicBundlePath":
                    $funcVar = (($funcVar === NULL) ? FALSE : $funcVar);
                    $return  = self::getBundlePath(TRUE, $funcVar);
                    break;

                default:
                    $return = "Variable / Funktion " . $funcName ." existiert nicht!";
            }

            return $return;
        }

    }



    /**
     * Get Bundle Namespace
     *
     * @return string
     */
    public static function getNamespace(): string
    {
        return static::$namespace;
    }



    /**
     * Get Bundle Sub-Namespace
     *
     * @return string
     */
    public static function getSubNamespace(): string
    {
        return static::$subNamespace;
    }



    /**
     * Get Bundle Sub Name
     *
     * @return string
     */
    public static function getSubName(): string
    {
        return strtolower( preg_replace('/Bundle$/', '', static::$subNamespace) );
    }



    /**
     * Get Bundle Prefix
     *
     * @return string
     */
    public static function getPrefix(): string
    {
        return strtolower(static::$namespace);
    }



    /**
     * Get Bundle Table Prefix
     *
     * @param boolean $includeSubname
     *
     * @return string
     */
    public static function getTablePrefix( $includeSubname = true ): string
    {
        return 'tl_' . self::getPrefix() . '_' . ( $includeSubname ? self::getSubName() . '_' : '');
    }



    /**
     * Get Bundle Table Field Prefix
     *
     * @return string
     */
    public static function getTableFieldPrefix(): string
    {
        return strtolower( self::getNamespace() ) . ucfirst( self::getSubName() ) . '_';
    }



    /**
     * Get Bundle Listener Name
     *
     * @param bool $includeListener
     *
     * @return string
     */
    public static function getListenerName( $includeListener = false): string
    {
        return self::getPrefix() . '_' . self::getSubName() . ($includeListener ? '.listener' : '');
    }



    /**
     * Get Bundle Name
     *
     * @return string
     */
    public static function getBundleName(): string
    {
        return static::$bundleName;
    }



    /**
     * Get Bundle Group
     *
     * @return string
     */
    public static function getBundleGroup(): string
    {
        return static::$bundleGroup;
    }



    /**
     * Get Bundle Path
     *
     * @param boolean $public
     * @param boolean $includeWebFolder
     *
     * @return string
     */
    public static function getBundlePath($public = FALSE, $includeWebFolder = TRUE): string
    {
        if( $public )
        {
            return ($includeWebFolder ? 'web/' : '') . 'bundles/' . self::getPrefix() . self::getSubName();
        }
        else
        {
            $rootDir    = self::getRootDir();
            $bundleName = self::getBundleGroup() . '/' . self::getBundleName();
            $addon      = '';

            if( is_dir($rootDir . '/vendor/' . $bundleName . '/src') )
            {
                $addon = '/src';
            }

            return 'vendor/' . $bundleName . $addon;
        }
    }



    /**
     * Get Table name from File name
     *
     * @param string $fileName
     *
     * @return string
     */
    public static function getFileTable( $fileName ): string
    {
        return self::getTableName( $fileName);
    }



    /**
     * Get Table name form File name
     *
     * @param string $fileName
     *
     * @return string
     */
    public static function getTableName( $fileName ): string
    {
        $arrParts       = explode("/", $fileName);
        $arrFileParts   = explode(".", array_pop( $arrParts ));

        array_pop( $arrFileParts );

        $fileName       = implode(".", $arrFileParts);

        return $fileName;
    }



    /**
     * Get Table Class form Table name
     *
     * @param string $strTable
     *
     * @return string
     * @TODO: überarbeiten!!!!!
     */
    public static function getTableClass( $strTable ): string
    {
        $tableClass     = preg_replace(array('/^Iido/', '/Model$/'), '', array_pop(explode("\\", \Model::getClassFromTable( $strTable ))));
        $arrClass       = preg_split('/(?=[A-Z])/', lcfirst($tableClass));

        $iidoTable      = ((preg_match('/^tl_iido/', $strTable)) ? TRUE : FALSE);
        $newTableClass  = (($iidoTable) ? 'IIDO\\' : 'IIDO\\' . self::getSubNamespace() . '\\Table\\');

        foreach( $arrClass as $i => $class)
        {
            $newTableClass .= ucfirst($class);

            if( $i === 0 )
            {
                if( $iidoTable )
                {
                    $newTableClass .= 'Bundle\\Table\\';
                }
            }

            if( $i === (count($arrClass) - 1) )
            {
                $newTableClass .= 'Table';
            }
        }

        if( static::$namespace !== "IIDO" && preg_match('/^IIDO/', $newTableClass) )
        {
            $newTableClass = preg_replace('/^IIDO/', strtoupper(static::$namespace), $newTableClass);
        }

        return $newTableClass;
    }



    /**
     * Get Contao Version
     *
     * @return string
     */
    public static function getContaoVersion(): string
    {
        $packages = System::getContainer()->getParameter('kernel.packages');
        return $packages['contao/core-bundle'];
    }



    /**
     * @param $bundleName
     *
     * @return bool
     */
    public static function isActiveBundle( $bundleName ): bool
    {
        $packages = System::getContainer()->getParameter('kernel.packages');
        return key_exists($bundleName, $packages);
    }



    /**
     * @param $includeSlash
     *
     * @return string
     */
    public static function getRootDir( $includeSlash = false ): string
    {
        return dirname(System::getContainer()->getParameter('kernel.root_dir')) . ($includeSlash ? '/' : '');
    }
}