<?php
/*******************************************************************
 * (c) 2020 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\EventListener;


//use IIDO\BasicBundle\Config\BundleConfig;
//use IIDO\BasicBundle\Helper\BasicHelper;
//use IIDO\BasicBundle\Helper\ColorHelper;
//use IIDO\BasicBundle\Helper\ContentElementHelper;
//use IIDO\BasicBundle\Helper\ContentHelper as Helper;
//use IIDO\BasicBundle\Helper\ContentHelper;
//use IIDO\BasicBundle\Helper\ScriptHelper;
//use IIDO\BasicBundle\Helper\StylesheetHelper;
//use IIDO\BasicBundle\Helper\WebsiteStylesHelper;
use Contao\ContentModel;
use Contao\Controller;
use Contao\StringUtil;
use Contao\System;
use IIDO\BasicBundle\Helper\BasicHelper;
use IIDO\BasicBundle\Helper\ColorHelper;
use IIDO\BasicBundle\Helper\ContentHelper;
use IIDO\BasicBundle\Helper\ImageHelper;
use IIDO\BasicBundle\Helper\ScriptHelper;
use IIDO\BasicBundle\Renderer\ContentRenderer;
use Symfony\Cmf\Bundle\RoutingBundle\Tests\Fixtures\App\Document\Content;
use Terminal42\ServiceAnnotationBundle\ServiceAnnotationInterface;
use Contao\CoreBundle\ServiceAnnotation\Hook;


/**
 * IIDO System Listener
 *
 * @package IIDO\BasicBundle
 * @author Stephan Preßl <development@prestep.at>
 */
class ContentListener implements ServiceAnnotationInterface
{

    protected $arrNotInsideClassElements = array
    (
        'sliderStart',
        'slick-content-start',
        'slick-slide-separator',

        'iido_wrapperStart',
        'iido_wrapperStop',
        'iido_wrapperSeparator',

        'rocksolid_slider',

        'htmlOpen',
        'htmlClose',

        'dlh_googlemaps'
    );



    /**
     * @Hook("getContentElement")
     */
    public function onGetContentElement($objRow, $strBuffer, &$objElement): string
    {
        $isBackend      = BasicHelper::isBackend();
        $cssID          = \StringUtil::deserialize($objRow->cssID, TRUE);
        $isMobile       = \Environment::get("agent")->mobile;

        if( $isMobile && ($objRow->hideOnMobile || FALSE !== strpos( $cssID[1], 'hide-on-mobile' )) )
        {
            return '';
        }
        elseif( !$isMobile && ($objRow->showOnMobile || FALSE !== strpos( $cssID[1], 'show-on-mobile' )) )
        {
            return '';
        }

        $objArticle         = false;
        $elementType        = $objRow->type;
        $elementClass       = ContentHelper::getElementClass( $objRow );
        $arrElementClasses  = [];
        $arrAttributes      = [];
        $objAliasElement    = false;

        if( $elementType === 'alias' )
        {
            $objAliasElement = ContentModel::findByPk( $objRow->cteAlias );
        }

        $strBuffer  = ContentRenderer::parseHeadline( $strBuffer, $objRow );
        $iidoConfig = \System::getContainer()->get('iido.basic.config');
        $strType    = $this->getElementType( $objRow, $objAliasElement );

        if( $elementType === 'text' )
        {
            if( false !== strpos($cssID[1], 'text-nav') )
            {
                $strBuffer = $this->renderTextNavigation( $strBuffer, $objRow, $objElement );
            }

            if( $objRow->addImage && $objRow->singleSRC )
            {
                $arrElementClasses[] = 'has-image';
                $arrElementClasses[] = 'ip-' . $objRow->floating;
            }

            if( FALSE !== strpos($cssID[1], 'show-as-columns') )
            {
                $addTCInside = '';

                if( FALSE === strpos($cssID[1], 'text-not-valign') )
                {
                    $addTCInside = '<div class="ctable"><div class="ctable-cell">';
                    $strBuffer .= '</div></div>';
                }

                $strBuffer = preg_replace('/<\/figure>/', '</figure><div class="text-container">' . $addTCInside, $strBuffer);
                $strBuffer .= '</div>';
            }

            if( $isBackend && $iidoConfig->get('enableLayout') )
            {
                $labelAddon = '';

                if( $objRow->addImage )
                {
                    $imgBePos = '';

                    if( $objRow->floating === 'above' )
                    {
                        $imgBePos = 'oberhalb';
                    }
                    elseif( $objRow->floating === 'below' )
                    {
                        $imgBePos = 'unterhalb';
                    }
                    if( $objRow->floating === 'left' )
                    {
                        $imgBePos = 'links';
                    }
                    if( $objRow->floating === 'right' )
                    {
                        $imgBePos = 'rechts';
                    }

                    $labelAddon = ' mit Bild (' . $imgBePos . ')';
                }

                $strBuffer = preg_replace('/<p/', '<div class="be-row"><div class="row-label">Text' . $labelAddon . ':</div><div class="row-value"><p', $strBuffer, 1, $repCount);

                if( $objRow->addImage && $objRow->floating === 'below' )
                {
                    $strBuffer = preg_replace('/<figure/', '</div></div><figure', $strBuffer);
                }
                else
                {
                    if( $repCount )
                    {
                        $strBuffer = $strBuffer . '</div></div>';
                    }

                }
            }

            if( FALSE !== strpos($cssID[1], 'first-word-bigger') )
            {
                $strBuffer = preg_replace('/<p>([A-Za-z0-9\-,;.:öäüÖÄÜß]+)/', '<p><span class="first-word">$1</span>', $strBuffer, 1);
            }
        }
        elseif( $elementType === 'image' )
        {
            if( FALSE !== strpos($cssID[1], 'parallax-img') )
            {
                ScriptHelper::addScript('parallax');

                preg_match_all('/<img([A-Za-z0-9\s=",;.:]+)src="([A-Za-z0-9\s\-_%.\/]+)"/', $strBuffer, $arrImageMatches);

                $imgSRC = $arrImageMatches[2][0];

                $strBuffer = preg_replace('/<figure/', '<figure data-parallax="scroll" data-z-index="100" data-speed="0.5" data-bleed="0" data-image-src="' . $imgSRC . '"', $strBuffer);
            }

            if( $objRow->caption )
            {
                $newCaption = $objRow->caption;

                if( false !== strpos($cssID[1], 'split-caption') )
                {
                    $newCaption = ContentHelper::renderText( $newCaption, true, true );
                }
                else
                {
                    $newCaption = ContentHelper::renderText( $newCaption );
                }

                $strBuffer = preg_replace('/<figcaption([A-Za-z0-9\s\-="]{0,})>' . preg_quote($objRow->caption, '/') . '<\/figcaption>/', '<figcaption$1>' . $newCaption . '</figcatpion>', $strBuffer);
            }
        }

        switch( $strType )
        {
            case "hyperlink":
                $strBuffer = $this->renderHyperlink( $strBuffer, $objRow, $objElement );
                break;

            case "toplink":
                $strBuffer = $this->renderToplink( $strBuffer, $objRow, $objElement );
                break;

            case "gallery":
                $strBuffer = $this->renderGallery( $strBuffer, $objRow, $objElement, $objAliasElement );
                break;
//
//            case "list":
//                $strBuffer = $this->renderList( $strBuffer, $objRow, $objElement );
//                break;
//
//            case "table":
//                $strBuffer = $this->renderTable( $strBuffer, $objRow, $objElement );
//                break;
//
//            case "slick-slider":
//                $strBuffer = $this->renderSlickSliderImages( $strBuffer, $objRow, $objElement );
        }

        $strBuffer = $this->renderNewHeadlines( $strBuffer, $objRow, $objElement );

        $arrElementClasses[] = 'content-element';

        if( !in_array($objRow->type, $this->arrNotInsideClassElements) )
        {
            $strBuffer = preg_replace('/<(div|section)([A-Za-z0-9\s\-=",;.:_]+)class="' . $elementClass . '([A-Za-z0-9\s\-,;.:_]{0,})"([A-Za-z0-9\s\-=",;.:_]{0,})>/', '<$1$2class="' . $elementClass . '$3"$4><div class="element-inside">', $strBuffer, -1, $countInside);

            if( $countInside )
            {
                $tagName = 'div';

                if( $objRow->type === 'accordionSingle' )
                {
                    $tagName = 'section';
                }
                $strBuffer = $strBuffer . '</' . $tagName . '>';
            }
        }

        if( $objRow->addAnimation || ($objArticle && $objArticle->addAnimation) )
        {
            $arrElementClasses[] = 'animate-box';

            $arrAttributes['data-animate']          = $objRow->animationType    ?:$objArticle->animationType;
            $arrAttributes['data-animate-offset']   = $objRow->animationOffset  ?:$objArticle->animationOffset;

            if( $objRow->animateRun === "once" || $objArticle->animateRun === "once" )
            {
                $arrAttributes['data-animate-trigger-once'] = 'true';
            }

            if( $objRow->animationWait || $objArticle->animationWait )
            {
                $arrElementClasses[] = 'animate-wait';

                $arrAttributes['data-wait'] = 1;
            }
        }

        if( count($arrElementClasses) )
        {
            $strBuffer = ContentHelper::addClassToElement( $strBuffer, $objRow, $arrElementClasses );
        }

        if( count($arrAttributes) )
        {
            $strBuffer = ContentHelper::addAttributesToContentElement( $strBuffer, $objRow, $arrAttributes );
        }

//        $strBuffer = ContentHelper::addClassToElement( $strBuffer, $objRow, $arrElementClasses );



        // GRID
        if( false === strpos($cssID[1], 'no-grid') && $iidoConfig->get('enableLayout') )
        {
            $classes    = [];
            $devices    = ["mobile", "tablet", "desktop", "wide"];
            $strClasses = '';

            $conditionalWidth   = StringUtil::deserialize($objRow->layout_cond_width);
            $smallerDeviceAlign = "";

            foreach ($devices as $device)
            {
                $deviceProperty = "layout_col_" . $device;

                if( $objRow->$deviceProperty )
                {
                    $classes[] = "col-" . $device . "-" . $objRow->$deviceProperty;
                }
                else
                {
                    $classes[] = "col-" . $device . "-inherit";
                }

                $deviceAlign = "layout_align_$device";

                if( $objRow->$deviceAlign )
                {
                    $classes[] = "col-$device-" . $objRow->$deviceAlign;

                    $smallerDeviceAlign = $deviceAlign;
                }
                else
                {
                    $classes[] = "col-$device-" . $objRow->$smallerDeviceAlign;
                }

                if( $conditionalWidth && !in_array($device, $conditionalWidth) )
                {
                    $classes[] = "col-" . $device . "-hidden";
                }
            }

            if( $isBackend )
            {
                $classes[] = "layout-element";
            }

            $strClasses .= implode(" ", $classes);

            $classPropertyPos = strpos($strBuffer, "class=\"");

            if( $classPropertyPos !== false )
            {
//                $strBuffer = substr_replace($strBuffer, "class=\"" . $strClasses . " ", $classPropertyPos, (strlen("class=\"") - 1));
                $strBuffer = substr_replace($strBuffer, "class=\"" . $strClasses . " ", $classPropertyPos, strlen("class=\""));
            }
        }

        return ContentRenderer::renderColumns($strBuffer, $objRow);

















        global $objPage;

        $elementClass   = $this->getElementClass( $objRow ); //$objRow->typePrefix . $objRow->type;
        $objArticle     = \ArticleModel::findByPk( $objRow->pid );

        $arrElementClasses = array();

        if( $objRow->type === "alias" )
        {
            $rowClasses = $objRow->classes[0];

            $objRow = \ContentModel::findByPk ( $objRow->cteAlias );

            $strBuffer = $this->addClassToContentElement($strBuffer, $objRow, explode(" ", $rowClasses) );
        }

//        if( $objRow->type === "module" )
//        {
//            $objModule = \ModuleModel::findByPk( $objRow->module );
//
//            if( $objModule )
//            {
//                $elementClass = 'mod_' . $objModule->type;
//            }
//        }

        if( $objRow->type === 'image' )
        {
            if( $objRow->fullsize )
            {
                $strBuffer = ContentHelper::generateImageHoverTags($strBuffer, $objRow);

                $arrElementClasses[] = 'hover-container';
            }

            if( (FALSE !== strpos( $cssID[1], 'bg-image' )) || ($objRow->elementIsBox && $objRow->boxImageIsBG) )
            {
                $bgImage    = '';
                $objImage   = \FilesModel::findByPk( $objRow->singleSRC );

                if( $objImage )
                {
                    $bgImage = $objImage->path;
                }

                $bgClass = 'cover';

                if( (FALSE !== strpos( $cssID[1], 'bg-contain' )) || ($objRow->elementIsBox && $objRow->boxImageMode === "contain") )
                {
                    $bgClass = 'contain';
                }

                $strAttributes = '';

                if( FALSE !== strpos( $cssID[1], 'box-image' ) )
                {
                    $strAttributes = ' data-stellar-offset-parent="true" data-stellar-background-ratio="0.85"';
                }

                $strBuffer = preg_replace('/<figure([A-Za-z0-9\s\-=",;.:_\(\)\{\}\/]{0,})>([A-Za-z0-9\s\n\-<>,;.:="\/_]{0,})<\/figure>/', '<figure$1 style="background-image:url(' . $bgImage . ')"' . $strAttributes . '></figure>', $strBuffer);
                $strBuffer = str_replace('image_container', 'image_container bg-image bg-' . $bgClass, $strBuffer);
            }

            if( FALSE !== strpos( $cssID[1], 'extend-caption' ) )
            {
                preg_match_all('/<figcaption([A-Za-z0-9\s\-=",;.:_\(\)\{\}\/]{0,})>(.*)<\/figcaption>/', $strBuffer, $captionMatches);

                $strBuffer = preg_replace('/<figcaption([A-Za-z0-9\s\-=",;.:_\(\)\{\}\/]{0,})>(.*)<\/figcaption>/', '<figcaption$1>' . ContentHelper::renderText($captionMatches[2][0], true) . '</figcatpion>', $strBuffer);
            }
        }
        elseif( $objRow->type === 'text')
        {
            if( $objRow->addImage )
            {
                $addClass = '';

                if( strlen($objRow->headlineImagePosition) )
                {
                    $arrHeadline        = \StringUtil::deserialize($objRow->headline, true);

                    $headlineUnit       = $arrHeadline['unit'];
                    $headlineValue      = $arrHeadline['value'];

                    if( strlen($headlineValue) )
                    {
                        $strHeadline        = '<' . $headlineUnit . '>' . $headlineValue . '</' . $headlineUnit . '>';

                        if( $objRow->floating == "above" && $objRow->headlineImagePosition == "bottom" )
                        {
                            if( $arrHeadline['value'] != "" )
                            {
                                $strBuffer = str_replace($strHeadline , '', $strBuffer);
                            }

                            $strBuffer = str_replace('</figure>' , '</figure>' . $strHeadline, $strBuffer);
                        }
                        elseif( $objRow->floating === "left" || $objRow->floating === "right" )
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
                }

                $addClass .= ' ip-' . preg_replace('/float_/', '', $objRow->floating);

                $strBuffer = str_replace('class="ce_text' , 'class="ce_text has-image' . $addClass, $strBuffer);

                if( $objRow->fullsize && !$objRow->imageUrl )
                {
                    $strBuffer = Helper::generateImageHoverTags( $strBuffer, $objRow );

                    $arrElementClasses[] = 'hover-container';
                }

                if( preg_match('/add-image-sizer/', $cssID[1]) )
                {
                    $strBuffer = preg_replace('/<figure([A-Za-z0-9\s\-_,;.:\/\\="]{0,})class="image_container([A-Za-z0-9\s\-_]{0,})"([A-Za-z0-9\s\-_,;.:\/\\="]{0,})>/', '<figure$1class="image_container$2"$3><div class="image-figure-inside">', $strBuffer);
                    $strBuffer = preg_replace('/<\/figure>/', '</div><div class="image-sizer"></div></figure>', $strBuffer);
                }
            }

            if( preg_match('/first-p-big/', $cssID[1]) && preg_match('/add-first-p-big-divider/', $cssID[1]))
            {
                $strBuffer = preg_replace('/<p>(.*)<\/p>/', '<p><span class="big">$1</span><span class="divider"></span></p>', $strBuffer, 1);
            }
            else
            {
                preg_match_all('/<p>(.*)<\/p>/', $strBuffer, $arrLineMatches);

                if( is_array($arrLineMatches[0]) && count($arrLineMatches[0]) )
                {
                    foreach($arrLineMatches[0] as $key => $strLine)
                    {
                        if( $key === 0 && preg_match('/<strong/', $strLine) )
                        {
                            $strNewLine = preg_replace('/<p/', '<p class="strong-line"', $strLine);
                            $strBuffer  = preg_replace('/' . preg_quote($strLine, '/') . '/', $strNewLine, $strBuffer);

                            $arrElementClasses[] = 'first-p-big';
                        }
                    }
                }
            }

            if( preg_match('/text-middle/', $cssID[1]) )
            {
                $attrMatcher    = '([A-Za-z0-9\s\-_="\(\)\{\}:;\/]{0,})';
                $classMatcher   = '([A-Za-z0-9\s\-\{\}_:;]{0,})';

                $toReplaceTag   = '/<div' . $attrMatcher . 'class="' . $elementClass . $classMatcher . '"' . $attrMatcher . '>/';
                $replaceTag     = '<div$1class="' . $elementClass . '$2"$3>';

                if( $objRow->addImage )
                {
                    $toReplaceTag   = '/<\/figure>/';
                    $replaceTag     = '</figure>';
                }

                $strBuffer = preg_replace($toReplaceTag, $replaceTag . '<div class="table-container"><div class="ctable"><div class="ctable-row"><div class="ctable-cell">', $strBuffer);
                $strBuffer = $strBuffer . '</div></div></div></div>';
            }

            if( FALSE !== strpos($strBuffer, 'arrow-link') )
            {
                $iconArrow = '<span class="icon-arrow"><i class="ico ico-arrow"><svg version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
	 width="408px" height="408px" viewBox="0 0 408 408" style="enable-background:new 0 0 408 408;" xml:space="preserve">
<g>
	<g id="arrow-forward">
		<polygon points="204,0 168.3,35.7 311.1,178.5 0,178.5 0,229.5 311.1,229.5 168.3,372.3 204,408 408,204 		"/>
	</g>
</g>
</svg></i></span>';

                $strBuffer = preg_replace('/<a([A-Za-z0-9\s\-,;.:_="]+)class="([A-Za-z0-9\s\-,;.:_\/]{0,})arrow-link([A-Za-z0-9\s\-,;.:_]{0,})"([A-Za-z0-9\s\-,;.:_="\/]+)>([A-Za-z0-9\s\-,;.:_="öäüÖÄÜß]+)<\/a>/', '<a$1class="$2arrow-link$3"$4>$5' . $iconArrow . '</a>', $strBuffer);
            }

//            $strBuffer = Helper::renderText($strBuffer);
        }

        elseif( $objRow->type === 'newslist' )
        {
            $arrGapSize = \StringUtil::deserialize($objRow->news_gapSize, TRUE);

            if( $arrGapSize['value'] || $arrGapSize['value'] === '0' )
            {
                $gapSize    = ((int) $arrGapSize['value'] / 2) . $arrGapSize['unit']?:'px';
                $strBuffer  = preg_replace('/class="([A-Za-z0-9\s\-_]{0,})layout_([A-Za-z0-9\s\-_]{0,})"/', 'class="$1layout_$2" style="padding-right:' . $gapSize . ';padding-left:' . $gapSize . ';"', $strBuffer, -1, $gapCounter);

//                if( $gapCounter > 0 )
//                {
//                    $gapSizeTwice   = $arrGapSize['value'] . $arrGapSize['unit']?:'px';
//
//                    $strBuffer      = preg_replace('/class="([A-Za-z0-9\s\-_]{0,})layout_([A-Za-z0-9\s\-_]{0,})last([A-Za-z0-9\s\-_]{0,})" style="padding-right:' . $gapSize . ';/', 'class="$1layout_$2last$3" style="padding-right:' . $gapSizeTwice . ';', $strBuffer);
//                    $strBuffer      = preg_replace('/class="([A-Za-z0-9\s\-_]{0,})layout_([A-Za-z0-9\s\-_]{0,})first([A-Za-z0-9\s\-_]{0,})" style="padding-right:' . $gapSize . ';padding-left:' . $gapSize . ';/', 'class="$1layout_$2first$3" style="padding-right:' . $gapSize . ';padding-left:' . $gapSizeTwice . ';', $strBuffer);
//                }
            }
        }

        elseif( $objRow->type == "gallery" )
        {
            if( $objRow->fullsize )
            {
                $strBuffer = Helper::generateImageHoverTags( $strBuffer, $objRow );
            }

            $arrImages = ImageHelper::getMultipleImages($objRow->multiSRC, $objRow->orderSRC);

            preg_match_all('/<figure([A-Za-z0-9\s\-,;.:_\/\(\)\{\}="]{0,})>(.*?)<\/figure>/si', $strBuffer, $arrImageMatches);

            foreach( $arrImageMatches[0] as $key => $strImageFigure)
            {
                $arrImage       = $arrImages[ $key ];
                $strImageClass  = ($arrImage['meta']['cssClass'] ? ' ' : '') . $arrImage['meta']['cssClass'];

                $newImageFigure = preg_replace('/class="image_container/', 'class="image_container' . $strImageClass, $strImageFigure);

                if( preg_match('/vmiddle/', $cssID[1]) )
                {
                    $newImageFigure = preg_replace(array('/<figure([A-Za-z0-9\s\-,;.:_\/\(\)\{\}="]{0,})>/', '/<\/figure>/'), array('<figure$1><div class="ctable"><div class="ctable-cell">', '</div></div></figure>'), $newImageFigure);
                }

                $strBuffer      = preg_replace('/' . preg_quote($strImageFigure, '/') . '/', $newImageFigure, $strBuffer);
            }
        }



        switch( $objRow->type )
        {
            case "hyperlink":
                $strBuffer = $this->renderHyperlink( $strBuffer, $objRow, $objElement );
                break;

            case "gallery":
                $strBuffer = $this->renderGallery( $strBuffer, $objRow, $objElement );
                break;

            case "list":
                $strBuffer = $this->renderList( $strBuffer, $objRow, $objElement );
                break;

            case "table":
                $strBuffer = $this->renderTable( $strBuffer, $objRow, $objElement );
                break;

            case "slick-slider":
                $strBuffer = $this->renderSlickSliderImages( $strBuffer, $objRow, $objElement );
        }

        if( !in_array($objRow->type, $this->arrNotInsideClassElements) )
        {
//            if( $elementClass === "ce_alias" )
//            {
//                $objAliasElement    = \ContentModel::findByPk ( $objRow->cteAlias );
//                $elementClass       = 'ce_' . $objAliasElement->type;
//            }

            $addDivs    = '';
            $addEndDivs = '';

            if( $objRow->textValign && $objRow->textValign !== 'top' )
            {
                $addDivs = '<div class="ctable"><div class="ctable-cell" style="vertical-align:' . $objRow->textValign . ';">';
                $addEndDivs = '</div></div>';
            }

            if( $objRow->textBlockFloat && $objRow->textBlockFloat !== 'left' )
            {
                $addDivs .= '<div class="text-block">';
                $addEndDivs .= '</div>';
            }

            $strBuffer = preg_replace('/<div([A-Za-z0-9\s\-_="\(\)\{\}:;\/]{0,})class="' . $elementClass . '([A-Za-z0-9\s\-\{\}_:;]{0,})"([A-Za-z0-9\s\-_="\(\)\{\}:;\/]{0,})>/', '<div$1class="' . $elementClass . '$2"$3><div class="element-inside">' . $addDivs, $strBuffer, -1, $count);

            if( $count )
            {
                $strBuffer = $strBuffer . $addEndDivs . '</div>';
            }

//            echo "<pre>";
//            print_r( $strBuffer );
//            echo "<br>";
//            print_r( $count );
//            echo "<br>";
//            print_r( $elementClass );
//            echo "</pre>";
        }

        $strBuffer = preg_replace('/class="' . $elementClass . '/', 'class="' . $elementClass . ' content-element', $strBuffer);


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


//        $arrClasses     = array();
        $arrAttributes  = array();

        //TODO: merge rendered position with position function in content helper #80
        $strStyles = '';

        if( $objRow->position && BasicHelper::isFrontend() ) //TODO: auslagern in helper class, auch für rsce und sonstiges!!
        {
            $arrElementClasses[] = 'pos-' . ($objRow->positionFixed ? 'fixed' : 'abs');
            $arrElementClasses[] = 'pos-' . str_replace('_', '-', $objRow->position);
        }

        if( $objRow->textSize )
        {
            $arrElementClasses[] = 'text-size-' . $objRow->textSize;
        }

        if( $objRow->textColor )
        {
            $arrElementClasses[] = 'text-color-' . $objRow->textColor;
        }

        if( $objRow->textLineHeight )
        {
            $arrElementClasses[] = 'text-lh-' . $objRow->textLineHeight;
        }

        if( $objRow->textValign && $objRow->type === 'text' )
        {
            $arrElementClasses[] = 'text-valign-' . $objRow->textValign;
        }

        if( $objRow->textBlockFloat && $objRow->type === 'text' )
        {
            $arrElementClasses[] = 'text-block-float-' . $objRow->textBlockFloat;
        }

        if( $objRow->boxWidth )
        {
            $arrElementClasses[] = $objRow->boxWidth;
        }

        $arrPosMargin = \StringUtil::deserialize($objRow->positionMargin, TRUE);

        if( BasicHelper::isFrontend() && ($arrPosMargin['top'] || $arrPosMargin['right'] || $arrPosMargin['bottom'] || $arrPosMargin['left']) )
        {
            $unit       = $arrPosMargin['unit']?:'px';
            $useUnit    = true;
            $prefix     = 'margin';
            $transform  = '';

            if( preg_match('/mip/', $cssID[1]) )
            {
                $prefix = 'padding';
            }

            if( $arrPosMargin['top'] )
            {
                if( preg_match('/(' . implode('|', $GLOBALS['TL_CSS_UNITS']) . ')$/', $arrPosMargin['top']) )
                {
                    $useUnit = false;
                }

                if( preg_match('/' . $unit . '$/', $arrPosMargin['top']) )
                {
                    $useUnit = false;
                }

                if( preg_match('/(transY|transX)/', $arrPosMargin['top']) )
                {
                    $useUnit = false;
                    $transKey = 'Y';

                    if( preg_match('/transX/', $arrPosMargin['top']) )
                    {
                        $transKey = 'X';
                    }

                    $transValue = preg_replace('/^(transX|transY)/', '', $arrPosMargin['top']);

                    $strStyles .= ' transform:translate' . $transKey . '(' . $transValue . ($useUnit ?$unit:'') . ')';

                    if( $transKey === 'X' && $objRow->position === 'center_center' )
                    {
                        $strStyles .= 'translateY(-50%)';
                    }

                    $strStyles .= ';';
                }
                else
                {
                    $strStyles .= " " . $prefix . "-top:" . $arrPosMargin['top'] . ($useUnit ?$unit:'') . ";";
                }

                $useUnit    = true;
            }

            if( $arrPosMargin['right'] )
            {
                if( preg_match('/(' . implode('|', $GLOBALS['TL_CSS_UNITS']) . ')$/', $arrPosMargin['right']) )
                {
                    $useUnit = false;
                }

                if( $useUnit && preg_match('/' . $unit . '$/', $arrPosMargin['right']) )
                {
                    $useUnit = false;
                }

                if( $useUnit && (FALSE !== strpos( $arrPosMargin['right'], 'calc' ) || FALSE !== strpos( $arrPosMargin['right'], 'trans' )) )
                {
                    $useUnit = false;
                }

                if( preg_match('/(transY|transX)/', $arrPosMargin['right']) )
                {
                    $transKey = 'Y';

                    if( FALSE !== strpos( $arrPosMargin['right'], 'transX' ) )
                    {
                        $transKey = 'X';
                    }

                    $noEnd = false;
                    $transValue = preg_replace('/^(transX|transY)(&#40;|\()([A-Za-z0-9%\-+]{0,})(&#41;|\))/ui', '$3', trim($arrPosMargin['right']));

                    if( FALSE !== strpos($strStyles, 'transform') )
                    {
                        $noEnd = true;
                        $strStyles = preg_replace('/transform:/', "transform:translate" . $transKey . "(" . $transValue . (($useUnit)?$unit:'') . ") ", $strStyles);
                    }
                    else
                    {
                        $strStyles .= " transform:translate" . $transKey . "(" . $transValue . (($useUnit)?$unit:'') . ")";
                    }

                    if( $transKey === 'X' && $objRow->position === 'center_center' )
                    {
                        $strStyles .= 'translateY(-50%)';
                    }

                    if( !$noEnd )
                    {
                        $strStyles .= ';';
                    }
                }
                else
                {
                    $strStyles .= " " . $prefix . "-right:" . $arrPosMargin['right'] . (($useUnit)?$unit:'') . ";";
                }

                $useUnit    = true;
            }

            if( $arrPosMargin['bottom'] )
            {
                if( preg_match('/(' . implode('|', $GLOBALS['TL_CSS_UNITS']) . ')$/', $arrPosMargin['bottom']) )
                {
                    $useUnit = false;
                }

                if( preg_match('/' . $unit . '$/', $arrPosMargin['bottom']) )
                {
                    $useUnit = false;
                }

                $strStyles .= " " . $prefix . "-bottom:" . $arrPosMargin['bottom'] . (($useUnit)?$unit:'') . ";";

                $useUnit    = true;
            }

            if( $arrPosMargin['left'] )
            {
                if( preg_match('/(' . implode('|', $GLOBALS['TL_CSS_UNITS']) . ')$/', $arrPosMargin['left']) )
                {
                    $useUnit = false;
                }

                if( preg_match('/' . $unit . '$/', $arrPosMargin['left']) )
                {
                    $useUnit = false;
                }

                $strStyles .= " " . $prefix . "-left:" . $arrPosMargin['left'] . (($useUnit)?$unit:'') . ";";

                $useUnit    = true;
            }
        }

        if( TL_MODE === 'FE' && $objRow->position && $objRow->positionInnerContentWidth )
        {
            $insideStyle        = '';
            $intWebsiteWidth    = WebsiteStylesHelper::getConfigFieldValue( $objPage->rootAlias, 'websiteContentWidth');

            if( $arrPosMargin['left'] )
            {
//                $strStyles = preg_replace('/' . $prefix . '-left:([0-9a-zA-Z\(\)]+);/', '', $strStyles, -1, $styleMatches);
//                $insideStyle .= $prefix . '-left:' . $styleMatches[0] . ';';
            }

            if( $objRow->position === 'left_center' || $objRow->position === 'left_top' ||$objRow->position === 'left_bottom' )
            {
                $strStyles .= 'left:calc((100% - ' . $intWebsiteWidth . ') / 2);';
            }

            $strBuffer = preg_replace('/element-inside([A-Za-z0-9\s\-_]+)"/', 'element-inside$1" style="' . $insideStyle . '', $strBuffer);
        }

        if( strlen($strStyles) )
        {
            $arrAttributes['style'] = trim($arrAttributes['style'] . $strStyles);
        }

//        if( preg_match('/<h([1-6])([A-Za-z0-9\s\-=":;,._]{0,})>/', $strBuffer) )
//        {
            //TODO: check if match class attribute!!
//            $strBuffer = preg_replace('/<h([1-6])([A-Za-z0-9\s\-=":;,._]{0,})>/', '<h$1 class="headline"$2>', $strBuffer);
//        }

        if( $objRow->elementMargin )
        {
            $strCEStyles    = '';
            $margin         = \StringUtil::deserialize($objRow->elementMargin, TRUE);

            if( strlen($margin['top']) )
            {
                $strCEStyles .= 'margin-top:' . $margin['top'] . $margin['unit'] . ';';
            }
            if( strlen($margin['right']) )
            {
                $strCEStyles .= 'margin-right:' . $margin['right'] . $margin['unit'] . ';';
            }
            if( strlen($margin['bottom']) )
            {
                $strCEStyles .= 'margin-bottom:' . $margin['bottom'] . $margin['unit'] . ';';
            }
            if( strlen($margin['left']) )
            {
                $strCEStyles .= 'margin-left:' . $margin['left'] . $margin['unit'] . ';';
            }

            if( strlen($strCEStyles) )
            {
//                if( $arrAttributes['style'] && isset($arrAttributes['style']) && key_exists($arrAttributes['style'], 'style') )
//                {
//                    $arrAttributes['style'] .= $strCEStyles;
//                }
//                else
//                {
                    $arrAttributes['style'] .= $strCEStyles;
//                }
            }
        }

        $strBuffer = $this->renderHeadlines($strBuffer, $objRow);
        $strBuffer = $this->renderBox($strBuffer, $objRow, $objElement);
        $strBuffer = $this->renderImages( $strBuffer, $objRow );

        $objRootPage = \PageModel::findByPk( $objPage->rootId );

        if( $objRow->addAnimation || $objArticle->addAnimation || $objPage->addAnimation || $objRootPage->addAnimation )
        {
            $arrElementClasses[] = 'animate-box';

            $arrAttributes['data-animate']          = $objRow->animationType    ?:$objArticle->animationType;
            $arrAttributes['data-animate-offset']   = $objRow->animationOffset  ?:$objArticle->animationOffset;

            if( $objRow->animateRun === "once" || $objArticle->animateRun === "once" )
            {
                $arrAttributes['data-animate-trigger-once'] = 'true';
            }

            if( $objRow->animationWait || $objArticle->animationWait )
            {
                $arrElementClasses[] = 'animate-wait';

                $arrAttributes['data-wait'] = 1;
            }
        }

//        if( $objRow->removeSpacing )
//        {
//            $arrElementClasses[] = 'blank-item';
//        }

        if( $objRow->addElementSpace )
        {
            $arrElementClasses[] = 'space-item';
        }

        if( $objRow->elementPadding )
        {
            switch( $objRow->elementPadding )
            {
                case 'top_bottom':
                    $arrElementClasses[] = 'pad-tb';
                    break;

                case 'left_right':
                    $arrElementClasses[] = 'pad-lr';
                    break;

                case 'right':
                    $arrElementClasses[] = 'pad-r';
                    break;

                case 'all':
                    $arrElementClasses[] = 'pad-all';
                    break;

                case 'none':
                    $arrElementClasses[] = 'pad-none';
                    break;
            }
        }

        if( preg_match('/box/', $cssID[1]) && $objRow->addImage )
        {
            $strBuffer = preg_replace('/<p/', '<div class="text-container"><p', $strBuffer, 1, $count);

            if( $count )
            {
//                $strBuffer = preg_replace('~<\/p>(?!<\/p>)~', '</p></div>', $strBuffer);
                $strBuffer = $strBuffer . '</div>';
            }
        }

        list($strBoxBuffer, $arrBoxElementClasses, $arrBoxAttributes) = $this->renderBoxElement( $strBuffer, $objRow );

        if( count($arrBoxElementClasses) )
        {
            $arrElementClasses = array_merge($arrElementClasses, $arrBoxElementClasses);
        }

        if( count($arrBoxAttributes) )
        {
            $arrAttributes = array_merge($arrAttributes, $arrBoxAttributes);
        }

        $strBuffer = $strBoxBuffer;

        if( count($arrElementClasses) )
        {
            $strBuffer = $this->addClassToContentElement( $strBuffer, $objRow, $arrElementClasses );
        }

        if( count($arrAttributes) )
        {
            $strBuffer = $this->addAttributesToContentElement( $strBuffer, $objRow, $arrAttributes );
        }

        $strBuffer  = str_replace(['[R]'], ['<sup>&reg;</sup>'], $strBuffer);
//        $strBuffer = preg_replace(['/\|([A-Za-zöäüÖÄÜß0-9]{1,})\|/'], ['<strong>$1</strong>'], $strBuffer);
//        $strBuffer = preg_replace('/\|\|([^\|\|]+)\|\|/', '<span class="light">$1</span>', $strBuffer);
        $strBuffer  = preg_replace('/\|([^\|]+)\|/', '<strong>$1</strong>', $strBuffer);

//        $strBuffer  = preg_replace('/<figcaption()>(.+)<\/figcaption>/','<figcaption$1>' . preg_replace('/;/', '<br>', '$2') . '</figcaption>', $strBuffer);
        $strBuffer  = preg_replace_callback('/<figcaption([A-Za-z0-9\s\-="]{0,})>(.+)<\/figcaption>/', function( $matches )
        {
            return '<figcaption' . $matches[1] . '>' . preg_replace('/;/', '<br>', $matches[2]) . '</figcaption>';
        }, $strBuffer);



        $strID = $cssID[0]; //preg_replace('/ id="([A-Za-z0-9\-_]{0,})"/', '$1', $cssID[0]);

        if( !$strID )
        {
            $strID = 'el' . $objRow->id;

            $strBuffer = $this->addAttributesToContentElement($strBuffer, $objRow, ['id'=>$strID]);
        }

//        $selector = '#main .content-element#' . $strID . ' .element-inside';
        $selector = '#main .content-element#' . $strID . '';
        $arrStyles = array
        (
            'selector'          => $selector,

            'background'        => TRUE,
            'bgcolor'           => ColorHelper::renderColorConfig( $objRow->boxBackgroundColor ),

            'gradientAngle'     => $objRow->boxGradientAngle,
            'gradientColors'    => $objRow->boxGradientColors
        );

        $objStyleSheets     = new \StyleSheets();
        $strStyles          = $objStyleSheets->compileDefinition($arrStyles, true);

        $strStyles          = str_replace($selector . '{}', '', $strStyles);

        if( strlen($strStyles) )
        {
            // TODO: add styles to element not in head!?
            $GLOBALS['TL_HEAD'][] = '<style>' . $strStyles . '</style>';
        }

        $runFlexBox = true;

        if( $objArticle->customTpl === 'mod_article_slider_carousel' || $objArticle->id == 3 || $objRow->type === 'rsce_nav-box' )
        {
            $runFlexBox = false;
        }

        if( $runFlexBox && $objArticle && $objArticle->inColumn === 'main' )
        {
            //        if( ContentElementHelper::isFirst( $objRow ) )
//        {
//            $GLOBALS['TL_FLEX_COL'] = 1;
//        }

            $GLOBALS['TL_FLEX_COL'] = (int) $GLOBALS['TL_FLEX_COL']?:1 || 1;

//        echo "<pre>";
//        echo "COL BEFORE: "; print_R( $GLOBALS['TL_FLEX_COL'] );

            if( $objRow->boxWidth === 'w2' )
            {
//            echo "<br>"; print_R("W2");
                $GLOBALS['TL_FLEX_COL'] = ($GLOBALS['TL_FLEX_COL'] + 1);

//            echo " ==> "; print_R($GLOBALS['TL_FLEX_COL']);
            }
            elseif( $objRow->boxWidth === 'w3' )
            {
                $GLOBALS['TL_FLEX_COL'] = ($GLOBALS['TL_FLEX_COL'] + 2);
            }
            elseif( $objRow->boxWidth === 'w4' )
            {
                $GLOBALS['TL_FLEX_COL'] = 4;
            }
//        else
//        {
//            $GLOBALS['TL_FLEX_COL'] = $GLOBALS['TL_FLEX_COL'];
//        }

            $isLast     = ContentElementHelper::isLast( $objRow );
            $isFirst    = ContentElementHelper::isFirst( $objRow );

            if( $isFirst && (!$objRow->boxWidth || $objRow->boxWidth === 'w1') )
            {
                $GLOBALS['TL_FLEX_COL'] = 1;
            }
//echo "<br>"; print_r( $objRow->type );
//        echo " ==> "; print_r( $objRow->boxWidth );
//        echo " ==> "; print_r( $GLOBALS['TL_FLEX_COL'] );

            if( $objRow->clearBefore || $objRow->clearAfter || $isLast )
            {
//            echo "<br>"; print_r('BREAK');
//            echo " ==> "; print_r($isLast);
                $addCols    = (4 - $GLOBALS['TL_FLEX_COL']);
                $addColDivs = '';
//            echo " ==> "; print_r( $addCols );
                if( $addCols > 0 )
                {
                    $strBuffer = preg_replace('/<div class="break"><\/div>([\s\n\t]{0,})$/', '', $strBuffer);
                    $addColDivs = '<div class="ce_rsce_dummy content-element w' . $addCols . ' block"><div class="element-inside"></div></div>';
                }

                $strBuffer = $strBuffer . $addColDivs . '<div class="break"></div>';

                $GLOBALS['TL_FLEX_COL'] = 4;
            }

            if( $GLOBALS['TL_FLEX_COL'] >= 4 )
            {
//            echo "<br>COL IS FOUR";
                $GLOBALS['TL_FLEX_COL'] = 0;

                if( !$objRow->clearBefore || !$isLast )
                {
                    $strBuffer .= '<div class="break"></div>';
                }
            }

            $GLOBALS['TL_FLEX_COL'] = ($GLOBALS['TL_FLEX_COL'] + 1);
//        echo "<br>COL AFTER: "; print_R( $GLOBALS['TL_FLEX_COL'] );
//        echo "</pre>";

            $strBuffer = preg_replace('/<div class="break"><\/div>([\s\n]{0,})<div class="break"><\/div>/', '<div class="break"></div>', $strBuffer);
        }


        return $strBuffer;
    }



    protected function renderTextNavigation( $strBuffer, $objRow, $objElement )
    {
        global $objPage;

        $strBuffer = preg_replace('/<li>([\s\n]{0,})<a([A-Za-z0-9\s\-öäüÖÄÜß\/=,;.:_"#+?!&%\{\}]+)>/', '<li>$1<a$2><span itemprop="name">', $strBuffer, -1, $count);

        if( $count )
        {
            preg_match_all('/<li>([\s\n]{0,})<a([A-Za-z0-9\s\-öäüÖÄÜß\/=,;.:_"#+?!&%\{\}]+)href="([A-Za-z0-9\s\-öäüÖÄÜß\/,;.:_#+?!&%\{\}]+)"([A-Za-z0-9\s\-öäüÖÄÜß\/=,;.:_"#+?!&%\{\}]{0,})>/', $strBuffer, $matches);

            $strBuffer = preg_replace('/<\/a>([\s\n]{0,})<\/li>/', '</span></a>$1</li>', $strBuffer);

            if( count($matches[3]) )
            {
                foreach( $matches[3] as $key => $url )
                {
                    if( false !== strpos($url, 'link_url') )
                    {
                        $pageID = str_replace(['{{link_url::', '}}'], '', $url);

                        if( $objPage->id == $pageID )
                        {
                            $strBuffer = preg_replace('/' . preg_quote($matches[0][ $key ], '/'). '/', '<li><strong>', $strBuffer);
                            $strBuffer = preg_replace('/<li><strong><span itemprop="name">([A-Za-z0-9\s\-,;.:_?!#+ßöäüÖÄÜ\/=\{\}%&\[\]]+)<\/span><\/a>/', '<li><strong><span itemprop="name">$1</span></strong>', $strBuffer);
                        }
                    }
                }
            }
        }

        return $strBuffer;
    }



    protected function renderBoxElement( $strBuffer, $objRow )
    {
        $cssID = \StringUtil::deserialize($objRow->cssID, TRUE);

        $GLOBALS['IIDO']['BOXES']['OPEN_CONT']      = $GLOBALS['IIDO']['BOXES']['OPEN_CONT'] || FALSE;
        $GLOBALS['IIDO']['BOXES']['MASONRY']        = $GLOBALS['IIDO']['BOXES']['MASONRY'] || FALSE;
        $GLOBALS['IIDO']['BOXES']['MASONRY_ID']     = $GLOBALS['IIDO']['BOXES']['MASONRY_ID']?:0 || 0;
//        $GLOBALS['IIDO']['BOXES']['BOX_CONT_WIDTH'] = $GLOBALS['IIDO']['BOXES']['BOX_CONT_WIDTH'] || 0;

        $arrElementClasses  = array();
        $arrAttributes      = array();

        if( $objRow->elementIsBox && TL_MODE === 'FE' )
        {
            if( !$GLOBALS['IIDO']['BOXES']['OPEN_CONT'] )
            {
                $GLOBALS['IIDO']['BOXES']['OPEN_CONT'] = TRUE;

                $addClasses = '';
                $addID      = '';

                if( preg_match('/sort-masonry/', $cssID[1]) )
                {
                    $addClasses .= ' sort-masonry';
                    $addID      = ' id="sortBoxes_' . $objRow->id . '"';

                    $GLOBALS['IIDO']['BOXES']['MASONRY'] = TRUE;
                    $GLOBALS['IIDO']['BOXES']['MASONRY_ID'] = $objRow->id;
                }

                $strBuffer = '<div class="box-element-container no-gap' . $addClasses . '"' . $addID . '><div class="bec-inside">' . $strBuffer;
            }

            $arrElementClasses[] = 'box-element';

            switch( $objRow->boxWidth )
            {
                case "w1":
                    $arrElementClasses[] = 'w25';
                    break;

                case "w2":
                    $arrElementClasses[] = 'w50';
                    break;

                case "w3":
                    $arrElementClasses[] = 'w75';
                    break;

                case "w4":
                    $arrElementClasses[] = 'w100';
                    break;

                default:
                    $arrElementClasses[] = $objRow->boxWidth;
                    break;
            }

            switch( $objRow->boxHeight )
            {
                case "ha":
                    $arrElementClasses[] = 'hauto';
                    break;

                case "h1":
                    $arrElementClasses[] = 'hone';
                    break;

                case "h2":
                    $arrElementClasses[] = 'htwo';
                    break;

                case "h1x1":
                case "h1_1":
                case "1_1":
                case "1x1":
                    $arrElementClasses[] = 'h100';
                    break;

                case "h1x2":
                case "h1_2":
                case "1_2":
                case "1x2":
                    $arrElementClasses[] = 'h200';
                    break;

                default:
                    $arrElementClasses[] = $objRow->boxHeight;
                    break;
            }

            if( $objRow->boxWidth )
            {
                $arrElementClasses[] = 'has-width';
            }

            if( $objRow->boxHeight )
            {
                $arrElementClasses[] = 'has-height';
            }

            if( $objRow->boxValignPosition )
            {
                $arrElementClasses[] = 'fval-' . $objRow->boxValignPosition;
            }

            $bgColor = ColorHelper::compileColor( $objRow->boxBackgroundColor, TRUE );

            if( $bgColor !== "transparent" && !$objRow->boxLink )
            {
                $arrAttributes['style'][] = 'background-color:' . $bgColor . ';';
            }

            if( $objRow->boxImageIsBG )
            {
                $arrElementClasses[] = 'bg-image';
                $arrElementClasses[] = 'bg-' . $objRow->boxImageMode;
            }

            $strIcon        = '';
            $strOpenLink    = '';

            if( $objRow->boxIcon )
            {
                $objImage = \FilesModel::findByPk( $objRow->boxIcon );

                if( $objImage )
                {
                    $strIcon = '<div class="icon-tag" style="background-image:url(' . $objImage->path . ');"></div>';

                    $arrElementClasses[] = 'has-icon';
                }
            }

            if( $objRow->boxLink )
            {
                $arrElementClasses[] = 'has-link';

                $styles = '';

                if( $bgColor !== "transparent" )
                {
                    $styles = ' style="background:' . $bgColor . ';"';
                }

                $strOpenLink = '<a href="' . $objRow->boxLink . '"' . $styles . '>';
            }

            $strBuffer = preg_replace('/<div([A-Za-z0-9\s\-=",;.:_\/\(\)\{\}]{0,})class="element-inside([A-Za-z\s\-_\{\}]{0,})"([A-Za-z0-9\s\-=",;.:_\/\(\)\{\}]{0,})>/',  '<div$1class="element-inside$2"$3>' . $strOpenLink . '<div class="element-box-inside">' . $strIcon, $strBuffer, -1, $count);

            if( $count )
            {
                $strCloseLink = '';

                if( $objRow->boxLink )
                {
                    $strCloseLink = '</a>';
                }

                $strBuffer = $strBuffer . '</div>' . $strCloseLink;
            }
            else
            {
                $strBuffer = preg_replace('/<\/div>$/', '</a></div>', trim($strBuffer));
            }

            $strContentTable    = \ContentModel::getTable();
            $objNextElement     = \ContentModel::findOneBy(array($strContentTable . '.pid=?', $strContentTable . '.invisible=?', $strContentTable . '.elementIsBox=?', $strContentTable . '.sorting>?'), array($objRow->pid, '', '1', $objRow->sorting));

            if( !$objNextElement )
            {
                $GLOBALS['IIDO']['BOXES']['OPEN_CONT'] = FALSE;

                $strBuffer = $strBuffer . '</div></div>';

                if( $GLOBALS['IIDO']['BOXES']['MASONRY'] )
                {
                    ScriptHelper::addScript('masonry');

                    $strBuffer = $strBuffer . $this->getMasonryScript( 'sortBoxes_', $GLOBALS['IIDO']['BOXES']['MASONRY_ID'], ' > .bec-inside' );
                }
            }
        }
        else
        {
            if( $GLOBALS['IIDO']['BOXES']['OPEN_CONT'] )
            {
                $GLOBALS['IIDO']['BOXES']['OPEN_CONT'] = FALSE;

                $strBuffer = '</div></div>' . $strBuffer;

                if( $GLOBALS['IIDO']['BOXES']['MASONRY'] )
                {
                    ScriptHelper::addScript('masonry');

                    $strBuffer = $strBuffer . $this->getMasonryScript( 'sortBoxes_', $GLOBALS['IIDO']['BOXES']['MASONRY_ID'], ' > .bec-inside' );
                }
            }
        }

        if( $objRow->elementIsBox && TL_MODE === 'FE' )
        {
            $arrHeadline     = \StringUtil::deserialize( $objRow->headline, TRUE );
            $headlineValue  = $arrHeadline['value'];
            $headlineUnit   = $arrHeadline['unit'];

            $addPosClass    = '';

            if( $objRow->boxTitlePosition )
            {
                $addPosClass = ' has-position pos-' . $objRow->boxTitlePosition;
            }

            $headlinContOpen    = '<div class="headline-container title-box' . $addPosClass . '">';
            $headlinContClose   = '</div>';

            $headlineRegexStart = '/<' . $headlineUnit . '/';
            $headlineRegexEnd   = '/<\/' . $headlineUnit . '>/';

            $headlineRepalceStart   = '<' . $headlineUnit;
            $headlineRepalceEnd     = '</' . $headlineUnit . '>';

            if( $objRow->topHeadline )
            {
                $headlineRegexStart     = '/<div class="top-headline/';
                $headlineRepalceStart   = '<div class="top-headline';
            }

            if( $objRow->subHeadline )
            {
                $headlineRegexEnd   = '/<div class="sub-headline([A-Za-z0-9\s\-;:,._]{0,})"([A-Za-z0-9\s\-;:,._="\(\))]{0,})>(.*)<\/div>/';
                $headlineRepalceEnd = '<div class="sub-headline$1"$2>$3</div>';
            }
//echo "<pre>"; print_R( $strBuffer ); exit;
//            echo "<pre>";
//            print_r( $headlineRegexStart );
//            echo "<br>";
//            print_r( $headlinContOpen );
//            echo "<br>";
//            print_r( $headlineRepalceStart );
//
//            echo "<br><br>";
//            print_r( $headlineRegexEnd );
//            echo "<br>";
//            print_r( $headlineRepalceEnd );
//            echo "<br>";
//            print_r( $headlinContClose );
//            exit;
//echo "<pre>"; print_r( $strBuffer ); echo "</pre>";
            $strBuffer = preg_replace($headlineRegexStart, $headlinContOpen . $headlineRepalceStart, $strBuffer);
            $strBuffer = preg_replace($headlineRegexEnd,  $headlineRepalceEnd . $headlinContClose, $strBuffer);
        }

//        echo "<pre>"; print_r( $strBuffer ); echo "</pre>";

        return [$strBuffer, $arrElementClasses, $arrAttributes];
    }



    protected function getMasonryScript( $strID, $intID, $strSelector = '' )
    {
        return '<script>
$("#' . $strID . $intID . $strSelector . '").masonry({
    itemSelector: ".box-element",
    percentPosition : true
});
</script>';
    }



    protected function renderNewHeadlines( $strContent, $objRow, &$objElement )
    {
        $objConfig      = System::getContainer()->get('iido.basic.config');
        $arrFields      = StringUtil::deserialize( $objConfig->get('elementFields'), true);

        $cssID          = StringUtil::deserialize( $objRow->cssID, true);
        $strContent     = str_replace('®', '&reg;', $strContent);

        $arrHeadline    = \StringUtil::deserialize($objRow->headline, TRUE);
        $unit           = $arrHeadline['unit'];
        $headline       = $arrHeadline['value'];
        $repHeadline    = '<' . $unit . '>' . $headline . '</' . $unit . '>';

        $topHeadline    = (($objConfig->get('includeElementFields') && in_array( 'topHeadline', $arrFields )) ? $objRow->topHeadline : '');
        $subHeadline    = (($objConfig->get('includeElementFields') && in_array( 'subHeadline', $arrFields )) ? $objRow->subHeadline : '');

        if( !$headline || false !== strpos($objRow->type, 'rsce_') || $objRow->type === 'headline' )
        {
            return $strContent;
        }

        if( false !== strpos($cssID[1], 'show-as-columns') )
        {
            $strContent = preg_replace('/<' . $unit . '>' . preg_quote($headline, '/') . '<\/' . $unit . '>/', '', $strContent);

            $strContent = preg_replace('/class="ctable-cell">/', 'class="ctable-cell">##HEADLINE##', $strContent);
            $repHeadline   = '##HEADLINE##';
        }

        $newHeadline    = '<' . $unit . ' class="headline">' . ContentHelper::renderText( $headline, true ) . '</' . $unit . '>';
//        $strContent     = preg_replace('/<' . $unit . '>/', '<' . $unit . ' class="headline">', $strContent);

        if( $topHeadline )
        {
            $topHeadline = '<div class="top-headline">' . $topHeadline . '</div>';
        }

        if( $subHeadline )
        {
            $subHeadline = '<div class="sub-headline">' . $subHeadline . '</div>';
        }

        return preg_replace('/' . preg_quote($repHeadline, '/') . '/',  $topHeadline . $newHeadline . $subHeadline, $strContent);
    }



    protected function renderHeadlines( $strContent, $objRow )
    {
        global $objPage;

        $strContent = str_replace('®', '&reg;', $strContent);

        $arrHeadlineClasses     = array();
        $arrTopHeadlineClasses  = array();
        $arrSubHeadlineClasses  = array();

        $arrContClasses         = array();

        $arrStyleHeadlineClasses    = array();
        $arrElementClasses          = array();

        $arrHeadline    = \StringUtil::deserialize($objRow->headline, TRUE);
        $unit           = $arrHeadline['unit'];
        $headline       = $arrHeadline['value'];

//        $arrTopHeadline = \StringUtil::deserialize($objRow->topHeadline, TRUE);
//        $arrSubHeadline = \StringUtil::deserialize($objRow->subHeadline, TRUE);

        $strTopClass    = $strSubClass = ' unit-' . $unit;
        $replaceClass   = 'headline';
        $strSubStyles   = $strHeadlineStyles = '';

        $cssID = \StringUtil::deserialize($objRow->cssID, TRUE);


//        if( $objRow->addTopHeadline )
        if( $objRow->topHeadline )
        {
            $arrHeadlineClasses[] = 'has-top-headline';

            $arrContClasses[] = 'cont-has-top-headline';

            if( $objRow->headlineTopFloating )
            {
                $topFloating = preg_replace('/header_/', '', $objRow->headlineTopFloating);

                $arrTopHeadlineClasses[]    = 'text-' . $topFloating;
            }

            if( $objRow->headlineTopColor )
            {
                $arrTopHeadlineClasses[]    = 'ht-color-' . $objRow->headlineTopColor;
            }

            if( $objRow->headlineTopBorder )
            {
                $arrTopHeadlineClasses[]    = 'ht-border-' . $objRow->headlineTopBorder;
            }

            if( $objRow->headlineTopStyles )
            {
                $arrConfig = WebsiteStylesHelper::getConfigFieldValue( $objPage->rootAlias, 'headlineTopStyle' );

                if( count($arrConfig) )
                {
                    $arrStyle = array();

                    foreach($arrConfig as $arrHTSConfig )
                    {
                        if( $arrHTSConfig['internID'] === $objRow->headlineTopStyles )
                        {
                            $arrStyle = $arrHTSConfig;
                            break;
                        }
                    }

                    if( $arrStyle['tagClasses'] || $arrStyle['classes'] )
                    {
                        $arrTagClasses          = explode(" ", $arrStyle['classes']);
                        $arrTopElementClasses   = explode(" ", $arrStyle['tagClasses']);

                        if( count($arrTopElementClasses) )
                        {
                            $arrElementClasses = array_merge($arrElementClasses, $arrTopElementClasses);
                        }

                        foreach($arrTagClasses as $arrTagClass)
                        {
                            if( !in_array($arrTagClass, $arrTopHeadlineClasses) )
                            {
                                $arrTopHeadlineClasses[] = $arrTagClass;
                            }
                        }
                    }
                }
            }
        }

        if( $objRow->subHeadline )
        {
            $arrHeadlineClasses[] = 'has-sub-headline';

            $arrContClasses[] = 'cont-has-sub-headline';

            if( $objRow->headlineBottomFloating )
            {
                $subFloating = preg_replace('/header_/', '', $objRow->headlineBottomFloating);

                $arrSubHeadlineClasses[]    = 'text-' . $subFloating;
            }

            if( $objRow->headlineBottomColor )
            {
                $arrSubHeadlineClasses[]    = 'ht-color-' . $objRow->headlineBottomColor;
            }

            if( $objRow->headlineBottomBorder )
            {
                $arrSubHeadlineClasses[]    = 'ht-border-' . $objRow->headlineBottomBorder;
            }

            if( $objRow->headlineBottomStyles )
            {
                $arrConfig = WebsiteStylesHelper::getConfigFieldValue( $objPage->rootAlias, 'headlineBottomStyle' );

                if( count($arrConfig) )
                {
                    $arrStyle = array();

                    foreach($arrConfig as $arrHSSConfig )
                    {
                        if( $arrHSSConfig['internID'] === $objRow->headlineBottomStyles )
                        {
                            $arrStyle = $arrHSSConfig;
                            break;
                        }
                    }

                    if( $arrStyle['tagClasses'] || $arrStyle['classes'] )
                    {
                        $arrTagClasses          = explode(" ", $arrStyle['classes']);
                        $arrSubElementClasses   = explode(" ", $arrStyle['tagClasses']);

                        if( count($arrSubElementClasses) )
                        {
                            $arrElementClasses = array_merge($arrElementClasses, $arrSubElementClasses);
                        }

                        foreach($arrTagClasses as $arrTagClass)
                        {
                            if( !in_array($arrTagClass, $arrSubHeadlineClasses) )
                            {
                                $arrSubHeadlineClasses[] = $arrTagClass;
                            }
                        }
                    }
                }
            }
        }

        if( $objRow->headlineFloating )
        {
            $floating = preg_replace('/header_/', '', $objRow->headlineFloating);

            $arrHeadlineClasses[]       = 'text-' . $floating;
//            $arrTopHeadlineClasses[]    = 'text-' . $floating;
//            $arrSubHeadlineClasses[]    = 'text-' . $floating;
        }

        if( $objRow->headlineColor )
        {
            $arrHeadlineClasses[]    = 'ht-color-' . $objRow->headlineColor;
        }

        if( $objRow->headlineBorder )
        {
            $arrHeadlineClasses[]    = 'ht-border-' . $objRow->headlineBorder;
        }

        if( $objRow->headlineStyles )
        {
//            $arrConfig = \StringUtil::deserialize( \Config::get( BundleConfig::getTableFieldPrefix() . 'headlineStyles' ), TRUE );

            $arrConfig = WebsiteStylesHelper::getConfigFieldValue( $objPage->rootAlias, 'headlineStyle' );

            if( count($arrConfig) )
            {
                $arrStyle = array();

                foreach($arrConfig as $arrHSConfig )
                {
                    if( $arrHSConfig['internID'] === $objRow->headlineStyles )
                    {
                        $arrStyle = $arrHSConfig;
                        break;
                    }
                }

                if( $arrStyle['tagClasses'] || $arrStyle['classes'] )
                {
                    $arrTagClasses      = explode(" ", $arrStyle['classes']);
                    $arrElementClasses  = explode(" ", $arrStyle['tagClasses']);

                    foreach($arrTagClasses as $arrTagClass)
                    {
                        if( !in_array($arrTagClass, $arrStyleHeadlineClasses) )
                        {
                            $arrStyleHeadlineClasses[] = $arrTagClass;
                        }
                    }
                }

//                foreach($arrConfig as $arrHeadlineConfig)
//                {
//                    if( $objRow->headlineStyles === $arrHeadlineConfig['id'] )
//                    {
//                        if( $arrHeadlineConfig['tagClasses'] )
//                        {
//                            $arrTagClasses      = explode(" ", $arrHeadlineConfig['tagClasses']);
//                            $arrElementClasses  = explode(" ", $arrHeadlineConfig['elementClasses']);
//
//                            foreach($arrTagClasses as $arrTagClass)
//                            {
//                                if( !in_array($arrTagClass, $arrStyleHeadlineClasses) )
//                                {
//                                    $arrStyleHeadlineClasses[] = $arrTagClass;
//                                }
//                            }
//
//                            if( count($arrElementClasses) )
//                            {
//                                $strContent = $this->addClassToContentElement($strContent, $objRow, $arrElementClasses);
//                            }
//                        }
//                    }
//                }
            }
        }

        if( count($arrTopHeadlineClasses) )
        {
            $strTopClass = $strTopClass . ' ' . implode(' ', $arrTopHeadlineClasses);
        }

        if( count($arrSubHeadlineClasses) )
        {
            $strSubClass = $strSubClass . ' ' . implode(' ', $arrSubHeadlineClasses);
        }

        $arrMarginBottom = \StringUtil::deserialize($objRow->headlineMarginBottom, TRUE);

        if( $objRow->subHeadline && trim($arrMarginBottom['value']) !== '' )
        {
            $strSubStyles = 'margin-bottom:' . $arrMarginBottom['value'] . ($arrMarginBottom['unit'] !=='' ? $arrMarginBottom['unit'] : 'px') . ';';
        }
        elseif( trim($arrMarginBottom['value']) !== '' )
        {
            $strHeadlineStyles = 'margin-bottom:' . $arrMarginBottom['value'] . ($arrMarginBottom['unit'] !=='' ? $arrMarginBottom['unit'] : 'px') . ';';
        }

        $renderTopLines     = false;
        $renderBottomLines  = false;

        if( $objRow->headlineTopStyles === 'hs10' || $objRow->headlineTopStyles === 'hs11' )
        {
            $renderTopLines = true;
        }

        if( $objRow->headlineBottomStyles === 'hs10' || $objRow->headlineBottomStyles === 'hs11' )
        {
            $renderBottomLines = true;
        }

        $topHeadline    = ($objRow->topHeadline     ? '<div class="top-headline' . $strTopClass . '"><span class="th-inside">' . ContentHelper::renderText($objRow->topHeadline, $renderTopLines) . '</span></div>' : '');
        $subHeadline    = ($objRow->subHeadline     ? '<div class="sub-headline' . $strSubClass . '"' . ($strSubStyles ? ' style="' . $strSubStyles . '"' : '') . '><span class="sh-inside">' . ContentHelper::renderText($objRow->subHeadline, $renderBottomLines) . '</span></div>' : '');

        if( FALSE !== strpos($cssID[1], 'show-as-shadow-box') )
        {
            $topHeadline = '<div class="headline-box">' . $topHeadline;
            $subHeadline = $subHeadline . '</div>';
        }

        $strHeadline    = preg_replace(array('/;/', '/_/'), array('<br>', '&nbsp;'), $headline);

        if( count($arrHeadlineClasses) )
        {
            preg_match_all('/<h([1-6])([A-Za-z0-9\s\-_="\/\\\(\)\{\}]{0,})>([A-Za-z0-9\s\-,;.:_#+!?$%&€§"\|\'\/\\\(\)\{\}=ßöäüÖÄÜ@éèáàóòúùüâûêôñãõ\|]{0,})<\/h([1-6])>/u', $strContent, $arrHeadlineMatches);

            if( count($arrHeadlineMatches[0]) )
            {
                foreach( $arrHeadlineMatches[0] as $headlineNum => $strFindHeadline )
                {
                    $findUnit = $arrHeadlineMatches[1][ $headlineNum ];

                    if( preg_match('/class="/', $arrHeadlineMatches[2][ $headlineNum ]) )
                    {
                        $strNewHeadline = preg_replace('/<h' . $findUnit . '([A-Za-z0-9\s\-="_\(\)\{\}]{0,})class="/', '<h' . $findUnit . '$1class="headline ', $strFindHeadline);
                    }
                    else
                    {
                        $strNewHeadline = preg_replace('/<h' . $findUnit . '/', '<h' . $findUnit . ' class="headline"' . ($strHeadlineStyles ? ' style="' . $strHeadlineStyles . '"' : ''), $strFindHeadline);
                    }

                    $strNewHeadline = preg_replace('/' . preg_quote($arrHeadlineMatches[3][ $headlineNum ], '/') . '/', preg_replace('/;/', '<br>', $arrHeadlineMatches[3][ $headlineNum ]), $strNewHeadline);


                    $strContent = preg_replace('/' . preg_quote($strFindHeadline, '/') . '/', $strNewHeadline, $strContent);
                }
            }

            $strContent = preg_replace('/<h([1-6]) class="' . $replaceClass . '/', '<h$1 class="' . $replaceClass . ' ' . implode(" ", $arrHeadlineClasses), $strContent);
        }

        if( count($arrStyleHeadlineClasses) )
        {
//            if( $objRow->type === "headline" && !$objRow->subHeadline )
//            {
//                $replaceClass = 'ce_headline';
//            }

            $strUnit    = $arrHeadline['unit'];
            $strContent = preg_replace('/<' . $strUnit . ' class="' . $replaceClass . '/', '<' . $strUnit . ' class="' . $replaceClass . ' ' . implode(" ", $arrStyleHeadlineClasses), $strContent);
        }

        if( $objRow->type === "headline" )
        {
            $strContent = preg_replace('/<' . $unit . '([A-Za-z0-9\s\-_="\{\}]{0,})>([\s]{0,})' . preg_quote($headline, '/') . '<\/' . $unit . '>/', '<' . $unit . '$1>' . $strHeadline . '</' . $unit . '>', $strContent);
        }
        else
        {
            if( preg_match('/<h([1-6])>/', $strContent) )
            {
                $strContent = preg_replace('/<h' . $unit . '>' . preg_quote($headline, '/') . '<\/h' . $unit . '>/', '<h' . $unit . '>' . $strHeadline . '</h' . $unit . '>', $strContent);
                $strContent = preg_replace('/<h([1-6])>/', '<h$1 class="headline">', $strContent);
            }
            else
            {
                $strContent = preg_replace('/<h' . $unit . ' class="headline">' . preg_quote($headline, '/') . '<\/h' . $unit . '>/', '<h' . $unit . ' class="headline">' . $strHeadline . '</h' . $unit . '>', $strContent);
            }
        }

        $strContent = preg_replace('/<h([1-6]{1})([A-Za-z0-9\s\-_="\{\}\|:;.\(\)]{0,})>/', $topHeadline . '<h$1$2><span class="headline-inside"><span class="headline-span">', $strContent);
        $strContent = preg_replace('/<\/h([1-6]{1})>/', '</span></span></h$1>' . $subHeadline, $strContent);

        if( preg_match('/<\/h([1-6]{1})>([\s\n]{0,})<h([1-6]{1})/', trim($strContent)) )
        {
            $strContent = preg_replace('/class="headline([^\-])/', 'class="headline has-subline$1', $strContent, 1);
            $strContent = BasicHelper::replaceLastMatch('/class="sub-headline([^\-])/', 'class="subline is-subline$1', 'class="headline$1', $strContent);
        }

        if( $objRow->type === 'headline' || $objRow->type === 'hyperlink' )
        {
            $arrContClasses[] = 'valign-' . $objRow->headlineValign?:'bottom';

            if( $objRow->type === 'headline' )
            {
                $strContent = preg_replace('/ce_headline content-element/', '', $strContent);
                $strContent = preg_replace('/ class=""/', '', $strContent);

                if( $objRow->headlineValign === 'bottom' || $objRow->headlineValign === 'middle' )
                {
                    $strContent = '<div class="ctable"><div class="ctable-cell">' . $strContent . '</div></div>';
                }

                $strContent = '<div class="ce_headline content-element ' . implode(" ", $arrContClasses) . (count($arrContClasses) ? ' ' : '') . implode(" ", $objRow->classes) . (count($objRow->classes) ? ' ' : '') . $cssID[1] . (strlen($cssID[1]) ? ' ' : '') . 'block"><div class="element-inside">' . $strContent . '</div></div>';
            }
            else
            {
                if( $objRow->headlineValign === 'bottom' || $objRow->headlineValign === 'middle' )
                {
                    $strContent = preg_replace('/<div class="element-inside">/', '<div class="element-inside"><div class="ctable"><div class="ctable-cell">', $strContent, 1, $tCount);

                    if( $tCount )
                    {
                        $strContent .= '</div></div>';
                    }
                }

                $strContent = preg_replace('/class="ce_' . $objRow->type . '/', 'class="ce_' . $objRow->type . ' ' . implode(" ", $arrContClasses), $strContent);
            }
        }

        if( count($arrElementClasses) )
        {
            $strContent = $this->addClassToContentElement($strContent, $objRow, $arrElementClasses);
        }

        $strContent = preg_replace('/<span class="headline-inside">([\n\s]{0,})<span class="headline-span">([\n\s]{0,})<span class="headline-inside">([\n\s]{0,})<span class="headline-span">(.*)<\/span>([\n\s]{0,})<\/span>([\n\s]{0,})<\/span>([\n\s]{0,})<\/span>/', '<span class="headline-inside"><span class="headline-span">$4</span></span>', $strContent);

        if( $objRow->type === 'image' && ($headline || $topHeadline || $subHeadline) && $objRow->imageUrl )
        {

        }

        return $strContent;
    }



    protected function renderImages( $strContent, $objRow )
    {
        global $objPage;

        $objRootPage = \PageModel::findByPk( $objPage->rootId );

        if( $objRootPage->enableLazyLoad || $objPage->enableLazyLoad )
        {
        }

        $objImage = \FilesModel::findByUuid( $objRow->singleSRC );

        if( $objImage && $objImage->extension === "svg" )
        {
            $imageColor = ColorHelper::compileColor( $objRow->imageColor );

            if( $imageColor && $imageColor !== "transparent" )
            {
                $strContent = preg_replace_callback('/<img([A-Za-z0-9\s\-="\/.:,;_]{0,})src="([A-Za-z0-9\s\-\/.:,;_%]{0,})"([A-Za-z0-9\s\-="\/.:,;_]{0,})>/', function( $matches )
                {
                    $width  = preg_replace('/([A-Za-z0-9\s\-="]{0,})width="([0-9]{1,})"([A-Za-z0-9\s\-="]{0,})/', '$2', $matches[3]);
                    $height = preg_replace('/([A-Za-z0-9\s\-="]{0,})height="([0-9]{1,})"([A-Za-z0-9\s\-="]{0,})/', '$2', $matches[3]);

                    if( !$width )
                    {
                        $width  = preg_replace('/([A-Za-z0-9\s\-="]{0,})width="([0-9]{1,})"([A-Za-z0-9\s\-="]{0,})/', '$2', $matches[1]);
                    }

                    if( !$height )
                    {
                        $height = preg_replace('/([A-Za-z0-9\s\-="]{0,})height="([0-9]{1,})"([A-Za-z0-9\s\-="]{0,})/', '$2', $matches[1]);
                    }

                    return '<div class="img-replaced is-svg" style="-webkit-mask:url(##IMAGEPATH##);mask:url(##IMAGEPATH##);width:' . $width . 'px;height:' . $height . 'px;background-color:##IMAGEBACKGROUND##;"></div>';
                }, $strContent);

                $strContent = preg_replace('/##IMAGEPATH##/', preg_replace('/ /', '%20', $objImage->path), $strContent);
                $strContent = preg_replace('/##IMAGEBACKGROUND##/', $imageColor, $strContent);
            }
        }

        return $strContent;
    }



    protected function renderHyperlink( $strContent, $objRow, &$objElement )
    {
        $arrClasses         = array();
        $arrRemoveClasses   = array();
        $cssID              = \StringUtil::deserialize( $objRow->cssID, true);

//        if( $objRow->showAsButton ) //TODO: wird das noch gebraucht, eigenes element für buttons (RSCE)?!
//        {
//            $arrClasses[] = 'btn';
//            $arrClasses[] = 'btn-' . $objRow->buttonStyle;
//            $arrClasses[] = 'btn-type-' . $objRow->buttonType;
//
//            if( $objRow->linkTitle === '' )
//            {
//                $arrClasses[] = 'btn-empty';
//            }
//
//            if( $objRow->buttonAddon )
//            {
//                $arrClasses[] = 'btn-addon';
//                $arrClasses[] = 'addon-' . $objRow->buttonAddon;
//                $arrClasses[] = 'addon-pos-' . $objRow->buttonAddonPosition;
//
//                switch( $objRow->buttonAddon )
//                {
//                    case "arrow":
//                        $arrClasses[] = 'arrow-' . $objRow->buttonAddonArrow;
//                        break;
//
//                    case "icon":
//                        $strLinkText    = $objRow->linkTitle?:$objRow->url;
//                        $iconColor      = ColorHelper::compileColor( $objRow->buttonAddonIconColor );
//                        $iconBackground = (($iconColor !== "transparent") ? 'background:' . $iconColor . ';' : '');
//                        $strIconPath    = ImageHelper::renderImagePath( \FilesModel::findByPk( $objRow->buttonAddonIcon )->path );
//                        $strIcon        = '<i class="icon icon-mask" style="mask-image:url(' . $strIconPath . ');-webkit-mask-image:url(' . $strIconPath . ');' . $iconBackground . '"></i>';
//
//
//                        if( FALSE !== strpos( $cssID[1], 'box-link' ) )
//                        {
//
//                            if( $objRow->buttonAddonPosition === 'left' )
//                            {
//                                $strIcon .= ContentHelper::renderText($strLinkText, true);
//                            }
//                            elseif( $objRow->buttonAddonPosition === 'right' )
//                            {
//                                $strIcon = ContentHelper::renderText($strLinkText, true) . $strIcon;
//                            }
//                        }
//
////                        $arrClasses[] = 'icon-' . $objRow->buttonAddonIcon;
////                        $strContent = preg_replace('/<a/', '<a data-icon="' . $objRow->buttonAddonIcon . '"', $strContent);
//                        $strContent = preg_replace('/class="hyperlink_txt/', 'class="hyperlink_txt icon-link', $strContent);
//                        $strContent = preg_replace('/' . preg_quote($strLinkText, '/') . '([\s]{0,})<\/a>/', $strIcon . '</a>', $strContent);
//
////                        echo "<pre>"; print_r( $objRow );
////                        echo "<br>"; print_r( $strContent ); exit;
//                        break;
//                }
//            }
//        }

        $arrLinkClasses = array();

//        if( $objRow->showAsButtonBox || FALSE !== strpos($cssID[1], 'box') )
//        {
//            $arrClasses[] = 'box';
//
//            $linkBoxStyles  = '';
//
//            $boxWidth       = \StringUtil::deserialize( $objRow->bb_width );
//            $boxHeight      = \StringUtil::deserialize( $objRow->bb_height );
//            $bgBoxColor     = ColorHelper::compileColor( $objRow->bb_bgColor );
//            $arrBoxPadding  = \StringUtil::deserialize( $objRow->bb_padding, TRUE);
//
//            if( $bgBoxColor !== "transparent" )
//            {
//                $linkBoxStyles      = 'background-color:' . $bgBoxColor . ';';
//                $bgHoverBoxColor    = ColorHelper::compileColor( $objRow->bb_bgColorHover );
//
//                if( $bgHoverBoxColor === "transparent" )
//                {
//                    $bgHoverBoxColor = ColorHelper::mixColors($bgBoxColor, '#000000', 20.0);
//                }
//
//                $strContent = preg_replace('/<a/', '<a data-hbg="' . $bgHoverBoxColor . '"', $strContent);
//
//                $arrLinkClasses[] = 'has-bg-color';
//            }
//
//            if( $boxWidth['value'] && $boxWidth['unit'] )
//            {
//                $linkBoxStyles .= 'width:' . $boxWidth['value'] . $boxWidth['unit'] . ';';
//            }
//
//            if( $objRow->bb_removeMinWidth )
//            {
//                $arrLinkClasses[] = 'no-min-width';
//            }
//
//            if( $boxHeight['value'] && $boxHeight['unit'] )
//            {
//                $linkBoxStyles .= 'height:' . $boxHeight['value'] . $boxHeight['unit'] . ';';
//
//                $arrLinkClasses[] = 'has-height';
//            }
//
//            if( $objRow->bb_fontSize )
//            {
//                $linkBoxStyles .= 'font-size:' . $objRow->bb_fontSize . 'px;';
//            }
//
//            if( $arrBoxPadding['top'] )
//            {
//                $linkBoxStyles .= 'padding-top:' . $arrBoxPadding['top'] . $arrBoxPadding['unit'] . ';';
//            }
//            elseif( $objRow->bb_textValignMiddle )
//            {
//                $linkBoxStyles .= 'padding-top:0;';
//            }
//
//            if( $arrBoxPadding['right'] )
//            {
//                $linkBoxStyles .= 'padding-right:' . $arrBoxPadding['right'] . $arrBoxPadding['unit'] . ';';
//            }
//
//            if( $arrBoxPadding['bottom'] )
//            {
//                $linkBoxStyles .= 'padding-bottom:' . $arrBoxPadding['bottom'] . $arrBoxPadding['unit'] . ';';
//            }
//            elseif( $objRow->bb_textValignMiddle )
//            {
//                $linkBoxStyles .= 'padding-bottom:0;';
//            }
//
//            if( $arrBoxPadding['left'] )
//            {
//                $linkBoxStyles .= 'padding-left:' . $arrBoxPadding['left'] . $arrBoxPadding['unit'] . ';';
//            }
//
//            $strContent     = preg_replace('/<a/', '<a style="' . $linkBoxStyles . '"', $strContent);
//
//            if( $objRow->bb_textValignMiddle )
//            {
//                $strContent = preg_replace('/<a([A-Za-z0-9\s\-\{\}=",;.:_\(\)\#\|öäüÖÄÜß]{0,})>/', '<a$1><span class="ctable"><span class="ctable-cell">', $strContent, -1, $tableCount);
//
//                if( $tableCount )
//                {
//                    $strContent = preg_replace('/<\/a>/', '</span></span></a>', $strContent);
//                }
//            }
//
////            echo "<pre>"; print_r( $strContent ); exit;
//        }

//        if( $objRow->showAsButton )
//        {
//            switch( $objRow->buttonLinkMode )
//            {
//                case "lightbox":
//                    $arrLinkClasses[] = 'open-in-lightbox';
//                    break;
//
//                case "scroll":
//                    $arrLinkClasses[] = 'scroll-to';
//                    break;
//
//                case "nolink":
//                    $arrLinkClasses[] = 'no-link';
//                    break;
//            }
//        }

        if( preg_match_all('/atag-([A-Za-z0-9\-]{0,})/', $cssID[1], $arrClassMatches) )
        {
            foreach($arrClassMatches[1] as $strMatchClass)
            {
                $arrLinkClasses[] = $strMatchClass;

//                $cssID[1] = str_replace($strMatchClass, '', $cssID[1]);
//                $cssID[1] = preg_replace('/(\s{2,})/', ' ', $cssID[1]);
                $arrRemoveClasses[] = 'atag-' . $strMatchClass;
            }
        }

        if( count($arrLinkClasses) )
        {
            $strContent = preg_replace('/class="hyperlink_txt/', 'class="hyperlink_txt ' . implode(' ', $arrLinkClasses), $strContent);
        }

//        $strContent = Helper::renderText( $strContent, true);
        preg_match_all('/<a([A-Za-zöäüÖÄÜ0-9\s\-="_,;.:\{\}\(\)\/#!?\|+@#&]{0,})>([A-Za-zöäüÖÄÜ0-9\s\-,;\{\}:.!?\(\)\|<>\/="_@+&#]{0,})<\/a>/', $strContent, $arrMatches);

        if( count($arrMatches[0]) )
        {
            $beforeLink     = '';
            $afterLink      = '';

            $renderLines    = false;

//            if( preg_match('/no-text-lines/', $cssID[1]) || preg_match('/no-lines/', $cssID[1]) )
//            {
//                $renderLines    = false;
//
//                $beforeLink     = '<span class="link-text">';
//                $afterLink      = '</span>';
//            }

            if( preg_match('/text-lines/', $cssID[1]) || preg_match('/render-text-lines/', $cssID[1]) )
            {
                $renderLines    = true;
            }

//            $strTitle   = trim(preg_replace('/;/', '', $arrMatches[2][0]));
//            $strContent = preg_replace('/title="' . preg_quote($arrMatches[2][0], '/') . '"/', 'title="' . $strTitle . '"', $strContent);
            $strContent = preg_replace('/>' . preg_quote($arrMatches[2][0], '/') . '</', '>' . $beforeLink . ContentHelper::renderText($arrMatches[2][0], $renderLines, false, true) . $afterLink . '<', $strContent);
//            $strContent = preg_replace('/title="([A-Za-z0-9\s\-;,.:\{\}<>="]{0,})" rel="/', 'title="' . $strTitle . '" rel="', $strContent);
        }

        $strContent = $this->addClassToContentElement( $strContent, $objRow, $arrClasses );
        $strContent = $this->removeClassFromContentElement( $strContent, $objRow, $arrRemoveClasses );

        if( preg_match('/\{\{article_url::([0-9]{1,})\}\}/', $strContent, $arrLinkMatches) )
        {
            $objLinkArticle = \ArticleModel::findByPk( $arrLinkMatches[1] );

            $newLink = '#' . $objLinkArticle->alias;

            $strContent = preg_replace('/' . $arrLinkMatches[0] . '/', $newLink, $strContent);
        }

        preg_match_all('/title="([A-Za-z0-9\s\-,;.:\|öäüÖÄÜß]{0,})"/', $strContent, $titleMatches);

        if( count($titleMatches) && count($titleMatches[0]) )
        {
            $newTitle = preg_replace('/\|/', '', $titleMatches[1][0]);

            $strContent = preg_replace('/' . preg_quote($titleMatches[0][0], '/') . '/', 'title="' . $newTitle . '"', $strContent);
        }

        if( FALSE !== strpos($objRow->url, ':void') )
        {
            $strContent = preg_replace('/hyperlink_txt/', 'hyperlink_txt no-link', $strContent);
        }

//        $strContent = preg_replace('/rel="/', 'data-lightbox="', $strContent);
        if( false !== strpos($cssID[1], 'insert-icon') )
        {
            preg_match_all('/icon-([A-Za-z0-9\-]+)/', $cssID[1], $matchIcons);

            $iconName       = strtolower( $matchIcons[1][0] );
            $customerDir    = BasicHelper::getFilesCustomerDir();
            $rootDir        = BasicHelper::getRootDir( true );

            if( !file_exists($rootDir . 'files/' . $customerDir . '/Uploads/Icons/' . $iconName . '.svg') )
            {
                $iconName = ucfirst($iconName);

                if( !file_exists($rootDir . 'files/' . $customerDir . '/Uploads/Icons/' . $iconName . '.svg') )
                {
                    $iconName = false;
                }
            }

            if( $iconName )
            {
                $svgIcon = file_get_contents($rootDir . 'files/' . $customerDir . '/Uploads/Icons/' . $iconName . '.svg');

                $strContent = preg_replace('/<a([A-Za-z0-9\s\-öäüÖÄÜß\(\)\{\}\[\],;.:_#+\/="]+)>/', '<a$1><span class="link-inside">', $strContent);
                $strContent = preg_replace('/<\/a>/', '<span class="icon">' . $svgIcon . '</span></span></a>', $strContent);
            }
        }

        return $strContent;
    }



    protected function renderToplink( $strBuffer, $objRow, $objElement )
    {
        $cssID      = \StringUtil::deserialize( $objRow->cssID, true);
        $arrow      = '';
        $linkTitle  = $objRow->linkTitle;

        $arrParts   = explode('-', $linkTitle);

        if( false !== strpos($cssID[1], 'add-icon') || false !== strpos($cssID[1], 'add-arrow') )
        {
            $rootDir        = BasicHelper::getRootDir( true );
            $customerDir    = BasicHelper::getFilesCustomerDir();
            $iconPath       = $rootDir . 'files/' . $customerDir . '/Uploads/Icons/Arrow-up.svg';

            if( file_exists($iconPath) )
            {
                $arrow = '<span class="icon">' . file_get_contents($iconPath) . '</span>';
            }
        }

        $newTitle   = '<span class="title-left">' . $arrParts[0] . '</span>' . $arrow . '<span class="title-right">' . $arrParts[1] . '</span>';
        $strBuffer  = preg_replace('/' . $linkTitle . '<\/a>/', $newTitle . '</a>', $strBuffer);

        return $strBuffer;
    }



    protected function renderGallery( $strContent, $objRow, $objElement, $objAliasElement = false )
    {
//        $strContent = preg_replace('/<a/', '<a class="no-barba"', $strContent);
        $cssID      = \StringUtil::deserialize($objElement->cssID, TRUE);
        $addTitle   = false;

        if( preg_match('/title-hover/', $cssID[1]) )
        {
            $addTitle = true;
        }

//        if( $objRow->fullsize )
//        {
//            $strContent = Helper::generateImageHoverTags( $strContent, $objRow );
//        }

        if( $objAliasElement )
        {
            $arrImages = ImageHelper::getMultipleImages($objAliasElement->multiSRC, $objAliasElement->orderSRC);
        }
        else
        {
            $arrImages = ImageHelper::getMultipleImages($objElement->multiSRC, $objElement->orderSRC);
        }


        preg_match_all('/<figure([A-Za-z0-9\s\-,;.:_\/\(\)\{\}="]{0,})>(.*?)<\/figure>/si', $strContent, $arrImageMatches);

        foreach( $arrImageMatches[0] as $key => $strImageFigure)
        {
            $arrImage       = $arrImages[ $key ];
            $strImageClass  = ($arrImage['meta']['cssClass'] ? ' ' : '') . $arrImage['meta']['cssClass'];

            $isSVG          = preg_match('/\.svg$/', $arrImage['singleSRC']);

            $newImageFigure = preg_replace('/class="image_container/', 'class="image_container' . $strImageClass, $strImageFigure);

            if( $addTitle )
            {
                $hoverTitle     = $arrImage['meta']['hoverTitle']?:$arrImage['meta']['title'];
                $newImageFigure = preg_replace('/<\/figure>/', '<div class="hover-title-cont"><div class="htc-inside">' . $hoverTitle . '</div></div></figure>', $newImageFigure);
            }

            if( $isSVG )
            {
                $strImage = file_get_contents(BasicHelper::getRootDir( true ) . $arrImage['singleSRC']);
                $newImageFigure = preg_replace('/<img([A-Za-z0-9\s\-\/\(\)\{\},;.:="_öäüÖÄÜß]+)>/', $strImage, $strImageFigure);
            }

            $strContent     = preg_replace('/' . preg_quote($strImageFigure, '/') . '/', $newImageFigure, $strContent);
        }

//        if( $objRow->text )
//        {
//            $strText = '<div class="gallery-text">' . \StringUtil::encodeEmail( \StringUtil::toHtml5($objRow->text) ) . '</div>';
//
//            $strContent = preg_replace('/<\/ul>/', '</ul>' . $strText, $strContent);
//        }

        $strContent = preg_replace(array('/<figure/', '/<\/figure>/'), array('<div class="gallery-item-inside"><figure', '</figure></div>'), $strContent);

        return $strContent;
    }



    protected function renderSlickSliderImages( $strBuffer, $objRow, $objElement )
    {
        if( preg_match('/data-lightbox/', $strBuffer) )
        {
            $strBuffer = preg_replace('/<\/a>([\s\n]{0,})<\/figure>/', '<div class="plus"></div></a></figure>', $strBuffer);
        }

        return $strBuffer;
    }



    protected function addClassToContentElement( $strContent, $objRow, array $arrClasses )
    {
        if( count($arrClasses) )
        {
            $elementClass       = $this->getElementClass( $objRow );
            $elementClassNew    = preg_replace('/(\s{2,})/', ' ', $elementClass);

            $newElementClasses  = preg_replace('/(\s{2,})/', ' ', ' ' . implode(' ', $arrClasses));

            $strContent     = preg_replace('/class="' . $elementClass . '/', 'class="' . trim($elementClassNew) . $newElementClasses, $strContent);
        }

        return $strContent;
    }



    protected function removeClassFromContentElement( $strContent, $objRow, array $arrClasses)
    {

        foreach($arrClasses as $strClass)
        {
            $strContent     = preg_replace('/ ' . preg_quote($strClass, '/') . '/', '', $strContent);
        }

//        $strContent     = preg_replace('/(\s{2,})/', ' ', $strContent);

        return $strContent;
    }



    protected function addAttributesToContentElement( $strContent, $objRow, array $arrAttributes)
    {
        $strAttributes = '';

        foreach($arrAttributes as $attributeName => $attributeValue)
        {
            if( !is_array($attributeValue) )
            {
                $attributeValue = array($attributeValue);
            }

            $strAttributes .= $attributeName . '="' . implode("", $attributeValue) . '"';
        }

        $elementClass   = $this->getElementClass( $objRow );
        $strContent     = preg_replace('/class="' . $elementClass . '/', $strAttributes . ' class="' . $elementClass, $strContent);

        return $strContent;
    }



    protected function renderBox($strBuffer, $objRow, $objElement)
    {
        if( TL_MODE === "FE" )
        {
            $cssID      = \StringUtil::deserialize( $objRow->cssID );
            $strType    = '';

            $GLOBALS['IIDO']['BOXES']['OPEN_WRAPPER'] = $GLOBALS['IIDO']['BOXES']['OPEN_WRAPPER'] || FALSE;

            if( $objRow->type === "slick-content-start" )
            {
                $GLOBALS['IIDO']['BOXES']['OPEN_WRAPPER'] = TRUE;
            }
            elseif( $objRow->type === "slick-content-stop" && $GLOBALS['IIDO']['BOXES']['OPEN_WRAPPER'])
            {
                $GLOBALS['IIDO']['BOXES']['OPEN_WRAPPER'] = FALSE;
            }

            if( $objRow->type === "alias" )
            {
                $objAliasElement = \ContentModel::findByPk( $objRow->cteAlias );
                $strType = $objAliasElement->type;
            }

            if( (preg_match('/ column/', $cssID[1]) || preg_match('/ box-item/', $cssID[1]) || $objRow->type === "rsce_box" || $strType === "rsce_box" ) && !$GLOBALS['IIDO']['BOXES']['OPEN_WRAPPER'] && !preg_match('/column-box/', $cssID[1]) )
            {
                $GLOBALS['IIDO']['BOXES']['OPEN'] = $GLOBALS['IIDO']['BOXES']['OPEN'] || FALSE;

                if( $GLOBALS['IIDO']['BOXES']['OPEN'] === FALSE )
                {
                    $GLOBALS['IIDO']['BOXES']['OPEN'] = TRUE;

                    $strClass       = 'fbc';
                    $strBoxClass    = '';

                    if( preg_match('/box-col/', $cssID[1]) || preg_match('/column/', $cssID[1]))
                    {
                        $strClass = 'columns';
                    }

                    if( $objRow->type === "rsce_box" )
                    {
                        $strClass       = 'boxes';
                        $strBoxClass    = ' no-padding';
                    }

                    if( preg_match('/is-multiline/', $cssID[1]))
                    {
                        $strClass       .= ' is-multiline';
                        $strBoxClass    .= ' multiline-boxes';
                    }

                    $strBuffer = '<div class="box-container clr-after' . $strBoxClass . '"><div class="box-cont-inside"><div class="box-cont-wrapper ' . $strClass . '">' . $strBuffer;
                }

                if( preg_match('/box-col/', $cssID[1]) || preg_match('/column/', $cssID[1]))
                {
                    $strBuffer = $this->addClassToContentElement($strBuffer, $objRow, array('column'));
                }

                if( $GLOBALS['IIDO']['BOXES']['OPEN'] === TRUE && $this->checkIfLastBox( $objRow ) )
                {
                    $GLOBALS['IIDO']['BOXES']['OPEN'] = FALSE;

                    $strBuffer = $strBuffer . '</div></div></div>';
                }
            }
        }

        return $strBuffer;
    }



    protected function checkIfLastBox( $objRow )
    {
        global $objPage;

        $lastElement    = FALSE;
        $objElements    = \ContentModel::findPublishedByPidAndTable( $objRow->pid, 'tl_article');

        if( $objElements )
        {
            $checkElement   = false;
            $isBox          = false;

            while( $objElements->next() )
            {
                if( $objElements->id === $objRow->id )
                {
                    $checkElement = true;
                    continue;
                }
                else
                {
                    if( $checkElement )
                    {
                        $cssID = \StringUtil::deserialize( $objElements->cssID );

                        if( preg_match('/box-item/', $cssID[1]) || $objRow->type === "rsce_box" )
                        {
                            $isBox = true;
                            break;
                        }
                    }
                }


//                $cssID = \StringUtil::deserialize( $objElements->cssID );

//                if( preg_match('/box-item/', $cssID[1]) || $objRow->type === "rsce_box" )
//                {
//                    $lastElement = $objElements->current();
//                }
            }

            if( !$isBox )
            {
                return TRUE;
            }
        }

//        if( $lastElement && $lastElement->id === $objRow->id )
//        {
//            return TRUE;
//        }

        return FALSE;
    }



    public function renderList( $strBuffer, $objRow, $objElement )
    {
        $cssID = \StringUtil::deserialize($objRow->cssID, TRUE);

        if( preg_match('/row-list/', $cssID[1]) )
        {
            preg_match_all('/<li([A-Za-z0-9\s\-="]{0,})>([A-Za-z0-9\s\-\|,;.:_;\'\\\[\]\(\)°öäüÖÄÜß#\{\}!?&%€$\"\/]{0,})<\/li>/', $strBuffer, $arrList);

            if( count($arrList[0]) )
            {
                foreach($arrList[0] as $key => $list)
                {
                    $listItem   = $arrList[0][ $key ];
                    $listText   = $strText = trim($arrList[2][ $key ]);

                    if( preg_match('/^W2\|/', $listText) )
                    {
                        $strText = preg_replace('/^W2\|/', '', $strText);

                        if( preg_match('/class="/', $listItem) )
                        {
                            $newListItem = preg_replace('/class="/', 'class="w2 ', $listItem);
                        }
                        else
                        {
                            $newListItem = preg_replace('/<li/', '<li class="w2"', $listItem);
                        }

                        $strBuffer = preg_replace('/' . preg_quote($listItem, '/') . '/', $newListItem, $strBuffer);
                    }

                    $strText    = ContentHelper::renderText($strText, true);
                    $strBuffer  = preg_replace('/' . preg_quote($listText, '/') . '/', $strText, $strBuffer);
                }
            }
        }

        if( FALSE !== strpos($strBuffer, '--') )
        {
            preg_match_all('/<li([A-Za-z0-9\s\-=";,.:\(\)]{0,})>(.*)<\/li>/', $strBuffer, $arrListMatches);

            if( count($arrListMatches) && count($arrListMatches[0]) )
            {
                foreach( $arrListMatches[2] as $key => $strList )
                {
                    $oldText    = $strList; //$arrListMatches[0][ $key ];
                    $newText    = '<div class="list-box first"><div class="list-box-inside">';

                    $arrTextBlocks = explode('--', $strList);
                    $newText .= implode('</div></div><div class="list-box"><div class="list-box-inside">', $arrTextBlocks);

                    $newText .= '</div></div>';

                    $strBuffer = preg_replace('/' . preg_quote($oldText, '/') . '/', $newText, $strBuffer);
                }
            }
        }

        return $strBuffer;
    }



    public function renderTable( $strBuffer, $objRow, $objElement )
    {
        $cssID = \StringUtil::deserialize($objRow->cssID, TRUE);

        if( preg_match('/replace-row-option/', $cssID[1]) )
        {
            preg_match_all('/<td([A-Za-z0-9\s\-,;.:_\(\)\|="]{0,})>([A-Za-z0-9\s\-,;.:_\(\)\öäüÖÄÜß!?$%&+#"\'\/\\@€|]{0,})<\/td>/', $strBuffer, $arrCols);

            if( count($arrCols[0]) )
            {
//                echo "<pre>"; print_r( $arrCols ); exit;
                foreach($arrCols[2] as $key => $col)
                {
                    $colTag  = $arrCols[0][ $key ];
                    $colAttr = $arrCols[1][ $key ];
                    $colText = ContentHelper::renderText( $col );
                    $arrText = explode("__", $colText);

                    if( count($arrText) > 1 )
                    {
                        $colText = '<span>' . implode('</span><span>', $arrText) . '</span>';
                    }
//echo "<pre>"; print_r( $colText ); echo "<br>"; print_r( $key ); echo "<br>"; print_r( $colAttr); echo "<br>"; print_r( $colTag); exit;
                    $strBuffer = preg_replace('/' . preg_quote($colTag, '/') . '/', '<td' . $colAttr . '>' . $colText . '</td>', $strBuffer);
                }
            }
        }

        return $strBuffer;
    }



    protected function getElementType( $objRow, $objAliasElement = false )
    {
        $strType = $objRow->type;

        if( $strType === 'alias' )
        {
            $objElement = $objAliasElement ?:ContentModel::findByPk( $objRow->cteAlias );

            if( $objElement )
            {
                $strType = $objElement->type;
            }
        }

        return $strType;
    }

}
