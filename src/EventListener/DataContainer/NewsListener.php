<?php
/*******************************************************************
 * (c) 2020 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\EventListener\DataContainer;


use Codefog\NewsCategoriesBundle\Criteria\NewsCriteria;
use Codefog\NewsCategoriesBundle\Model\NewsCategoryModel;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\DataContainer;
use Haste\Model\Model;
use Contao\Model\Collection;
use Contao\Module;
use Contao\NewsModel;
use Contao\System;
use Contao\Input;
use Contao\CoreBundle\Framework\FrameworkAwareInterface;
use Contao\CoreBundle\Framework\FrameworkAwareTrait;
use Doctrine\DBAL\Connection;
use IIDO\BasicBundle\Helper\BasicHelper;
use Terminal42\ServiceAnnotationBundle\ServiceAnnotationInterface;
use IIDO\BasicBundle\Permission\NewsPermissionChecker as PermissionChecker;


class NewsListener implements FrameworkAwareInterface, ServiceAnnotationInterface
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



    /**
     * @Hook("newsListFetchItems", priority=10)
     */
    public function onNewsListFetchItems( array $newsArchives, ?bool $featuredOnly, int $limit, int $offset, Module $module )
    {

        // check if news archives are defined
        if( !$newsArchives )
        {
            return null;
        }

        // support for news_categories
        if( !BasicHelper::isActiveBundle('codefog/contao-news_categories') )
        {
            return false;
        }

        $module->news_order = $module->news_order ?: $module->news_sorting;

//        if( $module->news_order !== 'order_categoryGroup' )
//        {
//            return false;
//        }

        $objCategories  = NewsCategoryModel::findPublishedByPid( 1 ); //TODO: im backend verwaltbar machen
        $arrNews        = [];
        $arrShown       = [];

        if( $objCategories )
        {
            while( $objCategories->next() )
            {
                $objCategory = $objCategories->current();
//                $objCategory->isHeader = true;

                /** @var Model $model */
                $model = $this->framework->getAdapter(Model::class);

                $newsIds = $model->getReferenceValues('tl_news', 'categories', [$objCategory->id]);
                $newsIds = $this->parseIds($newsIds);

                if (0 === \count($newsIds))
                {
                    continue;
                }
//                echo "<pre>"; print_r( $objCategory->title );
//                echo "<br>"; print_r( $newsIds ); echo "</pre>";

//                if( $newsIds && count($newsIds) )
//                {
//                    $arrNews[] = $objCategory;

                //TODO: save news in array / keine weitere datenbankabfrage für die selbe news!!
                    $i = 0;
                    foreach( $newsIds as $newsId )
                    {
                        $objNews = NewsModel::findByPk( $newsId );
                        $objNews = clone $objNews;

                        if( $i === 0 )
                        {
                            $objNews->hasHeader = true;
                            $objNews->headerCategory = $objCategory;
                        }
                        else
                        {
                            $objNews->hasHeader = false;
                        }

                        $arrShown[] = $newsId;
                        $arrNews[] = $objNews;

                        $i++;
                    }
//                }
//echo "<pre>"; print_r( $newsIds ); exit;
//                $criteria = new NewsCriteria( $this->framework );

//                $criteria->setBasicCriteria($newsArchives, $module->news_order, $featuredOnly);
//                $criteria->setDefaultCategories([$objCategory->id]);

//                $arrCatNews = $criteria->getNewsModelAdapter()->findBy(
//                    $criteria->getColumns(),
//                    $criteria->getValues(),
//                    $criteria->getOptions()
//                );

//                if( $arrCatNews )
//                {
//                    $arrNews[] = $objCategory;
//
//                    while( $arrCatNews->next() )
//                    {
//                        $arrNews[] = $arrCatNews->current();
//                    }
//                }
            }
        }
//        echo "<pre>"; print_r( $arrNews ); exit;

        if( count($arrNews) )
        {
            $collection = new Collection( $arrNews, 'tl_news' );

//            echo "<pre>"; print_r( count($arrNews) );
//            echo "<br>"; print_r( $collection->count() );
//            exit;

            return $collection;
        }

        return false;
    }



    /**
     * Parse the record IDs.
     *
     * @param array $ids
     *
     * @return array
     */
    private function parseIds(array $ids)
    {
        $ids = \array_map('intval', $ids);
        $ids = \array_filter($ids);
        $ids = \array_unique($ids);

        return \array_values($ids);
    }
}
