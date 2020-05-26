<?php
/*******************************************************************
 *
 * (c) 2020 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 *
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *
 *******************************************************************/

namespace IIDO\BasicBundle\Renderer;


use Contao\Controller;


class SearchRenderer
{
    public static function renderSearchTemplate( $strBuffer )
    {
        if( preg_match('/open-fullscreen-search/', $strBuffer) )
        {
            $objModule  = \ModuleModel::findOneBy("type", "search");

            if( $objModule )
            {
                $strModule = Controller::getFrontendModule( $objModule->id ); //6
                $pregMatch = '([A-Za-z0-9\s\-=",;.:_]{0,})';

//                $strSearchArticle =

                if( preg_match('/<div' . $pregMatch . 'class="mod_search([A-Za-z0-9\s\-_]{0,})"' . $pregMatch . '>/', $strModule, $arrMatches) )
                {
                    $strModule = preg_replace('/' . preg_quote($arrMatches[0],  '/') . '/', '', $strModule);
                    $strModule = preg_replace('/<\div>$/', '', trim($strModule));
                    $strModule = preg_replace('/<form/', '<form class="' . trim(preg_replace('/block/', '', $arrMatches[2])) . '"', trim($strModule));
                }
                $strModule = preg_replace('/<div class="formbody">/', '', $strModule);
                $strModule = preg_replace('/<\/div>([\s\n]{0,})<\/form>/', '</form>', $strModule);

//                $strLinks = '<div class="nav-search">';

//                $objServicePages = \PageModel::findPublishedByPid(25, ['order'=>'sorting']);
//
//                if( $objServicePages )
//                {
//                    $strLinks .= '<div class="nav-search-title">' . \Controller::replaceInsertTags('{{link_title::25}}') . '</div>';
//
//                    $strLinks .= '<ul class="level_1">';
//
//                    while( $objServicePages->next() )
//                    {
//                        $objServicePage = $objServicePages->current();
//
//                        if( $objServicePage->type === 'redirect' )
//                        {
//                            $strUrl = $objServicePage->url;
//                        }
//                        else
//                        {
//                            $strUrl = $objServicePage->getFrontendUrl();
//                        }
//
//                        $strLinks .= '<li><a href="' . $strUrl . '"><span>' . $objServicePage->title . '</span></a></li>';
//                    }
//                }

//                $strLinks .= '<li><a href="{{link_url::103}}"><span>{{link_title::103}}</span></a></li>';
//                $strLinks .= '<li><a href="{{link_url::102}}"><span>{{link_title::102}}</span></a></li>';
//                $strLinks .= '<li><a href="{{link_url::25}}"><span>{{link_title::25}}</span></a></li>';

//                $strLinks .= '</ul>';
//                $strLinks .= '</div>';

                $strModule = preg_replace('/<input' . $pregMatch . 'type="submit"' . $pregMatch . 'value="([A-Za-z0-9öäüÖÄÜß]{0,})"' . $pregMatch . '>/', '<button$1type="submit"$2$4>$3</button>', $strModule);
                $strModule = preg_replace('/<form([A-Za-z0-9\s\-,;.:_="\/]{0,})>/', '<form$1><div class="form-inside">', $strModule);
                $strModule = preg_replace('/<\/form>/', '</div><a href="" class="fullscreen-search-form-close">close</a></form>' . $strSearchArticle, $strModule);
                $strBuffer = preg_replace('/<\/body>/', $strModule . '</body>', $strBuffer);
            }
        }

        return $strBuffer;
    }
}
