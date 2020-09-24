<?php
/*******************************************************************
 * (c) 2020 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\Config;


use Contao\Config;
use Contao\CoreBundle\Monolog\ContaoContext;
use Contao\Files;
use Contao\System;
use IIDO\BasicBundle\Helper\BasicHelper;
use Symfony\Component\Yaml\Yaml;


class IIDOConfig
{

    protected $configFilePath   = 'config/iido.basic.config.yml';
    protected static $filePath  = 'config/iido.basic.config.yml';


    /**
     * Object instance (Singleton)
     * @var IIDOConfig
     */
    protected static $objInstance;


    /**
     * Modification indicator
     * @var boolean
     */
    protected $blnIsModified = false;


    /**
     * Local file existance
     * @var boolean
     */
    protected static $blnHasLcf;


    /**
     * Data
     * @var array
     */
    protected $arrData = [];


    /**
     * Cache
     * @var array
     */
    protected $arrCache = [];


    /**
     * Root dir
     * @var string
     */
    protected $strRootDir;



    /**
     * Prevent direct instantiation (Singleton)
     */
    protected function __construct()
    {
        $this->strRootDir = BasicHelper::getRootDir( true );

        if( !file_exists( $this->strRootDir . DIRECTORY_SEPARATOR . $this->configFilePath) )
        {
            touch($this->strRootDir . DIRECTORY_SEPARATOR . $this->configFilePath);
        }
    }



    /**
     * Automatically save the iido basic configuration
     */
    public function __destruct()
    {
        if( $this->blnIsModified )
        {
            $this->save();
        }
    }



    /**
     * Prevent cloning of the object (Singleton)
     */
    final public function __clone()
    {
    }



    /**
     * Return the current object instance (Singleton)
     *
     * @return static The object instance
     */
    public static function getInstance()
    {
        if( static::$objInstance === null )
        {
            static::$objInstance = new static();
            static::$objInstance->initialize();
        }

        return static::$objInstance;
    }



    /**
     * Load all configuration files
     */
    protected function initialize()
    {
        $fileName = $this->getConfigFilePath();
        if( !file_exists( $this->strRootDir . DIRECTORY_SEPARATOR . $fileName) )
        {
            touch($this->strRootDir . DIRECTORY_SEPARATOR . $fileName);
        }

        if( static::$blnHasLcf === null )
        {
            static::preload();
        }

        // Include the iido basic configuration file again
        if( static::$blnHasLcf )
        {
            static::loadParameters();
        }
    }



    /**
     * Mark the object as modified
     */
    protected function markModified()
    {
        // Return if marked as modified already
        if( $this->blnIsModified === true )
        {
            return;
        }

        $this->blnIsModified = true;

        // Parse the iido basic configuration file
        if( static::$blnHasLcf )
        {
            $this->arrData = Yaml::parseFile( $this->strRootDir . $this->configFilePath );
        }
    }



    /**
     * Save the iido basic configuration file
     */
    public function save()
    {
        $strFile = Yaml::dump( $this->arrData );

//        file_put_contents( $this->strRootDir . $this->configFilePath, $fileContent );

        $strTemp = md5(uniqid(mt_rand(), true));

        // Write to a temp file first
        $objFile = fopen($this->strRootDir . 'system/tmp/' . $strTemp, 'w');
        fwrite($objFile, $strFile);
        fclose($objFile);

        // Make sure the file has been written (see #4483)
        if (!filesize($this->strRootDir . 'system/tmp/' . $strTemp))
        {
            $logger = System::getContainer()->get('monolog.logger.contao');
            $logger->log(\Psr\Log\LogLevel::ERROR, 'The iido basic configuration file could not be written. Have your reached your quota limit?', array('contao' => new ContaoContext(__METHOD__, TL_ERROR)));

            return;
        }

        $this->Files = Files::getInstance();

        // Adjust the file permissions (see #8178)
        $this->Files->chmod('system/tmp/' . $strTemp, 0666 & ~umask());

        // Then move the file to its final destination
        $this->Files->rename('system/tmp/' . $strTemp, $this->configFilePath);

        // Reset the Zend OPcache
        if (\function_exists('opcache_invalidate'))
        {
            opcache_invalidate($this->strRootDir . $this->configFilePath, true);
        }

        // Recompile the APC file (thanks to Trenker)
        if (\function_exists('apc_compile_file') && !ini_get('apc.stat'))
        {
            apc_compile_file($this->strRootDir . $this->configFilePath);
        }

        $this->blnIsModified = false;
    }



    /**
     * Return true if the installation is complete
     *
     * @return boolean True if the installation is complete
     */
    public static function isComplete()
    {
        return static::$blnHasLcf !== null && Config::has('licenseAccepted');
    }



    /**
     * Add a configuration variable to the local configuration file
     *
     * @param string $strKey   The full variable name
     * @param mixed  $varValue The configuration value
     */
    public function add($strKey, $varValue, $strTable = 'tl_iido_config')
    {
        $this->markModified();
        $this->arrData[ $strTable ][ $strKey ] = $varValue; //$this->escape($varValue);
    }



    /**
     * Alias for IIDOConfig::add()
     *
     * @param string $strKey   The full variable name
     * @param mixed  $varValue The configuration value
     */
    public function update($strKey, $varValue, $strTable = 'tl_iido_config')
    {
        $this->add($strKey, $varValue, $strTable);
    }



    /**
     * Remove a configuration variable
     *
     * @param string $strKey The full variable name
     */
    public function delete( $strKey, $strTable = 'tl_iido_config' )
    {
        $this->markModified();
        unset( $this->arrData[ $strTable ][ $strKey ] );
    }



    /**
     * Check whether a configuration value exists
     *
     * @param string $strKey   The short key
     * @param string $strTable The table name
     *
     * @return boolean True if the configuration value exists
     */
    public static function has($strKey, $strTable = 'tl_iido_config')
    {
        return \array_key_exists($strKey, $GLOBALS['TL_IIDO_CONFIG'][ $strTable ]);
    }



    /**
     * Return a configuration value
     *
     * @param string $strKey   The short key
     * @param string $strTable The table name
     *
     * @return mixed|null The configuration value
     */
    public static function get($strKey, $strTable = 'tl_iido_config')
    {
        static::loadParameters( true );

        return $GLOBALS['TL_IIDO_CONFIG'][ $strTable ][ $strKey ] ?? null;
    }



    /**
     * Temporarily set a configuration value
     *
     * @param string $strKey   The short key
     * @param string $varValue The configuration value
     * @param string $strTable The table name
     */
    public static function set($strKey, $varValue, $strTable = 'tl_iido_config')
    {
        $GLOBALS['TL_IIDO_CONFIG'][ $strTable ][ $strKey ] = $varValue;
    }



    /**
     * Permanently set a configuration value
     *
     * @param string $strKey   The short key or full variable name
     * @param mixed  $varValue The configuration value
     */
    public static function persist($strKey, $varValue, $strTable = 'tl_iido_config')
    {
        $objConfig = static::getInstance();

        $objConfig->add($strKey, $varValue, $strTable);
    }



    /**
     * Permanently remove a configuration value
     *
     * @param string $strKey The short key or full variable name
     */
    public static function remove($strKey, $strTable = 'tl_iido_config')
    {
        $objConfig = static::getInstance();

        $objConfig->delete($strKey, $strTable);
    }



    /**
     * Preload the default and local configuration
     */
    public static function preload()
    {
        $rootDir = BasicHelper::getRootDir( true );

        // Include the local configuration file
        if( ($blnHasLcf = file_exists($rootDir . self::getFilePath())) === true )
        {
            static::loadParameters();
        }

        static::$blnHasLcf = $blnHasLcf;
    }



    /**
     * Override the database and SMTP parameters
     */
    protected static function loadParameters( $includeGlobals = false )
    {
        $fileName   = self::getFilePath();
        $rootDir    = BasicHelper::getRootDir();

        if( !file_exists( $rootDir . DIRECTORY_SEPARATOR . $fileName) )
        {
            touch($rootDir . DIRECTORY_SEPARATOR . $fileName);
        }

        $arrConfig = Yaml::parseFile( BasicHelper::getRootDir( true ) . self::getFilePath() );

        if( is_array($arrConfig) && count($arrConfig) )
        {
            foreach( $arrConfig as $strTable => $arrData )
            {
                foreach( $arrData as $key => $value )
                {
                    if( $includeGlobals && $GLOBALS['TL_IIDO_CONFIG'][ $strTable ][ $key ] )
                    {
                        $value = $GLOBALS['TL_IIDO_CONFIG'][ $strTable ][ $key ];
                    }

                    $GLOBALS['TL_IIDO_CONFIG'][ $strTable ][ $key ] = $value;
                }
            }
        }
    }



    /**
     * Escape a value depending on its type
     *
     * @param mixed $varValue The value
     *
     * @return mixed The escaped value
     */
    protected function escape($varValue)
    {
        if (is_numeric($varValue) && $varValue < PHP_INT_MAX && !preg_match('/e|^[+-]?0[^.]/', $varValue))
        {
            return $varValue;
        }

//        if (\is_bool($varValue))
//        {
//            return $varValue ? 'true' : 'false';
//        }

//        if ($varValue == 'true')
//        {
//            return 'true';
//        }

//        if ($varValue == 'false')
//        {
//            return 'false';
//        }

        return "'" . str_replace('\\"', '"', preg_replace('/[\n\r\t ]+/', ' ', addslashes($varValue))) . "'";
    }



    public static function getLink( string $strTable = 'tl_iido_config'): string
    {
        $router = System::getContainer()->get('router');
        /* @var $router \Symfony\Component\Routing\RouterInterface */

        return $router->generate('contao_backend', ['do' => 'config-settings', 'table' => $strTable, 'act' => 'edit', 'id' => 1, 'rt' => REQUEST_TOKEN, 'ref' => TL_REFERER_ID]);
    }



    public function getConfigFilePath()
    {
        return $this->configFilePath;
    }



    public static function getFilePath()
    {
        return static::$filePath;
    }
}