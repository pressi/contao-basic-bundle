<?php
/*******************************************************************
 *
 * (c) 2017 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 *
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *
 *******************************************************************/

namespace IIDO\BasicBundle\Cron;

class WeatherDataCron
{

    protected $weatherUrl   = 'http://api.wunderground.com/api/';
    protected $weatherCity  = 'zmw:00000.24.11357';
    protected $weatherKey   = '9d1d71aa8a832b9f';


    // TODO: get data from data-weather.json config file // make city and key changable
    public function generateCustomizeWeatherData()
    {
        $weatherData    = file_get_contents($this->weatherUrl . $this->weatherKey . '/conditions/lang:DL/q/' . $this->weatherCity . '.json');
//        $objWeather     = json_decode($weatherData);


        \File::putContent('system/tmp/weather-data.txt', $weatherData);
    }


    /*
     * Wetter Icons (Tag => Nacht)
     *
     * chanceflurries       => nt_chanceflurries
     * chancerain           => nt_chancerain
     * chancesleet          => nt_chancesleet
     * chancesnow           => nt_chancesnow
     * chancetstorms        => nt_chancetstorms
     * clear                => nt_clear
     * cloudy               => nt_cloudy
     * flurries             => nt_flurries
     * fog                  => nt_fog
     * hazy                 => nt_hazy
     * mostlycloudy         => nt_mostlycloudy
     * mostlysunny          => nt_mostlysunny
     * partlysunny          => nt_partlysunny
     * sleet                => nt_sleet
     * rain                 => nt_rain
     * snow                 => nt_snow
     * sunny                => nt_sunny
     * tstorms              => nt_tstorms
     * partlycloudy         => nt_partlycloudy
     */

}