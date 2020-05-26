<?php

/*
 * News Categories bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2017, Codefog
 * @author     Codefog <https://codefog.pl>
 * @license    MIT
 */

namespace IIDO\BasicBundle\EventListener\DataContainer;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\DataContainer;
use Contao\NewsArchiveModel;
use Contao\System;
use IIDO\BasicBundle\Permission\NewsPermissionChecker as PermissionChecker;
use Contao\CoreBundle\Framework\FrameworkAwareInterface;
use Contao\CoreBundle\Framework\FrameworkAwareTrait;
use Contao\Input;
use Doctrine\DBAL\Connection;

class NewsListener implements FrameworkAwareInterface
{
    use FrameworkAwareTrait;


    /**
     * Framework Object
     *
     * @var ContaoFramework
     */
    protected $framework;


    /**
     * @var Connection
     */
    private $db;


    /**
     * @var PermissionChecker
     */
    private $permissionChecker;

    /**
     * NewsListener constructor.
     *
     * @param Connection        $db
     * @param PermissionChecker $permissionChecker
     * @param ContaoFramework $framework
     */
    public function __construct(Connection $db, PermissionChecker $permissionChecker, ContaoFramework $framework)
    {
        $this->db = $db;
        $this->permissionChecker = $permissionChecker;
        $this->framework = $framework;
    }



    /**
     * On categories options callback
     *
     * @return array
     */
    public function onAreaOfApplicationOptionsCallback()
    {
        /** @var Input $input */
        $input = $this->framework->getAdapter(Input::class);

        // Do not generate the options for other views than listings
        if ($input->get('act') && $input->get('act') !== 'select') {
            return [];
        }

        return $this->generateOptionsRecursively();
    }



    /**
     * On categories options callback
     *
     * @return array
     */
    public function onUsageOptionsCallback()
    {
        /** @var Input $input */
        $input = $this->framework->getAdapter(Input::class);

        // Do not generate the options for other views than listings
        if ($input->get('act') && $input->get('act') !== 'select') {
            return [];
        }

        return $this->generateUsageOptionsRecursively();
    }



    /**
     * Generate the options recursively
     *
     * @param int    $pid
     * @param string $prefix
     *
     * @return array
     */
    private function generateOptionsRecursively($pid = 0, $prefix = '')
    {
        $options = [];
        $records = $this->db->fetchAll('SELECT * FROM tl_news_areaOfApplication WHERE pid=? ORDER BY sorting', [$pid]);

        foreach ($records as $record) {
            $options[$record['id']] = $prefix . $record['title'];

            foreach ($this->generateOptionsRecursively($record['id'], $record['title'] . ' / ') as $k => $v) {
                $options[$k] = $v;
            }
        }

        return $options;
    }



    /**
     * Generate the options recursively
     *
     * @param int    $pid
     * @param string $prefix
     *
     * @return array
     */
    private function generateUsageOptionsRecursively($pid = 0, $prefix = '')
    {
        $options = [];
        $records = $this->db->fetchAll('SELECT * FROM tl_news_usage WHERE pid=? ORDER BY sorting', [$pid]);

        foreach ($records as $record) {
            $options[$record['id']] = $prefix . $record['title'];

            foreach ($this->generateOptionsRecursively($record['id'], $record['title'] . ' / ') as $k => $v) {
                $options[$k] = $v;
            }
        }

        return $options;
    }



    /**
     * Auto-generate the news alias if it has not been set yet
     *
     * @param mixed                $varValue
     * @param DataContainer $dc
     *
     * @return string
     *
     * @throws \Exception
     */
    public function generateAliasAreaOfApplication($varValue, DataContainer $dc)
    {
        $aliasExists = function (string $alias) use ($dc): bool
        {
            return $this->db->prepare("SELECT id FROM tl_news_areaOfApplication WHERE alias=? AND id!=?")->execute($alias, $dc->id)->numRows > 0;
        };

        // Generate alias if there is none
        if ($varValue == '')
        {
//            $varValue = System::getContainer()->get('contao.slug')->generate($dc->activeRecord->headline, NewsArchiveModel::findByPk($dc->activeRecord->pid)->jumpTo, $aliasExists);
            $varValue = System::getContainer()->get('contao.slug')->generate($dc->activeRecord->title, $dc->activeRecord, $aliasExists);
        }
        elseif ($aliasExists($varValue))
        {
            throw new \Exception(sprintf($GLOBALS['TL_LANG']['ERR']['aliasExists'], $varValue));
        }

        return $varValue;
    }



    /**
     * Auto-generate the news alias if it has not been set yet
     *
     * @param mixed                $varValue
     * @param DataContainer $dc
     *
     * @return string
     *
     * @throws \Exception
     */
    public function generateAliasUsage($varValue, DataContainer $dc)
    {
        $aliasExists = function (string $alias) use ($dc): bool
        {
            return $this->db->prepare("SELECT id FROM tl_news_usage WHERE alias=? AND id!=?")->execute($alias, $dc->id)->numRows > 0;
        };

        // Generate alias if there is none
        if ($varValue == '')
        {
//            $varValue = System::getContainer()->get('contao.slug')->generate($dc->activeRecord->headline, NewsArchiveModel::findByPk($dc->activeRecord->pid)->jumpTo, $aliasExists);
            $varValue = System::getContainer()->get('contao.slug')->generate($dc->activeRecord->title, $dc->activeRecord, $aliasExists);
        }
        elseif ($aliasExists($varValue))
        {
            throw new \Exception(sprintf($GLOBALS['TL_LANG']['ERR']['aliasExists'], $varValue));
        }

        return $varValue;
    }
}
