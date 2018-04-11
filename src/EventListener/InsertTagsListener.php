<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\EventListener;


use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\Config;

use Contao\Environment;
use Contao\PageModel;
use Contao\LayoutModel;
use Contao\Model\Collection;
use Contao\NewsFeedModel;
use Contao\StringUtil;
use Contao\Template;
use Contao\Frontend;
//use IIDO\WebsiteBundle\Helper\SocialmediaHelper;
//use IIDO\WebsiteBundle\Helper\WebsiteHelper;


/**
 * DESCRIPTION
 *
 * @author Stephan Preßl <https://github.com/pressi>
 */
class InsertTagsListener extends DefaultListener
{

    /**
     * Replace IIDO Insert Tags
     *
     * @param string $strTag
     * @param $blnCache
     * @param $strCache
     * @param $flags
     * @param $tags
     * @param $arrCache
     * @param $_rit
     * @param $_cnt
     *
     * @return bool|mixed|string
     */
    public function replaceCustomizeInsertTags( $strTag, $blnCache, $strCache, $flags, $tags, &$arrCache, $_rit, $_cnt )
    {
        global $objPage;

        $return         = false;
        $arrSplit       = explode('::', $strTag);
        $strCurTag      = $tags[$_rit+1];

        switch( strtolower($arrSplit[0]) )
        {
            case "iido":

                switch( $arrSplit[1] )
                {
                    case "insert_article":
                        $strInsertAlias     = \Controller::replaceInsertTags( $arrSplit[2] );
                        $objInsertArticle   = \ArticleModel::findByAlias( $strInsertAlias );

                        if( $objInsertArticle )
                        {
                            $return = \Controller::replaceInsertTags( '{{insert_article::' . $arrSplit[2] . '}}' );
                            $return = '<div class="' . $arrSplit[3] . '"><div class="' . $arrSplit[3] . '-inside">' . $return . '</div></div>';
                        }
                        break;



//                    case "website":
//
//                        switch( $arrSplit[2] )
//                        {
//                            case "header":
//                                $return = WebsiteHelper::renderWebsiteHeader();
//                                break;
//
//                            case "footer":
//                                $return = WebsiteHelper::renderWebsiteFooter();
//                                break;
//                        }
//                        break;
                    
                    case "lang":
                        $langName   = $arrSplit[2];
                        $langValue  = $GLOBALS['TL_LANG']['IIDO'][ $langName ];

                        if( strlen($langValue) )
                        {
                            $return = $langValue;
                        }
                        break;

                    case "date":
                        if( isset($arrSplit[2]) )
                        {
                            $dateFormat = $arrSplit[2];

                            if( preg_match('/\+/', $dateFormat) )
                            {
                                $isSourceTime   = isset($arrSplit[3]);
                                $sourceTime     = $arrSplit[3];

                                $arrMode    = preg_split("/\+/", $dateFormat);
                                $isMode     = false;

                                $nextMonth  = false;

                                foreach($arrMode as $key => $value)
                                {
                                    if( $isMode )
                                    {
                                        preg_match('/^([0-9]{1,})/', $value, $arrModeMatches);

                                        $strValue   = preg_replace('/^([0-9]{1,})/', '', $value);
                                        $isMode     = false;

                                        $format     = $arrMode[ ($key - 1) ];
                                        $strReturn  = ($isSourceTime ? date($format, $sourceTime) : date($format));
                                        $strReturn  = ((int) $strReturn + (int) $arrModeMatches[1]);

                                        if( $format === "d" )
                                        {
                                            $monthDays = date("t");

                                            if( $strReturn > $monthDays )
                                            {
                                                $strReturn = ($strReturn - $monthDays);

                                                $nextMonth = true;
                                            }

                                            if( strlen($strReturn) === 1)
                                            {
                                                $strReturn = '0' . $strReturn;
                                            }
                                        }

                                        if( $nextMonth && preg_match('/m/', $strValue) )
                                        {
                                            $strMonth = ($isSourceTime ? date("m", $sourceTime) : date("m"));
                                            $strMonth = ((int) $strMonth + 1);

                                            if( strlen($strMonth) === 1 )
                                            {
                                                $strMonth = '0' . $strMonth;
                                            }

                                            $strValue = preg_replace('/m/', $strMonth, $strValue);
                                        }

                                        $return .= $strReturn . ($isSourceTime ? date($strValue, $sourceTime) : date($strValue));
                                    }
                                    else
                                    {
                                        $isMode = true;
                                    }
                                }
                            }
                            else
                            {
                                $return = (( isset($arrSplit[3]) ) ? date($dateFormat, $arrSplit[3]) : date($dateFormat) );
                            }
                        }
                        else
                        {
                            $return = date(Config::get('dateFormat'));
                        }
                        break;

//                    case "company":
//                        $strValue   = Config::get("iidoCompany" . ucfirst($arrSplit[2]) );
//
//                        if( $strValue )
//                        {
//                            $return = $strValue;
//                        }
//
//                        if( $arrSplit[3] == "link" )
//                        {
//                            $strHref = \Controller::replaceInsertTags( $arrSplit[4] );
//
//                            if( !$strHref )
//                            {
//                                if( $arrSplit[2] == 'email' )
//                                {
//                                    $strHref    = '&#109;&#97;&#105;&#108;&#116;&#111;&#58;' . \StringUtil::encodeEmail( $strValue );
//                                    $return     = \StringUtil::encodeEmail( $return );
//                                }
//                                elseif( $arrSplit[2] == "phone" || $arrSplit[2] == "mobile" )
//                                {
//                                    $strHref = 'tel:' . ( $strValue );
//                                }
//                            }
//
//                            $return = '<a href="' . $strHref . '">' . $return . '</a>';
//                        }
//
//                        break;

                    case "link":
                    case "link_open":
                    case "link_begin":
                    case "link_close":
                    case "link_end":
                        $strHref    = \Controller::replaceInsertTags( $arrSplit[2] );
                        $strTarget  = (($arrSplit[3])? ' target="_' . $arrSplit[3] . '"' : '');

                        if( $strHref == "close" || $strHref == "end" || $arrSplit[1] === "link_close" )
                        {
                            $return = '</a>';
                        }
                        else
                        {
                            $return = '<a href="' . $strHref . '"' .$strTarget . '>';
                        }

                        if( $arrSplit[2] === "void" )
                        {
                            $return = 'javascrpt:void(0)';
                        }

                        break;

//                    case "icon":
//                        $return = '<i class="icon icon-heart"><svg class="icon-tag" role="img"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="/bundles/iidowebsite/images/icons/svg/icons.svg#heart"></use></svg></i>';
//                        break;

//                    case "socialmedia":
//                        $return = SocialmediaHelper::render( $arrSplit[2] );
//                        break;

//                    case "if":
//                        if ($arrSplit[2] != '' && !$this->checkExpression( $arrSplit[2]))
//                        {
//                            break;
//                        }
//                        unset( $arrCache[ $strCurTag ] );
//                        break;

                    case "page":
                        switch( $arrSplit[2])
                        {
                            case "title":
                                $return = $objPage->pageTitle?:$objPage->title?:$objPage->alt_pagename;
                                break;

                            case "subtitle":
                                $return = $objPage->subtitle?:'&nbsp;';
                                break;
                        }
                        break;

                    case "event":
                        $adapter    = $this->framework->getAdapter(\CalendarEventsModel::class);
                        $idOrAlias  = \Input::get("auto_item");
                        $eventKey   = strtolower( $arrSplit[2] );
//echo "<pre>";
//print_r( $eventKey );
//echo "<br>";
                        if( !$idOrAlias )
                        {
                            $idOrAlias = \Input::get("events");
                        }
//print_r( $idOrAlias );
//                        exit;
                        if (null !== ($objEvent = $adapter->findByIdOrAlias($idOrAlias)))
                        {
                            $return = $objEvent->$eventKey;

                            if( $eventKey === "date" )
                            {
//                                echo "<pre>"; print_r( $objEvent ); exit;
                                $return = date(\Config::get("dateFormat"), $objEvent->startDate);
                            }
                            elseif( $eventKey === "dateto" )
                            {
                                if( $objEvent->endDate )
                                {
                                    $return = date(\Config::get("dateFormat"), $objEvent->endDate);

                                    if( strlen($return) )
                                    {
                                        $return = '<span class="date date-to">- ' . $return . '</span>';
                                    }
                                }
                            }
                        }

                        break;
                }
                break;
        }

        return $return;
    }



    protected function checkExpression( $strExpression )
    {
        global $objPage;

        $arrPregMatch       = explode('*=', $strExpression);
        $arrPregMatchNot    = explode('*!=', $strExpression);

        if( count($arrPregMatch) > 1 ||count($arrPregMatchNot) > 1 )
        {
            $not            = ((count($arrPregMatchNot)>1) ? TRUE : FALSE);

            $searchIn       = $arrPregMatch[0]?:$arrPregMatchNot[0];
            $searchThis     = $arrPregMatch[1]?:$arrPregMatchNot[1];
            $arrSearchParts = explode("_", $searchIn);

            if( count($arrSearchParts) > 1 )
            {
                if( $arrSearchParts[0] == "page" )
                {
                    $searchIn = $objPage->cssClass;
                }
//                else
//                {
//                    $modelClass = ucfirst($arrSearchParts[0]) . 'Model';
//                }
            }
//echo "<pre>";
//            print_r( $searchThis);
//            echo "<br>";
//            print_r( $searchIn );
//            echo "<br>";
//            print_r( $not );
//            exit;
            if( preg_match('/' . $searchThis . '/', $searchIn) )
            {
                if( !$not )
                {
                    return true;
                }
            }
            else
            {
                if( $not )
                {
                    return true;
                }
            }
        }

        return false;
    }
}
