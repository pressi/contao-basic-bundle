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


use Contao\StringUtil;
use IIDO\BasicBundle\Config\BundleConfig;


class StyleSheetHelper
{
    protected static $stylesPathFolder      = 'styles';


    protected static $variablesFilePath     = 'files/%s/%s/_variables.scss';
    protected static $masterFilePath        = 'files/master/scss/master.scss';

    protected static $customPath            = 'files/%s/%s/';
    protected static $configFilePath        = 'files/config/styles/variables_%s.scss';


    protected static $stylesheetPath        = '/Resources/public/%s/';


    protected static $stylesheetPathPublic  = '/%s/';


    protected static $stylesheetModeCombine = '||static';



    public static function addDefaultPageStyleSheets()
    {
        $rootAlias          = PageHelper::getRootPageAlias( true );
        $rootPath           = BasicHelper::getRootDir( true );
        $customStyleFiles   = ConfigHelper::get('cssCustomFiles');

        $stylesPathPublic   = BundleConfig::getBundlePath( true ) . sprintf(self::$stylesheetPathPublic, self::$stylesPathFolder);
        $includeMainFile    = file_exists($rootPath . sprintf(self::$customPath, $rootAlias, self::$stylesPathFolder) . 'main.scss');

        if( !file_exists($rootPath . sprintf(self::$configFilePath, $rootAlias)) )
        {
            $arrConfigPath = explode('/', self::$configFilePath);

            if( !is_dir($rootPath . 'files/' . $arrConfigPath[1]) )
            {
                mkdir( $rootPath . 'files/' . $arrConfigPath[1]);
                mkdir( $rootPath . 'files/' . $arrConfigPath[1] . '/' . $arrConfigPath[2]);
            }

            if( !is_dir($rootPath . 'files/' . $arrConfigPath[1] . '/' . $arrConfigPath[2]) )
            {
                mkdir( $rootPath . 'files/' . $arrConfigPath[1] . '/' . $arrConfigPath[2]);
            }

            touch( $rootPath . sprintf(self::$configFilePath, $rootAlias) );
        }

        if( $includeMainFile )
        {
            $arrCustomStyleFiles = StringUtil::trimsplit(',', $customStyleFiles);

            if( count($arrCustomStyleFiles) )
            {
                foreach( $arrCustomStyleFiles as $strFileName )
                {
                    if( !preg_match('/.css$/', $strFileName) )
                    {
                        $strFileName .= '.css';
                    }

                    $keyName = preg_replace(['/.css$/'], [''], $strFileName);

                    if( file_exists($rootPath . sprintf(self::$customPath, $rootAlias, self::$stylesPathFolder) . $strFileName) )
                    {
                        $GLOBALS['TL_USER_CSS']['iido_custom_' . $keyName] = $stylesPathPublic . $strFileName .  self::$stylesheetModeCombine;
                    }

                    $strSCSSFileName = preg_replace('/.css$/', '.scss', $strFileName);

                    if( !$includeMainFile && file_exists($rootPath . printf(self::$customPath, $rootAlias, self::$stylesPathFolder) . $strSCSSFileName) )
                    {
                        $GLOBALS['TL_USER_CSS']['iido_custom_style_' . $keyName] = $stylesPathPublic . $strSCSSFileName . self::$stylesheetModeCombine;
                    }
                }
            }

            $GLOBALS['TL_USER_CSS']['iido_main_self'] = sprintf(self::$customPath, $rootAlias, self::$stylesPathFolder) . 'main.scss' . self::$stylesheetModeCombine;
        }
        else
        {
            $GLOBALS['TL_USER_CSS']['iido_main_master'] = sprintf(self::$customPath, 'master', 'scss') . 'onlyMasters.scss' . self::$stylesheetModeCombine;
        }
    }



    public static function getValueFromVariables( $varName, $renderForOptions = false )
    {
        $varValue       = '';
        $rootAlias      = PageHelper::getRootPageAlias( true );
        $rootDir        = BasicHelper::getRootDir( true );
//        $filePath       = BasicHelper::replacePlaceholder( self::$variablesFilePath, true );
        $filePath       = sprintf(self::$variablesFilePath, $rootDir, $rootAlias);

        if( file_exists($rootDir . $filePath) )
        {
            $fileContent    = file_get_contents( $rootDir . $filePath );
            $arrFileRows    = explode("\n", $fileContent);

            if( count($arrFileRows) )
            {
                foreach( $arrFileRows as $fileRow )
                {
                    $fileRow = trim($fileRow);

                    if( 0 === strpos($fileRow, '$' . $varName) )
                    {
                        if( FALSE !== strpos($fileRow, '(') )
                        {
                            $fileRow    = trim( preg_replace(['/^\$' . $varName .':/', '/;$/'], '', $fileRow));
                            $varValue   = self::renderFileRowAsArray( $fileRow, $renderForOptions );
                        }
                    }
                }
            }
        }

        return $varValue;
    }



    protected static function renderFileRowAsArray( $fileRow, $renderForOptions = false )
    {
        $arrValue   = [];
        $fileRow    = preg_replace(['/^\(/', '/\)$/'], '', $fileRow);
        $arrRow     = \StringUtil::trimsplit(',', $fileRow);

        foreach( $arrRow as $row )
        {
            $arrRowData = \StringUtil::trimsplit(':', $row);

            $valueKey   = trim( preg_replace(["/^\'/", "/\'$/"], '', $arrRowData[0]) );
            $valueValue = preg_replace('/^\#/', '', $arrRowData[1]);

            if( $renderForOptions )
            {
                $arrValue[ $valueValue ] = $valueKey;
            }
            else
            {
                $arrValue[ $valueKey ] = $arrRowData[1];
            }
        }

        return $arrValue;
    }



    public static function addStylesheet( $stylesheetName )
    {
        if( !is_array($stylesheetName) )
        {
            $stylesheetName = array( $stylesheetName );
        }

        foreach($stylesheetName as $fileKey => $fileName)
        {
            if( is_numeric($fileKey) )
            {
                $fileKey = $fileName;
            }

            $filePath       = self::getStylesheetSource( $fileName, true );
            $filePathIntern = self::getStylesheetSource( $fileName );

            if( file_exists(BasicHelper::getRootDir( true ) . $filePathIntern) )
            {
                $GLOBALS['TL_CSS'][ $fileKey ] = $filePath . self::getStylesheetMode();
            }
        }
    }



    public static function addMasterStylesheet( $stylesheetName )
    {
        $GLOBALS['TL_USER_CSS'][ 'master_' . $stylesheetName ] = 'files/master/scss/' . $stylesheetName . '.scss' . self::getStylesheetMode();
    }



    public static function addTemplateStylesheet( $stylesheetName )
    {
        $GLOBALS['TL_USER_CSS'][ 'template_' . $stylesheetName ] = 'files/master/scss/templates/' . $stylesheetName . '.scss' . self::getStylesheetMode();
    }



    public static function addThemeStyle( $stylesheetName, $stylesheets)
    {
        $arrStyleSheets = $stylesheets;

        if( !is_array($arrStyleSheets) )
        {
            $arrStyleSheets = explode(",", $stylesheets);
        }

        if( !is_array($stylesheetName) )
        {
            $stylesheetName = array( $stylesheetName );
        }

        foreach($stylesheetName as $fileKey => $fileName)
        {
            if( is_numeric( $fileKey ) )
            {
                $fileKey = $fileName;
            }

            $filePath       = self::getStylesheetSource( $fileName, true, true );
            $filePathIntern = self::getStylesheetSource( $fileName, false, true );

            foreach($arrStyleSheets as $styleSheet)
            {
                $styleSheetKey = preg_replace('/.(scss|css)$/', '', $styleSheet);

                if( !preg_match('/.scss$/', $styleSheet) )
                {
                    $styleSheet = $styleSheet . '.scss';
                }

                if( file_exists( BasicHelper::getRootDir( true ) . $filePathIntern . '/theme/' . $styleSheet ) )
                {
                    $GLOBALS['TL_CSS'][ $fileKey . '-' . $styleSheetKey ] = $filePath . '/theme/' . $styleSheet . self::getStylesheetMode();
                }
                elseif( file_exists( BasicHelper::getRootDir( true ) . $filePathIntern . '/theme/' . preg_replace('/.scss$/', '.css', $styleSheet) ) )
                {
                    $GLOBALS['TL_CSS'][ $fileKey . '-' . $styleSheetKey ] = $filePath . '/theme/' . preg_replace('/.scss$/', '.css', $styleSheet) . self::getStylesheetMode();
                }
            }
        }
    }



    public static function getStylesheetSource( $scriptName, $public = false, $withoutFile = false )
    {
        $fileExtension  = 'scss';
        $strPath        = BundleConfig::getBundlePath() . sprintf(self::$stylesheetPath, $fileExtension);
        $folderVersion  = ScriptHelper::getScriptVersion( $scriptName );
        $arrFiles       = [];

        if( is_dir(BasicHelper::getRootDir( true ) . $strPath . $scriptName . '/' . $folderVersion) )
        {
            $arrFiles = scan( BasicHelper::getRootDir( true ) . $strPath . $scriptName . '/' . $folderVersion );
            $fileName = '';
        }

        if( !count($arrFiles) )
        {
            $strPath    = preg_replace('/\/scss\//', '/css', $strPath);
            $arrFiles   = scan( BasicHelper::getRootDir( true ) . $strPath . '/' . $scriptName . '/' . $folderVersion );

            $fileExtension = 'css';
        }

        foreach($arrFiles as $strFile)
        {
            if( preg_match('/.min.' . $fileExtension . '$/', $strFile) && !preg_match('/.map$/', $strFile) )
            {
                $fileName = $strFile;
                break;
            }
        }

        $filePath = BundleConfig::getBundlePath( $public ) . ($public ? sprintf(self::$stylesheetPathPublic, $fileExtension) : sprintf(self::$stylesheetPath, $fileExtension)) . $scriptName . '/' . $folderVersion;

//        if( !file_exists( BasicHelper::getRootDir(true) . $filePath . DIRECTORY_SEPARATOR . $fileName) )
//        {
//            $fileName =
//        }

        if( $fileExtension === 'css' )
        {
            $filePath = preg_replace('/\/scss\//', '/css/', $filePath);
        }

        return $filePath . ($withoutFile ? '' : '/' . $fileName);
    }



    public static function getStylesheetMode()
    {
        global $objPage;

        $objLayout = PageHelper::getPageLayout( $objPage );

        return ($objLayout->combineScripts ? self::$stylesheetModeCombine : '');
    }



    public static function addConfigVar( $varName, $varValue, $fileName )
    {
        $strNewFile = '';
        $insert     = false;
        $objFile    = fopen(BasicHelper::getRootDir( true ) . sprintf(self::$configFilePath, $fileName), 'r+');

        while( !feof($objFile) )
        {
            $fileLine = fgets($objFile);

            if( !$fileLine || !strlen($fileLine) )
            {
                continue;
            }

            $arrParts = explode(':', $fileLine);

            $key    = preg_replace('/^\$/', '', trim($arrParts[0]));
//            $value  = preg_replace('/;$/', '', trim($arrParts[1]));

            if( $key === $varName )
            {
                $insert = true;
                $value = $varValue;

                $strNewFile .= '$' . $key . ": '" . $value . "';\n";
            }
            else
            {
                $strNewFile .= $fileLine . "\n";
            }
        }

        if( !$insert )
        {
            $strNewFile .= '$' . $varName . ": '" . $varValue . "';";
        }

        fseek($objFile, 0);
        fwrite($objFile, $strNewFile);
        fclose($objFile);
    }
}