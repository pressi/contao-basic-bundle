<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\EventListener;


use Contao\System;
use Contao\Config;
use IIDO\BasicBundle\Config\BundleConfig;
use IIDO\BasicBundle\Helper\BasicHelper;


/**
 * IIDO System Listener
 *
 * @package IIDO\BasicBundle
 * @author Stephan Preßl <development@prestep.at>
 */
class SystemListener extends DefaultListener
{

    /**
     * initialize the customize system
     */
    public function initializeCustomizeSystem()
    {
        $container = System::getContainer();

        if( $container )
        {
            $request = $container->get('request_stack')->getCurrentRequest();

            if( $request )
            {
                $route = $request->get('_route');

//                if( $this->isBackendScope() && 'contao_backend' === $route && 'contao_install' != $route )
                if( 'contao_backend' === $route && 'contao_install' != $route )
                {
                    $this->initBackend();
//                    $this->runImport();
                }
            }
        }
    }



    protected function initBackend()
    {
        $rootDir            = dirname(System::getContainer()->getParameter('kernel.root_dir'));
        $imageFormat        = 'svg';

//        $backendThemePath   = $rootDir . '/system/themes/' . \Backend::getTheme() . '/images/';
        $backendThemePath   = $rootDir . '/vendor/contao/core-bundle/src/Resources/contao/themes/' . \Backend::getTheme() . '/icons/';
        $backendImagePath   = $rootDir . '/' . $this->bundlePathPublic . '/images/backend/';

        if( file_exists($rootDir . '/' . $this->bundlePathPublic . '/css/backend/backend.css') )
        {
            $GLOBALS['TL_CSS'][] = preg_replace('/^web\//', '', $this->bundlePathPublic) . '/css/backend/backend.css||static';
        }

        if( file_exists($rootDir . '/' . $this->bundlePathPublic . '/css/backend/dropdown.css') )
        {
            $GLOBALS['TL_CSS'][] = preg_replace('/^web\//', '', $this->bundlePathPublic) . '/css/backend/dropdown.css||static';
        }


        if( file_exists($rootDir . '/' . $this->bundlePathPublic . '/javascript/backend/IIDO.Backend.js') )
        {
            $GLOBALS['TL_JAVASCRIPT'][] = preg_replace('/^web\//', '', $this->bundlePathPublic) . '/javascript/backend/IIDO.Backend.js|static';
        }

//        if( \Config::get("iidoCustomize_SettingsProjectType") == "i" && $GLOBALS['IIDO']['isActiveBackendTheme'] )
//        {
//            $GLOBALS['TL_CSS'][]        = 'system/modules/ziido_customize/assets/css/frontend/font-awesome.css';
//-            $GLOBALS['TL_JAVASCRIPT'][] = 'bundles/iidowebsite/javascript/backend/IIDO.Backend.Functions.js|static';

//            $beUser = \BackendUser::getInstance();

//            if( !$beUser->authenticate() )
//            {
//                if( file_exists($rootDir . '/system/modules/ziido_customize/assets/css/backend-login.css') )
//                {
//                    $GLOBALS['TL_CSS'][] = 'system/modules/ziido_customize/assets/css/backend-login.css';
//                }
//            }
//            else
//            {
//                if( file_exists($rootDir . '/system/modules/ziido_customize/assets/css/backend-theme.css') )
//                {
//                -    $GLOBALS['TL_CSS'][] = 'system/modules/ziido_customize/assets/css/backend-theme.css';
//                }
//            }
//        }

//        $GLOBALS['TL_JAVASCRIPT'][] = 'system/modules/ziido_customize/assets/javascript/backend/IIDO.Backend.RowEntryWizard.js|static';

        if( !file_exists($backendThemePath . 'header_center.' . $imageFormat) && file_exists($backendImagePath . 'header_center.' . $imageFormat) )
        {
            copy($backendImagePath . 'header_center.' . $imageFormat,   $backendThemePath . 'header_center.' . $imageFormat);
            copy($backendImagePath . 'header_left.' . $imageFormat,     $backendThemePath . 'header_left.' . $imageFormat);
            copy($backendImagePath . 'header_right.' . $imageFormat,    $backendThemePath . 'header_right.' . $imageFormat);
        }

//        if( !file_exists($rootDir . '/templates/be_unavailable.html5') )
//        {
//            copy($rootDir . '/system/modules/ziido_customize/templates/backend/be_unavailable.html5', $rootDir . '/templates/be_unavailable.html5');
//        }

        $arrErrorFiles = array('service_unavailable');

        $this->generateErrorFiles( $arrErrorFiles );
    }



    protected function generateErrorFiles( $arrFiles )
    {
        $rootDir    = BasicHelper::getRootDir();
        $bundlePath = BundleConfig::getBundlePath();

        if( count($arrFiles) )
        {
            if( !is_dir($rootDir . $this->resourcePath) )
            {
                mkdir($rootDir . $this->resourcePath);
                mkdir($rootDir . $this->resourcePath . '/ContaoCoreBundle');
                mkdir($rootDir . $this->resourcePath . '/ContaoCoreBundle/views');
                mkdir($rootDir . $this->resourcePath . '/ContaoCoreBundle/views/Error');
            }
            else
            {
                if( !is_dir($rootDir . $this->resourcePath . '/ContaoCoreBundle') )
                {
                    mkdir($rootDir . $this->resourcePath . '/ContaoCoreBundle');
                    mkdir($rootDir . $this->resourcePath . '/ContaoCoreBundle/views');
                    mkdir($rootDir . $this->resourcePath . '/ContaoCoreBundle/views/Error');
                }
                else
                {
                    if( !is_dir($rootDir . $this->resourcePath . '/ContaoCoreBundle/views') )
                    {
                        mkdir($rootDir . $this->resourcePath . '/ContaoCoreBundle/views');
                        mkdir($rootDir . $this->resourcePath . '/ContaoCoreBundle/views/Error');
                    }
                    else
                    {
                        if( !is_dir($rootDir . $this->resourcePath . '/ContaoCoreBundle/views/Error') )
                        {
                            mkdir($rootDir . $this->resourcePath . '/ContaoCoreBundle/views/Error');
                        }
                    }
                }
            }

            foreach( $arrFiles as $strFile )
            {
                if( !file_exists($rootDir . $this->resourcePath . '/ContaoCoreBundle/views/Error/' . $strFile . '.html.twig') )
                {
                    if( file_exists($rootDir . '/' . $bundlePath . '/Resources/views/Error/' . $strFile . '.html.twig') )
                    {
                        copy($rootDir . '/' . $bundlePath . '/Resources/views/Error/' . $strFile . '.html.twig', $rootDir . $this->resourcePath . '/ContaoCoreBundle/views/Error/' . $strFile . '.html.twig');
                    }
                }
            }
        }
    }
    
    
    
    protected function runImport()
    {
        $oldDatabaseUsername    = '';
        $oldDatabasePassword    = '';
        $oldDatabaseName        = '';

        $connection = new \mysqli('localhost', $oldDatabaseUsername, $oldDatabasePassword, $oldDatabaseName);
        $result     = $connection->query("SELECT * FROM tl_content");

        $arrArticles    = array();
        $arrPages       = array();
$count=0;
        while( $row = $result->fetch_assoc() )
        {
            if( $row['ptable'] !== "tl_article" )
            {
                continue;
            }

            $arrArticle = $arrArticles[ $row['pid'] ];

            if( !isset($arrArticles[ $row['pid'] ]) )
            {
                $article    = $connection->query("SELECT * FROM tl_article WHERE id=" . $row['pid']);
                $arrArticle = $article->fetch_assoc();

                $arrArticles[ $row['pid'] ] = $arrArticle;
            }

            $arrPage = $arrPages[ $arrArticle['pid'] ];

            if( !isset($arrPages[ $arrArticle['pid'] ]) )
            {
                $page       = $connection->query("SELECT * FROM tl_page WHERE id=" . $arrArticle['pid']);
                $arrPage    = $page->fetch_assoc();

                $arrPages[ $arrArticle['pid'] ] = $arrPage;
            }

            if( $this->checkIfFirstLevelPageIsParent($connection, $arrPage, 3, $arrPages) )
            {
//                echo "Page: " . $arrPage['title'] . ' (' . $arrPage['id'] . ')<br>';
//                echo "Article: " . $arrArticle['title'] . ' (' . $arrArticle['id'] . ')<br>';
//                echo "Element: " . $row['type'] . ' (' . $row['id'] . ')<br>';

                if( $row['addImage'] === "1" )
                {
                    $image      = $connection->query("SELECT * FROM tl_files WHERE uuid=UNHEX('" . bin2hex($row['singleSRC']) . "')");
                    $objImage   = $image->fetch_assoc();

                    $singleSRC      = str_replace('files/Uploads', 'files/instec/Uploads', $objImage['path']);
                    $objNewImage    = \FilesModel::findByPath( $singleSRC );

                    if( $objNewImage )
                    {
                        $row['singleSRC'] = $objNewImage->uuid;
                    }
                }
//                echo "<pre>"; print_r( $row ); echo "</pre>";

//                echo "<br><br>";

                $objPage = \PageModel::findByTitle( utf8_encode($arrPage['title']) );

                if( $objPage )
                {
                    $objArticle = \ArticleModel::findByPid( $objPage->id );

                    if( $objArticle )
                    {
                        $objElement = new \ContentModel();
                        $objElement->pid = $objArticle->id;
                        $objElement->tstamp = time();

                        foreach( $row as $field => $value)
                        {
                            if( in_array($field, array('id', 'pid', 'tstamp')) )
                            {
                                continue;
                            }

                            if( !preg_match('/SRC/', $field) )
                            {
                                $value = utf8_encode( $value );
                            }

                            $objElement->$field = $value;
                        }

                        $objElement->save();
//                        echo "<pre>";
//                        print_r( $objPage->title );
//                        echo " == ";
//                        print_r( $arrPage['title'] );
//                        echo "<br><br>";
//
//                        $count++;
                    }
                }

            }
        }
//        echo "<br><br>Counter: ";
//        print_r( $count );
//        exit;
    }


    protected function checkIfFirstLevelPageIsParent( $connection, $arrPage, $rootPageId, &$arrPages )
    {
        if( $arrPage['pid'] == $rootPageId )
        {
            return true;
        }

        $arrParentPage = $arrPages[ $arrPage['pid'] ];

        if( !isset($arrPages[ $arrPage['pid'] ]) && $arrPage['pid'] > 0 )
        {
            $parentPage     = $connection->query("SELECT * FROM tl_page WHERE id=" . $arrPage['pid']);
            $arrParentPage  = $parentPage->fetch_assoc();
        }

        if( $arrParentPage['pid'] == $rootPageId )
        {
            return true;
        }

        $arrPParentPage = $arrPages[ $arrParentPage['pid'] ];

        if( !isset($arrPages[ $arrParentPage['pid'] ]) && $arrParentPage['pid'] > 0 )
        {
            $parentPPage     = $connection->query("SELECT * FROM tl_page WHERE id=" . $arrParentPage['pid']);
            $arrPParentPage  = $parentPPage->fetch_assoc();
        }

        if( $arrPParentPage['pid'] == $rootPageId )
        {
            return true;
        }

        $arrPPParentPage = $arrPages[ $arrPParentPage['pid'] ];

        if( !isset($arrPages[ $arrPParentPage['pid'] ]) && $arrPParentPage['pid'] > 0)
        {
            $parentPPPage     = $connection->query("SELECT * FROM tl_page WHERE id=" . $arrPParentPage['pid']);
            $arrPPParentPage  = $parentPPPage->fetch_assoc();
        }

        if( $arrPPParentPage['pid'] == $rootPageId )
        {
            return true;
        }

        return false;
    }
    
}
