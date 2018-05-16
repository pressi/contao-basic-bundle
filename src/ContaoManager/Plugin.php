<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\ContaoManager;


use IIDO\BasicBundle\IIDOBasicBundle;
use Contao\CoreBundle\ContaoCoreBundle;
use Contao\NewsBundle\ContaoNewsBundle;
use Contao\CalendarBundle\ContaoCalendarBundle;

use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;

use Contao\ManagerPlugin\Routing\RoutingPluginInterface;
use Symfony\Component\Config\Loader\LoaderResolverInterface;
use Symfony\Component\HttpKernel\KernelInterface;


/**
 * Plugin for the Contao Manager.
 *
 * @author Stephan Preßl <development@prestep.at>
 */
class Plugin implements BundlePluginInterface, RoutingPluginInterface
{
    /**
     * {@inheritdoc}
     */
    public function getBundles(ParserInterface $parser)
    {
        $arrLoadAfter   = [ContaoCoreBundle::class, ContaoNewsBundle::class, ContaoCalendarBundle::class];
        $vendorPath     = preg_replace('/2do\/contao-basic-bundle\/src\/ContaoManager/', '', __DIR__);

        if( is_dir( $vendorPath . 'delahaye/dlh_googlemaps') )
        {
            $arrLoadAfter[] = 'dlh_googlemaps';
            $arrLoadAfter[] = 'delahaye/dlh_googlemaps';
        }

        if( is_dir( $vendorPath . 'codefog/contao-news_categories') )
        {
            $arrLoadAfter[] = \Codefog\NewsCategoriesBundle\CodefogNewsCategoriesBundle::class;
        }

        return [
            BundleConfig::create(IIDOBasicBundle::class)
                ->setLoadAfter($arrLoadAfter)
        ];
    }


    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    public function getRouteCollection(LoaderResolverInterface $resolver, KernelInterface $kernel)
    {
        return $resolver
            ->resolve(__DIR__.'/../Resources/config/routing.yml')
            ->load(__DIR__.'/../Resources/config/routing.yml');
    }
}
