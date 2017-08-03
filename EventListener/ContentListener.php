<?php
/*******************************************************************
 *
 * (c) 2017 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 *
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *
 *******************************************************************/

namespace IIDO\BasicBundle\EventListener;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\CoreBundle\Framework\ScopeAwareTrait;
use Contao\System;
use Contao\Config;

use IIDO\BasicBundle\Config\BundleConfig;
use IIDO\BasicBundle\Helper\ContentHelper as Helper;


//use IIDO\WebsiteBundle\Table\Page;


/**
 * IIDO System Listener
 *
 * @author Stephan Preßl <https://github.com/pressi>
 */
class ContentListener
{
    use ScopeAwareTrait;


    /**
     * @var ContaoFrameworkInterface
     */
    private $framework;


    protected $bundlePathPublic;
    protected $bundlePath;

    protected $resourcePath     = '/app/Resources';



    /**
     * Constructor.
     *
     * @param ContaoFrameworkInterface $framework
     */
    public function __construct(ContaoFrameworkInterface $framework)
    {
        $this->framework = $framework;

        $this->bundlePathPublic = BundleConfig::getBundlePath(true);
        $this->bundlePath       = BundleConfig::getBundlePath();

    }



    /**
     * edit content element
     */
    public function getCustomizeContentElement($objRow, $strBuffer, &$objElement)
    {
        global $objPage;

        $elementClass   = $objRow->typePrefix . $objRow->type;
        $cssID          = \StringUtil::deserialize($objRow->cssID, TRUE);

        if( $objRow->type == "module" )
        {
            $objModule = \ModuleModel::findByPk( $objRow->module );

            if( $objModule )
            {
                $elementClass = 'mod_' . $objModule->type;
            }
        }


        if( $objRow->type == "text")
        {
            if( $objRow->addImage )
            {
                $addClass = "";

                if( strlen($objRow->headlineImagePosition) )
                {
                    $arrHeadline        = deserialize($objRow->headline, true);

                    $headlineUnit       = $arrHeadline['unit'];
                    $headlineValue      = $arrHeadline['value'];

                    $strHeadline        = '<' . $headlineUnit . '>' . $headlineValue . '</' . $headlineUnit . '>';

                    if( $objRow->floating == "above" && $objRow->headlineImagePosition == "bottom" )
                    {
                        if( $arrHeadline['value'] != "" )
                        {
                            $strBuffer = str_replace($strHeadline , '', $strBuffer);
                        }

                        $strBuffer = str_replace('</figure>' , '</figure>' . $strHeadline, $strBuffer);
                    }
                    elseif( $objRow->floating == "left" || $objRow->floating == "right" )
                    {
                        if( $objRow->headlineImagePosition == "nextTo" || $objRow->headlineImagePosition == "bottom" )
                        {
                            if( $arrHeadline['value'] != "" )
                            {
                                $strBuffer = str_replace($strHeadline , '', $strBuffer);
                            }

                            $strBuffer = str_replace('</figure>' , '</figure>' . $strHeadline, $strBuffer);
                        }
                    }
                    $addClass =  ' headline-position-' . $objRow->headlineImagePosition;
                }

                $addClass .= ' ip-' . preg_replace('/float_/', '', $objRow->floating);

                $strBuffer = str_replace('class="ce_text' , 'class="ce_text has-image' . $addClass, $strBuffer);

                if( $objRow->fullsize && !$objRow->imageUrl )
                {
                    $strBuffer = Helper::generateImageHoverTags( $strBuffer, $objRow );
                }
            }

            if( preg_match('/first-p-big/', $cssID[1]) )
            {
                $strBuffer = preg_replace('/<p>(.*)<\/p>/', '<p><span class="big">$1</span><span class="divider"></span></p>', $strBuffer, 1);
            }
        }

        elseif( $objRow->type == "gallery" )
        {
            if( $objRow->fullsize )
            {
                $strBuffer = Helper::generateImageHoverTags( $strBuffer, $objRow );
            }
        }


        switch( $objRow->type )
        {
            case "hyperlink":
                $strBuffer = $this->renderHyperlink( $strBuffer, $objRow, $objElement );
                break;
        }

        $strBuffer = preg_replace('/class="' . $elementClass . '/', 'class="' . $elementClass . ' content-element', $strBuffer);


//        $strBuffer = preg_replace('/<div([A-Za-z0-9\s\-_="\(\)\{\}:;\/]{0,})class="' . $elementClass . '([A-Za-z0-9\s\-\{\}_:;]{0,})"([A-Za-z0-9\s\-_="\(\)\{\}:;\/]{0,})>/', '<div$1class="' . $elementClass . '$2"$3><div class="element-inside">', $strBuffer, -1, $count);

//        if( $count )
//        {
//            $strBuffer = $strBuffer . '</div>';
//        }

        preg_match_all('/class="ce_([A-Za-z0-9\s\-_\{\}]{0,})"/', $strBuffer, $arrClassMatches);

        if( is_array($arrClassMatches) && is_array($arrClassMatches[0]) && count($arrClassMatches[0]) )
        {
            $strClass       = "ce_" . $arrClassMatches[1][0];
            $strNewClass    = $strClass;
            $arrClass       = explode(" ", $strClass);
            $arrAttributes  = array();

            foreach($arrClass as $strClassName)
            {
                if( preg_match('/^v_/', $strClassName) )
                {
                    $arrClasses     = array();
                    $arrParts       = array();
                    $cAttributeName = "";
                    $attributeName  = "";
                    $value          = "";

                    if( preg_match('/^v_ma/', $strClassName) )
                    {
                        $cAttributeName = "m";
                        $attributeName  = "margin";

                        $property   = substr($strClassName, 5);
                        $arrParts   = explode("_", $property);
                        $value      = $arrParts[0];
                    }

                    if( count($arrParts) > 1 )
                    {
                        $value = $arrParts[1];

                        switch( $arrParts[0] )
                        {
                            case "t":
                            case "to":
                            case "top":
                                $cAttributeName .= 't';
                                $attributeName  .= '-top';
                                break;

                            case "b":
                            case "bo":
                            case "bottom":
                                $cAttributeName .= 'b';
                                $attributeName  .= '-bottom';
                                break;

                            case "l":
                            case "le":
                            case "left":
                                $cAttributeName .= 'l';
                                $attributeName  .= '-left';
                                break;

                            case "r":
                            case "ri":
                            case "right":
                                $cAttributeName .= 'r';
                                $attributeName  .= '-right';
                                break;
                        }
                    }

                    $strNewClass = preg_replace('/ ' . $strClassName . '/', '', $strNewClass);

//                    $arrClasses[]       = 'd' . $cAttributeName;
//                    $arrAttributes[]    = 'data-' . $attributeName . '="' . $value . '"';
                    $arrAttributes[]    = $attributeName . ':' . $value . 'px;';
                }
            }

            if( count($arrAttributes) )
            {
//                $strBuffer = preg_replace('/class="' . $strClass . '"/', 'class="' . $strClass . ' ' . implode(" ", $arrClasses) . '" ' . implode(" ", $arrAttributes), $strBuffer);
                $strBuffer = preg_replace('/class="' . $strClass . '"/', 'class="' . $strNewClass . '" style="' . implode("", $arrAttributes) . '"', $strBuffer);
            }
        }


        $arrClasses     = array();
        $arrAttributes  = array();

        if( $objRow->position )
        {
            $strStyles = '';

            $arrClasses[] = 'pos-' . ($objRow->positionFixed ? 'fixed' : 'abs');
            $arrClasses[] = 'pos-' . str_replace('_', '-', $objRow->position);

            $arrPosMargin = deserialize($objRow->position_margin, TRUE);

            if( $arrPosMargin['top'] || $arrPosMargin['right'] || $arrPosMargin['bottom'] || $arrPosMargin['left'] )
            {
                $unit       = $arrPosMargin['unit']?:'px';
                $useUnit    = true;

                if( $arrPosMargin['top'] )
                {
                    if( preg_match('/(' . implode('|', $GLOBALS['TL_CSS_UNITS']) . ')$/', $arrPosMargin['top']) )
                    {
                        $useUnit = false;
                    }

                    $strStyles .= " margin-top:" . $arrPosMargin['top'] . (($useUnit)?$unit:'') . ";";

                    $useUnit    = true;
                }

                if( $arrPosMargin['right'] )
                {
                    if( preg_match('/(' . implode('|', $GLOBALS['TL_CSS_UNITS']) . ')$/', $arrPosMargin['right']) )
                    {
                        $useUnit = false;
                    }

                    $strStyles .= " margin-right:" . $arrPosMargin['right'] . (($useUnit)?$unit:'') . ";";

                    $useUnit    = true;
                }

                if( $arrPosMargin['bottom'] )
                {
                    if( preg_match('/(' . implode('|', $GLOBALS['TL_CSS_UNITS']) . ')$/', $arrPosMargin['bottom']) )
                    {
                        $useUnit = false;
                    }

                    $strStyles .= " margin-bottom:" . $arrPosMargin['bottom'] . (($useUnit)?$unit:'') . ";";

                    $useUnit    = true;
                }

                if( $arrPosMargin['left'] )
                {
                    if( preg_match('/(' . implode('|', $GLOBALS['TL_CSS_UNITS']) . ')$/', $arrPosMargin['left']) )
                    {
                        $useUnit = false;
                    }

                    if( !preg_match('/' . $unit . '$/', $arrPosMargin['left']) )
                    {
                        $useUnit = false;
                    }

                    $strStyles .= " margin-left:" . $arrPosMargin['left'] . (($useUnit)?$unit:'') . ";";

                    $useUnit    = true;
                }
            }

            if( strlen($strStyles) )
            {
                $arrAttributes['style'] = trim($arrAttributes['style'] . $strStyles);
            }
        }

        if( count($arrClasses) )
        {
            $strBuffer = $this->addClassToContentElement( $strBuffer, $objRow, $arrClasses );
        }

        if( count($arrAttributes) )
        {
            $strBuffer = $this->addAttributesToContentElement( $strBuffer, $objRow, $arrAttributes );
        }

        $strBuffer = $this->renderHeadlines($strBuffer, $objRow);

        return $strBuffer;
    }



    protected function renderHeadlines( $strContent, $objRow )
    {
        $strContent = preg_replace('/<h([1-6]{1})([A-Za-z0-9\s\-_="]{0,})>/', '<h$1$2><span class="headline-inside">', $strContent);
        $strContent = preg_replace('/<\/h([1-6]{1})>/', '</span></h$1>', $strContent);

        return $strContent;
    }



    protected function renderHyperlink( $strContent, $objRow, $objElement )
    {
        $arrClasses = array();

        if( $objRow->showAsButton )
        {
            $arrClasses[] = 'btn';
            $arrClasses[] = 'btn-' . $objRow->buttonStyle;
            $arrClasses[] = 'btn-type-' . $objRow->buttonType;

            if( !strlen($objRow->linkTitle) )
            {
                $arrClasses[] = 'btn-empty';
            }

            if( $objRow->buttonAddon )
            {
                $arrClasses[] = 'btn-addon';
                $arrClasses[] = 'addon-' . $objRow->buttonAddon;
                $arrClasses[] = 'addon-pos-' . $objRow->buttonAddonPosition;

                switch( $objRow->buttonAddon )
                {
                    case "arrow":
                        $arrClasses[] = 'arrow-' . $objRow->buttonAddonArrow;
                        break;

                    case "icon":
//                        $arrClasses[] = 'icon-' . $objRow->buttonAddonIcon;
                        $strContent = preg_replace('/<a/', '<a data-icon="' . $objRow->buttonAddonIcon . '"', $strContent);
                        $strContent = preg_replace('/class="hyperlink_txt/', 'class="hyperlink_txt icon-link', $strContent);
                        break;
                }
            }
        }

        $arrLinkClasses = array();

        switch( $objRow->buttonLinkMode )
        {
            case "lightbox":
                $arrLinkClasses[] = 'open-in-lightbox';
                break;

            case "scroll":
                $arrLinkClasses[] = 'scroll-to';
                break;

            case "nolink":
                $arrLinkClasses[] = 'no-link';
                break;
        }

        if( count($arrLinkClasses) )
        {
            $strContent = preg_replace('/class="hyperlink_txt/', 'class="hyperlink_txt ' . implode(' ', $arrLinkClasses), $strContent);
        }

        $strContent = $this->addClassToContentElement( $strContent, $objRow, $arrClasses );

        return $strContent;
    }



    protected function addClassToContentElement( $strContent, $objRow, array $arrClasses )
    {
        $elementClass   = $this->getElementClass( $objRow );
        $strContent     = preg_replace('/class="' . $elementClass . '/', 'class="' . $elementClass . ' ' . implode(" ", $arrClasses), $strContent);

        return $strContent;
    }



    protected function addAttributesToContentElement( $strContent, $objRow, array $arrAttributes)
    {
        $strAttributes = '';

        foreach($arrAttributes as $attributeName => $attributeValue)
        {
            $strAttributes .= $attributeName . '="' . $attributeValue . '"';
        }

        $elementClass   = $this->getElementClass( $objRow );
        $strContent     = preg_replace('/class="' . $elementClass . '/', $strAttributes . ' class="' . $elementClass, $strContent);

        return $strContent;
    }



    protected function getElementClass( $objRow )
    {
        $elementClass   = $objRow->typePrefix . $objRow->type;

        if( $objRow->type == "module" )
        {
            $objModule = \ModuleModel::findByPk( $objRow->module );

            if( $objModule )
            {
                $elementClass = 'mod_' . $objModule->type;
            }
        }

        return $elementClass;
    }
    
}
