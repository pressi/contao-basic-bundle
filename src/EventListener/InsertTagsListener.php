<?php
/*******************************************************************
 * (c) 2020 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\EventListener;


use Contao\Controller;
use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\System;
use IIDO\BasicBundle\Config\IIDOConfig;
use IIDO\BasicBundle\Helper\BasicHelper;
use IIDO\BasicBundle\Helper\HeaderHelper;
use IIDO\BasicBundle\Renderer\SectionRenderer;
use Terminal42\ServiceAnnotationBundle\ServiceAnnotationInterface;


/**
 * Class Backend Template Hook
 * @package IIDO\Customize\Hook
 */
class InsertTagsListener extends DefaultListener implements ServiceAnnotationInterface
{

    private const TAG = 'iido';



    /**
     * @Hook("replaceInsertTags")
     */
    public function onReplaceInsertTags( string $tag )
    {
        $chunks = explode('::', $tag);
        $return = false;

        if (self::TAG === $chunks[0])
        {
            switch( $chunks[1] )
            {
                case "insert_article":
                    if( ($strOutput = Controller::getArticle($chunks[2], false, true)) !== false )
                    {
                        $return     = ltrim($strOutput);
                        $strClass   = $chunks[3];

                        $addClasses     = '';
                        $strAttributes  = '';

                        $topBarExists   = false;
                        $isStickyHeader = false !== strpos($chunks[2], 'sticky-header');

                        if( $strClass )
                        {
                            $renderWrapper = true;

                            if( $strClass === "header-bar" || $strClass === "header-top-bar" )
                            {
                                $isTopBar       = ($strClass === "header-top-bar");
                                $objTopHeader   = HeaderHelper::headerTopBarExists();

                                if( $objTopHeader )
                                {
                                    $topBarExists = true;

                                    if( $isTopBar )
                                    {
                                        $arrData = HeaderHelper::getTopHeaderData();
                                    }
                                    else
                                    {
                                        $arrData = HeaderHelper::getHeaderData();
                                    }

                                    $addClasses     = ' ' . $arrData['class'];
                                    $strAttributes  = ' ' . $arrData['attributes'];
                                }
                                else
                                {
                                    $renderWrapper = false;
                                }
                            }

                            $rowClass = ((IIDOConfig::get('enableLayout') && !$topBarExists) ? ' row' : '');

                            $layoutDivStart = '';
                            $layoutDivEnd   = '';

                            if( IIDOConfig::get('enableLayout') && $topBarExists )
                            {
                                $layoutDivStart = '<div class="hbi-cont row">';
                                $layoutDivEnd   = '</div>';
                            }

                            if( $renderWrapper )
                            {
                                $return = '<div class="' . $strClass . $addClasses . '"' . $strAttributes . '><div class="' . $strClass . '-inside' . $rowClass . '">' . $layoutDivStart . $return . $layoutDivEnd . '</div></div>';
                            }
                        }

                        if( $isStickyHeader )
                        {
                            $return .= SectionRenderer::getOffsetNavigationToggler();
//                            $return = preg_replace('/<\/div>([\s\n]{0,})<\/div>/', SectionRenderer::getOffsetNavigationToggler() . '</div></div>', $return);
                        }
                    }
                    else
                    {
                        $return = '';
                    }
                    break;


                case "date":
                    if( isset($chunks[2]) )
                    {
                        $dateFormat = $chunks[2];

                        if( preg_match('/\+/', $dateFormat) )
                        {
                            $isSourceTime   = isset($chunks[3]);
                            $sourceTime     = $chunks[3];

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
                            $return = (( isset($chunks[3]) ) ? date($dateFormat, $chunks[3]) : date($dateFormat) );
                        }
                    }
                    else
                    {
                        $return = date(Config::get('dateFormat'));
                    }
                    break;



                case "get":
                    $return = Input::get( $chunks[2] );

                    if( ($chunks[2] === 'adults' || $chunks[2] === 'children') && !$return )
                    {
                        $return = (($chunks[2] === 'adults') ? 2 : 0);
                    }
                    elseif( $chunks[2] === 'arrival' && !$return )
                    {
                        $return = date('d.m.Y', strtotime('+1 day'));
                    }
                    elseif( $chunks[2] === 'depature' && !$return )
                    {
                        $return = date('d.m.Y', strtotime('+4 days'));
                    }

                    break;
            }
        }

        if( $chunks[0] === 'icon' )
        {
            $rootDir    = BasicHelper::getRootDir( true );
            $iconPath   = 'files/' . BasicHelper::getFilesCustomerDir() . '/Uploads/Icons/';
            $iconName   = ucfirst($chunks[1]) . '.svg';

            if( file_exists( $rootDir . $iconPath . $iconName ) )
            {
                $return = file_get_contents( $rootDir . $iconPath . $iconName );
            }
        }

        return $return;
    }

}
