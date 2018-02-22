<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\Helper;
use IIDO\BasicBundle\Config\BundleConfig;


/**
 * Class Helper
 * @package IIDO\BasicBundle
 */
class EventsHelper
{

    private static $arrEvents = array();

    static $cal_noSpan = false;
    static $intTodayEnd = '';
    static $intTodayBegin = '';

    /**
     * URL cache array
     * @var array
     */
    private static $arrUrlCache = array();



    /**
     * Get all events of a certain period
     *
     * @param array   $arrCalendars
     * @param integer $intStart
     * @param integer $intEnd
     *
     * @return array
     */
    public static function getAllEvents( $arrCalendars, $intStart, $intEnd )
    {
        if (!\is_array($arrCalendars))
        {
            return array();
        }

//        $intStart   = time();
//        $intEnd     = 2145913200;

        foreach ($arrCalendars as $id)
        {
            // Get the events of the current period
            $objEvents = \CalendarEventsModel::findCurrentByPid($id, $intStart, $intEnd);

            if ($objEvents === null)
            {
                continue;
            }

            while ($objEvents->next())
            {
                self::addEvent($objEvents, $objEvents->startTime, $objEvents->endTime, $intStart, $intEnd, $id);

                // Recurring events
                if ($objEvents->recurring)
                {
                    $arrRepeat = \StringUtil::deserialize($objEvents->repeatEach);

                    if (!\is_array($arrRepeat) || !isset($arrRepeat['unit']) || !isset($arrRepeat['value']) || $arrRepeat['value'] < 1)
                    {
                        continue;
                    }

                    $count = 0;
                    $intStartTime = $objEvents->startTime;
                    $intEndTime = $objEvents->endTime;
                    $strtotime = '+ ' . $arrRepeat['value'] . ' ' . $arrRepeat['unit'];

                    while ($intEndTime < $intEnd)
                    {
                        if ($objEvents->recurrences > 0 && $count++ >= $objEvents->recurrences)
                        {
                            break;
                        }

                        $intStartTime = strtotime($strtotime, $intStartTime);
                        $intEndTime = strtotime($strtotime, $intEndTime);

                        // Stop if the upper boundary is reached (see #8445)
                        if ($intStartTime === false || $intEndTime === false)
                        {
                            break;
                        }

                        // Skip events outside the scope
                        if ($intEndTime < $intStart || $intStartTime > $intEnd)
                        {
                            continue;
                        }

                        self::addEvent($objEvents, $intStartTime, $intEndTime, $intStart, $intEnd, $id);
                    }
                }
            }
        }

        // Sort the array
        foreach (array_keys(self::$arrEvents) as $key)
        {
            ksort(self::$arrEvents[$key]);
        }

        return self::$arrEvents;
    }



    /**
     * Add an event to the array of active events
     *
     * @param \CalendarEventsModel $objEvents
     * @param integer              $intStart
     * @param integer              $intEnd
     * @param integer              $intBegin
     * @param integer              $intLimit
     * @param integer              $intCalendar
     */
    protected static function addEvent($objEvents, $intStart, $intEnd, $intBegin, $intLimit, $intCalendar)
    {
        $intDate    = $intStart;
        $intKey     = date('Ymd', $intStart);
        $strDate    = \Date::parse(\Config::get("dateFormat"), $intStart);
        $strDay     = $GLOBALS['TL_LANG']['DAYS'][date('w', $intStart)];
        $strMonth   = $GLOBALS['TL_LANG']['MONTHS'][(date('n', $intStart)-1)];
        $span       = \Calendar::calculateSpan($intStart, $intEnd);

        if ($span > 0)
        {
            $strDate = \Date::parse(\Config::get("dateFormat"), $intStart) . $GLOBALS['TL_LANG']['MSC']['cal_timeSeparator'] . \Date::parse(\Config::get("dateFormat"), $intEnd);
            $strDay = '';
        }

        $strTime = '';

        if ($objEvents->addTime)
        {
            if ($span > 0)
            {
                $strDate = \Date::parse(\Config::get("datimFormat"), $intStart) . $GLOBALS['TL_LANG']['MSC']['cal_timeSeparator'] . \Date::parse(\Config::get("datimFormat"), $intEnd);
            }
            elseif ($intStart == $intEnd)
            {
                $strTime = \Date::parse(\Config::get("timeFormat"), $intStart);
            }
            else
            {
                $strTime = \Date::parse(\Config::get("timeFormat"), $intStart) . $GLOBALS['TL_LANG']['MSC']['cal_timeSeparator'] . \Date::parse(\Config::get("timeFormat"), $intEnd);
            }
        }

        $until = '';
        $recurring = '';

        // Recurring event
        if ($objEvents->recurring)
        {
            $arrRange = \StringUtil::deserialize($objEvents->repeatEach);

            if (\is_array($arrRange) && isset($arrRange['unit']) && isset($arrRange['value']))
            {
                $strKey = 'cal_' . $arrRange['unit'];
                $recurring = sprintf($GLOBALS['TL_LANG']['MSC'][$strKey], $arrRange['value']);

                if ($objEvents->recurrences > 0)
                {
                    $until = sprintf($GLOBALS['TL_LANG']['MSC']['cal_until'], \Date::parse(\Config::get("dateFormat"), $objEvents->repeatEnd));
                }
            }
        }

        // Store raw data
        $arrEvent = $objEvents->row();

        // Overwrite some settings
        $arrEvent['date']       = $strDate;
        $arrEvent['time']       = $strTime;
        $arrEvent['datetime']   = $objEvents->addTime ? date('Y-m-d\TH:i:sP', $intStart) : date('Y-m-d', $intStart);
        $arrEvent['day']        = $strDay;
        $arrEvent['month']      = $strMonth;
        $arrEvent['parent']     = $intCalendar;
        $arrEvent['calendar']   = $objEvents->getRelated('pid');
        $arrEvent['link']       = $objEvents->title;
        $arrEvent['target']     = '';
        $arrEvent['title']      = \StringUtil::specialchars($objEvents->title, true);
        $arrEvent['href']       = self::generateEventUrl($objEvents);
        $arrEvent['class']      = ($objEvents->cssClass != '') ? ' ' . $objEvents->cssClass : '';
        $arrEvent['recurring']  = $recurring;
        $arrEvent['until']      = $until;
        $arrEvent['begin']      = $intStart;
        $arrEvent['end']        = $intEnd;
        $arrEvent['details']    = '';
        $arrEvent['hasDetails'] = false;
        $arrEvent['hasTeaser']  = false;

        // Override the link target
        if ($objEvents->source == 'external' && $objEvents->target)
        {
            $arrEvent['target'] = ' target="_blank"';
        }

        // Clean the RTE output
        if ($arrEvent['teaser'] != '')
        {
            $arrEvent['hasTeaser'] = true;
            $arrEvent['teaser'] = \StringUtil::toHtml5($arrEvent['teaser']);
            $arrEvent['teaser'] = \StringUtil::encodeEmail($arrEvent['teaser']);
        }

        // Display the "read more" button for external/article links
        if ($objEvents->source != 'default')
        {
            $arrEvent['details'] = true;
            $arrEvent['hasDetails'] = true;
        }

        // Compile the event text
        else
        {
            $id = $objEvents->id;

//            $arrEvent['details'] = function () use ($id)
//            {
//                $strDetails = '';
//                $objElement = \ContentModel::findPublishedByPidAndTable($id, 'tl_calendar_events');
//
//                if ($objElement !== null)
//                {
//                    while ($objElement->next())
//                    {
//                        $strDetails .= $this->getContentElement($objElement->current());
//                    }
//                }
//
//                return $strDetails;
//            };

            $arrEvent['hasDetails'] = function () use ($id)
            {
                return \ContentModel::countPublishedByPidAndTable($id, 'tl_calendar_events') > 0;
            };
        }

        // Get todays start and end timestamp
        if (self::$intTodayBegin === null)
        {
            self::$intTodayBegin = strtotime('00:00:00');
        }
        if (self::$intTodayEnd === null)
        {
            self::$intTodayEnd = strtotime('23:59:59');
        }

        // Mark past and upcoming events (see #3692)
        if ($intEnd < self::$intTodayBegin)
        {
            $arrEvent['class'] .= ' bygone';
        }
        elseif ($intStart > self::$intTodayEnd)
        {
            $arrEvent['class'] .= ' upcoming';
        }
        else
        {
            $arrEvent['class'] .= ' current';
        }

        self::$arrEvents[$intKey][$intStart][] = $arrEvent;

        // Multi-day event
        for ($i=1; $i<=$span; $i++)
        {
            // Only show first occurrence
            if (self::$cal_noSpan)
            {
                break;
            }

            $intDate = strtotime('+1 day', $intDate);

            if ($intDate > $intLimit)
            {
                break;
            }

            $arrNewEvent = $arrEvent;
            $arrNewEvent['date'] = date(\Config::get("dateFormat"), $intDate);

            self::$arrEvents[date('Ymd', $intDate)][$intDate][] = $arrNewEvent;
        }
    }



    /**
     * Generate a URL and return it as string
     *
     * @param \CalendarEventsModel $objEvent
     *
     * @return string
     */
    public static function generateEventUrl($objEvent)
    {
        $strCacheKey = 'id_' . $objEvent->id;

        // Load the URL from cache
        if (isset(self::$arrUrlCache[$strCacheKey]))
        {
            return self::$arrUrlCache[$strCacheKey];
        }

        // Initialize the cache
        self::$arrUrlCache[$strCacheKey] = null;

        switch ($objEvent->source)
        {
            // Link to an external page
            case 'external':
                if (substr($objEvent->url, 0, 7) == 'mailto:')
                {
                    self::$arrUrlCache[$strCacheKey] = \StringUtil::encodeEmail($objEvent->url);
                }
                else
                {
                    self::$arrUrlCache[$strCacheKey] = ampersand($objEvent->url);
                }
                break;

            // Link to an internal page
            case 'internal':
                if (($objTarget = $objEvent->getRelated('jumpTo')) instanceof \PageModel)
                {
                    /** @var \PageModel $objTarget */
                    self::$arrUrlCache[$strCacheKey] = ampersand($objTarget->getFrontendUrl());
                }
                break;

            // Link to an article
            case 'article':
                if (($objArticle = \ArticleModel::findByPk($objEvent->articleId, array('eager'=>true))) !== null && ($objPid = $objArticle->getRelated('pid')) instanceof \PageModel)
                {
                    /** @var \PageModel $objPid */
                    self::$arrUrlCache[$strCacheKey] = ampersand($objPid->getFrontendUrl('/articles/' . ($objArticle->alias ?: $objArticle->id)));
                }
                break;
        }

        // Link to the default page
        if (self::$arrUrlCache[$strCacheKey] === null)
        {
            $objPage = \PageModel::findByPk($objEvent->getRelated('pid')->jumpTo);

            if (!$objPage instanceof \PageModel)
            {
                self::$arrUrlCache[$strCacheKey] = ampersand(\Environment::get('request'));
            }
            else
            {
                self::$arrUrlCache[$strCacheKey] = ampersand($objPage->getFrontendUrl((\Config::get('useAutoItem') ? '/' : '/events/') . ($objEvent->alias ?: $objEvent->id)));
            }
        }

        return self::$arrUrlCache[$strCacheKey];
    }

}
