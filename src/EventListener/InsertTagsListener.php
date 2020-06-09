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
        $config = System::getContainer()->get('iido.basic.config');

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
