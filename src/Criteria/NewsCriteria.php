<?php
/*******************************************************************
 * (c) 2020 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\Criteria;


use Codefog\NewsCategoriesBundle\Exception\NoNewsException;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\Database;
use Contao\Date;
use Contao\NewsModel;
use Haste\Model\Model;
use IIDO\BasicBundle\Model\NewsAreaOfApplicationModel;
use IIDO\BasicBundle\Model\NewsUsageModel;


class NewsCriteria
{
    /**
     * @var ContaoFrameworkInterface
     */
    private $framework;

    /**
     * @var array
     */
    private $columns = [];

    /**
     * @var array
     */
    private $values = [];

    /**
     * @var array
     */
    private $options = [];

    /**
     * NewsCriteria constructor.
     *
     * @param ContaoFrameworkInterface $framework
     */
    public function __construct(ContaoFrameworkInterface $framework)
    {
        $this->framework = $framework;
    }

    /**
     * Set the basic criteria.
     *
     * @param array  $archives
     * @param string $sorting
     *
     * @throws NoNewsException
     */
    public function setBasicCriteria(array $archives, $sorting = null, $featured = null)
    {
        $archives = $this->parseIds($archives);

        if (0 === \count($archives)) {
            throw new NoNewsException();
        }

        $t = $this->getNewsModelAdapter()->getTable();

        $this->columns[] = "$t.pid IN(".\implode(',', \array_map('intval', $archives)).')';

        $order = '';

        if ('featured_first' === $featured) {
            $order .= "$t.featured DESC, ";
        }

        // Set the sorting
        switch ($sorting) {
            case 'order_headline_asc':
                $order .= "$t.headline";
                break;
            case 'order_headline_desc':
                $order .= "$t.headline DESC";
                break;
            case 'order_random':
                $order .= 'RAND()';
                break;
            case 'order_date_asc':
                $order .= "$t.date";
                break;
            default:
                $order .= "$t.date DESC";
                break;
        }

        $this->options['order'] = $order;

        // Never return unpublished elements in the back end, so they don't end up in the RSS feed
        if (!BE_USER_LOGGED_IN || TL_MODE === 'BE') {
            /** @var Date $dateAdapter */
            $dateAdapter = $this->framework->getAdapter(Date::class);

            $time = $dateAdapter->floorToMinute();
            $this->columns[] = "($t.start=? OR $t.start<=?) AND ($t.stop=? OR $t.stop>?) AND $t.published=?";
            $this->values = \array_merge($this->values, ['', $time, '', ($time + 60), 1]);
        }
    }

    /**
     * Set the features items.
     *
     * @param bool $enable
     */
    public function setFeatured($enable)
    {
        $t = $this->getNewsModelAdapter()->getTable();

        if (true === $enable) {
            $this->columns[] = "$t.featured=?";
            $this->values[] = 1;
        } elseif (false === $enable) {
            $this->columns[] = "$t.featured=?";
            $this->values[] = '';
        }
    }

    /**
     * Set the time frame.
     *
     * @param int $begin
     * @param int $end
     */
    public function setTimeFrame($begin, $end)
    {
        $t = $this->getNewsModelAdapter()->getTable();

        $this->columns[] = "$t.date>=? AND $t.date<=?";
        $this->values[] = $begin;
        $this->values[] = $end;
    }



    /**
     * Set the default categories.
     *
     * @param array       $defaultAreasOfApplication
     * @param bool        $includeSubAreasOfApplication
     * @param string|null $order
     *
     * @throws NoNewsException
     */
    public function setDefaultAreasOfApplication(array $defaultAreasOfApplication, $includeSubAreasOfApplication = true, $order = null)
    {
        $defaultAreasOfApplication = $this->parseIds($defaultAreasOfApplication);

        if (0 === \count($defaultAreasOfApplication)) {
            throw new NoNewsException();
        }

        // Include the subcategories
        if ($includeSubAreasOfApplication) {
            /** @var NewsAreaOfApplicationModel $newsCategoryModel */
            $newsCategoryModel = $this->framework->getAdapter(NewsAreaOfApplicationModel::class);
            $defaultAreasOfApplication = $newsCategoryModel->getAllSubAreaOfApplicationIds($defaultAreasOfApplication);
        }

        /** @var Model $model */
        $model = $this->framework->getAdapter(Model::class);

        $newsIds = $model->getReferenceValues('tl_news', 'areaOfApplication', $defaultAreasOfApplication);
        $newsIds = $this->parseIds($newsIds);

        if (0 === \count($newsIds)) {
            throw new NoNewsException();
        }

        $t = $this->getNewsModelAdapter()->getTable();

        $this->columns['defaultAreasOfApplication'] = "$t.id IN(".\implode(',', $newsIds).')';

        // Order news items by best match
        if ($order === 'best_match') {
            $mapper = [];

            // Build the mapper
            foreach (array_unique($newsIds) as $newsId) {
                $mapper[$newsId] = count(array_intersect($defaultAreasOfApplication, array_unique($model->getRelatedValues($t, 'areasOfApplication', $newsId))));
            }

            arsort($mapper);

            $this->options['order'] = Database::getInstance()->findInSet("$t.id", array_keys($mapper));
        }
    }



    /**
     * Set the default categories.
     *
     * @param array       $defaultUsage
     * @param bool        $includeSubUses
     * @param string|null $order
     *
     * @throws NoNewsException
     */
    public function setDefaultUsage(array $defaultUsage, $includeSubUses = true, $order = null)
    {
        $defaultUsage = $this->parseIds($defaultUsage);

        if (0 === \count($defaultUsage)) {
            throw new NoNewsException();
        }

        // Include the subcategories
        if ($includeSubUses) {
            /** @var NewsUsageModel $newsCategoryModel */
            $newsCategoryModel = $this->framework->getAdapter(NewsUsageModel::class);
            $defaultAreasOfApplication = $newsCategoryModel->getAllSubUsageIds($defaultUsage);
        }

        /** @var Model $model */
        $model = $this->framework->getAdapter(Model::class);

        $newsIds = $model->getReferenceValues('tl_news', 'usage', $defaultUsage);
        $newsIds = $this->parseIds($newsIds);

        if (0 === \count($newsIds)) {
            throw new NoNewsException();
        }

        $t = $this->getNewsModelAdapter()->getTable();

        $this->columns['defaultUsage'] = "$t.id IN(".\implode(',', $newsIds).')';

        // Order news items by best match
        if ($order === 'best_match') {
            $mapper = [];

            // Build the mapper
            foreach (array_unique($newsIds) as $newsId) {
                $mapper[$newsId] = count(array_intersect($defaultUsage, array_unique($model->getRelatedValues($t, 'usage', $newsId))));
            }

            arsort($mapper);

            $this->options['order'] = Database::getInstance()->findInSet("$t.id", array_keys($mapper));
        }
    }



    /**
     * Set the category.
     *
     * @param int  $category
     * @param bool $preserveDefault
     * @param bool $includeSubcategories
     *
     * @return NoNewsException
     */
    public function setAreaOfApplication($category, $preserveDefault = false, $includeSubAreasOfApplication = false)
    {
        /** @var Model $model */
        $model = $this->framework->getAdapter(Model::class);

        // Include the subcategories
        if ($includeSubAreasOfApplication) {
            /** @var NewsAreaOfApplicationModel $newsCategoryModel */
            $newsCategoryModel = $this->framework->getAdapter(NewsAreaOfApplicationModel::class);
            $category = $newsCategoryModel->getAllSubAreaOfApplicationIds($category);
        }

        $newsIds = $model->getReferenceValues('tl_news', 'areasOfApplication', $category);
        $newsIds = $this->parseIds($newsIds);

        if (0 === \count($newsIds)) {
            throw new NoNewsException();
        }

        // Do not preserve the default categories
        if (!$preserveDefault) {
            unset($this->columns['defaultAreasOfApplication']);
        }

        $t = $this->getNewsModelAdapter()->getTable();

        $this->columns[] = "$t.id IN(".\implode(',', $newsIds).')';
    }



    /**
     * Set the category.
     *
     * @param int  $category
     * @param bool $preserveDefault
     * @param bool $includeSubcategories
     *
     * @return NoNewsException
     */
    public function setUsage($category, $preserveDefault = false, $includeSubAreasOfApplication = false)
    {
        /** @var Model $model */
        $model = $this->framework->getAdapter(Model::class);

        // Include the subcategories
        if ($includeSubAreasOfApplication) {
            /** @var NewsUsageModel $newsCategoryModel */
            $newsCategoryModel = $this->framework->getAdapter(NewsUsageModel::class);
            $category = $newsCategoryModel->getAllSubUsageIds($category);
        }

        $newsIds = $model->getReferenceValues('tl_news', 'uses', $category);
        $newsIds = $this->parseIds($newsIds);

        if (0 === \count($newsIds)) {
            throw new NoNewsException();
        }

        // Do not preserve the default categories
        if (!$preserveDefault) {
            unset($this->columns['defaultUsage']);
        }

        $t = $this->getNewsModelAdapter()->getTable();

        $this->columns[] = "$t.id IN(".\implode(',', $newsIds).')';
    }



    /**
     * Set the excluded news IDs.
     *
     * @param array $newsIds
     */
    public function setExcludedNews(array $newsIds)
    {
        $newsIds = $this->parseIds($newsIds);

        if (0 === \count($newsIds)) {
            throw new NoNewsException();
        }

        $t = $this->getNewsModelAdapter()->getTable();

        $this->columns[] = "$t.id NOT IN (".\implode(',', $newsIds).')';
    }



    /**
     * Set the limit.
     *
     * @param int $limit
     */
    public function setLimit($limit)
    {
        $this->options['limit'] = $limit;
    }



    /**
     * Set the offset.
     *
     * @param int $offset
     */
    public function setOffset($offset)
    {
        $this->options['offset'] = $offset;
    }



    /**
     * @return array
     */
    public function getColumns()
    {
        return $this->columns;
    }



    /**
     * @return array
     */
    public function getValues()
    {
        return $this->values;
    }



    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }



    /**
     * Get the news model adapter.
     *
     * @return NewsModel
     */
    public function getNewsModelAdapter()
    {
        /** @var NewsModel $adapter */
        $adapter = $this->framework->getAdapter(NewsModel::class);

        return $adapter;
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
