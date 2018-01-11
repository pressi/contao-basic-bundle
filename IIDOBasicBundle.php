<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use IIDO\BasicBundle\DependencyInjection\IIDOBasicExtension;

/**
 * Configures the Contao IIDO Basic Bundle.
 *
 * @author Stephan Preßl <development@prestep.at>
 */
class IIDOBasicBundle extends Bundle
{

    /**
     * Register extension
     * @return \IIDO\BasicBundle\DependencyInjection\IIDOBasicExtension
     */
    public function getContainerExtension()
    {
        return new IIDOBasicExtension();
    }
}
