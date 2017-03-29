<?php
/*******************************************************************
 * (c) 2017 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\Config;

class BundleConfig
{
    static $namespace           = 'IIDO';
    static $subNamespace        = 'BasicBundle';

    static $bundleName          = "contao-basic-bundle";
    static $bundleGroup         = "2do";



    public static function getBundleConfigArray() // TODO: custom order?
    {
        return array(self::getNamespace(), self::getSubNamespace(), self::getSubName(), self::getPrefix(), self::getTablePrefix(), self::getListenerName());
    }


    public static function getBundleConfig( $funcName )
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

                case "listener":
                case "listenername":
                    $return = self::getListenerName();
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
                    $return = self::getBundlePath( true );
                    break;

                default:
                    $return = "Variable / Funktion existiert nicht!";
            }

            return $return;
        }

    }


    public static function getNamespace()
    {
        return static::$namespace;
    }


    public static function getSubNamespace()
    {
        return static::$subNamespace;
    }


    public static function getSubName()
    {
        return strtolower( preg_replace('/Bundle$/', '', static::$subNamespace) );
    }


    public static function getPrefix()
    {
        return strtolower(static::$namespace);
    }


    public static function getTablePrefix()
    {
        return $tablePrefix = 'tl_' . self::getPrefix() . '_';
    }


    public static function getListenerName()
    {
        return self::getPrefix() . '_' . self::getSubName();
    }


    public static function getBundleName()
    {
        return static::$bundleName;
    }


    public static function getBundleGroup()
    {
        return static::$bundleGroup;
    }


    public static function getBundlePath( $public = false )
    {
        if( $public )
        {
            return 'web/bundles/' . self::getPrefix() . self::getSubName();
        }
        else
        {
            return 'vendor/' . self::getBundleGroup() . '/' . self::getBundleName();
        }
    }
}