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

namespace IIDO\BasicBundle\Permission;


use Contao\BackendUser;
use Contao\CoreBundle\Framework\FrameworkAwareInterface;
use Contao\CoreBundle\Framework\FrameworkAwareTrait;
use Doctrine\DBAL\Connection;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;



class PermissionChecker implements FrameworkAwareInterface
{
    use FrameworkAwareTrait;


    /**
     * @var Connection
     */
    private $db;


    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;



    /**
     * PermissionChecker constructor.
     *
     * @param Connection            $db
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(Connection $db, TokenStorageInterface $tokenStorage)
    {
        $this->db           = $db;
        $this->tokenStorage = $tokenStorage;
    }



    /**
     * Return true if the user can manage categories.
     *
     * @param string $permsPrefix
     *
     * @return bool
     */
    public function canUserManageCategories( $permsPrefix )
    {
        return $this->getUser()->hasAccess('manage', $permsPrefix . 'categories');
    }



    /**
     * Return true if the user can assign categories.
     *
     * @param string $strTable
     *
     * @return bool
     */
    public function canUserAssignCategories( $strTable )
    {
        $user = $this->getUser();
        return $user->isAdmin || \in_array($strTable . '::categories', $user->alexf, true);
    }



    /**
     * Get the user default categories.
     *
     * @param string $permsPrefix
     *
     * @return array
     */
    public function getUserDefaultCategories( $permsPrefix )
    {
        $varName = $permsPrefix . 'categories_default';

        $user = $this->getUser();
        return \is_array($user->$varName) ? $user->$varName : [];
    }



    /**
     * Get the user allowed roots. Return null if the user has no limitation.
     *
     * @param string $permsPrefix
     *
     * @return array|null
     */
    public function getUserAllowedRoots( $permsPrefix )
    {
        $varName = $permsPrefix . 'categories_roots';

        $user = $this->getUser();

        if ($user->isAdmin)
        {
            return null;
        }

        return \array_map('intval', (array) $user->$varName);
    }



    /**
     * Return if the user is allowed to manage the category.
     *
     * @param int $categoryId
     * @param string $permsPrefix
     * @param string $strTable
     *
     * @return bool
     */
    public function isUserAllowedNewsCategory( $categoryId, $permsPrefix, $strTable )
    {
        if (null === ($roots = $this->getUserAllowedRoots( $permsPrefix )))
        {
            return true;
        }

        /** @var \Database $db */
        $db     = $this->framework->createInstance(\Database::class);
        $ids    = $db->getChildRecords($roots, $strTable, false, $roots);
        $ids    = \array_map('intval', $ids);

        return \in_array((int) $categoryId, $ids, true);
    }


    /**
     * Add the category to allowed roots.
     *
     * @param int    $categoryId
     * @param string $permsPrefix
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function addCategoryToAllowedRoots( $categoryId, $permsPrefix )
    {
        if (null === ($roots = $this->getUserAllowedRoots($permsPrefix)))
        {
            return;
        }

        $categoryId = (int) $categoryId;
        $user       = $this->getUser();

        /** @var \StringUtil $stringUtil */
        $stringUtil = $this->framework->getAdapter(\StringUtil::class);

        // Add the permissions on group level
        if ('custom' !== $user->inherit)
        {
            $groups = $this->db->fetchAll('SELECT id, newscategories, newscategories_roots FROM tl_user_group WHERE id IN('.\implode(',', \array_map('intval', $user->groups)).')');

            foreach ($groups as $group)
            {
                $permissions = $stringUtil->deserialize($group[ $permsPrefix . 'categories'], true);

                if (\in_array('manage', $permissions, true))
                {
                    $categoryIds = $stringUtil->deserialize($group[ $permsPrefix . 'categories_roots'], true);
                    $categoryIds[] = $categoryId;

                    $this->db->update('tl_user_group', [ $permsPrefix . 'categories_roots' => \serialize($categoryIds)], ['id' => $group['id']]);
                }
            }
        }
        // Add the permissions on user level
        if ('group' !== $user->inherit)
        {
            $userData = $this->db->fetchAssoc('SELECT ' . $permsPrefix . 'categories, ' . $permsPrefix . 'categories_roots FROM tl_user WHERE id=?', [$user->id]);
            $permissions = $stringUtil->deserialize($userData[ $permsPrefix .  'categories'], true);

            if (\in_array('manage', $permissions, true))
            {
                $categoryIds = $stringUtil->deserialize($userData[ $permsPrefix .  'categories_roots'], true);
                $categoryIds[] = $categoryId;

                $this->db->update('tl_user', [ $permsPrefix . 'categories_roots' => \serialize($categoryIds)], ['id' => $user->id]);
            }
        }
        $varName = $permsPrefix . 'categories_roots';

            // Add the new element to the user object
        $user->$varName = \array_merge($roots, [$categoryId]);
    }



    /**
     * Get the user.
     *
     * @throws \RuntimeException
     *
     * @return BackendUser
     */
    private function getUser()
    {
        if (null === $this->tokenStorage)
        {
            throw new \RuntimeException('No token storage provided');
        }

        $token = $this->tokenStorage->getToken();

        if (null === $token)
        {
            throw new \RuntimeException('No token provided');
        }

        $user = $token->getUser();

        if (!$user instanceof BackendUser)
        {
            throw new \RuntimeException('The token does not contain a back end user object');
        }

        return $user;
    }
}