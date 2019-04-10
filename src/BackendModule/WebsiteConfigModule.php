<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\BackendModule;


use IIDO\BasicBundle\Helper\BackendHelper;
use IIDO\BasicBundle\Helper\BasicHelper;
use IIDO\BasicBundle\Helper\ColorHelper;
use IIDO\BasicBundle\Helper\WebsiteStylesHelper;
use IIDO\BasicBundle\Model\WebsiteStyleModel;
use Symfony\Bundle\FrameworkBundle\Templating\Helper\FormHelper;


/**
 * Backend Module: Website Styles
 *
 * @author Stephan Preßl <development@prestep.at>
 */
class WebsiteConfigModule extends \BackendModule
{

    /**
     * Template
     * @var string
     */
    protected $strTemplate  = 'be_mod_iido_websiteConfig';



    public function generate()
    {
        $this->import("BackendUser", "User");

        return parent::generate();
    }



    /**
     * Generate the module
     */
    protected function compile()
    {
        $arrConfigurations      = array();
        $GLOBALS['TL_CSS'][]    = 'bundles/iidobasic/css/backend/website-styles.css||static';

        $detailView = false;
        $noBacklink = false;

        $this->Template->User           = $this->User;
        $this->Template->configurations = $GLOBALS['IIDO']['CONFIGS'];

        if( \Input::get("config") || \Input::get('table') )
        {
            $detailView = true;

            $callback = $this->getCallbackClass();

            $this->import($callback, 'WebsiteConfig');

            if (!$this->WebsiteConfig instanceof \executable)
            {
                throw new \Exception("$callback is not an executable class");
            }

            $this->WebsiteConfig->addDataContainerObject( $this->objDc );

            $buffer = $this->WebsiteConfig->run();

            if( $this->WebsiteConfig->isActive() )
            {
                $this->Template->content = $buffer;

                if( method_exists($this->WebsiteConfig, "showBacklink") )
                {
                    if( !$this->WebsiteConfig->showBacklink() )
                    {
                        $noBacklink = true;
                    }
                }
            }
//            else
//            {
//                $this->Template->content .= $buffer;
//            }
        }

        $this->Template->detailView = $detailView;
        $this->Template->noBackLink = $noBacklink;
    }



    protected function getCallbackClass()
    {
        $class = '';

        foreach( $GLOBALS['IIDO']['CONFIGS'] as $key => $importer )
        {
            if( $key === \Input::get("config") || $importer['alias'] === \Input::get("config") || \Input::get('table') === $importer['table'] )
            {
                $class = $importer['class'];
                break;
            }
        }

        return $class;
    }

}
