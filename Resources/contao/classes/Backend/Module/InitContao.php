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

namespace IIDO\BasciBundle\Backend\Module;

use IIDO\BasciBundle\Connection\MasterConnection;


/**
 * Backend Module: Contao Init
 *
 * @author Stephan Preßl <development@prestep.at>
 */
class InitContao extends \BackendModule
{

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'be_mod_iido_initContao';



    /**
     * Generate the module
     */
    protected function compile()
    {
        $masterData = MasterConnection::getData();

        echo "<pre>";
        print_r("HUHU");
        exit;
    }

}
