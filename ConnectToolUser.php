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

namespace IIDO\BasicBundle;

use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Handles the user authentication.
 *
 * @author Stephan Preßl <https://github.com/pressi>
 */
class ConnectToolUser
{
    /**
     * @var Session
     */
    private $session;

    /**
     * @var int
     */
    private $timeout = 300;



    /**
     * Constructor.
     *
     * @param Session $session
     */
    public function __construct(Session $session)
    {
        $this->session = $session;
    }



    /**
     * Checks if the user is authenticated.
     *
     * @return bool
     */
    public function isAuthenticated()
    {
        if (!$this->session->has('_auth_until') || $this->session->get('_auth_until') < time())
        {
            return false;
        }

        // Update the expiration date
        $this->session->set('_auth_until', time() + $this->timeout);

        return true;
    }



    /**
     * Sets the authentication flag.
     *
     * @param bool $authenticated
     */
    public function setAuthenticated($authenticated)
    {
        if (true === $authenticated)
        {
            $this->session->set('_auth_until', time() + $this->timeout);
        }
        else
        {
            $this->session->remove('_auth_until');
        }
    }



    public function setPassword( $strPassword )
    {
        $this->session->set('_auth_connect_pwd', md5($strPassword));
    }



    public function getPassword()
    {
        return $this->session->get('_auth_connect_pwd');
    }
}
