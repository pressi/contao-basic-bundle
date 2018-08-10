<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\EventListener;


use Contao\Database;
use Contao\Input;
use Contao\Validator;


/**
 * IIDO System Listener
 *
 * @package IIDO\BasicBundle
 * @author Stephan Preßl <development@prestep.at>
 */
class UserListener extends DefaultListener
{

    public function importCustomizeUser( $strUser, $strPassword, $strTable )
    {
        if( !$this->framework->getAdapter( Validator::class )->isEmail( $strUser ) )
        {
            return false;
        }

        switch( $strTable )
        {
            case "tl_member":
                $objMember = $this->framework->createInstance( Database::class )->prepare('SELECT * FROM tl_member WHERE lower(email) = ?')->limit(1)->execute($strUser);

                if( $objMember->numRows > 0 )
                {
                    $this->framework->getAdapter( Input::class )->setPost('username', $objMember->username);

                    return true;
                }
                break;

            case "tl_user":
                $objUser = $this->framework->createInstance( Database::class )->prepare('SELECT * FROM tl_user WHERE lower(email) = ?')->limit(1)->execute($strUser);

                if( $objUser->numRows > 0 )
                {
                    $this->framework->getAdapter( Input::class )->setPost('username', $objUser->username);

                    return true;
                }
                break;
        }

        return false;
    }

}
