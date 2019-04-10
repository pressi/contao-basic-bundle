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
use HeimrichHannot\TinySliderBundle\Model\TinySliderConfigModel;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use IIDO\BasicBundle\Permission\PermissionChecker;


class DefaultTable extends \Backend
{
    /**
     * @var ContaoFramework
     */
    protected $framework;


    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;


    /**
     * @var SessionInterface
     */
    protected $session;


    /**
     * @var PermissionChecker
     */
    protected $permissionChecker;


    protected $entityManager;


    protected $User;


    protected $Database = null;


    protected $strTable;

    protected $tablePermissionsName;



    // Dependency Injection: Die Klasse werden in der Datei /src/Resources/config/services.yml definiert
    public function __construct(ContaoFramework $framework, TokenStorageInterface $tokenStorage, SessionInterface $session, $entityManager, PermissionChecker $permissionChecker)
    {
        parent::__construct();
//        $this->import('BackendUser', 'User');

        $this->framework        = $framework;
        $this->tokenStorage     = $tokenStorage;
        $this->session          = $session;
        $this->entityManager    = $entityManager;
        $this->permissionChecker = $permissionChecker;

        $token                  = $this->tokenStorage->getToken();
        $this->User             = $token->getUser();
        $this->Database         = System::importStatic('Database');
    }



    /**
     * Auto-generate an article alias if it has not been set yet
     * @param mixed
     * @param \DataContainer
     *
     * @return string
     * @throws Exception
     *
     *                  TODO: ALIAS FUNCTION ÜBERARBEITEN!!! slug.generator???
     */
    public function generateAlias($varValue, \DataContainer $dc)
    {
        $autoAlias = false;

        // Generate an alias if there is none
        if ($varValue == '')
        {
            $autoAlias  = true;
            $varValue   = standardize(StringUtil::restoreBasicEntities($dc->activeRecord->title?:$dc->activeRecord->name));
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
     * Check permissions to edit table
     *
     * @throws \Contao\CoreBundle\Exception\AccessDeniedException
     */
    public function checkPermission()
    {
        $bundles = \System::getContainer()->getParameter('kernel.bundles');

        // HOOK: comments extension required
        if (!isset($bundles['ContaoCommentsBundle']))
        {
            unset($GLOBALS['TL_DCA'][ $this->strTable ]['fields']['allowComments']);
        }

        if ($this->User->isAdmin)
        {
            return;
        }

        $tablePerms     = $this->tablePermissionsName;
        $tablePermsP    = preg_replace('/s$/', 'p', $tablePerms);

        // Set root IDs
        if (!\is_array($this->User->$tablePerms) || empty($this->User->$tablePerms))
        {
            $root = array(0);
        }
        else
        {
            $root = $this->User->$tablePerms;
        }

        if( !$GLOBALS['TL_DCA'][ $this->strTable ]['config']['ptable'] && $GLOBALS['TL_DCA'][ $this->strTable ]['config']['ctable'] )
        {
            $GLOBALS['TL_DCA'][ $this->strTable ]['list']['sorting']['root'] = $root;

            // Check permissions to add archives
            if(!$this->User->hasAccess('create', $tablePermsP))
            {
                $GLOBALS['TL_DCA'][ $this->strTable ]['config']['closed'] = true;
            }

            /** @var \Symfony\Component\HttpFoundation\Session\SessionInterface $objSession */
            $objSession = \System::getContainer()->get('session');

            // Check current action
            switch (\Input::get('act'))
            {
                case 'create':
                case 'select':
                    // Allow
                    break;

                case 'edit':
                    // Dynamically add the record to the user profile
                    if (!\in_array(\Input::get('id'), $root))
                    {
                        /** @var \Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface $objSessionBag */
                        $objSessionBag = $objSession->getBag('contao_backend');

                        $arrNew = $objSessionBag->get('new_records');

                        if (\is_array($arrNew[ $this->strTable ]) && \in_array(\Input::get('id'), $arrNew[ $this->strTable ]))
                        {
                            // Add the permissions on group level
                            if ($this->User->inherit != 'custom')
                            {
                                $objGroup = $this->Database->execute("SELECT id, " . $tablePerms . ", " . $tablePermsP . " FROM tl_user_group WHERE id IN(" . implode(',', array_map('intval', $this->User->groups)) . ")");

                                while ($objGroup->next())
                                {
                                    $arrNewp = \StringUtil::deserialize($objGroup->$tablePermsP);

                                    if (\is_array($arrNewp) && \in_array('create', $arrNewp))
                                    {
                                        $arrItems = \StringUtil::deserialize($objGroup->$tablePerms, true);
                                        $arrItems[] = \Input::get('id');

                                        $this->Database->prepare("UPDATE tl_user_group SET " . $tablePerms . "=? WHERE id=?")
                                            ->execute(serialize($arrItems), $objGroup->id);
                                    }
                                }
                            }

                            // Add the permissions on user level
                            if ($this->User->inherit != 'group')
                            {
                                $objUser = $this->Database->prepare("SELECT " . $tablePerms . ", " . $tablePermsP . " FROM tl_user WHERE id=?")
                                    ->limit(1)
                                    ->execute($this->User->id);

                                $arrNewp = \StringUtil::deserialize($objUser->$tablePermsP);

                                if (\is_array($arrNewp) && \in_array('create', $arrNewp))
                                {
                                    $arrItems = \StringUtil::deserialize($objUser->$tablePerms, true);
                                    $arrItems[] = \Input::get('id');

                                    $this->Database->prepare("UPDATE tl_user SET " . $tablePerms . "=? WHERE id=?")
                                        ->execute(serialize($arrItems), $this->User->id);
                                }
                            }

                            // Add the new element to the user object
                            $root[] = \Input::get('id');
                            $this->User->$tablePerms = $root;
                        }
                    }
                // No break;

                case 'copy':
                case 'delete':
                case 'show':
                    if (!\in_array(\Input::get('id'), $root) || (\Input::get('act') == 'delete' && !$this->User->hasAccess('delete', $tablePermsP)))
                    {
                        throw new \Contao\CoreBundle\Exception\AccessDeniedException('Not enough permissions to ' . \Input::get('act') . ' archive ID ' . \Input::get('id') . ' (' . $this->strTable . ').');
                    }
                    break;

                case 'editAll':
                case 'deleteAll':
                case 'overrideAll':
                    $session = $objSession->all();
                    if (\Input::get('act') == 'deleteAll' && !$this->User->hasAccess('delete', $tablePermsP))
                    {
                        $session['CURRENT']['IDS'] = array();
                    }
                    else
                    {
                        $session['CURRENT']['IDS'] = array_intersect($session['CURRENT']['IDS'], $root);
                    }
                    $objSession->replace($session);
                    break;

                default:
                    if (\Input::get('act') && \strlen(\Input::get('act')))
                    {
                        throw new \Contao\CoreBundle\Exception\AccessDeniedException('Not enough permissions to ' . \Input::get('act') . ' item archives (' . $this->strTable . ').');
                    }
                    break;
            }
        }
        else
        {
            $id = \strlen(Input::get('id')) ? Input::get('id') : CURRENT_ID;

            // Check current action
            switch( Input::get('act') )
            {
                case 'paste':
                case 'select':
                    if (!\in_array(CURRENT_ID, $root)) // check CURRENT_ID here (see #247)
                    {
                        throw new \Contao\CoreBundle\Exception\AccessDeniedException('Not enough permissions to access archive ID ' . $id . '.');
                    }
                    break;

                case 'create':
                    if (!\strlen(Input::get('pid')) || !\in_array(Input::get('pid'), $root))
                    {
                        throw new \Contao\CoreBundle\Exception\AccessDeniedException('Not enough permissions to create items in archive ID ' . Input::get('pid') . '.');
                    }
                    break;

                case 'cut':
                case 'copy':
                    if (Input::get('act') == 'cut' && Input::get('mode') == 1)
                    {
                        $objArchive = $this->Database->prepare("SELECT pid FROM " . $this->strTable . " WHERE id=?")
                            ->limit(1)
                            ->execute(Input::get('pid'));

                        if ($objArchive->numRows < 1)
                        {
                            throw new \Contao\CoreBundle\Exception\AccessDeniedException('Invalid item ID ' . Input::get('pid') . '.');
                        }

                        $pid = $objArchive->pid;
                    }
                    else
                    {
                        $pid = Input::get('pid');
                    }

                    if (!\in_array($pid, $root))
                    {
                        throw new \Contao\CoreBundle\Exception\AccessDeniedException('Not enough permissions to ' . Input::get('act') . ' item ID ' . $id . ' to archive ID ' . $pid . '.');
                    }
                // NO BREAK STATEMENT HERE

                case 'edit':
                case 'show':
                case 'delete':
                case 'toggle':
                case 'feature':
                    $objArchive = $this->Database->prepare("SELECT pid FROM " . $this->strTable . " WHERE id=?")
                        ->limit(1)
                        ->execute($id);

                    if ($objArchive->numRows < 1)
                    {
                        throw new \Contao\CoreBundle\Exception\AccessDeniedException('Invalid item ID ' . $id . '.');
                    }

                    if (!\in_array($objArchive->pid, $root))
                    {
                        throw new \Contao\CoreBundle\Exception\AccessDeniedException('Not enough permissions to ' . Input::get('act') . ' item ID ' . $id . ' of archive ID ' . $objArchive->pid . '.');
                    }
                    break;

                case 'editAll':
                case 'deleteAll':
                case 'overrideAll':
                case 'cutAll':
                case 'copyAll':
                    if (!\in_array($id, $root))
                    {
                        throw new \Contao\CoreBundle\Exception\AccessDeniedException('Not enough permissions to access archive ID ' . $id . '.');
                    }

                    $objArchive = $this->Database->prepare("SELECT id FROM " . $this->strTable . " WHERE pid=?")
                        ->execute($id);

                    if ($objArchive->numRows < 1)
                    {
                        throw new \Contao\CoreBundle\Exception\AccessDeniedException('Invalid archive ID ' . $id . '.');
                    }

                    /** @var \Symfony\Component\HttpFoundation\Session\SessionInterface $objSession */
                    $objSession = System::getContainer()->get('session');

                    $session = $objSession->all();
                    $session['CURRENT']['IDS'] = array_intersect((array) $session['CURRENT']['IDS'], $objArchive->fetchEach('id'));
                    $objSession->replace($session);
                    break;

                default:
                    if ( Input::get('act') && \strlen(Input::get('act')))
                    {
                        throw new \Contao\CoreBundle\Exception\AccessDeniedException('Invalid command "' . Input::get('act') . '".');
                    }
                    elseif (!\in_array($id, $root))
                    {
                        throw new \Contao\CoreBundle\Exception\AccessDeniedException('Not enough permissions to access archive ID ' . $id . '.');
                    }
                    break;
            }
        }
    }



    /**
     * Return the edit header button
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
    public function editHeader($row, $href, $label, $title, $icon, $attributes)
    {
        return $this->User->canEditFieldsOf( $this->strTable ) ? '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.\StringUtil::specialchars($title).'"'.$attributes.'>'.\Image::getHtml($icon, $label).'</a> ' : \Image::getHtml(preg_replace('/\.svg$/i', '_.svg', $icon)).' ';
    }



    /**
     * Return the copy archive button
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
    public function copyArchive($row, $href, $label, $title, $icon, $attributes)
    {
        $perms = preg_replace('/s$/', 'p', $this->tablePermissionsName);
        return $this->User->hasAccess('create', $perms) ? '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.\StringUtil::specialchars($title).'"'.$attributes.'>'.\Image::getHtml($icon, $label).'</a> ' : \Image::getHtml(preg_replace('/\.svg$/i', '_.svg', $icon)).' ';
    }



    /**
     * Return the delete archive button
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
    public function deleteArchive($row, $href, $label, $title, $icon, $attributes)
    {
        $perms = preg_replace('/s$/', 'p', $this->tablePermissionsName);
        return $this->User->hasAccess('delete', $perms) ? '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.\StringUtil::specialchars($title).'"'.$attributes.'>'.\Image::getHtml($icon, $label).'</a> ' : \Image::getHtml(preg_replace('/\.svg$/i', '_.svg', $icon)).' ';
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
            $this->toggleVisibility( Input::get('tid'), (Input::get('state') == 1), (@func_get_arg(12) ?: null) );
            $this->redirect( $this->getReferer() );
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
            throw new \Contao\CoreBundle\Exception\AccessDeniedException('Not enough permissions to publish/unpublish item ID ' . $intId . ' (' . $this->strTable . ').');
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



    /**
     * Return the "feature/unfeature element" button
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
    public function iconFeatured($row, $href, $label, $title, $icon, $attributes)
    {
        if( Input::get('fid') === null )
        {
            Input::setGet('fid', '');
        }

        if( \strlen(Input::get('fid')) )
        {
            $this->toggleFeatured(Input::get('fid'), (Input::get('state') == 1), (@func_get_arg(12) ?: null));
            $this->redirect($this->getReferer());
        }

        // Check permissions AFTER checking the fid, so hacking attempts are logged
        if (!$this->User->hasAccess($this->strTable . '::featured', 'alexf'))
        {
            return '';
        }

        $href .= '&amp;fid=' . $row['id'] . '&amp;state=' . ($row['featured'] ? '' : 1);

        if( !$row['featured'] )
        {
            $icon = 'featured_.svg';
        }

        return '<a href="' . $this->addToUrl($href) . '" title="' . StringUtil::specialchars($title) . '"' . $attributes . '>' . Image::getHtml($icon, $label, 'data-state="' . ($row['featured'] ? 1 : 0) . '"') . '</a> ';
    }



    /**
     * Feature/unfeature a item
     *
     * @param integer       $intId
     * @param boolean       $blnVisible
     * @param DataContainer $dc
     *
     * @throws \Contao\CoreBundle\Exception\AccessDeniedException
     */
    public function toggleFeatured($intId, $blnVisible, DataContainer $dc=null)
    {
        // Check permissions to edit
        Input::setGet('id', $intId);
        Input::setGet('act', 'feature');
        $this->checkPermission();

        // Check permissions to feature
        if (!$this->User->hasAccess($this->strTable . '::featured', 'alexf'))
        {
            throw new \Contao\CoreBundle\Exception\AccessDeniedException('Not enough permissions to feature/unfeature item ID ' . $intId . '.');
        }

        $objVersions = new Versions($this->strTable, $intId);
        $objVersions->initialize();

        // Trigger the save_callback
        if (\is_array($GLOBALS['TL_DCA'][ $this->strTable ]['fields']['featured']['save_callback']))
        {
            foreach ($GLOBALS['TL_DCA'][ $this->strTable ]['fields']['featured']['save_callback'] as $callback)
            {
                if (\is_array($callback))
                {
                    $this->import($callback[0]);
                    $blnVisible = $this->{$callback[0]}->{$callback[1]}($blnVisible, $dc);
                }
                elseif (\is_callable($callback))
                {
                    $blnVisible = $callback($blnVisible, $this);
                }
            }
        }

        // Update the database
        $this->Database->prepare("UPDATE " . $this->strTable . " SET tstamp=". time() .", featured='" . ($blnVisible ? 1 : '') . "' WHERE id=?")
            ->execute($intId);

        $objVersions->create();
    }



    public function onGetGallerySliderConfigs( $dc )
    {
        $arrOptions = array();

        $activeTnySlider        = \IIDO\BasicBundle\Config\BundleConfig::isActiveBundle('heimrichhannot/contao-tiny-slider-bundle');
        $activeRocksolidSlider  = \IIDO\BasicBundle\Config\BundleConfig::isActiveBundle('madeyourday/contao-rocksolid-slider');

        if( $activeTnySlider || $activeRocksolidSlider )
        {
            if( $activeRocksolidSlider )
            {
                $arrSlider  = array();
                $objModules = $this->Database->execute("SELECT id, pid, name FROM tl_module WHERE type = 'rocksolid_slider' ORDER BY name");

                while( $objModules->next() )
                {
                    $objTheme = \ThemeModel::findById($objModules->pid);

                    $arrSlider[ $objTheme->name ][ $objModules->id ] = $objModules->name;
                }

                if (count($arrSlider) === 1)
                {
                    $arrSlider = array_values($arrSlider)[0];
                }

                $arrOptions[ $GLOBALS['TL_LANG']['MOD']['rocksolid_slider'][0] ] = $arrSlider;
            }

            if( $activeTnySlider )
            {
                /** @var TinySliderConfigModel $configAdapter */
                $configAdapter = $this->framework->getAdapter(TinySliderConfigModel::class);

                if( null !== ($configs = $configAdapter->findBy(['type != ?'], 'responsive')) )
                {
                    $arrSlider = $configs->fetchEach('title');

                    $arrOptions[ $GLOBALS['TL_LANG']['MOD']['tiny_slider_config'][0] ] = $arrSlider;
                }
            }

        }

        return $arrOptions;
    }
}