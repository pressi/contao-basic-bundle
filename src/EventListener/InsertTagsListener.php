<?php
/*******************************************************************
 * (c) 2020 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\EventListener;


use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\System;
use IIDO\BasicBundle\Helper\HeaderHelper;
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
        $config = System::getContainer()->get('iido.basic.config');

        if (self::TAG === $chunks[0])
        {
            switch( $chunks[1] )
            {
                case "insert_article":
                    if( ($strOutput = \Controller::getArticle($chunks[2], false, true)) !== false )
                    {
                        $return     = ltrim($strOutput);
                        $strClass   = $chunks[3];

                        $addClasses     = '';
                        $strAttributes  = '';

                        $topBarExists   = false;

                        if( $strClass )
                        {
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
                            }

                            $rowClass = (($config->get('enableLayout') && !$topBarExists) ? ' row' : '');

                            $layoutDivStart = '';
                            $layoutDivEnd   = '';

                            if( $config->get('enableLayout') && $topBarExists )
                            {
                                $layoutDivStart = '<div class="hbi-cont row">';
                                $layoutDivEnd   = '</div>';
                            }

                            $return = '<div class="' . $strClass . $addClasses . '"' . $strAttributes . '><div class="' . $strClass . '-inside' . $rowClass . '">' . $layoutDivStart . $return . $layoutDivEnd . '</div></div>';
                        }
                    }
                    else
                    {
                        $return = '';
                    }
                    break;
            }
        }

        if( $chunks[0] === 'icon' )
        {
            return '';
        }

        return $return;
    }

}
