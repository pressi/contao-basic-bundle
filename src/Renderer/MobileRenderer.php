<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\Renderer;


use IIDO\BasicBundle\Helper\BasicHelper;
use IIDO\BasicBundle\Helper\GlobalElementsHelper;


class MobileRenderer
{

    public static function renderMobileMenuTemplate( $strBuffer )
    {
        /* @var \PageModel $objPage */
        global $objPage;

        $objRootPage    = \PageModel::findByPk( $objPage->rootId );
        $objLayout      = BasicHelper::getPageLayout( $objPage );
        $objTheme       = \ThemeModel::findByPk( $objLayout->pid );

        $menuOpen   = '<a href="javascript:void(0)" class="main-navigation-mobile-open hamburger hamburger--squeeze js-hamburger"><div class="hamburger-box"><div class="hamburger-inner"></div></div></a>';
        $menuClose  = '<button class="main-navigation-mobile-close">close</button>';

        $strArticle = GlobalElementsHelper::get('mobile-menu', $objRootPage->alias );

        if( $strArticle )
        {
            $modNavi = $strArticle;
        }
        else
        {
            $strModuleTable = \ModuleModel::getTable();
            $objNavModule   = \ModuleModel::findOneBy(array($strModuleTable . ".type=?", $strModuleTable . ".pid=?"), array("navigation", $objTheme->id));

            if ($objNavModule )
            {
                $strClass = \Module::findClass( $objNavModule->type );

                if (class_exists($strClass))
                {
                    $objNavModule->typePrefix = 'ce_';

                    /** @var \Module $objNavModule */
                    $objNavModule = new $strClass($objNavModule, "main");

                    $objNavModule->cssID = array('', 'nav-mobile-main');
                    $objNavModule->navigationTpl = 'nav_mobile';

                    $modNavi = $objNavModule->generate();
                }
            }
        }

        $menuMobile = '<div class="main-navigation-mobile"><div class="mobile-menu-inside">' . $modNavi . $menuClose . '</div></div>';

        return preg_replace('/<\/body>/',  $menuOpen . $menuMobile . '</body>', $strBuffer);
    }

}