<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\Cron;

use IIDO\BasicBundle\Config\BundleConfig;
use IIDO\BasicBundle\Helper\BasicHelper;


class WeatherDataCron
{

    protected $weatherUrl   = 'http://api.wunderground.com/api/';
    protected $weatherCity  = 'zmw:00000.24.11357';
    protected $weatherKey   = '9d1d71aa8a832b9f';



    public function generateCustomizeWeatherData()
    {
        $arrData            = BasicHelper::getWeatherData();
        $tableFieldPrefix   = BundleConfig::getTablePrefix();

        $lang               = (\Config::get($tableFieldPrefix . 'weatherLanguage')?:'DL');
        $weatherUrl         = (\Config::get($tableFieldPrefix . 'weatherUrl')?:$this->weatherUrl);
        $weatherKey         = (\Config::get($tableFieldPrefix . 'weatherKey')?:$this->weatherKey);
        $weatherCity        = (\Config::get($tableFieldPrefix . 'weatherCity')?:$this->weatherCity);

        $arrRepData         = array($weatherUrl, $weatherKey, $lang, $weatherCity);
        $weatherDataUrl     = str_replace(array('##API_URL##', '##API_KEY##', '##API_LANG##', '##API_QUERY##'), $arrRepData, $arrData['fullApiUrl']);

        $weatherData        = file_get_contents($weatherDataUrl);

        \File::putContent(str_replace('##ROOT##/', '', $arrData['tmpFile']), $weatherData);
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