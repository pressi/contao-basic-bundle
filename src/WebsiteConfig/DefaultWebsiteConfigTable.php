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


use Contao\DataContainer;


abstract class DefaultWebsiteConfigTable extends \Backend implements \executable
{

    /**
     * Template
     *
     * @var string
     */
    protected $strTemplate = 'be_mod_iido_websiteConfig_table';


    /**
     * Table name
     *
     * @var string
     */
    protected $strTable;


    /**
     * Data container object
     *
     * @var null|DataContainer
     */
    protected $objDc = null;


    /**
     * If table has backlink
     *
     * @var boolean
     */
    protected $hasBacklink = false;



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
        return $this->hasBacklink;
    }



    /**
     * Generate the module
     */
    public function run()
    {
        $objTemplate = new \BackendTemplate( $this->strTemplate );

        \Controller::loadDataContainer( $this->strTable );

        $strClass       = '\DC_Table';
        $tableFuncName  = 'showAll';

        if( $GLOBALS['TL_DCA'][ $this->strTable ]['config']['dataContainer'] === 'File' )
        {
            $strClass       = '\DC_File';
            $tableFuncName  = 'edit';

            $objTable       = new $strClass( $this->strTable );
        }
        else
        {
            $objTable = $this->objDc?:new $strClass( $this->strTable );
        }

        $action = \Input::get("act");

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