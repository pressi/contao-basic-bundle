<?php


namespace IIDO\BasicBundle\Renderer;


use Contao\Template;
use IIDO\BasicBundle\Config\IIDOConfig;
use IIDO\BasicBundle\Helper\ColorHelper;
use IIDO\BasicBundle\Helper\StylesHelper;
//use PHPHtmlParser\Dom;
//use PHPHtmlParser\Selector\Parser;
//use stringEncode\Encode;
//use \Wa72\HtmlPageDom\HtmlPageCrawler;


class ArticleTemplateRenderer
{
    public static function parseTemplate( $strContent, $templateName ): string
    {
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

        $strContent = preg_replace('/<div([A-Za-z0-9öäüÖÄÜß\s\-_="\'.,;:\(\)\/#]{0,})class="mod_article([A-Za-z0-9öäüÖÄÜß\s\-_\{\}\(\)\']{0,})"([A-Za-z0-9öäüÖÄÜß\s\-_="\'.,;:\(\)\/#%]{0,})>/u', '<section$1class="mod_article$2' . ($strArticleClasses ? ' ' . $strArticleClasses : '') . '"$3' . $strArticleStyles . '>' . $divOverlay . '<div class="article-inside' . $strArticleInsideClasses . '"' . $strArticleInsideStyles . '>' . $divTableStart, $strContent, -1, $count);

//        $objDom = new Dom();
//        $objDom->load( $strContent );

//        $article = $objDom->find('.mod_article');

//        if( $article )
//        {
//            $tag = $article->getTag();
//            $tag->setAttribute('class',  $tag->getAttribute('class')['value'] . ' ' . $strArticleClasses);
//
//            $newTag = new Dom\Tag('section');
//            $newTag->setAttributes( $tag->getAttributes() );
//
//            $encode = new Encode();
//            $encode->from('UTF-8');
//
//            $newTag->setEncoding( $encode );
//
//            $newNode = new Dom\HtmlNode( $newTag );
//
//            $parent     = $article->getParent();
//            $arrChilds  = $article->getChildren();
//
//            $parent->replaceChild($article->id(), $newNode);
//
//            if( count($arrChilds) )
//            {
//                foreach($arrChilds as $childNode)
//                {
//                    $newNode->addChild( $childNode );
//                }
//            }
//        }

//        $strContent = $objDom->outerHtml;


//        $tag->name();
//        $tag->setName('section');
//        $section = $objDom->createElement("section", $tag->nodeValue);
//        $objDom->replaceChild( $tag, $section);

//        echo "<pre>";
//        print_r( $tag );
//        echo "<br>";
//        print_r( $newTag );

//        print_r( $article );
//        print_r( $objDom->outerHtml );
//        exit;

        if( $count )
        {
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

            $styles = Template::generateInlineStyle('.mod_article#' . $articleID . $styles);
        }

        return $styles . $strContent;
    }
}