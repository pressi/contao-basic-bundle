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

                $strBuffer = str_replace('class="ce_text' , 'class="ce_text has-image' . $addClass, $strBuffer);

                if( $objRow->fullsize && !$objRow->imageUrl )
                {
                    $strBuffer = Helper::generateImageHoverTags( $strBuffer, $objRow );
                }
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

        return $strBuffer;
    }
    
}
