<?php
/*******************************************************************
 * (c) 2020 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\Controller\Elements;


use Contao\ContentModel;
use Contao\Controller;
use Contao\CoreBundle\Controller\ContentElement\AbstractContentElementController;
use Contao\CoreBundle\ServiceAnnotation\ContentElement;
use Contao\System;
use Contao\Template;
use IIDO\BasicBundle\Helper\BasicHelper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


/**
 * Class News Related
 *
 * @package IIDO\BasicBundle\Controller\Elements
 *
 * (at)ContentElement("iido_news_related",
 *     category="news",
 *     template="ce_iido_news_related",
 *     renderer="forward"
 * )
 */
class NewsRelatedElement extends AbstractContentElementController
{
    protected function getResponse(Template $template, ContentModel $model, Request $request): ?Response
    {
        if( BasicHelper::isBackend() )
        {
            return new Response(' ## NEWS - RELATED ## ');
        }

        return $template->getResponse();
    }
}