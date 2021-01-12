<?php


namespace IIDO\BasicBundle\Renderer;


use Contao\Template;
use IIDO\BasicBundle\Config\IIDOConfig;
use IIDO\BasicBundle\Helper\ColorHelper;
use IIDO\BasicBundle\Helper\ScriptHelper;
use IIDO\BasicBundle\Helper\StylesHelper;
//use PHPHtmlParser\Dom;
//use PHPHtmlParser\Selector\Parser;
//use stringEncode\Encode;
//use \Wa72\HtmlPageDom\HtmlPageCrawler;


class ArticleTemplateRenderer
{
    public static function parseTemplate( $strContent, $templateName ): string
    {
        if( 0 === strpos($templateName, 'mod_articlenav') )
        {
            return $strContent;
        }

        $objArticle = false;

        preg_match_all('/id="([A-Za-z0-9\-_]{0,})"/', $strContent, $idMatches);
//        preg_match_all('/class="mod_article([A-Za-z0-9\s\-\{\}\/\',;.:\\\(\)_]{0,})"([A-Za-z0-9\s\-,;.:="\'_]{0,})id="([A-Za-z0-9\-_]{0,})"/', $strContent, $arrMatches);

        if( is_array($idMatches) && count($idMatches[0]) > 0 )
        {
            $idOrAlias = $idMatches[1][0];

            if( preg_match('/^article-/', $idOrAlias) )
            {
                $idOrAlias = preg_replace('/^article-/', '', $idOrAlias);
            }

            $objArticle = \ArticleModel::findByIdOrAlias( $idOrAlias );
        }

        if( $objArticle && $objArticle->noContent )
        {
            return '';
        }

        $cssID              = \StringUtil::deserialize($objArticle->cssID, TRUE);
//        $bgColor            = ColorHelper::compileColor( $objArticle->bgColor );
        $arrArticleClasses  = [];

//        if( $objArticle->bgImage )
//        {
//            $arrArticleClasses[] = 'has-bg-image';
//        }

//        if( $bgColor !== 'transparent' )
//        {
//            $arrArticleClasses[] = 'has-bg-color';
//        }

//        if( $objArticle->width )
//        {
//            $arrArticleClasses[] = 'width-' . $objArticle->width;
//        }

//        if( $objArticle->height )
//        {
//            $arrArticleClasses[] = 'height-' . $objArticle->height;
//        }

        $strArticleClasses  = (count($arrArticleClasses) ? implode(' ', $arrArticleClasses) : '');
        $strArticleStyles   = '';
        $divOverlay         = '';

        $divTableStart      = '';
        $divTableEnd        = '';

        $strArticleInsideStyles     = '';
        $strArticleInsideClasses    = '';

        if( FALSE !== strpos($cssID[1], 'show-grid') )
        {
            $strContent = preg_replace('/ show-grid/', '', $strContent);

            $divTableStart  = '<div class="grid-container">';
            $divTableEnd    = '</div>';
        }

        if( IIDOConfig::get('enableLayout') )
        {
            $strArticleInsideClasses .= ' row';
        }

        $divInsideContainer = $divOverlay . '<div class="article-inside' . $strArticleInsideClasses . '"' . $strArticleInsideStyles . '>' . $divTableStart;

        if( IIDOConfig::get('enableBootstrap') || ScriptHelper::hasPageFullPage(true) )
        {
            $divInsideContainer = '';
            $divTableEnd        = '';
        }

        $attributes = '';

        if( ScriptHelper::hasPageFullPage( true ) )
        {
            $attributes .= 'data-anchor="' . $objArticle->alias . '" ';
            $attributes .= 'data-title="' . ($objArticle->frontendTitle ? : $objArticle->title) . '" ';

//            $divInsideContainer .= '<div class="section-index"></div>';
        }

        $strContent = preg_replace('/<div([A-Za-z0-9öäüÖÄÜß\s\-_="\'.,;:\(\)\/#]{0,})class="mod_article ([A-Za-z0-9öäüÖÄÜß\s\-_\{\}\(\)\']{0,})"([A-Za-z0-9öäüÖÄÜß\s\-_="\'.,;:\(\)\/#%]{0,})>/u', '<section$1' . $attributes . 'class="mod_article $2' . ($strArticleClasses ? ' ' . $strArticleClasses : '') . '"$3' . $strArticleStyles . '>' . $divInsideContainer, $strContent, -1, $count);

        if( $count )
        {
            $strContent = preg_replace('/<\/div>([\s\n]{0,})$/', '', $strContent);
            $strContent .= $divTableEnd . '</section>';
        }

        $styles = preg_replace('/^\{\}$/', '', trim( StylesHelper::getArticleStyles( $objArticle ) ) );

        if( strlen($styles) )
        {
            $articleID = 'article-' . $objArticle->id;

            if( $cssID[0] )
            {
                $articleID = $cssID[0];
            }

//            $styles = Template::generateInlineStyle('.mod_article#' . $articleID . $styles);
            $GLOBALS['TL_HEAD']['art_' . $articleID ] = Template::generateInlineStyle('.mod_article#' . $articleID . $styles);

            $styles = '';
        }

        return $styles . $strContent;
    }
}