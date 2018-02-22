<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\EventListener;


use IIDO\BasicBundle\Helper\BasicHelper;
use IIDO\BasicBundle\Helper\ContentHelper as Helper;
use IIDO\BasicBundle\Helper\ImageHelper;


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
//        'slick-slide-separator'

        'iido_wrapperStart',
        'iido_wrapperStop',
        'iido_wrapperSeparator'
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

//        if( $objRow->type == "module" )
//        {
//            $objModule = \ModuleModel::findByPk( $objRow->module );
//
//            if( $objModule )
//            {
//                $elementClass = 'mod_' . $objModule->type;
//            }
//        }


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
                }

                $addClass .= ' ip-' . preg_replace('/float_/', '', $objRow->floating);

                $strBuffer = str_replace('class="ce_text' , 'class="ce_text has-image' . $addClass, $strBuffer);

                if( $objRow->fullsize && !$objRow->imageUrl )
                {
                    $strBuffer = Helper::generateImageHoverTags( $strBuffer, $objRow );
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

                $strBuffer      = preg_replace('/' . preg_quote($strImageFigure, '/') . '/', $newImageFigure, $strBuffer);
            }

//            echo "<pre>";
//            print_r( $strBuffer );
//            echo "<br>";
//            print_r( $arrImageMatches );
//            echo "<br>";
//            print_r( $arrImages );
//            echo "<br>";
//            print_r( $objRow );
//            exit;
        }


        switch( $objRow->type )
        {
            case "hyperlink":
                $strBuffer = $this->renderHyperlink( $strBuffer, $objRow, $objElement );
                break;

            case "gallery":
                $strBuffer = $this->renderGallery( $strBuffer, $objRow, $objElement );
                break;
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

        if( $objRow->position )
        {
            $strStyles = '';

            $arrElementClasses[] = 'pos-' . ($objRow->positionFixed ? 'fixed' : 'abs');
            $arrElementClasses[] = 'pos-' . str_replace('_', '-', $objRow->position);

            $arrPosMargin = deserialize($objRow->positionMargin, TRUE);

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

        if( preg_match('/<h([1-6])>/', $strBuffer) )
        {
            $strBuffer = preg_replace('/<h([1-6])>/', '<h$1 class="headline">', $strBuffer);
        }

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

        if( count($arrElementClasses) )
        {
            $strBuffer = $this->addClassToContentElement( $strBuffer, $objRow, $arrElementClasses );
        }

        if( count($arrAttributes) )
        {
            $strBuffer = $this->addAttributesToContentElement( $strBuffer, $objRow, $arrAttributes );
        }

        return $strBuffer;
    }



    protected function renderHeadlines( $strContent, $objRow )
    {
        $arrHeadlineClasses     = array();
        $arrTopHeadlineClasses  = array();
        $arrSubHeadlineClasses  = array();

        $arrHeadline    = deserialize($objRow->headline, TRUE);
        $unit           = $arrHeadline['unit'];
        $headline       = $arrHeadline['value'];

        $strTopClass    = $strSubClass = ' unit-' . $unit;


        if( $objRow->addTopHeadline )
        {
            $arrHeadlineClasses[] = 'has-top-headline';
        }

        if( $objRow->subHeadline )
        {
            $arrHeadlineClasses[] = 'has-sub-headline';
        }

        if( $objRow->headlineFloating )
        {
            $floating = preg_replace('/header_/', '', $objRow->headlineFloating);

            $arrHeadlineClasses[]       = 'text-' . $floating;
            $arrTopHeadlineClasses[]    = 'text-' . $floating;
            $arrSubHeadlineClasses[]    = 'text-' . $floating;
        }

        if( count($arrTopHeadlineClasses) )
        {
            $strTopClass = $strTopClass . " " . implode(" ", $arrTopHeadlineClasses);
        }

        if( count($arrSubHeadlineClasses) )
        {
            $strSubClass = $strSubClass . " " . implode(" ", $arrSubHeadlineClasses);
        }

        $topHeadline    = ($objRow->addTopHeadline  ? '<div class="top-headline' . $strTopClass . '">' . $objRow->topHeadline . '</div>' : '');
        $subHeadline    = ($objRow->subHeadline     ? '<div class="sub-headline' . $strSubClass . '">' . $objRow->subHeadline . '</div>' : '');

        $strHeadline    = preg_replace(array('/;/'), array('<br>'), $headline);

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
            $strContent = BasicHelper::replaceLastMatch('/class="headline([^\-])/', 'class="headline is-subline$1', 'class="headline$1', $strContent);
        }

        if( count($arrHeadlineClasses) )
        {
            $replaceClass = 'headline';

            if( $objRow->type === "headline" )
            {
                $replaceClass = 'ce_headline';
            }

            $strContent = preg_replace('/<h([1-6]) class="' . $replaceClass . '/', '<h$1 class="' . $replaceClass . ' ' . implode(" ", $arrHeadlineClasses), $strContent);
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

//        $strContent = Helper::renderText( $strContent, true);
        preg_match_all('/<a([A-Za-z0-9\s\-="_,;.:\{\}\(\)\/]{0,})>([A-Za-z0-9\s\-,;\{\}:.!?\(\)]{0,})<\/a>/', $strContent, $arrMatches);

        if( count($arrMatches[0]) )
        {
            $strTitle   = trim(preg_replace('/;/', '', $arrMatches[2][0]));
//            $strContent = preg_replace('/title="' . preg_quote($arrMatches[2][0], '/') . '"/', 'title="' . $strTitle . '"', $strContent);
            $strContent = preg_replace('/>' . preg_quote($arrMatches[2][0], '/') . '</', '>' . Helper::renderText($arrMatches[2][0], true). '<', $strContent);
//            $strContent = preg_replace('/title="([A-Za-z0-9\s\-;,.:\{\}<>="]{0,})" rel="/', 'title="' . $strTitle . '" rel="', $strContent);
        }

        $strContent = $this->addClassToContentElement( $strContent, $objRow, $arrClasses );

        return $strContent;
    }



    protected function renderGallery( $strContent, $objRow, $objElement )
    {
        $strContent = preg_replace('/<a/', '<a class="no-barba"', $strContent);

        if( $objRow->fullsize )
        {
            $strContent = Helper::generateImageHoverTags( $strContent, $objRow );
        }

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

        if( $objRow->type === "module" )
        {
            $objModule = \ModuleModel::findByPk( $objRow->module );

            if( $objModule )
            {
                $elementClass = 'mod_' . $objModule->type;
            }
        }

        if( $elementClass === "ce_alias" )
        {
            $objAliasElement    = \ContentModel::findByPk ( $objRow->cteAlias );
            $elementClass       = 'ce_' . $objAliasElement->type;
        }

//        if( $elementClass === "ce_iido_navigation" )
//        {
//            $elementClass = 'mod_navigation';
//        }

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

            if( (preg_match('/box-item/', $cssID[1]) || $objRow->type === "rsce_box" || $strType === "rsce_box" ) && !$GLOBALS['IIDO']['BOXES']['OPEN_WRAPPER'] )
            {
                $GLOBALS['IIDO']['BOXES']['OPEN'] = $GLOBALS['IIDO']['BOXES']['OPEN'] || FALSE;

                if( $GLOBALS['IIDO']['BOXES']['OPEN'] === FALSE )
                {
                    $GLOBALS['IIDO']['BOXES']['OPEN'] = TRUE;

                    $strClass       = 'fbc';
                    $strBoxClass    = '';

                    if( preg_match('/box-col/', $cssID[1]))
                    {
                        $strClass = 'columns';
                    }

                    if( $objRow->type === "rsce_box" )
                    {
                        $strClass       = 'boxes';
                        $strBoxClass    = ' no-padding';
                    }

                    $strBuffer = '<div class="box-container clr-after' . $strBoxClass . '"><div class="box-cont-inside"><div class="box-cont-wrapper ' . $strClass . '">' . $strBuffer;
                }

                if( preg_match('/box-col/', $cssID[1]))
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

}
