<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\EventListener;


use IIDO\BasicBundle\Config\BundleConfig;
use IIDO\BasicBundle\Helper\BasicHelper;
use IIDO\BasicBundle\Helper\ColorHelper;
use IIDO\BasicBundle\Helper\ContentHelper as Helper;
use IIDO\BasicBundle\Helper\ContentHelper;
use IIDO\BasicBundle\Helper\ImageHelper;
use IIDO\BasicBundle\Helper\ScriptHelper;
use IIDO\BasicBundle\Helper\WebsiteStylesHelper;


/**
 * IIDO System Listener
 *
 * @package IIDO\BasicBundle
 * @author Stephan Preßl <development@prestep.at>
 */
class ContentListener extends DefaultListener
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
     * edit content element
     */
    public function getCustomizeContentElement($objRow, $strBuffer, &$objElement)
    {
        global $objPage;

        $cssID          = \StringUtil::deserialize($objRow->cssID, TRUE);
        $isMobile       = \Environment::get("agent")->mobile;

        if( $isMobile && ($objRow->hideOnMobile || preg_match('/hide-on-mobile/', $cssID[1])) )
        {
            return '';
        }
        elseif( !$isMobile && ($objRow->showOnMobile || preg_match('/show-on-mobile/', $cssID[1])) )
        {
            return '';
        }

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
            }

            if( FALSE !== strpos( $cssID[1], 'bg-image' ) )
            {
                $bgImage    = '';
                $objImage   = \FilesModel::findByPk( $objRow->singleSRC );

                if( $objImage )
                {
                    $bgImage = $objImage->path;
                }

                $strBuffer = preg_replace('/<figure([A-Za-z0-9\s\-=",;.:_\(\)\{\}\/]{0,})>([A-Za-z0-9\s\n\-<>,;.:="\/_]{0,})<\/figure>/', '<figure$1 style="background-image:url(' . $bgImage . ')"></figure>', $strBuffer);
                $strBuffer = str_replace('image_container', 'image_container bg-image bg-cover', $strBuffer);
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
                    $arrHeadline        = deserialize($objRow->headline, true);

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

//            $strBuffer = Helper::renderText($strBuffer);
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

            $strBuffer = preg_replace('/<div([A-Za-z0-9\s\-_="\(\)\{\}:;\/]{0,})class="' . $elementClass . '([A-Za-z0-9\s\-\{\}_:;]{0,})"([A-Za-z0-9\s\-_="\(\)\{\}:;\/]{0,})>/', '<div$1class="' . $elementClass . '$2"$3><div class="element-inside">', $strBuffer, -1, $count);

            if( $count )
            {
                $strBuffer = $strBuffer . '</div>';
            }
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

        if(  TL_MODE === "FE" ) //TODO: auslagern in helper class, auch für rsce und sonstiges!!
        {
            //TODO: merge rendered position with position function in content helper #80
            $strStyles = '';

            if( $objRow->position )
            {
                $arrElementClasses[] = 'pos-' . ($objRow->positionFixed ? 'fixed' : 'abs');
                $arrElementClasses[] = 'pos-' . str_replace('_', '-', $objRow->position);
            }

            $arrPosMargin = \StringUtil::deserialize($objRow->positionMargin, TRUE);

//            if( $arrPosMargin['top'] || $arrPosMargin['right'] || $arrPosMargin['bottom'] || $arrPosMargin['left'] )
//            {
                $unit       = $arrPosMargin['unit']?:'px';
                $useUnit    = true;
                $prefix     = 'margin';

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
                        $transKey = 'Y';

                        if( preg_match('/transX/', $arrPosMargin['top']) )
                        {
                            $transKey = 'X';
                        }

                        $transValue = preg_replace('/^(transX|transY)/', '', $arrPosMargin['top']);

                        $strStyles .= " transform:translate" . $transKey . "(" . $transValue . (($useUnit)?$unit:'') . ")"; if( $transKey === 'X' && $objRow->position === 'center_center' )
                    {
                        $strStyles .= 'translateY(-50%)';
                    } $strStyles .= ';';
                    }
                    else
                    {
                        $strStyles .= " " . $prefix . "-top:" . $arrPosMargin['top'] . (($useUnit)?$unit:'') . ";";
                    }

                    $useUnit    = true;
                }

                if( $arrPosMargin['right'] )
                {
                    if( preg_match('/(' . implode('|', $GLOBALS['TL_CSS_UNITS']) . ')$/', $arrPosMargin['right']) )
                    {
                        $useUnit = false;
                    }

                    if( preg_match('/' . $unit . '$/', $arrPosMargin['right']) )
                    {
                        $useUnit = false;
                    }

                    if( preg_match('/calc/', $arrPosMargin['right']) )
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

                        $transValue = preg_replace('/^(transX|transY)(&#40;|\()([A-Za-z0-9%\-+]{0,})(&#41;|\))/ui', '$3', trim($arrPosMargin['right']));

                        $strStyles .= " transform:translate" . $transKey . "(" . $transValue . (($useUnit)?$unit:'') . ")";

                        if( $transKey === 'X' && $objRow->position === 'center_center' )
                        {
                            $strStyles .= 'translateY(-50%)';
                        }

                        $strStyles .= ';';
                    }
                    else
                    {
                        $strStyles .= " " . $prefix . "-right:" . $arrPosMargin['right'] . (($useUnit)?$unit:'') . ";";
                    }

                    $useUnit    = true;
                }

                if( $arrPosMargin['bottom'] ||$arrPosMargin['bottom'] === '0' )
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
//            }

            if( strlen($strStyles) )
            {
                $arrAttributes['style'] = trim($arrAttributes['style'] . $strStyles);
            }
        }

//        if( preg_match('/<h([1-6])([A-Za-z0-9\s\-=":;,._]{0,})>/', $strBuffer) )
//        {
            //TODO: check if match class attribute!!
//            $strBuffer = preg_replace('/<h([1-6])([A-Za-z0-9\s\-=":;,._]{0,})>/', '<h$1 class="headline"$2>', $strBuffer);
//        }

        $strBuffer = $this->renderHeadlines($strBuffer, $objRow);
        $strBuffer = $this->renderBox($strBuffer, $objRow, $objElement);
        $strBuffer = $this->renderImages( $strBuffer, $objRow );

        if( $objRow->addAnimation || $objArticle->addAnimation )
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

        if( $objRow->elementIsBox )
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

            $bgColor = ColorHelper::compileColor( $objRow->boxBackgroundColor, TRUE );

            if( $bgColor !== "transparent" && !$objRow->boxLink )
            {
                $arrAttributes['style'][] = 'background:' . $bgColor . ';';
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



    protected function renderHeadlines( $strContent, $objRow )
    {
        global $objPage;

        $arrHeadlineClasses     = array();
        $arrTopHeadlineClasses  = array();
        $arrSubHeadlineClasses  = array();

        $arrContClasses         = array();

        $arrStyleHeadlineClasses    = array();
        $arrElementClasses          = array();

        $arrHeadline    = deserialize($objRow->headline, TRUE);
        $unit           = $arrHeadline['unit'];
        $headline       = $arrHeadline['value'];

        $strTopClass    = $strSubClass = ' unit-' . $unit;
        $replaceClass   = 'headline';


        if( $objRow->addTopHeadline )
        {
            $arrHeadlineClasses[] = 'has-top-headline';

            $arrContClasses[] = 'cont-has-top-headline';
        }

        if( $objRow->subHeadline )
        {
            $arrHeadlineClasses[] = 'has-sub-headline';

            $arrContClasses[] = 'cont-has-sub-headline';
        }

        if( $objRow->headlineFloating )
        {
            $floating = preg_replace('/header_/', '', $objRow->headlineFloating);

            $arrHeadlineClasses[]       = 'text-' . $floating;
            $arrTopHeadlineClasses[]    = 'text-' . $floating;
            $arrSubHeadlineClasses[]    = 'text-' . $floating;
        }

        if( $objRow->headlineStyles )
        {
//            $arrConfig = \StringUtil::deserialize( \Config::get( BundleConfig::getTableFieldPrefix() . 'headlineStyles' ), TRUE );

            $arrConfig = WebsiteStylesHelper::getConfigFieldValue( $objPage->rootAlias, 'headlineStyle' );

            if( !is_array($arrConfig) || (is_array($arrConfig) && !count($arrConfig)) )
            {
                $fieldPrefix    = BundleConfig::getTableFieldPrefix();

                $arrConfig  = array();
                $arrStyles  = \StringUtil::deserialize( \Config::get($fieldPrefix . 'headlineStyles'), TRUE);

                if( count($arrStyles) )
                {
                    foreach($arrStyles as $arrStyle)
                    {
                        if( strlen(trim($arrStyle['name'])) )
                        {
                            $arrInternStyle = $arrStyle;

                            $arrInternStyle['internID']         = $arrStyle['id'];

                            $arrInternStyle['classes']          = $arrStyle['tagClasses'];
                            $arrInternStyle['tagClasses']       = $arrStyle['elementClasses'];

                            $arrConfig[ $arrStyle['id'] ] = $arrInternStyle;
                        }
                    }
                }
            }

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

                if( $arrStyle['tagClasses'] )
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

        $topHeadline    = ($objRow->addTopHeadline  ? '<div class="top-headline' . $strTopClass . '"><span class="th-inside">' . $objRow->topHeadline . '</span></div>' : '');
        $subHeadline    = ($objRow->subHeadline     ? '<div class="sub-headline' . $strSubClass . '"><span class="sh-inside">' . $objRow->subHeadline . '</span></div>' : '');

        $strHeadline    = preg_replace(array('/;/'), array('<br>'), $headline);

        if( count($arrHeadlineClasses) )
        {
            preg_match_all('/<h([1-6])([A-Za-z0-9\s\-_="\/\\\(\)\{\}]{0,})>([A-Za-z0-9\s\-,;.:_#+!?$%&€§"\'\/\\\(\)\{\}=ßöäüÖÄÜ@éèáàóòúùüâûêôñãõ]{0,})<\/h([1-6])>/u', $strContent, $arrHeadlineMatches);

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
                        $strNewHeadline = preg_replace('/<h' . $findUnit . '/', '<h' . $findUnit . ' class="headline"', $strFindHeadline);
                    }

                    $strNewHeadline = preg_replace('/' . preg_quote($arrHeadlineMatches[3][ $headlineNum ], '/') . '/', preg_replace('/;/', '<br>', $arrHeadlineMatches[3][ $headlineNum ]), $strNewHeadline);


                    $strContent = preg_replace('/' . preg_quote($strFindHeadline, '/') . '/', $strNewHeadline, $strContent);
                }
            }

            $strContent = preg_replace('/<h([1-6]) class="' . $replaceClass . '/', '<h$1 class="' . $replaceClass . ' ' . implode(" ", $arrHeadlineClasses), $strContent);
        }

        if( count($arrStyleHeadlineClasses) )
        {
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

        $strContent = preg_replace('/<h([1-6]{1})([A-Za-z0-9\s\-_="\{\}]{0,})>/', $topHeadline . '<h$1$2><span class="headline-inside"><span class="headline-span">', $strContent);
        $strContent = preg_replace('/<\/h([1-6]{1})>/', '</span></span></h$1>' . $subHeadline, $strContent);

        if( preg_match('/<\/h([1-6]{1})>([\s\n]{0,})<h([1-6]{1})/', trim($strContent)) )
        {
            $strContent = preg_replace('/class="headline([^\-])/', 'class="headline has-subline$1', $strContent, 1);
            $strContent = BasicHelper::replaceLastMatch('/class="sub-headline([^\-])/', 'class="subline is-subline$1', 'class="headline$1', $strContent);
        }

        if( $objRow->type === "headline" )
        {
            $cssID = \StringUtil::deserialize($objRow->cssID, TRUE);

            $strContent = '<div class="ce_headline content-element ' . implode(" ", $arrContClasses) . (count($arrContClasses) ? ' ' : '') . implode(" ", $objRow->classes) . (count($objRow->classes) ? ' ' : '') . $cssID[1] . (strlen($cssID[1]) ? ' ' : '') . 'block"><div class="element-inside">' . $strContent . '</div></div>';
        }

        if( count($arrElementClasses) )
        {
            $strContent = $this->addClassToContentElement($strContent, $objRow, $arrElementClasses);
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

        if( $objRow->showAsButton )
        {
            $arrClasses[] = 'btn';
            $arrClasses[] = 'btn-' . $objRow->buttonStyle;
            $arrClasses[] = 'btn-type-' . $objRow->buttonType;

            if( $objRow->linkTitle === '' )
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
                        $strLinkText    = $objRow->linkTitle?:$objRow->url;
                        $iconColor      = ColorHelper::compileColor( $objRow->buttonAddonIconColor );
                        $iconBackground = (($iconColor !== "transparent") ? 'background:' . $iconColor . ';' : '');
                        $strIconPath    = ImageHelper::renderImagePath( \FilesModel::findByPk( $objRow->buttonAddonIcon )->path );
                        $strIcon        = '<i class="icon icon-mask" style="mask-image:url(' . $strIconPath . ');-webkit-mask-image:url(' . $strIconPath . ');' . $iconBackground . '"></i>';


                        if( FALSE !== strpos( $cssID[1], 'box-link' ) )
                        {

                            if( $objRow->buttonAddonPosition === 'left' )
                            {
                                $strIcon .= ContentHelper::renderText($strLinkText, true);
                            }
                            elseif( $objRow->buttonAddonPosition === 'right' )
                            {
                                $strIcon = ContentHelper::renderText($strLinkText, true) . $strIcon;
                            }
                        }

//                        $arrClasses[] = 'icon-' . $objRow->buttonAddonIcon;
//                        $strContent = preg_replace('/<a/', '<a data-icon="' . $objRow->buttonAddonIcon . '"', $strContent);
                        $strContent = preg_replace('/class="hyperlink_txt/', 'class="hyperlink_txt icon-link', $strContent);
                        $strContent = preg_replace('/' . preg_quote($strLinkText, '/') . '([\s]{0,})<\/a>/', $strIcon . '</a>', $strContent);

//                        echo "<pre>"; print_r( $objRow );
//                        echo "<br>"; print_r( $strContent ); exit;
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
        preg_match_all('/<a([A-Za-zöäüÖÄÜ0-9\s\-="_,;.:\{\}\(\)\/#!?\|]{0,})>([A-Za-zöäüÖÄÜ0-9\s\-,;\{\}:.!?\(\)\|]{0,})<\/a>/u', $strContent, $arrMatches);

        if( count($arrMatches[0]) )
        {
            $beforeLink     = '';
            $afterLink      = '';

            $renderLines    = true;

            if( preg_match('/no-text-lines/', $cssID[1]) || preg_match('/no-lines/', $cssID[1]) )
            {
                $renderLines    = false;

                $beforeLink     = '<span class="link-text">';
                $afterLink      = '</span>';
            }

//            $strTitle   = trim(preg_replace('/;/', '', $arrMatches[2][0]));
//            $strContent = preg_replace('/title="' . preg_quote($arrMatches[2][0], '/') . '"/', 'title="' . $strTitle . '"', $strContent);
            $strContent = preg_replace('/>' . preg_quote($arrMatches[2][0], '/') . '</', '>' . $beforeLink . Helper::renderText($arrMatches[2][0], $renderLines) . $afterLink . '<', $strContent);
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

        return $strContent;
    }



    protected function renderGallery( $strContent, $objRow, $objElement )
    {
        $strContent = preg_replace('/<a/', '<a class="no-barba"', $strContent);
        $cssID      = \StringUtil::deserialize($objRow->cssID, TRUE);
        $addTitle   = false;

        if( preg_match('/title-hover/', $cssID[1]) )
        {
            $addTitle = true;
        }

        if( $objRow->fullsize )
        {
            $strContent = Helper::generateImageHoverTags( $strContent, $objRow );
        }

        $arrImages = ImageHelper::getMultipleImages($objRow->multiSRC, $objRow->orderSRC);

        preg_match_all('/<figure([A-Za-z0-9\s\-,;.:_\/\(\)\{\}="]{0,})>(.*?)<\/figure>/si', $strContent, $arrImageMatches);

        foreach( $arrImageMatches[0] as $key => $strImageFigure)
        {
            $arrImage       = $arrImages[ $key ];
            $strImageClass  = ($arrImage['meta']['cssClass'] ? ' ' : '') . $arrImage['meta']['cssClass'];

            $newImageFigure = preg_replace('/class="image_container/', 'class="image_container' . $strImageClass, $strImageFigure);

            if( $addTitle )
            {
                $hoverTitle     = $arrImage['meta']['hoverTitle']?:$arrImage['meta']['title'];
                $newImageFigure = preg_replace('/<\/figure>/', '<div class="hover-title-cont"><div class="htc-inside">' . $hoverTitle . '</div></div></figure>', $newImageFigure);
            }

            $strContent     = preg_replace('/' . preg_quote($strImageFigure, '/') . '/', $newImageFigure, $strContent);
        }

        if( $objRow->text )
        {
            $strText = '<div class="gallery-text">' . \StringUtil::encodeEmail( \StringUtil::toHtml5($objRow->text) ) . '</div>';

            $strContent = preg_replace('/<\/ul>/', '</ul>' . $strText, $strContent);
        }

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



    protected function getElementClass( $objRow )
    {
        $elementClass   = $objRow->typePrefix . $objRow->type;

        if( $objRow->type === "module" )
        {
            $objModule = \ModuleModel::findByPk( $objRow->module );

            if( $objModule )
            {
                $elementClass = 'mod_' . $objModule->type;
            }
        }

        if( preg_match('/rocksolid/', $objRow->type) )
        {
            $elementClass = 'mod_' . $objRow->type;
        }

        if( $elementClass === "ce_alias" )
        {
            $objAliasElement    = \ContentModel::findByPk ( $objRow->cteAlias );
            $elementClass       = 'ce_' . $objAliasElement->type;
        }

        if( preg_match('/^rsce_/', $elementClass) )
        {
            $elementClass = 'ce_' . $elementClass;
        }

//        if( $elementClass === "ce_iido_navigation" )
//        {
//            $elementClass = 'mod_navigation';
//        }

        if( $objRow->type === "newslist" )
        {
            $elementClass = preg_replace('/^ce_/', 'mod_', $elementClass);
        }

        return $elementClass;
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

}
