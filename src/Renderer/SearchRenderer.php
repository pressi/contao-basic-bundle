<?php
/*******************************************************************
 *
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 *
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *
 *******************************************************************/

namespace IIDO\BasicBundle\Renderer;


class SearchRenderer
{
    public static function renderSearchTemplate( $strBuffer )
    {
        if( preg_match('/open-fullscreen-search/', $strBuffer) )
        {
            $objModule  = \ModuleModel::findOneBy("type", "search");

            if( $objModule )
            {
                $strModule = \Controller::getFrontendModule( $objModule->id ); //6
                $pregMatch = '([A-Za-z0-9\s\-=",;.:_]{0,})';

//                if( preg_match('/<div' . $pregMatch . 'class="mod_search([A-Za-z0-9\s\-_]{0,})"' . $pregMatch . '>/', $strModule, $arrMatches) )
//                {
//                    $strModule = preg_replace('/' . preg_quote($arrMatches[0],  '/') . '/', '', $strModule);
//                    $strModule = preg_replace('/<\div>$/', '', trim($strModule));
//                    $strModule = preg_replace('/<form/', '<form class="' . trim(preg_replace('/block/', '', $arrMatches[2])) . '"', trim($strModule));
//                }
//                $strModule = preg_replace('/<div class="formbody">/', '', $strModule);
//                $strModule = preg_replace('/<\/div>([\s\n]{0,})<\/form>/', '</form>', $strModule);

                $strModule = preg_replace('/<input' . $pregMatch . 'type="submit"' . $pregMatch . 'value="([A-Za-z0-9öäüÖÄÜß]{0,})"' . $pregMatch . '>/', '<button$1type="submit"$2$4>$3</button>', $strModule);
                $strModule = preg_replace('/<\/form>/', '<a href="" class="fullscreen-search-form-close">close</a></form>', $strModule);
                $strBuffer = preg_replace('/<\/body>/', $strModule . '</body>', $strBuffer);
            }
        }

        return $strBuffer;
    }
}