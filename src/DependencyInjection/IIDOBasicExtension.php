<?php

declare(strict_types=1);

/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\DependencyInjection;


use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Configures the Contao IIDO Basic Bundle.
 *
 * @author Stephan Preßl <https://github.com/pressi>
 */
class IIDOBasicExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    public function load(array $mergedConfig, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../Resources/config')
        );

        $loader->load('listener.yml');
        $loader->load('services.yml');
    }


    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    public function prepend(ContainerBuilder $container)
    {
        $rootDir = $container->getParameter('kernel.root_dir');

        if (file_exists($rootDir.'/config/parameters.yml') || !file_exists($rootDir.'/config/parameters.yml.dist')) {
            return;
        }

        $loader = new YamlFileLoader(
            $container,
            new FileLocator($rootDir.'/config')
        );

        $loader->load('parameters.yml.dist');
    }
}
