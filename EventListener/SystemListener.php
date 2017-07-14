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
use IIDO\BasicBundle\Connection\MasterConnection;
use IIDO\BasicBundle\Config\BundleConfig;


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


    protected $bundlePathPublic;
    protected $bundlePath;

    protected $resourcePath     = '/app/Resources';



    /**
     * Constructor.
     *
     * @param ContaoFrameworkInterface $framework
     */
    public function __construct(ContaoFrameworkInterface $framework)
    {
        $this->framework = $framework;

        $this->bundlePathPublic = BundleConfig::getBundlePath(true);
        $this->bundlePath       = BundleConfig::getBundlePath();

    }



    /**
     * initialize the customize system
     */
    public function initializeCustomizeSystem()
    {

//        $route = "FE";
        $container  = System::getContainer();

        if( $container )
        {
            $request = $container->get('request_stack')->getCurrentRequest();

            if( $request )
            {
                $route = $request->get('_route');

//                if( $this->isBackendScope() && 'contao_backend' === $route && 'contao_install' != $route )
                if( 'contao_backend' === $route && 'contao_install' != $route )
                {
//                    $this->initSystem();
                    $this->initBackend();
                }
            }
        }
    }



    protected function initSystem()
    {
        if( !Config::get("iido_initSystem") )
        {
            if( !\Input::get("do") == "iidoConfigContao" )
            {
                \Controller::redirect( \Controller::addToUrl("do=iidoConfigContao") );
            }
        }
    }



    protected function initBackend()
    {
        $rootDir            = dirname(System::getContainer()->getParameter('kernel.root_dir'));

        $backendThemePath   = $rootDir . '/system/themes/' . \Backend::getTheme() . '/images/';
        $backendImagePath   = $rootDir . $this->bundlePathPublic . '/images/backend/';

        if( file_exists($rootDir . '/' . $this->bundlePathPublic . '/css/backend/backend.css') )
        {
            $GLOBALS['TL_CSS'][] = preg_replace('/^web\//', '', $this->bundlePathPublic) . '/css/backend/backend.css||static';
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

        $arrErrorFiles = array('service_unavailable');

        $this->generateErrorFiles( $arrErrorFiles );
    }


    protected function generateErrorFiles( $arrFiles )
    {
        $rootDir            = dirname(System::getContainer()->getParameter('kernel.root_dir'));

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
                    if( file_exists($rootDir . '/vendor/2do/contao-basic-bundle/Resources/views/Error/' . $strFile . '.html.twig') )
                    {
                        copy($rootDir . '/vendor/2do/contao-basic-bundle/Resources/views/Error/' . $strFile . '.html.twig', $rootDir . $this->resourcePath . '/ContaoCoreBundle/views/Error/' . $strFile . '.html.twig');
                    }
                }
            }
        }
    }
    
}
