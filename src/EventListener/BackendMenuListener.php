<?php
/*******************************************************************
 * (c) 2020 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\EventListener;


use Contao\BackendUser;
use Contao\Controller;
use Contao\CoreBundle\Event\MenuEvent;
use Contao\Database;
use Contao\StringUtil;
use Contao\System;
use IIDO\BasicBundle\Config\IIDOConfig;
use Knp\Menu\Util\MenuManipulator;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;


class BackendMenuListener
{
    protected $router;
    protected $requestStack;


    public function __construct(RouterInterface $router, RequestStack $requestStack)
    {
        $this->router       = $router;
        $this->requestStack = $requestStack;
    }



    public function onBuild(MenuEvent $event): void
    {
        $objUser = BackendUser::getInstance();

        $factory    = $event->getFactory();
        $tree       = $event->getTree();

        if ('mainMenu' !== $tree->getName() )
        {
            return;
        }

        if( $objUser->isAdmin )
        {
            $systemNode = $tree->getChild('system');
            $node = $systemNode->getChild('config-settings');

            $url = $node->getUri();
            $url = $url . '&act=edit&id=1&rt=' . REQUEST_TOKEN;

            $node->setUri( $url );

            $systemNode->removeChild('config-settings');
//            $systemNode->addChild( $node );

//            $node = $factory->createItem('config-settings')
//                                ->setLabel('Website Einstellungen')
//                                ->setLinkAttribute('title', 'Website Einstellungen')
//                                ->setLinkAttribute('class', 'config-settings');

//            $node->setCurrent($this->requestStack->getCurrentRequest()->get('_backend_module') === 'config-settings');

//            if( $this->requestStack->getCurrentRequest()->get('_backend_module') === 'config-settings' )
//            {
//                $GLOBALS['TL_CSS'][] = 'bundles/iidomasterconnect/css/backend.css';
//            }

//            $contentNode->addChild($node);

//            $manipulator = new MenuManipulator();
//            $manipulator->moveToPosition($systemNode['config-settings'], 3);
        }

//            $objConfigTable = Database::getInstance()->prepare('SELECT * FROM tl_iido_config WHERE id=?')->execute(1);
        $contentNode = $tree->getChild('content');

        if( IIDOConfig::get('navLabels') && $contentNode )
        {
            $navLabels = StringUtil::deserialize(IIDOConfig::get('navLabels'), true);

            if( count($navLabels) )
            {
//                    Controller::loadLanguageFile('default');

                foreach( $navLabels as $arrLabel )
                {
                    $key = $arrLabel['value'];

                    $node = $contentNode->getChild( $key );

                    if($node && $node->getLabel() !== $arrLabel['label'] )
                    {
//                            $node->setName( $arrLabel['label'] );
                        $node->setLabel( $arrLabel['label'] );
                    }

//                        if( $GLOBALS['TL_LANG']['MOD'][ $key ][0] !== $arrLabel['label'] )
//                        {
//                            $GLOBALS['TL_LANG']['MOD'][ $key ][0] = $arrLabel['label'];
//                        }
                }
            }
        }
    }
}