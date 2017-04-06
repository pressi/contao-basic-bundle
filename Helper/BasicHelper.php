<?php
/******************************************************************
 *
 * (c) 2015 Stephan Preßl <development@prestep.at>
 * All rights reserved
 *
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *
 ******************************************************************/

namespace IIDO\BasicBundle\Helper;


/**
 * Class Helper
 * @package IIDO\BasicBundle
 */
class BasicHelper extends \Frontend
{

    /**
     * Compile a color value and return a hex or rgba color
     * @param mixed
     * @param boolean
     * @param array
     * @return string
     */
    public static function compileColor($color, $blnWriteToFile=false, $vars=array())
    {
        if (!is_array($color))
        {
            return ((strlen($color)) ? '#' . self::shortenHexColor($color) : 'transparent');
        }
        elseif (!isset($color[1]) || empty($color[1]))
        {
            if($color[0] == "")
            {
                return "transparent";
            }

            return '#' . self::shortenHexColor($color[0]);
        }
        else
        {
            return 'rgba(' . implode(',', self::convertHexColor($color[0], $blnWriteToFile, $vars)) . ','. ($color[1] / 100) .')';
        }
    }


    /**
     * Try to shorten a hex color
     * @param string
     * @return string
     */
    public static function shortenHexColor($color)
    {
        if ($color[0] == $color[1] && $color[2] == $color[3] && $color[4] == $color[5])
        {
            return $color[0] . $color[2] . $color[4];
        }

        return $color;
    }


    /**
     * Convert hex colors to rgb
     * @param string
     * @param boolean
     * @param array
     * @return array
     * @see http://de3.php.net/manual/de/function.hexdec.php#99478
     */
    public static function convertHexColor($color, $blnWriteToFile=false, $vars=array())
    {
        // Support global variables
        if (strncmp($color, '$', 1) === 0)
        {
            if (!$blnWriteToFile)
            {
                return array($color);
            }
            else
            {
                $color = str_replace(array_keys($vars), array_values($vars), $color);
            }
        }

        $rgb = array();

        // Try to convert using bitwise operation
        if (strlen($color) == 6)
        {
            $dec = hexdec($color);
            $rgb['red'] 	= 0xFF & ($dec >> 0x10);
            $rgb['green'] 	= 0xFF & ($dec >> 0x8);
            $rgb['blue'] 	= 0xFF & $dec;
        }

        // Shorthand notation
        elseif (strlen($color) == 3)
        {
            $rgb['red'] 	= hexdec(str_repeat(substr($color, 0, 1), 2));
            $rgb['green'] 	= hexdec(str_repeat(substr($color, 1, 1), 2));
            $rgb['blue'] 	= hexdec(str_repeat(substr($color, 2, 1), 2));
        }

        return $rgb;
    }

    public static function compileRGBtoHex($color)
    {
        $hex	= "";
        $red 	= $color[0];
        $green 	= $color[1];
        $blue 	= $color[2];

        $hex	.= str_pad(dechex($red), 2, "0", STR_PAD_LEFT);
        $hex	.= str_pad(dechex($green), 2, "0", STR_PAD_LEFT);
        $hex	.= str_pad(dechex($blue), 2, "0", STR_PAD_LEFT);

        return self::compileColor($hex);
    }

	public static function isSerializedValue( $strValue, $strict = true )
	{
		$data = $strValue;

		// if it isn't a string, it isn't serialized
		if ( ! is_string( $data ) )
		{
			return false;
		}

		$data = trim( $data );

		if ( 'N;' == $data )
		{
			return true;
		}

		$length = strlen( $data );

		if ( $length < 4 )
		{
			return false;
		}

		if ( ':' !== $data[1] )
		{
			return false;
		}

		if ( $strict )
		{
			$lastc = $data[ $length - 1 ];

			if ( ';' !== $lastc && '}' !== $lastc )
			{
				return false;
			}
		}
		else
		{
			$semicolon	= strpos( $data, ';' );
			$brace		= strpos( $data, '}' );

			// Either ; or } must exist.
			if ( false === $semicolon && false === $brace )
			{
				return false;
			}

			// But neither must be in the first X characters.
			if ( false !== $semicolon && $semicolon < 3 )
			{
				return false;
			}

			if ( false !== $brace && $brace < 4 )
			{
				return false;
			}
		}

		$token = $data[0];

		switch ( $token )
		{
			case 's' :
				if ( $strict )
				{
					if ( '"' !== $data[ $length - 2 ] )
					{
						return false;
					}
				}
				elseif ( false === strpos( $data, '"' ) )
				{
					return false;
				}
				// or else fall through


			case 'a' :
			case 'O' :

				return (bool) preg_match( "/^{$token}:[0-9]+:/s", $data );


			case 'b' :
			case 'i' :
			case 'd' :

				$end = $strict ? '$' : '';
				return (bool) preg_match( "/^{$token}:[0-9.E-]+;$end/", $data );
		}

		return false;
	}



	public static function getHomepageUrl()
	{
		$strLanguage = $GLOBALS['TL_LANGUAGE'];

		$objRootPage = \PageModel::findOneBy(array('type', 'language'), array('root', $strLanguage));

		if( $objRootPage )
		{
			$objHomePage = \PageModel::findFirstPublishedRegularByPid( $objRootPage->id );

			if( $objHomePage )
			{
				return $objHomePage->getFrontendUrl();
			}
			else
			{
				$homepageUrl = array
				(
					'de'		=> 'startseite.html',
					'en'		=> 'home.html'
				);

				return $homepageUrl[ $strLanguage ];
			}
		}

		return false;
	}


    /**
     * Get a page layout and return it as database result object
     * @param \Model
     * @return \Model|boolean
     */
    public static function getPageLayout($objPage)
    {
        if($objPage == NULL)
        {
            return false;
        }

        $object 	= new self();
        $blnMobile	= ($objPage->mobileLayout && \Environment::get('agent')->mobile);

        // Override the autodetected value
        if (\Input::cookie('TL_VIEW') == 'mobile' && $objPage->mobileLayout)
        {
            $blnMobile = true;
        }
        elseif (\Input::cookie('TL_VIEW') == 'desktop')
        {
            $blnMobile = false;
        }

        $intId 		= $blnMobile ? $objPage->mobileLayout : $objPage->layout;
        $objLayout 	= \LayoutModel::findByPk($intId);

        // Die if there is no layout
        if ($objLayout === null)
        {
            $objLayout = false;
            if($objPage->pid > 0)
            {
                $objParentPage 	= $object->getParentPage( $objPage->pid );
                $objLayout 		= $object->getPageLayout($objParentPage);
            }
        }

        return $objLayout;
    }



    public static function getParentPage($id)
    {
        return \PageModel::findByPk($id);
    }



    /**
     * Compile a color value and return a hex or rgba color
     *
     * @param $videoSRC mixed
     * @param $posterSRC mixed
     * @param $arrParams array
     *
     * @return string
     */
    public static function renderVideoTag( $videoSRC, $posterSRC, array $arrParams = array('autoplay', 'loop', 'preload="none"') )
    {
        $poster = '';

        if( $posterSRC )
        {
            $objImage = \FilesModel::findByUuid( $posterSRC );

            if( $objImage )
            {
                if( file_exists(TL_ROOT . '/' . $objImage->path) )
                {
                    $poster = 'poster="' . $objImage->path . '"';
                }
            }
        }

        $strTag     = '<div class="video-container"><video' . ((count($arrParams) > 0) ? ' ' : '') . implode(' ', $arrParams) . $poster . '>';

        $source     = deserialize($videoSRC);
        $isFolder   = FALSE;

        if (!is_array($source) || empty($source))
        {
            return '';
        }

        if( count($source) == 1 )
        {
            $objFile = \FilesModel::findByUuid( $source[0] );

            if( $objFile->type == "folder" )
            {

            }
        }

        if( !$isFolder )
        {
            $objFiles = \FilesModel::findMultipleByUuidsAndExtensions($source, array('mp4','m4v','mov','wmv','webm','ogv','m4a','mp3','wma','mpeg','wav','ogg'));

            if ($objFiles === null)
            {
                return '';
            }

            /** @var \FilesModel $objFirst */
            $objFirst = $objFiles->current();

            // Pre-sort the array by preference
            if ( !in_array($objFirst->extension , array('mp4','m4v','mov','wmv','webm','ogv')) )
            {
                return '';
            }

            $strName    = '';
            $strPath    = '';
            $arrFiles   = array('mp4'=>null, 'm4v'=>null, 'mov'=>null, 'wmv'=>null, 'webm'=>null, 'ogv'=>null);

            $objFiles->reset();

            while ($objFiles->next())
            {
//                $arrMeta = deserialize($objFiles->meta);

//                if (is_array($arrMeta) && isset($arrMeta[$strLanguage]))
//                {
//                    $strTitle = $arrMeta[$strLanguage]['title'];
//                }
//                else
//                {
//                    $strTitle = $objFiles->name;
//                }

                $objFile = new \File($objFiles->path, true);
//                $objFile->title = specialchars($strTitle);

                $arrFiles[ $objFile->extension ] = $objFile;

                $strName    = $objFile->filename;
                $strPath    = $objFile->path;
            }

            foreach($arrFiles as $extension => $objFile)
            {
                if( $objFile === null )
                {
                    $strPath    = preg_replace('/.([a-zA-Z0-9]{3,4})$/', '.' . $extension, $strPath);

                    $objNewFile = \FilesModel::findByPath( $strPath );

                    if( $objNewFile )
                    {
                        $arrFiles[ $extension ] = $objNewFile;
                    }
                }
            }

            foreach($arrFiles as $extension => $file )
            {
                if( $file )
                {
                    $strTag .= '<source type="video/' . $extension . '" src="' . $file->path . '">';
                }
            }
        }

       return $strTag . '</video></div>';
    }public static function getConfig($field, $moduleName, $fullField = false)
{
    $module 			= self::renderModuleName($moduleName);
    $arrConfig			= array();
    $arrModuleConfig	= array();

    foreach($GLOBALS['TL_CONFIG'] as $fieldName => $strValue)
    {
        if( preg_match('/^' . $module . '_/', $fieldName) )
        {
            $arrModuleConfig[ $fieldName ] = $strValue;
        }
    }

    if( count($arrModuleConfig) > 0 )
    {
        foreach($arrModuleConfig as $name => $value)
        {
            if( ($field == "" || $field == "all") || preg_match('/' . $field . '/i', $name) )
            {
                $modName = preg_replace('/^' . $module . '_/', '', $name);

                $arrConfig[ $modName ] = $value;
            }
        }
    }

    return $arrConfig;
}

    public static function renderModuleName($moduleName)
    {
        $parts	= explode("_", $moduleName);
        $module	= $parts[0];
        $run	= 0;

        foreach($parts as $part)
        {
            if($run > 0)
            {
                $module .= ucfirst($part);
            }

            $run++;
        }

        return $module;
    }

    public static function checkIfFileIsVideo($file)
    {
        $isVideo	= false;
        $parts 		= explode(".", $file);
        $type		= array_pop($parts);

        if( !in_array($type, explode(",", \Config::get('validImageTypes'))) )
        {
            if($type == "mp4")
            {
                $isVideo = true;
            }
        }

        return $isVideo;
    }

    public static function setStyles($strContent, $styles, $styleTag = true)
    {
        $styles			= '<style type="text/css">' . $styles . '</style>';
        $strContent		= str_replace('</head>', $styles . "\n" . '</head>', $strContent);

        return $strContent;
    }



    public static function getFullpageNavigationUrl( $arrItem )
    {
        $objSubpages	= \PageModel::findByPk( $arrItem['id'] );
        $language		= null;
        $strAlias		= preg_replace('/\/home/', '', $arrItem['alias']);

        // Get href
        switch ($objSubpages->type)
        {
            case 'redirect':
                $href = $objSubpages->url;

                if (strncasecmp($href, 'mailto:', 7) === 0)
                {
                    $href = \String::encodeEmail($href);
                }
                break;

            case 'forward':
                if ($objSubpages->jumpTo)
                {
                    $objNext = $objSubpages->getRelated('jumpTo');
                }
                else
                {
                    $objNext = \PageModel::findFirstPublishedRegularByPid($objSubpages->id);
                }

                if ($objNext !== null)
                {
                    $strForceLang = null;
                    $objNext->loadDetails();

                    // Check the target page language (see #4706)
                    if ($GLOBALS['TL_CONFIG']['addLanguageToUrl'])
                    {
                        $strForceLang = $objNext->language;
                    }

                    $href = self::generateFrontendUrl($objNext->row(), '/' . $strAlias, $strForceLang);

                    // Add the domain if it differs from the current one (see #3765)
                    if ($objNext->domain != '' && $objNext->domain != \Environment::get('host'))
                    {
                        $href = (\Environment::get('ssl') ? 'https://' : 'http://') . $objNext->domain . TL_PATH . '/' . $href;
                    }
                    break;
                }
            // DO NOT ADD A break; STATEMENT

            default:
                $href = self::generateFrontendUrl($objSubpages->row(), null, $language);

                // Add the domain if it differs from the current one (see #3765)
                if ($objSubpages->domain != '' && $objSubpages->domain != \Environment::get('host'))
                {
                    $href = (\Environment::get('ssl') ? 'https://' : 'http://') . $objSubpages->domain . TL_PATH . '/' . $href;
                }
                break;
        }

        return $href;
    }



    public static function replaceOtherDefaultScripts()
    {
        global $objPage;

        $config		= \Config::getInstance();
        $objLayout 	= self::getPageLayout($objPage);


        if( is_array($GLOBALS['TL_JAVASCRIPT']) )
        {
            foreach($GLOBALS['TL_JAVASCRIPT'] as $key => $file)
            {
                if($objLayout->addJQuery)
                {
                    if( preg_match("/moo_simple_columns/", $file) )
                    {
                        $GLOBALS['TL_JAVASCRIPT'][ $key ] = 'system/modules/zdps_customize/assets/javascript/jquery/src/j_simple_columns.js|static';
                    }
                }
                else
                {
                    if( preg_match("/moo_simple_columns/", $file) )
                    {
                        $GLOBALS['TL_JAVASCRIPT'][ $key ] = $file . '|static';
                    }
                }
            }
        }

        if( is_array($GLOBALS['TL_CSS']) )
        {
            foreach($GLOBALS['TL_CSS'] as $key => $file)
            {
                if( preg_match("/simple_columns/", $file) )
                {
                    $GLOBALS['TL_CSS'][ $key ] = $file . '||static';
                }

                if( preg_match("/caroufredsel/", $file) )
                {
                    $GLOBALS['TL_CSS'][ $key ] = $file . '||static';
                }
            }
        }
    }

    public static function checkForUniqueScripts()
    {
        if( is_array($GLOBALS['TL_JAVASCRIPT']) )
        {
            $GLOBALS['TL_JAVASCRIPT'] 	= array_unique($GLOBALS['TL_JAVASCRIPT']);
            $GLOBALS['TL_JAVASCRIPT'] 	= array_values($GLOBALS['TL_JAVASCRIPT']);
        }

        if( is_array($GLOBALS['TL_CSS']) )
        {
            $GLOBALS['TL_CSS']			= array_unique($GLOBALS['TL_CSS']);
            $GLOBALS['TL_CSS']			= array_values($GLOBALS['TL_CSS']);
        }

        if( is_array($GLOBALS['TL_USER_CSS']) )
        {
            $GLOBALS['TL_USER_CSS']		= array_unique($GLOBALS['TL_USER_CSS']);
            $GLOBALS['TL_USER_CSS']		= array_values($GLOBALS['TL_USER_CSS']);
        }
    }



    public static function renderStyles($styles, $includeStylesTag = true)
    {
        $strStyles = '';
        foreach($styles as $selector => $style)
        {
            $strStyles .= $selector . '{';

            foreach($style as $key => $value)
            {
                $strStyles .= $key . ':' . $value . ';';

                if($key == 'background-size' && !array_key_exists('-webkit-background-size', $style))
                {
                    $strStyles .= '-webkit-' . $key . ':' . $value . ';';
                    $strStyles .= '-moz-' . $key . ':' . $value . ';';
                    $strStyles .= '-o-' . $key . ':' . $value . ';';
                }
            }

            $strStyles .= '}';
        }

        if($includeStylesTag)
        {
            $strStyles = '<style type="text/css">' . $strStyles . '</style>';
        }

        return $strStyles;
    }

    public static function getRandomHash($length = 6, $chars = '')
    {
        $uid			= '';
        $length 		= empty($length) 	? 11 : $length;
        $length 		= $length > 64 		? 64 : $length;

        if(!is_array($chars) || (is_array($chars) && empty($chars))) {
            for($i=65;$i<=90;$i++) {
                $chars[] = chr($i);
            }
            for($i=97;$i<=122;$i++) {
                $chars[] = chr($i);
            }
            $chars[] = '_';
        }

        $c = count($chars);
        for($i=0;$i<$length;$i++) {
            $uid .= $chars[rand(0, $c-1)];
        }
        return $uid;
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



    public static function renderFilterName( $filterName )
    {
        $filterName = preg_replace(array('/ü/', '/ö/', '/ä/', '/ &amp; /', '/ & /', '/&#40;/', '/&#41;/', '/\(/', '/\)/', '/, /', '/\s/'), array('ue', 'oe', 'ae', '_and_', '_and_', '', '', '', '', ',', '_'), trim($filterName));

        return trim($filterName);
    }
}
