<?php


namespace IIDO\BasicBundle\Renderer;


use Contao\ArticleModel;
use Contao\Controller;
use Contao\FrontendTemplate;
use Contao\StringUtil;
use IIDO\BasicBundle\Helper\BasicHelper;
use IIDO\BasicBundle\Helper\ColorHelper;
use IIDO\BasicBundle\Helper\ContentHelper;


class SectionRenderer
{
//    public static function renderStickyHeader( string $strContent, string $offsetNavToggler = '' ): string
    public static function renderStickyHeader( string $strContent ): string
    {
        $objStickyHeader = ArticleModel::findBy(['articleType=?', 'published=?'], ['stickyHeader', '1'] );

        if( $objStickyHeader )
        {
            $cssID = StringUtil::deserialize( $objStickyHeader->cssID, true );

            if( $cssID[1] )
            {
                $strContent = preg_replace('/id="stickyHeader"/', 'id="stickyHeader" class="' . $cssID[1] . '"', $strContent);
            }

//            if( $offsetNavToggler )
//            {
//                $strContent = preg_replace('/<\/div>([A-Za-z0-9\s\n\-]{0,})<\/div>$/', $offsetNavToggler . '</div></div>', $strContent);
//            }
        }

        return $strContent;
    }



    public static function renderFixedButtons( string $strContent ): string
    {
        $objFixedButtons = ArticleModel::findBy(['articleType=?', 'published=?'], ['fixedButtons', '1'] );

        if( $objFixedButtons )
        {
            $cssID = StringUtil::deserialize( $objFixedButtons->cssID, true );

            if( $cssID[1] )
            {
                $strContent = preg_replace('/id="fixedContainer"/', 'id="fixedContainer" class="' . $cssID[1] . '"', $strContent);
            }
        }

        return $strContent;
    }



    public static function renderOffsetNavigation( string $strContent, $noCheck = false, $objModel = null ): string
    {
        $objOffsetNavigation = $noCheck ? true : ArticleModel::findBy(['articleType=?', 'published=?'], ['navigationCont', '1'] );

        if( $objOffsetNavigation )
        {
            $objTemplate = new FrontendTemplate('iido_offsetNavigation');

            $objTemplate->offsetNavAlias    = $objModel ? $objModel->alias : $objOffsetNavigation->alias;

            $offsetNavigation = Controller::replaceInsertTags( $objTemplate->parse() );

            $strContent = preg_replace('/<\/body>/',  $offsetNavigation . '</body>', $strContent);
        }

        return $strContent;
    }



    public static function getOffsetNavigationToggler( $objHeader )
    {
        $cssID = StringUtil::deserialize($objHeader->cssID, true);

        $arrClasses =
        [
            'nav-toggler',
            'offset-navigation-toggler'
        ];

        $strHamburger = '<div class="nav-toggler-inside"></div>';

        if( false === strpos($cssID[1], 'no-hamburger') )
        {
            $arrClasses[] = 'hamburger';
            $arrClasses[] = 'hamburger--squeeze';
            $arrClasses[] = 'hamburger--accessible';
            $arrClasses[] = 'js-hamburger';

            $strHamburger = '<div class="hamburger-box"><div class="hamburger-inner"></div></div><span class="hamburger-label">Menü</span>';
        }

        return '<a href="javascript:void(0)" class="' . implode(' ', $arrClasses) . '">' . $strHamburger . '</a>';
    }
}