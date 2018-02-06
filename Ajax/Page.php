<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\Ajax;

/**
 * IIDO Page Ajax
 *
 * @author Stephan Preßl <https://github.com/pressi>
 * @deprecated NOT IN USE!
 */
class Page extends \Frontend
{

	public function renderPageContent( $strValue )
	{
		$strContent		= "";
		$pageAlias		= preg_replace('/^de\//', '', $strValue);
		$pageAlias		= preg_replace('/.html$/', '', $pageAlias);

		$objOpenPage	= \PageModel::findByIdOrAlias( $pageAlias );

		if( $objOpenPage )
		{
			$arrElements	= array();
//			$pageUrl	= \Environment::get("base") . $strValue;
//			$strContent = file_get_contents( $pageUrl );

			$objOpenArticles 	= \ArticleModel::findPublishedByPidAndColumn($objOpenPage->id, "main");// findBy("pid", $objPage->id);

			if( $objOpenArticles )
			{
				while( $objOpenArticles->next() )
				{
					$objOpenElements = \ContentModel::findPublishedByPidAndTable($objOpenArticles->id, 'tl_article');

					if($objOpenElements)
					{
						while ($objOpenElements->next())
						{
							$strElementContent 	= $this->getContentElement($objOpenElements->current());

							if($objOpenElements->type == "caroufredsel_gallery")
							{
								$strCSSContent		= $this->renderCSS( $GLOBALS['TL_CSS'], "caroufredsel" );
//								$strCSSContent		.= $this->renderCSS( $GLOBALS['TL_FRAMEWORK_CSS'], "caroufredsel" );
								$strJSContent		= $this->renderJS( $GLOBALS['TL_JAVASCRIPT'], "caroufredsel" );

								$strElementContent 	= $strCSSContent . $strElementContent . $strJSContent;
							}
							elseif($objOpenElements->type == "text")
							{
								if( preg_match('/id="/', $strContent) )
								{
									$strElementContent	= preg_replace('/id="()"/', 'id=""', $strElementContent);
								}
								else
								{
									$strElementContent	= preg_replace('/class="ce_text/', 'id="ceTextElement_' . $objOpenElements->id . '" class="ce_text', $strElementContent);
								}

//-->close Link								$strElementContent = preg_replace('/<div([A-Za-z0-9\s\-_,;.:#\(\)="]{0,})class="ce_text([A-Za-z0-9\s\-_]{0,})"([A-Za-z0-9\s\-_,;.:#\(\)="]{0,})>/', '<div$1class="ce_text$2"$3><div class="close-element" onclick="DPS.Content.closeLigthboxText(' . $objOpenElements->id . ')">schließen</div>', $strElementContent);

//								$strElementContent .= '<script type="text/javascript">DPS.Content.initLigthboxText(' . $objOpenElements->id . ');</script>';
							}
							elseif($objOpenElements->type == "dlh_googlemaps")
							{
								$strCSSContent		= $this->renderCSS( $GLOBALS['TL_CSS'], "googlemaps" );
//								$strCSSContent		.= $this->renderCSS( $GLOBALS['TL_FRAMEWORK_CSS'], "googlemaps" );
								$strJSContent		= $this->renderJS( $GLOBALS['TL_JAVASCRIPT'], "maps.google" );

								$strElementContent	= $strCSSContent . $strJSContent . $strElementContent;

								$strElementContent = preg_replace('/function gmap([0-9]{0,})_initialize\(\) {/', 'DPS.Content.loadGoogleMap( function() {', $strElementContent);
								$strElementContent = preg_replace('/gmap([0-9]{0,})_initialize\(\);/', '', $strElementContent);
								$strElementContent = preg_replace('/if\(window.gmap([0-9]{0,})_dynmap\){([\s\b]{0,})gmap([0-9]{0,})_dynmap\(gmap([0-9]{0,})\);([\s\n]{0,})}([\s\n]{0,})}/', 'if(window.gmap$1_dynmap) { gmap$3_dynmap(gmap$4); }});', $strElementContent);
								$strElementContent = preg_replace('/window.setTimeout\("gmap([0-9]{0,})_initialize\(\)", ([0-9]{0,})\);/', '', $strElementContent);

//								$strElementContent = preg_replace('/gmap([0-9]{0,})_initialize();/', 'DPS.Content.loadGoogleMaps("gmap$1_initialize");', $strElementContent);
//								$strElementContent = preg_replace('/gmap([0-9]{0,})_initialize();/', 'console.log("loading"); gmap$1_initialize();', $strElementContent);
//								$strElementContent = preg_replace('/function gmap([0-9]{0,})_initialize\(\) {/', 'function gmap$1_initialize() { console.log("load");', $strElementContent);

//								$match				= preg_match('/<script>([\\a-zA-Z0-9\s\n_(){}=:;.,"$\/]{0,})<\/script>/', $strContent, $matches);
//
//								$map 		= $objOpenElements->dlh_googlemap;
//
//								if($match && is_array($matches) && strlen($matches[0]))
//								{
//									$mapContent = preg_replace('/<script.*>/', '', $matches[0]);
//									$mapContent = preg_replace('/<\/script>/', '', $mapContent);
//
//									$mapContent = preg_replace('/window.setTimeout\("gmap' . $map . '_initialize\(\)", ([0-9]{0,})\);/', '', $mapContent);
//									$mapContent = preg_replace('/function gmap' . $map . '_initialize\(\)/', 'Map.init = function()', $mapContent);
//
//									$function		= trim($mapContent);
//								}
							}

							$strElementContent 	= $this->replaceInsertTags( preg_replace("/TL_FILES_URL/", "", $strElementContent) );

							$arrElements[] 	= str_replace("{{request_token}}", REQUEST_TOKEN, $strElementContent);
						}
					}
				}
			}

			if( count($arrElements) > 0 )
			{
				$strContent = implode("", $arrElements);
			}
		}
		else
		{
			$strContent = '<div class="message error">Diese Seite konnte nicht geladen werden!</div>';
		}

		header('Content-Type: text/html');
		echo $strContent;
		exit;
	}

	protected function renderCSS( array $arrCSS = array(), $matchPrefix )
	{
		$strContent = "";

		foreach($arrCSS as $filePath)
		{
			if( preg_match('/' . $matchPrefix . '/i', $filePath) )
			{
				$pathParts		= explode("|", $filePath);
				$fileRealPath	= $pathParts[0];

				$strContent		.= '<style type="text/css">' . file_get_contents(TL_ROOT . '/' . $fileRealPath) . '</style>';
			}
		}

		if( is_array($GLOBALS['TL_FRAMEWORK_CSS']) )
		{
			foreach($GLOBALS['TL_FRAMEWORK_CSS'] as $filePath)
			{
				if( preg_match('/' . $matchPrefix . '/i', $filePath) )
				{
					$pathParts		= explode("|", $filePath);
					$fileRealPath	= $pathParts[0];

					$strContent		.= '<style type="text/css">' . file_get_contents(TL_ROOT . '/' . $fileRealPath) . '</style>';
				}
			}
		}

		return $strContent;
	}

	protected function renderJS( array $arrJS = array(), $matchPrefix )
	{
		$strContent = "";

		foreach($arrJS as $filePath)
		{
			if( preg_match('/' . $matchPrefix . '/i', $filePath) )
			{
				$pathParts		= explode("|", $filePath);
				$fileRealPath	= $pathParts[0];

				if( $matchPrefix == "maps.google" )
				{
//					$strContent		.= '<script type="text/javascript">
//					var headElement			= document.getElementsByTagName("head")[0],
//						newScriptElement	= document.createElement("script")
//
//					newScriptElement.type = "text/javascript";
//					newScriptElement.src = "' . $fileRealPath . '";
//					headElement.appendChild(newScriptElement)
//
//					</script>';

					$strFileContent = file_get_contents($fileRealPath);
					$strFileContent = preg_replace('/function getScript\(src\) {([\s\n]{0,})document.write\(([A-Za-z0-9\s\n="\'+\/<>]{0,})\);([\s\n]{0,})\}/', '', $strFileContent);
					$strFileContent = preg_replace('/getScript\(/', 'DPS.Content.loadScript(', $strFileContent);


					$strContent		.= '<script type="text/javascript">' . $strFileContent . '</script>';
				}
				else
				{
					$strContent		.= '<script type="text/javascript" src="' . $fileRealPath . '"></script>';
				}
			}
		}

		if( is_array($GLOBALS['TL_JQUERY']) )
		{
			foreach($GLOBALS['TL_JQUERY'] as $script)
			{
				$strContent .= $script;
			}
		}

		return $strContent;
	}
}
