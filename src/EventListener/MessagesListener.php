<?php
declare(strict_types=1);

/*******************************************************************
 * (c) 2020 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\EventListener;


use Contao\Backend;
use Contao\BackendUser;
use Contao\Controller;
use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\System;
use IIDO\BasicBundle\Config\IIDOConfig;
use Terminal42\ServiceAnnotationBundle\ServiceAnnotationInterface;


class MessagesListener extends Backend implements ServiceAnnotationInterface
{
    public function __construct()
    {
        parent::__construct();
    }



    /**
     * @Hook("getSystemMessages")
     */
    public function onGetSystemMessages(): string
    {
        $this->import(BackendUser::class, 'User');

        $strMessages    = '';

        if( IIDOConfig::get('previewMode') )
        {
            $adminAddon = '';

            if( $this->User->isAdmin )
            {
                $adminAddon = ' <a href="' . IIDOConfig::getLink() . '" onclick="window.parent.location.href = this.getAttribute(\'href\'); return false;">Deaktivieren</a>';
            }

            $strMessages .= '<div class="tl_error">Der Vorschau-Modus (Meta: noindex,nofollow) ist aktiv.' . $adminAddon . '</div>';
        }

        if( $this->User->isAdmin )
        {
            if( IIDOConfig::get('customLogin') )
            {
                $strMessages .= '<div class="tl_info">Der personalisierte Login ist aktiviert.</div>';
            }

            $enableMobile = '<strong>deaktiviert</strong>';
            if( IIDOConfig::get('enableMobileNavigation') )
            {
                $enableMobile = 'aktiviert';
            }
            $strMessages .= '<div class="tl_info">Mobile Navigation ist ' . $enableMobile . '.</div>';

            $arrFieldMessage = [];

            if( IIDOConfig::get('includeElementFields') )
            {
                $arrFieldMessage[] = 'bei den Inhalts Elementen';
            }

            if( IIDOConfig::get('includeArticleFields') )
            {
                $arrFieldMessage[] = 'bei den Artikeln';
            }

            if( IIDOConfig::get('includePageFields') )
            {
                $arrFieldMessage[] = 'bei den Seiten';
            }

            if( count($arrFieldMessage) )
            {
                if( count($arrFieldMessage) > 1 )
                {
                    $lastMessage = trim( preg_replace('/bei den /', '', array_pop( $arrFieldMessage )) );

                    $strFieldMessage = implode(', ', $arrFieldMessage) . ' und ' . $lastMessage;
                    $strFieldMessage = preg_replace('/, bei den /', ', ', $strFieldMessage);
                }
                else
                {
                    $strFieldMessage = implode(', ', $arrFieldMessage);
                }

                $strMessages .= '<div class="tl_info">IIDO Felder ' . $strFieldMessage . ' wurden aktiviert.</div>';
            }
            else
            {
                $strMessages .= '<div class="tl_info">IIDO Felder sind deaktiviert.</div>';
            }
        }

        return $strMessages;
    }
}