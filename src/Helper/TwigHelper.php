<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\Helper;


use IIDO\BasicBundle\Config\BundleConfig;
use Symfony\Component\HttpFoundation\Response;


/**
 * Description
 *
 */
class TwigHelper
{

    /**
     * Renders a template.
     *
     * @param string $name
     * @param array  $context
     *
     * @return Response
     */
    public static function render( $name, $context = [] )
    {
        return new Response(
            \System::getContainer()->get('twig')->render(
                '@IIDOBasic/' . $name,
                self::addDefaultsToContext($context)
            )
        );
    }



    /**
     * Adds the default values to the context.
     *
     * @param array $context
     *
     * @return array
     */
    private static function addDefaultsToContext(array $context)
    {
        $container = \System::getContainer();
//        $context = array_merge($this->context, $context);

        if (!isset($context['request_token']))
        {
            $context['request_token'] = self::getRequestToken( $container );
        }

        if (!isset($context['language']))
        {
            $context['language'] = $container->get('translator')->getLocale();
        }

        if (!isset($context['ua']))
        {
            $context['ua'] = \Environment::get('agent')->class;
        }

        if (!isset($context['path']))
        {
            $context['path'] = $container->get('request_stack')->getCurrentRequest()->getBasePath();
        }

        return $context;
    }



    /**
     * Returns the request token.
     *
     * @return string
     */
    private static function getRequestToken( $container )
    {
        $version = BundleConfig::getContaoVersion();

        if( version_compare($version, '4.5.0', '>=') )
        {
            $tokenName = self::getContainerParameter('contao.csrf_token_name', $container);

            if (null === $tokenName) {
                return '';
            }

            return $container->get('contao.csrf.token_manager')->getToken($tokenName)->getValue();
        }
        else
        {
            if (!$container->hasParameter('contao.csrf_token_name'))
            {
                return '';
            }

            return $container
                ->get('security.csrf.token_manager')
                ->getToken(self::getContainerParameter('contao.csrf_token_name', $container))
                ->getValue();
        }
    }



    /**
     * Returns a parameter from the container.
     *
     * @param string $name
     *
     * @return mixed
     */
    private static function getContainerParameter($name, $container)
    {
        if ($container->hasParameter($name))
        {
            return $container->getParameter($name);
        }

        return null;
    }
}
