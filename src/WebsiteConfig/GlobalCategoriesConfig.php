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



class GlobalCategoriesConfig extends \Backend implements \executable
{

    /**
     * Template
     * @var string
     */
    protected $strTemplate  = 'be_mod_iido_websiteConfig_globalCategories';



    protected $strTable = 'tl_iido_global_category';

    protected $objDc = null;



    public function __construct()
    {
        parent::__construct();
    }



    public function isActive()
    {
        return true;
    }



    public function showBacklink()
    {
        return false;
    }



    /**
     * Generate the module
     */
    public function run()
    {
        $objTemplate = new \BackendTemplate( $this->strTemplate );

        \Controller::loadDataContainer( $this->strTable );


        $objTable = $this->objDc?:new \DC_Table( $this->strTable );

        $tableFuncName  = 'showAll';
        $action         = \Input::get("act");

//        if( $GLOBALS['TL_DCA'][ $this->strTable ]['list']['sorting']['mode'] === 5 )
//        {
//            $objTable->treeView = true;
//        }

        if( $action )
        {
            if( $action !== "select" && $action !== "paste" )
            {
                $tableFuncName = $action;
            }
        }

        $objTemplate->strTableContent = $objTable->$tableFuncName();


        return $objTemplate->parse();
    }



    public function addDataContainerObject( $objDc )
    {
        $this->objDc = $objDc;
    }

}