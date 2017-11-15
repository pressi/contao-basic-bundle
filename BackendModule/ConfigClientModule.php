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

namespace IIDO\BasicBundle\BackendModule;

use IIDO\BasicBundle\Connection\ClientSetup;
use IIDO\BasicBundle\Connection\MasterConnection;
use IIDO\BasicBundle\Helper\Message;


/**
 * Backend Module: Contao Init
 *
 * @author Stephan Preßl <development@prestep.at>
 * @deprecated NO MORE IN USE!!
 */
class ConfigClientModule extends \BackendModule
{

    /**
     * Template
     * @var string
     */
    protected $strTemplate  = 'be_mod_iido_configClient';

    protected $loggedIn     = FALSE;



    /**
     * Parse the template
     *
     * @return string
     */
    public function generate()
    {
        if( \Input::get("method") == "login" )
        {
            $this->strTemplate  = 'be_mod_iido_loginMaster'; // TODO: own module?
        }

        if( \Input::post("FORM_SUBMIT") )
        {
            if( MasterConnection::getInstance()->isPasswordValid() )
            {
                $this->loggedIn     = TRUE;
            }
            else
            {
                MasterConnection::redirect("login", array("error" => "Falsches Passwort!"));
            }

            if( \Input::post("FORM_SUBMIT") == "tl_iido_configContao" )
            {
                ClientSetup::initClient();
            }
        }

        if( !$this->loggedIn )
        {
            if( \Input::get("method") != "login" )
            {
                MasterConnection::redirect("login");
            }
        }

        $connection = MasterConnection::getInstance()->testConnection();

        if( key_exists("ERROR", $connection) )
        {
            if( preg_match('/connection failed/', strtolower($connection['ERROR'])) )
            {
                $this->strTemplate  = 'be_mod_iido_errorMaster';
                \Session::getInstance()->set("iidoMessage", $connection['ERROR']);
                return parent::generate();
            }
        }

        return parent::generate();
    }



    /**
     * Generate the module
     */
    protected function compile()
    {
        $masterData = MasterConnection::getInstance()->getData( true );
        $strMessage = "";

        if( \Session::getInstance()->get("iidoMessage") )
        {
            $arrMessage = \Session::getInstance()->get("iidoMessage");

            if( is_array($arrMessage) && count($arrMessage) )
            {
                $strMessage = Message::render($arrMessage);
            }
            else
            {
                $strMessage = Message::render( array("error"=>$arrMessage) );
            }

            \Session::getInstance()->remove("iidoMessage");
        }

        $this->Template->masterData     = $masterData;
        $this->Template->postPassword   = \Input::post("password");
        $this->Template->message        = $strMessage;
    }

}
