<?php
/*******************************************************************
 * (c) 2020 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\ContaoManager;


use Codefog\NewsCategoriesBundle\CodefogNewsCategoriesBundle;
use Contao\NewsBundle\ContaoNewsBundle;
use ContaoNewsRelatedBundle\ContaoNewsRelatedBundle;
use IIDO\BasicBundle\Helper\BasicHelper;
use IIDO\BasicBundle\IIDOBasicBundle;
//use IIDO\BasicBundle\Config\BundleConfig as IIDOBundleConfig;
use Contao\CoreBundle\ContaoCoreBundle;
use HeimrichHannot\FieldpaletteBundle\HeimrichHannotContaoFieldpaletteBundle;

use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;

//use Contao\ManagerPlugin\Routing\RoutingPluginInterface;
//use Contao\ManagerPlugin\Dependency\DependentPluginInterface;
//use Symfony\Component\Config\Loader\LoaderResolverInterface;
//use Symfony\Component\HttpKernel\KernelInterface;

//use Contao\ManagerPlugin\Config\ConfigPluginInterface;
//use Symfony\Component\Config\Loader\LoaderInterface;


/**
 * Plugin for the Contao Manager.
 *
 * @author Stephan Preßl <development@prestep.at>
 */
//class Plugin implements BundlePluginInterface, RoutingPluginInterface, ConfigPluginInterface, DependentPluginInterface
class Plugin implements BundlePluginInterface
{
    /**
     * {@inheritdoc}
     */
    public function getBundles(ParserInterface $parser)
    {
        $arrLoadAfter = [ContaoCoreBundle::class, HeimrichHannotContaoFieldpaletteBundle::class];

        if( is_dir( 'vendor/contao/news-bundle') )
        {
            $arrLoadAfter[] = ContaoNewsBundle::class;

            if( is_dir( 'vendor/codefog/contao-news_categories') )
            {
                $arrLoadAfter[] = CodefogNewsCategoriesBundle::class;
            }

            if( is_dir( 'vendor/fritzmg/contao-news-related') )
            {
                $arrLoadAfter[] = ContaoNewsRelatedBundle::class;
            }
        }

        return [
            BundleConfig::create( IIDOBasicBundle::class )
                ->setLoadAfter( $arrLoadAfter )
        ];
    }



    /**
     * {@inheritdoc}
     */
//    public function getPackageDependencies()
//    {
//        return [];
//    }



    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
//    public function getRouteCollection(LoaderResolverInterface $resolver, KernelInterface $kernel)
//    {
//        $file = '@IIDOBasicBundle/Resources/config/routing.yml';
//
//        return $resolver->resolve($file)->load($file);
//    }



//    public function registerContainerConfiguration(LoaderInterface $loader, array $config)
//    {
//        $loader->load('@IIDOMasterConnectBundle/Resources/config/config.yml');
//    }
}
