<?php
/*******************************************************************
 * (c) 2020 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\Renderer;


use Contao\PageModel;
use Contao\ThemeModel;
use Contao\ModuleModel;
use Contao\System;
use IIDO\BasicBundle\Config\IIDOConfig;
use IIDO\BasicBundle\Helper\BasicHelper;
use IIDO\BasicBundle\Helper\GlobalElementsHelper;


class MobileRenderer
{

    public static function renderMobileMenuTemplate( $strBuffer )
    {
        if( IIDOConfig::get('enableMobileNavigation') )
        {
            /* @var PageModel $objPage */
            global $objPage;

            $objRootPage    = PageModel::findByPk( $objPage->rootId );
            $objLayout      = BasicHelper::getPageLayout( $objPage );
            $objTheme       = ThemeModel::findByPk( $objLayout->pid );
            $modNavi        = '';

            $arrClasses     =
            [
                'main-navigation-mobile-open',
                'hamburger',
                'hamburger--squeeze',
                'js-hamburger'
            ];

            if( IIDOConfig::get('showMobileNavOnTablet') )
            {
                $arrClasses[] = 'show-on-tablet';
            }

            if( !IIDOConfig::get('showMobileNavBurgerDark') )
            {
                $arrClasses[] = 'light';
            }

            $menuOpen       = '<a href="javascript:void(0)" class="' . implode(' ', $arrClasses) . '"><div class="hamburger-box"><div class="hamburger-inner"></div></div></a>';
            $menuClose      = ''; //'<button class="main-navigation-mobile-close">close</button>';

            $strArticle = GlobalElementsHelper::get('mobile-menu', $objRootPage->alias );

            if( $strArticle )
            {
                $modNavi = $strArticle;
            }
            else
            {
                $strModuleTable = ModuleModel::getTable();
                $objNavModule   = ModuleModel::findOneBy(array($strModuleTable . ".type=?", $strModuleTable . ".pid=?"), array("navigation", $objTheme->id));

                if ($objNavModule )
                {
                    $strClass = \Module::findClass( $objNavModule->type );

                    if (class_exists($strClass))
                    {
                        $objNavModule->typePrefix = 'ce_';

                        /** @var \Module $objNavModule */
                        $objNavModule = new $strClass($objNavModule, "main");

                        $objNavModule->cssID = array('', 'nav-mobile-main');
//                    $objNavModule->navigationTpl = 'nav_mobile';

                        $modNavi = $objNavModule->generate();
                    }
                }
            }

            $menuMobile = '<div class="main-navigation-mobile"><div class="mobile-menu-inside">' . $modNavi . $menuClose . '</div></div>';

            $strBuffer = preg_replace('/<\/body>/',  $menuOpen . $menuMobile . '</body>', $strBuffer);
        }

        return $strBuffer;
    }

}