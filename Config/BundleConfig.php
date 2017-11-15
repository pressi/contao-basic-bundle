<?php
/*******************************************************************
 * (c) 2017 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\Config;

/**
 * Class BundleConfig
 *
 * @package IIDO\BasicBundle
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
     * @return array
     */
    public static function getBundleConfigArray() // TODO: custom order?
    {
        return array(self::getNamespace(), self::getSubNamespace(), self::getSubName(), self::getPrefix(), self::getTablePrefix(), self::getListenerName());
    }


    /**
     * Get Bundle Config Data from function name
     *
     * @param string       $funcName  Name of the get function
     * @param null|string  $funcVar   function variable
     *
     * @return string
     */
    public static function getBundleConfig( $funcName, $funcVar = null )
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
                    $funcVar    = (($funcVar === null) ? false : $funcVar);
                    $return     = self::getBundlePath( true, $funcVar );
                    break;

                default:
                    $return = "Variable / Funktion existiert nicht!";
            }

            return $return;
        }

    }


    /**
     * Get Bundle Namespace
     *
     * @return string
     */
    public static function getNamespace()
    {
        return static::$namespace;
    }



    /**
     * Get Bundle Sub-Namespace
     *
     * @return string
     */
    public static function getSubNamespace()
    {
        return static::$subNamespace;
    }



    /**
     * Get Bundle Sub Name
     *
     * @return string
     */
    public static function getSubName()
    {
        return strtolower( preg_replace('/Bundle$/', '', static::$subNamespace) );
    }



    /**
     * Get Bundle Prefix
     *
     * @return string
     */
    public static function getPrefix()
    {
        return strtolower(static::$namespace);
    }



    /**
     * Get Bundle Table Prefix
     *
     * @return string
     */
    public static function getTablePrefix()
    {
        return 'tl_' . self::getPrefix() . '_' . self::getSubName() . '_';
    }



    /**
     * Get Bundle Table Field Prefix
     *
     * @return string
     */
    public static function getTableFieldPrefix()
    {
        return strtolower( self::getNamespace() ) . ucfirst( self::getSubName() ) . '_';
    }



    /**
     * Get Bundle Listener Name
     *
     * @return string
     */
    public static function getListenerName()
    {
        return self::getPrefix() . '_' . self::getSubName();
    }



    /**
     * Get Bundle Name
     *
     * @return string
     */
    public static function getBundleName()
    {
        return static::$bundleName;
    }



    /**
     * Get Bundle Group
     *
     * @return string
     */
    public static function getBundleGroup()
    {
        return static::$bundleGroup;
    }

    /**
     * Get Bundle Path
     *
     * @return string
     */
    public static function getBundlePath( $public = false, $includeWebFolder = true )
    {
        if( $public )
        {
            return ($includeWebFolder ? 'web/' : '') .'bundles/' . self::getPrefix() . self::getSubName();
        }
        else
        {
            return 'vendor/' . self::getBundleGroup() . '/' . self::getBundleName();
        }
    }
}