<?php
/*******************************************************************
 *
 * (c) 2019 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 *
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *
 *******************************************************************/

namespace IIDO\BasicBundle\WebsiteConfig;



class ScriptSettingsConfig extends DefaultWebsiteConfigTable
{

    protected $strTable = 'tl_iido_basic_scriptSettings';



    public function run()
    {
        return parent::run();
    }

}