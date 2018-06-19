<?php
/*******************************************************************
 *
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 *
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *
 *******************************************************************/

namespace IIDO\BasicBundle\Module;


use Contao\CoreBundle\Controller\AbstractFrontendModuleController;
use Contao\Model;
use Contao\Template;
use Symfony\Component\HttpFoundation\Request;


class FEMODController extends AbstractFrontendModuleController
{

    protected function getResponse(Template $template, Model $model, Request $request)
    {
        return $template->getResponse();
    }
}