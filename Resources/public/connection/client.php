<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

use Contao\ManagerBundle\ContaoManager\Plugin as ManagerBundlePlugin;
use Contao\ManagerBundle\HttpKernel\ContaoCache;
use Contao\ManagerBundle\HttpKernel\ContaoKernel;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Symfony\Component\HttpFoundation\Request;

use \Contao\Config;


/** @var Composer\Autoload\ClassLoader */
$loader = require __DIR__.'/../../../../../autoload.php';

AnnotationRegistry::registerLoader([$loader, 'loadClass']);
ManagerBundlePlugin::autoloadModules(__DIR__.'/../../../../../../system/modules');

$kernel = new ContaoKernel('prod', false);
$kernel->setRootDir(dirname(__DIR__).'/../../../../../../../app');

// Enable the Symfony reverse proxy
$kernel = new ContaoCache($kernel);
Request::enableHttpMethodParameterOverride();

// Handle the request
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
//$response->send();
//$kernel->terminate($request, $response);

class Connection {

    var $password = 'iido345client21';

    public function run()
    {
        // TODO: make a client class!!

        if( \Input::get("pwd") == $this->password )
        {
            $packages = System::getContainer()->getParameter('kernel.packages');

            $arrConfig = array
            (
                'config'    => array
                (
                    'title'     => Config::get("websiteTitle")
                ),

                'version'   => $packages['contao/core-bundle'],
                'bundles'   => array_keys(System::getContainer()->getParameter('kernel.bundles') ),
                'packages'  => $packages
            );


            header('Content-Type: application/json');
            echo json_encode($arrConfig);
            exit;
        }

        header('Content-Type: application/json');
        echo json_encode(array('ERROR'=>'No correct Password'));
        exit;
    }

}

$connection = new Connection();
$connection->run();