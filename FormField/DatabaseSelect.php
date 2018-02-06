<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\FormField;


use Contao\CalendarModel;
use IIDO\BasicBundle\Helper\BasicHelper;
use IIDO\BasicBundle\Helper\EventsHelper;


/**
 * Class Database Select
 *
 */
class DatabaseSelect extends \FormSelectMenu
{

    /**
     * Generate the options
     *
     * @return array The options array
     */
    protected function getOptions()
    {
        $arrOptions = array();

        if( $this->optionsBlankLabel )
        {
            $arrOptions[] = array
            (
                'value'     => $this->optionsBlankLabel,
                'label'     => $this->optionsBlankLabel,
                'type'      => 'option',
                'selected'  => false
            );
        }

        if( $this->optionsFrom )
        {
            switch( $this->optionsFrom )
            {
                case "news":
                    break;

                case "events":
                    $arrOptions = $this->getEventOptions( $arrOptions );
                    break;
            }
        }

        foreach( parent::getOptions() as $arrOption )
        {
            if( strlen($arrOption['value']) && $arrOption['label'] !== "-" )
            {
                $arrOptions[] = $arrOption;
            }
        }

        if( count($arrOptions) === 1 || (count($arrOptions) === 2 && $this->optionsBlankLabel) )
        {
            $optNum = (count($arrOptions) - 1);

            $arrOptions[ $optNum ]['selected'] = ' selected="selected"';
        }

        return $arrOptions;
    }



    /**
     * Generate the widget and return it as string
     *
     * @param array $arrOptions
     *
     * @return array|string
     */
//    public function generate()
//    {
//        $strWidget  = parent::generate();
//        $arrOptions = array();
//
//        if( $this->optionsBlankLabel )
//        {
//            $arrOptions[''] = $this->optionsBlankLabel;
//        }
//
//        if( $this->optionsFrom )
//        {
//            switch( $this->optionsFrom )
//            {
//                case "news":
//                    break;
//
//                case "events":
//                    $arrOptions = $this->getEventOptions( $arrOptions );
//                    break;
//            }
//        }
//
//        if( count($arrOptions) )
//        {
//            $strOptions = '';
//
//            foreach( $arrOptions as $strKey => $strValue )
//            {
//                $strOptions .= '<option value="' . $strKey . '">' . $strValue . '</option>';
//            }
//
//            $strWidget = preg_replace('/<select([A-Za-z0-9\s\-=",;.:_\{\}\(\)]{0,})>/', '<select$1>' . $strOptions, $strWidget);
//        }
//
////        if( $this->mandatory )
////        {
////            $this->label = '<span class="invisible">Pflichtfeld </span>' . $this->label . '<span class="mandatory">*</span>';
////        }
//
////        $strWidget = '<div class="widget widget-select select ' . $this->class . '"><label for="ctrl_' . $this->id . '" class="select ' . $this->class . '">' . $this->label . '</label>' . $strWidget . '</div>';
//
//        return $strWidget;
//    }



    /**
     * @param array $arrOptions
     *
     * @return array
     */
    protected function getEventOptions($arrOptions = array() )
    {
        $arrCalendars   = $this->sortOutProtectedCalendars(\StringUtil::deserialize($this->eventsArchives, true));
        $objDate        = new \Date();

        if( preg_match('/separate-lang-archive/', $this->class) )
        {
            $strLang = BasicHelper::getLanguage();

            foreach($arrCalendars as $key => $calendarID)
            {
                $objCalendar = CalendarModel::findByPk( $calendarID );

                if( $objCalendar )
                {
                    if( $strLang === "de" && $objCalendar->master > 0 )
                    {
                        unset( $arrCalendars[ $key ] );
                    }
                    elseif( $strLang !== "de" && $objCalendar->master === 0 )
                    {
                        unset( $arrCalendars[ $key ] );
                    }
                }
            }
        }

        $arrCalendars = array_values($arrCalendars);

        list($intStart, $intEnd, $strEmpty) = $this->getDatesFromFormat($objDate, 'next_all');

        $arrEvents = EventsHelper::getAllEvents( $arrCalendars, $intStart, $intEnd );

        if( is_array($arrEvents) && count($arrEvents) )
        {
            foreach( $arrEvents as $strKey => $arrSubEvents)
            {
                foreach($arrSubEvents as $key => $arrKeyEvents)
                {
                    foreach($arrKeyEvents as $arrEvent)
                    {
                        $strDate    = $arrEvent['date']; //date(\Config::get("dateFormat"), strtotime($arrEvent['datetime']));
                        $strTitle   = $arrEvent['title'];

                        if( preg_match('/–/', $strDate) )
                        {
                            $arrDate = explode("–", $strDate);
                            $strDate = $arrDate[0];
                        }

                        $eventKey = $strDate . ' ' . $strTitle;

                        if( preg_match('/load-on-post/', $this->class) )
                        {
                            $eventIdOrAlias     = \Input::get("event"); //(\Config::get("useAutoItem") ? \Input::get("auto_item") : (\Input::get("events")?:\Input::get("event")));
                            $objEvent           = \CalendarEventsModel::findByIdOrAlias( $eventIdOrAlias );

                            if( $objEvent && $objEvent->id === $arrEvent['id'] )
                            {
                                $arrOptions[] = array
                                (
                                    'value'     => $eventKey . ' (' . $arrEvent['id'] . ')',
                                    'label'     => $eventKey,
                                    'type'      => 'option',
                                    'selected'  => false
                                );
                            }
                        }
                        else
                        {
                            $arrOptions[] = array
                            (
                                'value'     => $eventKey . ' (' . $arrEvent['id'] . ')',
                                'label'     => $eventKey,
                                'type'      => 'option',
                                'selected'  => false
                            );
                        }
                    }
                }
            }
        }

        return $arrOptions;
    }



    /**
     * Sort out protected archives
     *
     * @param array $arrCalendars
     *
     * @return array
     */
    protected function sortOutProtectedCalendars( $arrCalendars )
    {
        if (!\is_array($arrCalendars) || empty($arrCalendars))
        {
            return $arrCalendars;
        }

        $this->import('FrontendUser', 'User');
        $objCalendar = \CalendarModel::findMultipleByIds($arrCalendars);
        $arrCalendars = array();

        if ($objCalendar !== null)
        {
            while ($objCalendar->next())
            {
                if ($objCalendar->protected)
                {
                    if (!FE_USER_LOGGED_IN)
                    {
                        continue;
                    }

                    $groups = \StringUtil::deserialize($objCalendar->groups);

                    if (!\is_array($groups) || empty($groups) || \count(array_intersect($groups, $this->User->groups)) < 1)
                    {
                        continue;
                    }
                }

                $arrCalendars[] = $objCalendar->id;
            }
        }

        return $arrCalendars;
    }



    /**
     * Return the begin and end timestamp and an error message as array
     *
     * @param \Date   $objDate
     * @param string  $strFormat
     *
     * @return array
     */
    protected static function getDatesFromFormat(\Date $objDate, $strFormat)
    {
        switch ($strFormat)
        {
            case 'cal_day':
                return array($objDate->dayBegin, $objDate->dayEnd, $GLOBALS['TL_LANG']['MSC']['cal_emptyDay']);

            default:
            case 'cal_month':
                return array($objDate->monthBegin, $objDate->monthEnd, $GLOBALS['TL_LANG']['MSC']['cal_emptyMonth']);

            case 'cal_year':
                return array($objDate->yearBegin, $objDate->yearEnd, $GLOBALS['TL_LANG']['MSC']['cal_emptyYear']);

            case 'cal_all': // 1970-01-01 00:00:00 - 2038-01-01 00:00:00
                return array(0, 2145913200, $GLOBALS['TL_LANG']['MSC']['cal_empty']);
                break;

            case 'next_7':
                return array(time(), strtotime('+7 days'), $GLOBALS['TL_LANG']['MSC']['cal_empty']);

            case 'next_14':
                return array(time(), strtotime('+14 days'), $GLOBALS['TL_LANG']['MSC']['cal_empty']);

            case 'next_30':
                return array(time(), strtotime('+1 month'), $GLOBALS['TL_LANG']['MSC']['cal_empty']);

            case 'next_90':
                return array(time(), strtotime('+3 months'), $GLOBALS['TL_LANG']['MSC']['cal_empty']);

            case 'next_180':
                return array(time(), strtotime('+6 months'), $GLOBALS['TL_LANG']['MSC']['cal_empty']);

            case 'next_365':
                return array(time(), strtotime('+1 year'), $GLOBALS['TL_LANG']['MSC']['cal_empty']);

            case 'next_two':
                return array(time(), strtotime('+2 years'), $GLOBALS['TL_LANG']['MSC']['cal_empty']);

            case 'next_cur_month':
                return array(time(), strtotime('last day of this month 23:59:59'), $GLOBALS['TL_LANG']['MSC']['cal_empty']);

            case 'next_cur_year':
                return array(time(), strtotime('last day of december this year 23:59:59'), $GLOBALS['TL_LANG']['MSC']['cal_empty']);

            case 'next_next_month':
                return array(strtotime('first day of next month 00:00:00'), strtotime('last day of next month 23:59:59'), $GLOBALS['TL_LANG']['MSC']['cal_empty']);

            case 'next_next_year':
                return array(strtotime('first day of january next year 00:00:00'), strtotime('last day of december next year 23:59:59'), $GLOBALS['TL_LANG']['MSC']['cal_empty']);

            case 'next_all': // 2038-01-01 00:00:00
                return array(time(), 2145913200, $GLOBALS['TL_LANG']['MSC']['cal_empty']);

            case 'past_7':
                return array(strtotime('-7 days'), time(), $GLOBALS['TL_LANG']['MSC']['cal_empty']);

            case 'past_14':
                return array(strtotime('-14 days'), time(), $GLOBALS['TL_LANG']['MSC']['cal_empty']);

            case 'past_30':
                return array(strtotime('-1 month'), time(), $GLOBALS['TL_LANG']['MSC']['cal_empty']);

            case 'past_90':
                return array(strtotime('-3 months'), time(), $GLOBALS['TL_LANG']['MSC']['cal_empty']);

            case 'past_180':
                return array(strtotime('-6 months'), time(), $GLOBALS['TL_LANG']['MSC']['cal_empty']);

            case 'past_365':
                return array(strtotime('-1 year'), time(), $GLOBALS['TL_LANG']['MSC']['cal_empty']);

            case 'past_two':
                return array(strtotime('-2 years'), time(), $GLOBALS['TL_LANG']['MSC']['cal_empty']);

            case 'past_cur_month':
                return array(strtotime('first day of this month 00:00:00'), time(), $GLOBALS['TL_LANG']['MSC']['cal_empty']);

            case 'past_cur_year':
                return array(strtotime('first day of january this year 00:00:00'), time(), $GLOBALS['TL_LANG']['MSC']['cal_empty']);

            case 'past_prev_month':
                return array(strtotime('first day of last month 00:00:00'), strtotime('last day of last month 23:59:59'), $GLOBALS['TL_LANG']['MSC']['cal_empty']);

            case 'past_prev_year':
                return array(strtotime('first day of january last year 00:00:00'), strtotime('last day of december last year 23:59:59'), $GLOBALS['TL_LANG']['MSC']['cal_empty']);

            case 'past_all': // 1970-01-01 00:00:00
                return array(0, time(), $GLOBALS['TL_LANG']['MSC']['cal_empty']);
        }
    }
}