<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\Helper;


/**
 * Class Helper
 * @package IIDO\BasicBundle
 */
class DateHelper extends \Frontend
{

    public static function month_diff( $ts_alt, $ts_neu)
    {
        if($ts_alt > $ts_neu ) return(FALSE);

        $alt = getdate($ts_alt);
        $neu = getdate($ts_neu);

        list($years, $months, $days) = self::date_diff(
            $alt['year'], $alt['mon'], $alt['mday'],
            $neu['year'], $neu['mon'], $neu['mday']
        );

        return($years * 12 + $months);
    }



    public static function date_diff(
        $year1 = FALSE,  // Datum alt
        $month1 = FALSE, // dieses Datum muss kleiner oder
        $day1 = FALSE,   // gleich dem "neuen" Datum sein

        $year2 = FALSE,  // Datum neu
        $month2 = FALSE,
        $day2 = FALSE
    )
    {
        // sanity checks
        if(!is_integer($month1) || $month1 < 1 || $month1 > 12) return(FALSE);
        if(!is_integer($month2) || $month2 < 1 || $month2 > 12) return(FALSE);

        // das brauchen wir später noch
        $days_in_month1 = self::days_in_month($year1, $month1);

        if(!is_integer($day1) || $day1 < 1 || $day1 > $days_in_month1) return(FALSE);
        if(!is_integer($day2) || $day2 < 1 || $day2 > self::days_in_month($year2, $month2) ) return(FALSE);

        $diff_years = FALSE;
        $diff_months = FALSE;
        $diff_days = FALSE;

        // Tageswerte ermitteln
        $days_in_month1 = self::days_in_month($year1, $month1);

        if($year1 < $year2) $diff_years = $year2 - $year1 - 1;
        // gleiches Jahr
        elseif($year1 == $year2) {
            $diff_years = 0;
            if($month1 < $month2) $diff_months = $month2 - $month1 - 1;
            // gleicher Monat
            elseif($month1 == $month2) {
                $diff_months = 0;
                if($day1 < $day2)$diff_days = $day2 - $day1;
                elseif($day1 == $day2)$diff_days = 0;
                else return(FALSE);
            }
            else return(FALSE);
        }
        else return(FALSE);

        if(FALSE === $diff_days) $diff_days = $day2 + $days_in_month1 - $day1;
        if(FALSE === $diff_months) $diff_months = $month2 + 12 - $month1 - 1;

        if($diff_days > $days_in_month1) {
            $diff_days = $diff_days - $days_in_month1;
            $diff_months++;
        }

        if($diff_months > 12) {
            $diff_months = $diff_months - 12;
            $diff_years++;
        }

        return( array($diff_years, $diff_months, $diff_days));
    }



    public static function days_in_month(
        $year = FALSE,
        $month = FALSE
    ) {
        // ein 30-Tage-Monat?
        if(
            $month == 4 or
            $month == 6 or
            $month == 9 or
            $month == 11
        ) {
            return 30;
        }
        // Februar-Tage berechnen
        elseif($month == 2) {
            if( self::is_leap_year($year) ) return(29);
            else return(28);
        }
        // im Oktober 1582 wurden die Tage 5 bis 14 gestrichen
        elseif( ($year == 1582) and ($month == 10) ) {
            return 21;
        }
        // einer der anderen Monate
        return 31;
    }



    public static function is_leap_year(
        $year = FALSE
    ) {
        if(FALSE === $year) return(FALSE);
        if( $year % 4 != 0 ) return(FALSE);    // alle nicht durch 4 teilbaren Jahre (die meisten);
        // 1600 war das erste Jahrhundert, dass nach der neuen Regel ein Schaltjahr war
        if($year >= 1600 && ($year % 400 == 0) ) return(TRUE); // alle Jahrhunderte, die durch 4 teilbar sind
        if( $year % 100 == 0 ) return(FALSE);  // alle anderen Jahrhunderte
        return(TRUE);                          // alle anderen durch 4 teilbaren Jahre
    }



    public static function renderMonth( $month, $nums = 2)
    {
        if( strlen($month) < $nums)
        {
            $month = '0' . $month;
        }

        return $month;
    }





    /**
     * Ermittle Feiertage, Arbeitstage und Wochenenden von einem Datum
     *
     * @param $datum als String im Format Y-m-d oder als Timestamp
     * @param string $bundesland<br>
     *    B   = Burgenland<br>
     *    K   = Kärnten<br>
     *    NOE = Niederösterreich<br>
     *    OOE = Oberösterreich<br>
     *    S   = Salzburg<br>
     *    ST  = Steiermark<br>
     *    T   = Tirol<br>
     *    V   = Vorarlberg<br>
     *    W   = Wien
     * @return 'Arbeitstag', 'Wochenende' oder Name des Feiertags als String
     */
    public static function feiertag ($datum, $bundesland='')
    {
        $bundesland = strtoupper($bundesland);
        if (is_object($datum))
        {
            $datum = date("Y-m-d", $datum);
        }
        $datum = explode("-", $datum);

        $datum[1] = str_pad($datum[1], 2, "0", STR_PAD_LEFT);
        $datum[2] = str_pad($datum[2], 2, "0", STR_PAD_LEFT);

        if (!checkdate($datum[1], $datum[2], $datum[0])) return false;

        $datum_arr = getdate(mktime(0,0,0,$datum[1],$datum[2],$datum[0]));

        $easter_d = date("d", easter_date($datum[0]));
        $easter_m = date("m", easter_date($datum[0]));

        $status = 'Arbeitstag';
        if ($datum_arr['wday'] == 0 || $datum_arr['wday'] == 6) $status = 'Wochenende';

        if ($datum[1].$datum[2] == '0101')
        {
            return 'Neujahr';
        }
        elseif ($datum[1].$datum[2] == '0106')
        {
            return 'Heilige Drei Könige';
        }
        elseif ($datum[1].$datum[2] == '0319' && ($bundesland == 'K' || $bundesland == 'ST' || $bundesland == 'T' || $bundesland == 'V'))
        {
            return 'Josef';
        }
        elseif ($datum[1].$datum[2] == $easter_m.$easter_d)
        {
            return 'Ostersonntag';
        }
        elseif ($datum[1].$datum[2] == date("md",mktime(0,0,0,$easter_m,$easter_d+1,$datum[0])))
        {
            return 'Ostermontag';
        }
        elseif ($datum[1].$datum[2] == date("md",mktime(0,0,0,$easter_m,$easter_d+39,$datum[0])))
        {
            return 'Christi Himmelfahrt';
        }
        elseif ($datum[1].$datum[2] == date("md",mktime(0,0,0,$easter_m,$easter_d+49,$datum[0])))
        {
            return 'Pfingstsonntag';
        }
        elseif ($datum[1].$datum[2] == date("md",mktime(0,0,0,$easter_m,$easter_d+50,$datum[0])))
        {
            return 'Pfingstmontag';
        }
        elseif ($datum[1].$datum[2] == date("md",mktime(0,0,0,$easter_m,$easter_d+60,$datum[0])))
        {
            return 'Fronleichnam';
        }
        elseif ($datum[1].$datum[2] == '0501')
        {
            return 'Erster Mai';
        }
        elseif ($datum[1].$datum[2] == '0504' && $bundesland == 'OOE')
        {
            return 'Florian';
        }
        elseif ($datum[1].$datum[2] == '0815')
        {
            return 'Mariä Himmelfahrt';
        }
        elseif ($datum[1].$datum[2] == '0924' && $bundesland == 'S')
        {
            return 'Rupertitag';
        }
        elseif ($datum[1].$datum[2] == '1010' && $bundesland == 'K')
        {
            return 'Tag der Volksabstimmung';
        }
        elseif ($datum[1].$datum[2] == '1026')
        {
            return 'Nationalfeiertag';
        }
        elseif ($datum[1].$datum[2] == '1101')
        {
            return 'Allerheiligen';
        }
        elseif ($datum[1].$datum[2] == '1111' && $bundesland == 'B')
        {
            return 'Martini';
        }
        elseif ($datum[1].$datum[2] == '1115' && ($bundesland == 'NOE' || $bundesland == 'W'))
        {
            return 'Leopoldi';
        }
        elseif ($datum[1].$datum[2] == '1208')
        {
            return 'Mariä Empfängnis';
        }
//        elseif ($datum[1].$datum[2] == '1224')
//        {
//            return 'Heiliger Abend';
//        }
        elseif ($datum[1].$datum[2] == '1225')
        {
            return 'Christtag';
        }
        elseif ($datum[1].$datum[2] == '1226')
        {
            return 'Stefanitag';
        }
        elseif ($datum[1].$datum[2] == '1231' || $datum[1].$datum[2] == '1224')
        {
            return 'Geschlossen';
        }
        else
        {
            return $status;
        }
    }

}
