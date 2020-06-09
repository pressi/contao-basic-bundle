<?php
declare (strict_types=1);
/*******************************************************************
 * (c) 2020 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\Dca\Table;


use Contao\BackendUser;
use Contao\DataContainer;
use Contao\Image;
use Contao\Input;
use Contao\StringUtil;
use Contao\System;
use Contao\Backend;
use Contao\Versions;
use Contao\CoreBundle\Framework\ContaoFramework;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;


/**
 * Class DefaultTable
 *
 * @package IIDO\BasicBundle\Dca\Table
 *
 * @todo still needed?? eine schönere lösung finden, die global einsetzbar ist
 */
class DefaultTable
{
    /**
     * @var ContaoFramework
     */
    private $framework;


    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;


    /**
     * @var SessionInterface
     */
    private $session;


    private $entityManager;


    private $User;


    private $Database = null;

    protected $strTableName;



    // Dependency Injection: Die Klasse werden in der Datei /src/Resources/config/services.yml definiert
    public function __construct(ContaoFramework $framework, TokenStorageInterface $tokenStorage, SessionInterface $session, $entityManager)
    {
        $this->framework        = $framework;
        $this->tokenStorage     = $tokenStorage;
        $this->session          = $session;
        $this->entityManager    = $entityManager;

        $token                  = $this->tokenStorage->getToken();
        $this->User             = $token->getUser();
        $this->Database         = System::importStatic('Database');
    }



    public function editItem($row, $href, $label, $title, $icon, $attributes)
    {
        return '<a href="' . Backend::addToUrl($href . '&amp;id=' . $row['id']) . '" title="' . StringUtil::specialchar($title) . '"' . $attributes . '>' . Image::getHtml($icon, $label) . '</a> ';
    }



    /**
     * Return the "toggle visibility" button
     *
     * @param array  $row
     * @param string $href
     * @param string $label
     * @param string $title
     * @param string $icon
     * @param string $attributes
     *
     * @return string
     */
    public static function toggleIconStatic($row, $href, $label, $title, $icon, $attributes, $strTable)
    {
        if( Input::get('tid') !== null && \strlen(Input::get('tid')) )
        {
            self::toggleVisibilityStatic( Input::get('tid'), (Input::get('state') == 1), (@func_get_arg(12) ?: null) );
            Backend::redirect( Backend::getReferer() );
        }

        $User = BackendUser::getInstance();

        // Check permissions AFTER checking the tid, so hacking attempts are logged
        if (!$User->hasAccess($strTable . '::published', 'alexf'))
        {
            return '';
        }

        $href .= '&amp;tid=' . $row['id'] . '&amp;state=' . ($row['published'] ? '' : 1);

        if (!$row['published'])
        {
            $icon = 'invisible.gif';
        }

        return '<a href="' . Backend::addToUrl($href) . '" title="' . StringUtil::specialchars($title) . '"' . $attributes . '>' . Image::getHtml($icon, $label, 'data-state="' . ($row['published'] ? 1 : 0) . '"') . '</a> ';
    }



    /**
     * Disable/enable a user group
     *
     * @param integer       $intId
     * @param boolean       $blnVisible
     * @param DataContainer $dc
     */
    public static function toggleVisibilityStatic($intId, $blnVisible, DataContainer $dc = null, $strTable = '')
    {
        // Set the ID and action
        Input::setGet('id', $intId);
        Input::setGet('act', 'toggle');

        if ($dc)
        {
            $dc->id = $intId;
        }

        // Trigger the onload_callback
        if (\is_array($GLOBALS['TL_DCA'][ $strTable ]['config']['onload_callback']))
        {
            foreach ($GLOBALS['TL_DCA'][ $strTable ]['config']['onload_callback'] as $callback)
            {
                if (\is_array($callback))
                {
                    System::importStatic($callback[0])->{$callback[1]}($dc);
                }
                elseif (\is_callable($callback))
                {
                    $callback($dc);
                }
            }
        }

        $User = BackendUser::getInstance();

        // Check the field access
        if (!$User->hasAccess($strTable . '::published', 'alexf'))
        {
            throw new \Contao\CoreBundle\Exception\AccessDeniedException('Not enough permissions to publish/unpublish item ID ' . $intId . ' (' . $strTable . ').');
        }

        // Set the current record
        if ($dc)
        {
            $objRow = \Database::getInstance()->prepare("SELECT * FROM " . $strTable . " WHERE id=?")
                ->limit(1)
                ->execute($intId);

            if ($objRow->numRows)
            {
                $dc->activeRecord = $objRow;
            }
        }

        $objVersions = new Versions($strTable, $intId);
        $objVersions->initialize();

        // Trigger the save_callback
        if (\is_array($GLOBALS['TL_DCA'][ $strTable ]['fields']['published']['save_callback']))
        {
            foreach ($GLOBALS['TL_DCA'][ $strTable ]['fields']['published']['save_callback'] as $callback)
            {
                if (\is_array($callback))
                {
                    $blnVisible = System::importStatic($callback[0])->{$callback[1]}($blnVisible, $dc);
                }
                elseif (\is_callable($callback))
                {
                    $blnVisible = $callback($blnVisible, $dc);
                }
            }
        }
        $time = time();

        // Update the database
        \Database::getInstance()->prepare("UPDATE " . $strTable . " SET tstamp=$time, published='" . ($blnVisible ? '1' : '') . "' WHERE id=?")
            ->execute($intId);

        if ($dc)
        {
            $dc->activeRecord->tstamp = $time;
            $dc->activeRecord->published = ($blnVisible ? '1' : '');
        }

        // Trigger the onsubmit_callback
        if (\is_array($GLOBALS['TL_DCA'][ $strTable ]['config']['onsubmit_callback']))
        {
            foreach ($GLOBALS['TL_DCA'][ $strTable ]['config']['onsubmit_callback'] as $callback)
            {
                if (\is_array($callback))
                {
                    System::importStatic($callback[0])->{$callback[1]}($dc);
                }
                elseif (\is_callable($callback))
                {
                    $callback($dc);
                }
            }
        }

        $objVersions->create();
    }
}