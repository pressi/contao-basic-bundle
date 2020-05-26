<?php


namespace IIDO\BasicBundle\Renderer;


use Contao\StringUtil;
use IIDO\BasicBundle\Helper\BasicHelper;
use IIDO\BasicBundle\Helper\ColorHelper;
use IIDO\BasicBundle\Helper\ContentHelper;


class ContentRenderer
{
    public static function parseHeadline( $strContent, $objRow ): string
    {
        if( $objRow->type !== 'headline' )
        {
            return $strContent;
        }

        $arrElementClasses = [];

        $isBackend      = BasicHelper::isBackend();

        $imageBefore    = '';
        $cssID          = StringUtil::deserialize( $objRow->cssID, TRUE );

        $arrHeadline    = StringUtil::deserialize( $objRow->headline, TRUE );
        $unit           = $arrHeadline['unit'];
        $floating       = $objRow->headlineFloating;
        $color          = ColorHelper::compileColor($objRow->headlineColor);
        $classes        = $floating;
        $styles         = '';

        $strNewHeadline = preg_replace(['/;/', '/--([\w\d\s!?]+)--/'], ['<br>', '<span class="normal">$1</span>'], $arrHeadline['value']);
        $strTopHeadline = '';

        if( $objRow->type === 'headline' )
        {
            preg_match_all('/<' . $unit . ' class="ce_headline([A-Za-z0-9\s\-_]{0,})">/', $strContent, $matches);

            $strContent = preg_replace('/<' . $unit . ' class="ce_headline([A-Za-z0-9\s\-_]{0,})">/', '<' . $unit . '>', $strContent, -1);
            $strContent = '<div class="ce_headline' . $matches[1][0] . '">' . $strContent . '</div>';
        }

        $arrElementClasses[] = 'headline-unit-' . $unit;

        if( $objRow->topHeadline )
        {
            $arrElementClasses[] = 'has-top-headline';

            $classes .= ' has-top-headline';

            $strTopHeadline = '<div class="top-headline h-unit-' . $unit . ' text-' . $objRow->topHeadlineFloating . '">' . $objRow->topHeadline . '</div>';

            if( $isBackend && 1 == 2 ) // TODO: changeable in backend?!!
            {
                $strTopHeadline = '<div class="be-row">
<div class="row-label">Überschrift oben: </div>
<div class="row-value">' . $strTopHeadline . '</div>
</div>';
            }
        }

        if( $color !== 'transparent' )
        {
            $styles .= 'color:' . $color . ';';
        }

        if  (
            $objRow->type === 'text' && $objRow->addImage && $objRow->singleSRC &&
            (
                FALSE !== strpos($cssID[1], 'headline-nextto')
                ||
                FALSE !== strpos($cssID[1], 'headline-nextTo')
                ||
                FALSE !== strpos($cssID[1], 'show-as-columns')
                ||
                (
                    FALSE !== strpos($cssID[1], 'img-box')
                    &&
                    (
                        FALSE !== strpos($cssID[1], 'layout-01')
//                        ||
//                        FALSE !== strpos($cssID[1], 'layout-02')
                    )
                )
            )
        )
        {
            preg_match_all('/<figure([A-Za-z0-9\s\-=",;.:_]{0,})>([A-Za-z0-9\s\-,;.:_="\n\/<>\(\)%]+)<\/figure>/', $strContent, $arrImgElement);

            if( count($arrImgElement) && count($arrImgElement[0]) )
            {
                $strImage       = $arrImgElement[0][0];
                $imageBefore    = $strImage;
                $strContent     = preg_replace('/' . preg_quote($strImage, '/') . '/', '', $strContent);
            }
        }

        if( $styles )
        {
            $styles = ' style="' . $styles . '"';
        }

        $addContStart   = '';
        $addContClose   = '';

        if( $objRow->type === 'image' && FALSE !== strpos($cssID[1], 'parallax-img') )
        {
            $addContStart   = '<div class="headline-container">';
            $addContClose   = '</div>';
        }

        if( $isBackend && 1 == 2 ) //TODO: make changeabel in iido config settings!!
        {
            $strTopHeadline .= '<div class="be-row">
<div class="row-label">Überschrift (' . $unit . '): </div>
<div class="row-value">';

            $addContClose .= '</div></div>';
        }

        $strContent     = preg_replace('/<' . $unit . '>/', $addContStart . $imageBefore . $strTopHeadline . '<' . $unit . ' class="headline text-' . $classes . '"' . $styles . '><span>', $strContent, 1);
        $strContent     = preg_replace('/<\/' . $unit . '>/' , '</span></' . $unit . '>' . $addContClose, $strContent);
        $strContent     = ContentHelper::addClassToElement( $strContent, $objRow, $arrElementClasses );

        $strContent     = preg_replace('/' . preg_quote($arrHeadline['value'], '/') . '/', $strNewHeadline, $strContent);

        return $strContent;
    }



    public static function renderColumns( $strContent, $objRow )
    {
        if( $objRow->isSubElement )
        {
            return $strContent;
        }

        $GLOBALS['IIDO']['COLUMNS']['OPEN'] = $GLOBALS['IIDO']['COLUMNS']['OPEN'] || false;
        $GLOBALS['IIDO']['COLUMNS']['COUNT'] = (int) $GLOBALS['IIDO']['COLUMNS']['COUNT'] || 0;

        $classes = StringUtil::deserialize( $objRow->cssID, TRUE )[1];
//echo "<pre>"; print_r( $GLOBALS['IIDO']['COLUMNS']['OPEN'] );
//        echo "<br>"; print_r( $GLOBALS['IIDO']['COLUMNS']['COUNT'] );
//        echo "<br>"; print_r( $objRow->id );
//        echo "<br>"; print_r( $classes );
//echo "</pre>";
        if( false !== strpos($classes, 'column-item') )
        {
            if( !$GLOBALS['IIDO']['COLUMNS']['OPEN'] )
            {
                $GLOBALS['IIDO']['COLUMNS']['OPEN'] = true;
                $strContent = '<div class="columns-container">' . $strContent;
            }

            preg_match_all('/col-w-([0-9]+)/', $classes, $matches);

            $columnCount = (int) $matches[1][0];

            $GLOBALS['IIDO']['COLUMNS']['COUNT'] = ($GLOBALS['IIDO']['COLUMNS']['COUNT'] + $columnCount);
        }

        if( (in_array('last', $objRow->classes) && $GLOBALS['IIDO']['COLUMNS']['OPEN'])
        || (false === strpos($classes, 'column-item') && $GLOBALS['IIDO']['COLUMNS']['OPEN'])
        || $GLOBALS['IIDO']['COLUMNS']['COUNT'] >= 100 )
        {
            $GLOBALS['IIDO']['COLUMNS']['OPEN'] = false;
            $GLOBALS['IIDO']['COLUMNS']['COUNT'] = 0;

            $strContent = $strContent . '</div>';
        }

        return $strContent;
    }
}