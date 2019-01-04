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
use IIDO\BasicBundle\Helper\HeaderHelper;

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
                    case "countdown":
                        $return = $this->renderCountdown( $arrSplit[2], $arrSplit[3], $arrSplit[4], $arrSplit[5] );
                        break;


                    case "insert_article":
                        if( ($strOutput = \Controller::getArticle($arrSplit[2], false, true)) !== false )
                        {
                            $return     = ltrim($strOutput);
                            $strClass   = $arrSplit[3];

                            $addClasses     = '';
                            $strAttributes  = '';

                            if( $strClass )
                            {
                                if( $strClass === "header-bar" || $strClass === "header-top-bar" )
                                {
                                    $isTopBar       = ($strClass === "header-top-bar");
                                    $objTopHeader   = HeaderHelper::headerTopBarExists();

                                    if( $objTopHeader )
                                    {
                                        if( $isTopBar )
                                        {
                                            $arrData = HeaderHelper::getTopHeaderData();
                                        }
                                        else
                                        {
                                            $arrData = HeaderHelper::getHeaderData( );
                                        }

                                        $addClasses     = ' ' . $arrData['class'];
                                        $strAttributes  = ' ' . $arrData['attributes'];
                                    }
                                }

                                $return = '<div class="' . $strClass . $addClasses . '"' . $strAttributes . '><div class="' . $strClass . '-inside">' . $return . '</div></div>';
                            }
                        }
                        else
                        {
                            $return = '';
                        }
                        break;


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
//                        break;

                    case "link_void":
                        $return = 'javascript:void(0);';
                        break;

                    case "link":
                    case "link_open":
                    case "link_begin":
                    case "link_close":
                    case "link_end":
                        $strHref    = \Controller::replaceInsertTags( $arrSplit[2] );
                        $strTarget  = (($arrSplit[3]) ? ' target="_' . $arrSplit[3] . '"' : '');

                        if( $strHref === "close" || $strHref === "end" || $arrSplit[1] === "link_close" || $arrSplit[1] === "link_end" )
                        {
                            $return = '</a>';
                        }
                        else
                        {
                            $return = '<a href="' . $strHref . '"' .$strTarget . '>';
                        }

                        if( $arrSplit[2] === "void" )
                        {
                            $return = 'javascript:void(0)';
                        }
                        break;

//                    case "icon":
//                        $return = '<i class="icon icon-heart"><svg class="icon-tag" role="img"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="/bundles/iidowebsite/images/icons/svg/icons.svg#heart"></use></svg></i>';
//                        break;

//                    case "socialmedia":
//                        $return = SocialmediaHelper::render( $arrSplit[2] );
//                        break;


                    case "page":
                        switch( $arrSplit[2])
                        {
                            case "title":
                                $return = $objPage->pageTitle?:$objPage->title?:$objPage->alt_pagename;

                                if( $arrSplit[3] )
                                {
                                    $level = -1;

                                    if( preg_match('/level-/', $arrSplit[3]) )
                                    {
                                        $level = (int) preg_replace('/level-/', '', $arrSplit[3]);
                                    }

                                    if( $level >= 0 )
                                    {
                                        if( count($objPage->trail) > ($level + 1) )
                                        {
                                            $objLevelPage = \PageModel::findByPk( $objPage->trail[ $level ] );
                                            $return = $objLevelPage->pageTitle?:$objLevelPage->alt_pagename?:$objLevelPage->title;
                                        }
                                    }
                                }
                                break;

                            case "subtitle":
                                $return = $objPage->subtitle?:'&nbsp;';
                                break;

                            case "navtitle":
                                $return = $objPage->navTitle?:$objPage->pageTitle?:$objPage->title?:$objPage->alt_pagename;

                                if( $arrSplit[3] )
                                {
                                    $level = -1;

                                    if( preg_match('/level-/', $arrSplit[3]) )
                                    {
                                        $level = (int) preg_replace('/level-/', '', $arrSplit[3]);
                                    }

                                    if( $level >= 0 )
                                    {
                                        if( count($objPage->trail) > ($level + 1) )
                                        {
                                            $objLevelPage = \PageModel::findByPk( $objPage->trail[ $level ] );
                                            $return = $objLevelPage->navTitle?:$objLevelPage->pageTitle?:$objLevelPage->alt_pagename?:$objLevelPage->title;
                                        }
                                    }
                                }

                                break;
                        }
                        break;


                    case "event":
                        $adapter    = $this->framework->getAdapter(\CalendarEventsModel::class);
                        $idOrAlias  = \Input::get("auto_item");
                        $eventKey   = strtolower( $arrSplit[2] );

                        if( !$idOrAlias )
                        {
                            $idOrAlias = \Input::get("events");
                        }

                        if (null !== ($objEvent = $adapter->findByIdOrAlias($idOrAlias)))
                        {
                            $return = $objEvent->$eventKey;

                            if( $eventKey === "date" )
                            {
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



                    case "get":
                        $return = \Input::get( $arrSplit[2] );
                        break;


                    case "post":
                        $return = \Input::post( $arrSplit[2] );
                        break;

                    case "postRaw":
                        $return = \Input::postRaw( $arrSplit[2] );
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
                if( $arrSearchParts[0] === "page" )
                {
                    $searchIn = $objPage->cssClass;
                }
//                else
//                {
//                    $modelClass = ucfirst($arrSearchParts[0]) . 'Model';
//                }
            }

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



    protected function renderCountdown( $date, $textAfter = 'Countdown ist beendet', $mode = 'static', $design = 'text' )
    {
        $originDate = $date;

        $date = new \DateTime( $date );
        $now  = new \DateTime();
//        echo "<pre>"; print_r( $date ); exit;
        $strDate = $date->getTimestamp() . '000';

        $boxed = ($design === "box" || $design === "boxed");

        if( $now > $date )
        {
            return $textAfter;
        }

        $countdown  = '';
        $interval   = $date->diff( $now );

//        $years      = $interval->y;
//        $months     = $interval->m;
        $days       = $interval->days;
        $hours      = $interval->h;
        $minutes    = $interval->i;
        $seconds    = $interval->s;

//        if( $years )
//        {
//            $strYearsLabel = $GLOBALS['TL_LANG']['MSC'][ ($years > 1 ? 'years' : 'year') ];
//
//            if( $boxed )
//            {
//                $countdown .= '<div class="box box-years"><span class="value">' . $years . '</span><span class="label">' . $strYearsLabel . '</span></div>';
//            }
//            else
//            {
//                $countdown .= $years . ' ' . $strYearsLabel;
//            }
//        }
//
//        if( $months )
//        {
//            $strMonthsLabel = $GLOBALS['TL_LANG']['MSC'][ ($months > 1 ? 'months' : 'month') ];
//
//            if( $boxed )
//            {
//                $countdown .= '<div class="box box-months"><span class="value">' . $months . '</span><span class="label">' . $strMonthsLabel . '</span></div>';
//            }
//            else
//            {
//                if( strlen($countdown) )
//                {
//                    $countdown .= ', ';
//                }
//
//                $countdown .= $months . ' ' . $strMonthsLabel;
//            }
//        }

//        if( $days || (!$days && ($months || $years)) )
//        if( $days )
//        {
            $strDaysLabel = $GLOBALS['TL_LANG']['MSC'][ (($days > 1 || $days === 0) ? 'days' : 'day') ];

            if( $boxed )
            {
                $countdown .= '<div class="box box-days"><span class="value">' . $days . '</span><span class="label">' . $strDaysLabel . '</span></div>';
            }
            else
            {
                if( strlen($countdown) )
                {
                    $countdown .= ', ';
                }

                $countdown .= $days . ' ' . $strDaysLabel;
            }
//        }

//        if( $hours || (!$hours && ($days || $months || $years))  )
//        {
//            $strHoursLabel = $GLOBALS['TL_LANG']['MSC'][ (($hours > 1 || $hours === 0) ? 'hours' : 'hour') ];
            $strHoursLabel = $GLOBALS['TL_LANG']['MSC'][ 'hours'];

            if( $boxed )
            {
                $countdown .= '<div class="box box-hours"><span class="value">' . $hours . '</span><span class="label">' . $strHoursLabel . '</span></div>';
            }
            else
            {
                if( strlen($countdown) )
                {
                    $countdown .= ', ';
                }

                $countdown .= $hours . ' ' . $strHoursLabel;
            }
//        }

//        if( $minutes )
//        {
//            $strMinutesLabel = $GLOBALS['TL_LANG']['MSC'][ ($minutes > 1 ? 'minutes' : 'minute') ];
            $strMinutesLabel = $GLOBALS['TL_LANG']['MSC']['minutes'];

            if( $boxed )
            {
                $countdown .= '<div class="box box-minutes"><span class="value">' . $minutes . '</span><span class="label">' . $strMinutesLabel . '</span></div>';
            }
            else
            {
                if( strlen($countdown) )
                {
                    if( $mode === "live" )
                    {
                        $countdown .= ', ';
                    }
                    else
                    {
                        $countdown .= ' und ';
                    }
                }

                $countdown .= $minutes . ' ' . $strMinutesLabel;
            }
//        }

        if( $mode === "live" )
        {
//            if( $seconds )
//            {
//                $strSecondsLabel = $GLOBALS['TL_LANG']['MSC'][ ($seconds > 1 ? 'seconds' : 'second') ];
                $strSecondsLabel = $GLOBALS['TL_LANG']['MSC']['seconds'];

                if( $boxed )
                {
                    $countdown .= '<div class="box box-seconds"><span class="value">' . $seconds . '</span><span class="label">' . $strSecondsLabel . '</span></div>';
                }
                else
                {
                    if( strlen($countdown) )
                    {
                        $countdown .= ' und ';
                    }

                    $countdown .= $seconds . ' ' . $strSecondsLabel;
                }
//            }
        }

        if( $boxed )
        {
            $countdown = '<div class="countdown-container" data-date="' . $strDate . '" data-text="' . $textAfter . '">' . $countdown . '</div>';
        }

        return $countdown;
    }
}
