<?php

namespace IIDO\BasicBundle\Permission;

use Contao\BackendUser;
use Contao\CoreBundle\Framework\FrameworkAwareInterface;
use Contao\CoreBundle\Framework\FrameworkAwareTrait;
use Contao\Database;
use Contao\StringUtil;
use Doctrine\DBAL\Connection;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class NewsPermissionChecker implements FrameworkAwareInterface
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
        $this->db = $db;
        $this->tokenStorage = $tokenStorage;
    }



    /**
     * Return true if the user can manage news companies.
     *
     * @return bool
     */
    public function canUserManageAreaOfApplication()
    {
        return $this->getUser()->hasAccess('manage', 'newsAreaOfApplication');
    }



    /**
     * Return true if the user can manage news companies.
     *
     * @return bool
     */
    public function canUserManageUsage()
    {
        return $this->getUser()->hasAccess('manage', 'newsUsage');
    }



    /**
     * Return true if the user can assign news companies.
     *
     * @return bool
     */
    public function canUserAssignAreaOfApplication()
    {
        $user = $this->getUser();

        return $user->isAdmin || \in_array('tl_news::areasOfApplication', $user->alexf, true);
    }



    /**
     * Return true if the user can assign news companies.
     *
     * @return bool
     */
    public function canUserAssignUsage()
    {
        $user = $this->getUser();

        return $user->isAdmin || \in_array('tl_news::usage', $user->alexf, true);
    }



    /**
     * Get the user default companies.
     *
     * @return array
     */
    public function getUserDefaultAreaOfApplication()
    {
        $user = $this->getUser();

        return \is_array($user->newsAreaOfApplication_default) ? $user->newsAreaOfApplication_default : [];
    }



    /**
     * Get the user default companies.
     *
     * @return array
     */
    public function getUserDefaultUsage()
    {
        $user = $this->getUser();

        return \is_array($user->newsUsage_default) ? $user->newsUsage_default : [];
    }



    /**
     * Get the user allowed roots. Return null if the user has no limitation.
     *
     * @return array|null
     */
    public function getUserAllowedRoots()
    {
        $user = $this->getUser();

        if ($user->isAdmin) {
            return null;
        }

        return \array_map('intval', (array) $user->newsAreaOfApplication_roots);
    }



    /**
     * Get the user allowed roots. Return null if the user has no limitation.
     *
     * @return array|null
     */
    public function getUserAllowedUsageRoots()
    {
        $user = $this->getUser();

        if ($user->isAdmin) {
            return null;
        }

        return \array_map('intval', (array) $user->newsUsage_roots);
    }



    /**
     * Return if the user is allowed to manage the news company.
     *
     * @param int $companyId
     *
     * @return bool
     */
    public function isUserAllowedNewsAreaOfApplication($companyId)
    {
        if (null === ($roots = $this->getUserAllowedRoots())) {
            return true;
        }

        /** @var Database $db */
        $db = $this->framework->createInstance(Database::class);

        $ids = $db->getChildRecords($roots, 'tl_news_areaOfApplication', false, $roots);
        $ids = \array_map('intval', $ids);

        return \in_array((int) $companyId, $ids, true);
    }



    /**
     * Return if the user is allowed to manage the news company.
     *
     * @param int $companyId
     *
     * @return bool
     */
    public function isUserAllowedNewsUsage($companyId)
    {
        if (null === ($roots = $this->getUserAllowedUsageRoots())) {
            return true;
        }

        /** @var Database $db */
        $db = $this->framework->createInstance(Database::class);

        $ids = $db->getChildRecords($roots, 'tl_news_usage', false, $roots);
        $ids = \array_map('intval', $ids);

        return \in_array((int) $companyId, $ids, true);
    }



    /**
     * Add the company to allowed roots.
     *
     * @param int $companyId
     */
    public function addAreaOfApplicationToAllowedRoots($companyId)
    {
        if (null === ($roots = $this->getUserAllowedRoots())) {
            return;
        }

        $companyId = (int) $companyId;
        $user = $this->getUser();

        /** @var StringUtil $stringUtil */
        $stringUtil = $this->framework->getAdapter(StringUtil::class);

        // Add the permissions on group level
        if ('custom' !== $user->inherit) {
            $groups = $this->db->fetchAll('SELECT id, newsAreaOfApplication, newsAreaOfApplication_roots FROM tl_user_group WHERE id IN('.\implode(',', \array_map('intval', $user->groups)).')');

            foreach ($groups as $group) {
                $permissions = $stringUtil->deserialize($group['newsAreaOfApplication'], true);

                if (\in_array('manage', $permissions, true)) {
                    $companyIds = $stringUtil->deserialize($group['newsAreaOfApplication_roots'], true);
                    $companyIds[] = $companyId;

                    $this->db->update('tl_user_group', ['newsAreaOfApplication_roots' => \serialize($companyIds)], ['id' => $group['id']]);
                }
            }
        }

        // Add the permissions on user level
        if ('group' !== $user->inherit) {
            $userData = $this->db->fetchAssoc('SELECT newsAreaOfApplication, newsAreaOfApplication_roots FROM tl_user WHERE id=?', [$user->id]);
            $permissions = $stringUtil->deserialize($userData['newsAreaOfApplication'], true);

            if (\in_array('manage', $permissions, true)) {
                $companyIds = $stringUtil->deserialize($userData['newsAreaOfApplication_roots'], true);
                $companyIds[] = $companyId;

                $this->db->update('tl_user', ['newsAreaOfApplication_roots' => \serialize($companyIds)], ['id' => $user->id]);
            }
        }

        // Add the new element to the user object
        $user->newsAreaOfApplication_roots = \array_merge($roots, [$companyId]);
    }



    /**
     * Add the company to allowed roots.
     *
     * @param int $companyId
     */
    public function addUsageToAllowedRoots($companyId)
    {
        if (null === ($roots = $this->getUserAllowedUsageRoots())) {
            return;
        }

        $companyId = (int) $companyId;
        $user = $this->getUser();

        /** @var StringUtil $stringUtil */
        $stringUtil = $this->framework->getAdapter(StringUtil::class);

        // Add the permissions on group level
        if ('custom' !== $user->inherit) {
            $groups = $this->db->fetchAll('SELECT id, newsUsage, newsUsage_roots FROM tl_user_group WHERE id IN('.\implode(',', \array_map('intval', $user->groups)).')');

            foreach ($groups as $group) {
                $permissions = $stringUtil->deserialize($group['newsUsage'], true);

                if (\in_array('manage', $permissions, true)) {
                    $companyIds = $stringUtil->deserialize($group['newsUsage_roots'], true);
                    $companyIds[] = $companyId;

                    $this->db->update('tl_user_group', ['newsUsage_roots' => \serialize($companyIds)], ['id' => $group['id']]);
                }
            }
        }

        // Add the permissions on user level
        if ('group' !== $user->inherit) {
            $userData = $this->db->fetchAssoc('SELECT newsUsage, newsUsage_roots FROM tl_user WHERE id=?', [$user->id]);
            $permissions = $stringUtil->deserialize($userData['newsUsage'], true);

            if (\in_array('manage', $permissions, true)) {
                $companyIds = $stringUtil->deserialize($userData['newsUsage_roots'], true);
                $companyIds[] = $companyId;

                $this->db->update('tl_user', ['newsUsage_roots' => \serialize($companyIds)], ['id' => $user->id]);
            }
        }

        // Add the new element to the user object
        $user->newsUsage_roots = \array_merge($roots, [$companyId]);
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
        if (null === $this->tokenStorage) {
            throw new \RuntimeException('No token storage provided');
        }

        $token = $this->tokenStorage->getToken();

        if (null === $token) {
            throw new \RuntimeException('No token provided');
        }

        $user = $token->getUser();

        if (!$user instanceof BackendUser) {
            throw new \RuntimeException('The token does not contain a back end user object');
        }

        return $user;
    }
}
