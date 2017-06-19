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
                                $cAttributeName .= 't';
                                $attributeName  .= '-top';
                                break;

                            case "b":
                            case "bo":
                                $cAttributeName .= 'b';
                                $attributeName  .= '-bottom';
                                break;

                            case "l":
                            case "le":
                                $cAttributeName .= 'l';
                                $attributeName  .= '-left';
                                break;

                            case "r":
                            case "ri":
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

        return $strBuffer;
    }
    
}
