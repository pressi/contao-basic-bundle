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

declare (strict_types = 1);

namespace IIDO\BasicBundle\Table;


use Contao\DataContainer;
//use Contao\Exception;
use Contao\Image;
use Contao\Input;
use Contao\StringUtil;
use Contao\System;
use Contao\Backend;
use Contao\Versions;
use Contao\CoreBundle\Framework\ContaoFramework;
use Exception;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;


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


    protected $strTable;


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



    /**
     * Auto-generate an article alias if it has not been set yet
     * @param mixed
     * @param DataContainer
     *
     * @return string
     * @throws Exception
     *
     *                  TODO: ALIAS FUNCTION ÜBERARBEITEN!!! slug.generator???
     */
    public function generateAlias($varValue, DataContainer $dc)
    {
        $autoAlias = false;

        // Generate an alias if there is none
        if ($varValue == '')
        {
            $autoAlias  = true;
            $varValue   = standardize(StringUtil::restoreBasicEntities($dc->activeRecord->title));
        }

        $objAlias = $this->Database->prepare("SELECT id FROM " . $dc->table . " WHERE (id=? OR alias=?)")
            ->execute($dc->id, $varValue);

        // Check whether the page alias exists
        if ($objAlias->numRows > 1)
        {
            if (!$autoAlias)
            {
                throw new Exception(sprintf($GLOBALS['TL_LANG']['ERR']['aliasExists'], $varValue));
            }

            $varValue .= '-' . $dc->id;
        }

        return $varValue;
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
    public function toggleIcon($row, $href, $label, $title, $icon, $attributes)
    {
        if( Input::get('tid') !== null && \strlen(Input::get('tid')) )
        {
            $this->toggleVisibility(Input::get('tid'), (Input::get('state') == 1), (@func_get_arg(12) ?: null));
            $this->redirect($this->getReferer());
        }

        // Check permissions AFTER checking the tid, so hacking attempts are logged
        if (!$this->User->hasAccess($this->strTable . '::published', 'alexf'))
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
    public function toggleVisibility($intId, $blnVisible, DataContainer $dc = null)
    {
        // Set the ID and action
        Input::setGet('id', $intId);
        Input::setGet('act', 'toggle');

        if ($dc)
        {
            $dc->id = $intId;
        }

        // Trigger the onload_callback
        if (\is_array($GLOBALS['TL_DCA'][ $this->strTable ]['config']['onload_callback']))
        {
            foreach ($GLOBALS['TL_DCA'][ $this->strTable ]['config']['onload_callback'] as $callback)
            {
                if (\is_array($callback))
                {
                    $this->import($callback[0]);
                    $this->{$callback[0]}->{$callback[1]}($dc);
                }
                elseif (\is_callable($callback))
                {
                    $callback($dc);
                }
            }
        }
        // Check the field access
        if (!$this->User->hasAccess($this->strTable . '::published', 'alexf'))
        {
            throw new Contao\CoreBundle\Exception\AccessDeniedException('Not enough permissions to publish/unpublish Booking Plan Room item ID ' . $intId . '.');
        }

        // Set the current record
        if ($dc)
        {
            $objRow = $this->Database->prepare("SELECT * FROM " . $this->strTable . " WHERE id=?")
                ->limit(1)
                ->execute($intId);

            if ($objRow->numRows)
            {
                $dc->activeRecord = $objRow;
            }
        }

        $objVersions = new Versions($this->strTable, $intId);
        $objVersions->initialize();

        // Trigger the save_callback
        if (\is_array($GLOBALS['TL_DCA'][ $this->strTable ]['fields']['published']['save_callback']))
        {
            foreach ($GLOBALS['TL_DCA'][ $this->strTable ]['fields']['published']['save_callback'] as $callback)
            {
                if (\is_array($callback))
                {
                    $this->import($callback[0]);
                    $blnVisible = $this->{$callback[0]}->{$callback[1]}($blnVisible, $dc);
                }
                elseif (\is_callable($callback))
                {
                    $blnVisible = $callback($blnVisible, $dc);
                }
            }
        }
        $time = time();

        // Update the database
        $this->Database->prepare("UPDATE " . $this->strTable . " SET tstamp=$time, published='" . ($blnVisible ? '1' : '') . "' WHERE id=?")
            ->execute($intId);

        if ($dc)
        {
            $dc->activeRecord->tstamp = $time;
            $dc->activeRecord->published = ($blnVisible ? '1' : '');
        }

        // Trigger the onsubmit_callback
        if (\is_array($GLOBALS['TL_DCA'][ $this->strTable ]['config']['onsubmit_callback']))
        {
            foreach ($GLOBALS['TL_DCA'][ $this->strTable ]['config']['onsubmit_callback'] as $callback)
            {
                if (\is_array($callback))
                {
                    $this->import($callback[0]);
                    $this->{$callback[0]}->{$callback[1]}($dc);
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