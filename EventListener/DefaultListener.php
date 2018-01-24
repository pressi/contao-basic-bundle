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
use IIDO\BasicBundle\Config\BundleConfig;


/**
 * IIDO Default Listener
 *
 * @package IIDO\BasicBundle
 * @author Stephan Preßl <development@prestep.at>
 */
class DefaultListener
{

    /**
     * Framework Object
     *
     * @var ContaoFrameworkInterface
     */
    protected $framework;


    /**
     * Public bundle path (web)
     *
     * @var string
     */
    protected $bundlePathPublic;


    /**
     * Bundle path
     *
     * @var string
     */
    protected $bundlePath;


    /**
     * Global resources path
     *
     * @var string
     */
    protected $resourcePath     = '/app/Resources';


    /**
     * Root directory
     *
     * @var string
     */
    protected $rootDir;


    /**
     * Bundles array
     *
     * @var array
     */
    protected $bundles = array();



    /**
     * Constructor.
     *
     * @param ContaoFrameworkInterface $framework
     */
    public function __construct(ContaoFrameworkInterface $framework)
    {
        $system = \System::getContainer();

        $this->framework        = $framework;

        $this->bundlePathPublic = BundleConfig::getBundlePath(true);
        $this->bundlePath       = BundleConfig::getBundlePath();

        $this->rootDir          = dirname( $system->getParameter('kernel.root_dir') );
        $this->bundles          = array_keys( $system->getParameter('kernel.bundles') );
    }

}
