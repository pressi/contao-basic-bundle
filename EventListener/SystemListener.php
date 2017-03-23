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

namespace IIDO\BasicBundle\EventListener;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\CoreBundle\Framework\ScopeAwareTrait;
use Contao\System;
use Contao\Config;

use Contao\Environment;
use Contao\PageModel;
use Contao\LayoutModel;
use Contao\Model\Collection;
use Contao\NewsFeedModel;
use Contao\StringUtil;
use Contao\Template;
use Contao\Frontend;
//use IIDO\WebsiteBundle\Table\Page;


/**
 * IIDO System Listener
 *
 * @author Stephan Preßl <https://github.com/pressi>
 */
class SystemListener
{
    use ScopeAwareTrait;


    /**
     * @var ContaoFrameworkInterface
     */
    private $framework;


    protected $bundlePathPublic = 'web/bundles/iidobasic';
    protected $bundlePath       = 'vendor/2do/contao-basic-bundle';

    protected $resourcePath     = '/app/Resources';



    /**
     * Constructor.
     *
     * @param ContaoFrameworkInterface $framework
     */
    public function __construct(ContaoFrameworkInterface $framework)
    {
        $this->framework = $framework;
    }



    /**
     * initialize the customize system
     */
    public function initializeCustomizeSystem()
    {
        $route = System::getContainer()->get('request_stack')->getCurrentRequest()->get('_route');

//        if( $this->isBackendScope() && 'contao_backend' === $route && 'contao_install' != $route )
        if( 'contao_backend' === $route && 'contao_install' != $route )
        {
            $this->initSystem();
            $this->initBackend();
        }
//        echo "<pre>";
//        print_r("NO");
//        exit;
    }



    protected function initSystem()
    {
        if( !Config::get("iido_initSystem") )
        {
            $rootDir            = dirname(System::getContainer()->getParameter('kernel.root_dir'));
            $connectionFile     = $rootDir . '/' . $this->bundlePath . '/Resources/config/master-connection.json';
            $settingsFile       = $rootDir . '/' . $this->bundlePath . '/Resources/config/default-settings.json';

            $cmsConfig      = json_decode( file_get_contents( $settingsFile ) );
            $arrFolders     = array();

            foreach( $cmsConfig->files as $strFolder => $arrSubfolders)
            {
                $strFolderPath  = "files/" . $strFolder;

                if( is_dir($rootDir . '/' . $strFolderPath) )
                {
                    $objFolder      = \FilesModel::findByPath( $strFolderPath );
                }
                else
                {
                    $objFolder      = new \Folder( $strFolderPath );
                }

                $arrFolders[] = $objFolder->path;

                if( is_array($arrSubfolders) && count($arrSubfolders) && $objFolder && is_dir( $rootDir . '/' . $objFolder->path) )
                {
                    foreach($arrSubfolders as $strSubFolder)
                    {
                        if( is_dir($rootDir . '/' . $objFolder->path . $strSubFolder) )
                        {
                            $objSubFolder = \FilesModel::findByPath( $objFolder->path . '/' . $strSubFolder );
                        }
                        else
                        {
                            $objSubFolder = new \Folder( $objFolder->path . '/' . $strSubFolder );
                        }

                        $arrFolders[] = $objSubFolder->path;
                    }
                }
            }

            if( count($arrFolders) )
            {
                \Dbafs::updateFolderHashes($arrFolders);
            }

            foreach( $cmsConfig->templates as $strTemplateFolder)
            {
                $strFolderPath  = "templates/" . $strTemplateFolder;

                if( !is_dir($rootDir . '/' . $strFolderPath) )
                {
                    mkdir( $rootDir . '/' . $strFolderPath );
                }
            }


        }

//        echo "<pre>";
//        print_r( "INIT" );
//        exit;
    }



    protected function initBackend()
    {
        $rootDir            = dirname(System::getContainer()->getParameter('kernel.root_dir'));

        $backendThemePath   = $rootDir . '/system/themes/' . \Backend::getTheme() . '/images/';
        $backendImagePath   = $rootDir . $this->bundlePathPublic . '/images/backend/';

        if( file_exists($rootDir . $this->bundlePathPublic . '/css/backend/backend.css') )
        {
            $GLOBALS['TL_CSS'][] = preg_replace('/^\/web/', '', $this->bundlePathPublic) . '/css/backend/backend.css||static';
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

//        if( !file_exists($backendThemePath . 'header_center.gif') )
//        {
//            copy($backendImagePath . 'header_center.gif',   $backendThemePath . 'header_center.gif');
//            copy($backendImagePath . 'header_left.gif',     $backendThemePath . 'header_left.gif');
//            copy($backendImagePath . 'header_right.gif',    $backendThemePath . 'header_right.gif');
//        }

//        if( !file_exists($rootDir . '/templates/be_unavailable.html5') )
//        {
//            copy($rootDir . '/system/modules/ziido_customize/templates/backend/be_unavailable.html5', $rootDir . '/templates/be_unavailable.html5');
//        }
/*
        if( !file_exists($rootDir . $this->resourcePath . '/ContaoCoreBundle/views/Error/service_unavailable.html.twig') )
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

            copy($rootDir . '/vendor/iido/contao-website-bundle/Resources/views/Error/service_unavailable.html.twig', $rootDir . $this->resourcePath . '/ContaoCoreBundle/views/Error/service_unavailable.html.twig');
        }
*/
    }
    
}
