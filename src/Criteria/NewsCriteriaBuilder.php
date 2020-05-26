<?php
/*******************************************************************
 * (c) 2020 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\Criteria;


use IIDO\BasicBundle\Exception\AreaOfApplicationNotFoundException;
use Codefog\NewsCategoriesBundle\Exception\NoNewsException;
use Codefog\NewsCategoriesBundle\FrontendModule\CumulativeFilterModule;
use IIDO\BasicBundle\Model\NewsAreaOfApplicationModel;
use IIDO\BasicBundle\Manager\NewsAreaOfApplicationManager;
use Contao\CoreBundle\Framework\FrameworkAwareInterface;
use Contao\CoreBundle\Framework\FrameworkAwareTrait;
use Contao\Database;
use Contao\Input;
use Contao\Module;
use Contao\StringUtil;
use Doctrine\DBAL\Connection;
use Haste\Model\Model;


class NewsCriteriaBuilder implements FrameworkAwareInterface
{
    use FrameworkAwareTrait;

    /**
     * @var Connection
     */
    private $db;

    /**
     * @var NewsAreaOfApplicationManager
     */
    private $manager;

    /**
     * NewsCriteriaBuilder constructor.
     *
     * @param Connection            $db
     * @param NewsAreaOfApplicationManager $manager
     */
    public function __construct(Connection $db, NewsAreaOfApplicationManager $manager)
    {
        $this->db = $db;
        $this->manager = $manager;
    }

    /**
     * Get the criteria for archive module.
     *
     * @param array  $archives
     * @param int    $begin
     * @param int    $end
     * @param Module $module
     *
     * @return NewsCriteria|null
     */
    public function getCriteriaForArchiveModule(array $archives, $begin, $end, Module $module)
    {
        $criteria = new NewsCriteria($this->framework);

        try {
            $criteria->setBasicCriteria($archives, $module->news_order);

            // Set the time frame
            $criteria->setTimeFrame($begin, $end);

            // Set the regular list criteria
            $this->setRegularListCriteria($criteria, $module);
        } catch (NoNewsException $e) {
            return null;
        }

        return $criteria;
    }

    /**
     * Get the criteria for list module.
     *
     * @param array     $archives
     * @param bool|null $featured
     * @param Module    $module
     *
     * @return NewsCriteria|null
     */
    public function getCriteriaForListModule(array $archives, $featured, Module $module)
    {
        $criteria = new NewsCriteria($this->framework);

        try {
            $criteria->setBasicCriteria($archives, $module->news_order, $module->news_featured);

            // Set the featured filter
            if (null !== $featured) {
                $criteria->setFeatured($featured);
            }

            // Set the criteria for related categories
            if ($module->news_relatedAreasOfApplication) {
                $this->setRelatedListCriteria($criteria, $module);
            } else {
                // Set the regular list criteria
                $this->setRegularListCriteria($criteria, $module);
            }
        } catch (NoNewsException $e) {
            return null;
        }

        return $criteria;
    }

    /**
     * Get the criteria for menu module.
     *
     * @param array  $archives
     * @param Module $module
     *
     * @return NewsCriteria|null
     */
    public function getCriteriaForMenuModule(array $archives, Module $module)
    {
        $criteria = new NewsCriteria($this->framework);

        try {
            $criteria->setBasicCriteria($archives, $module->news_order);

            // Set the regular list criteria
            $this->setRegularListCriteria($criteria, $module);
        } catch (NoNewsException $e) {
            return null;
        }

        return $criteria;
    }

    /**
     * Set the regular list criteria.
     *
     * @param NewsCriteria $criteria
     * @param Module       $module
     *
     * @throws AreaOfApplicationNotFoundException
     * @throws NoNewsException
     */
    private function setRegularListCriteria(NewsCriteria $criteria, Module $module)
    {
        // Filter by default categories
        if (\count($default = StringUtil::deserialize($module->news_filterDefault, true)) > 0) {
            $criteria->setDefaultAreaOfApplication($default);
        }

        // Filter by multiple active categories
        if ($module->news_filterAreasOfApplicationCumulative) {
            /** @var Input $input */
            $input = $this->framework->getAdapter(Input::class);
            $param = $this->manager->getParameterName();

            if ($aliases = $input->get($param)) {
                $aliases = StringUtil::trimsplit(CumulativeFilterModule::getAreaOfApplicationSeparator(), $aliases);
                $aliases = array_unique(array_filter($aliases));

                if (count($aliases) > 0) {
                    /** @var NewsAreaOfApplicationModel $model */
                    $model = $this->framework->getAdapter(NewsAreaOfApplicationModel::class);

                    foreach ($aliases as $alias) {
                        // Return null if the category does not exist
                        if (null === ($category = $model->findPublishedByIdOrAlias($alias))) {
                            throw new AreaOfApplicationNotFoundException(sprintf('News Area Of Application "%s" was not found', $alias));
                        }

                        $criteria->setAreaOfApplication($category->id, (bool) $module->news_filterPreserve, (bool) $module->news_includeSubAreasOfApplication);
                    }
                }
            }

            return;
        }

        // Filter by active category
        if ($module->news_filterAreasOfApplication) {
            /** @var Input $input */
            $input = $this->framework->getAdapter(Input::class);
            $param = $this->manager->getParameterName();

            if ($alias = $input->get($param)) {
                /** @var NewsAreaOfApplicationModel $model */
                $model = $this->framework->getAdapter(NewsAreaOfApplicationModel::class);

                // Return null if the category does not exist
                if (null === ($category = $model->findPublishedByIdOrAlias($alias))) {
                    throw new AreaOfApplicationNotFoundException(sprintf('News Area Of Application "%s" was not found', $alias));
                }

                $criteria->setAreaOfApplication($category->id, (bool) $module->news_filterPreserve, (bool) $module->news_includeSubAreasOfApplication);
            }
        }
    }

    /**
     * Set the related list criteria.
     *
     * @param NewsCriteria $criteria
     * @param Module       $module
     *
     * @throws NoNewsException
     */
    private function setRelatedListCriteria(NewsCriteria $criteria, Module $module)
    {
        if (null === ($news = $module->currentNews)) {
            throw new NoNewsException();
        }

        /** @var Model $adapter */
        $adapter = $this->framework->getAdapter(Model::class);
        $areasOfApplication = \array_unique($adapter->getRelatedValues($news->getTable(), 'areasOfApplication', $news->id));

        // This news has no news categories assigned
        if (0 === \count($areasOfApplication)) {
            throw new NoNewsException();
        }

        $areasOfApplication = \array_map('intval', $areasOfApplication);
        $excluded = $this->db->fetchAll('SELECT id FROM tl_news_areaOfApplication WHERE excludeInRelated=1');

        // Exclude the categories
        foreach ($excluded as $areaOfApplication) {
            if (false !== ($index = \array_search((int) $areaOfApplication['id'], $areasOfApplication, true))) {
                unset($areaOfApplication[$index]);
            }
        }

        // Exclude categories by root
        if ($module->news_areasOfApplicationRoot > 0) {
            $areasOfApplication = array_intersect($areasOfApplication, NewsAreaOfApplicationModel::getAllSubAreasOfApplicationIds($module->news_areasOfApplicationRoot));
        }

        // There are no categories left
        if (0 === \count($areasOfApplication)) {
            throw new NoNewsException();
        }

        $criteria->setDefaultAreasOfApplication($areasOfApplication, (bool) $module->news_includeSubAreasOfApplication, $module->news_relatedAreasOfApplicationOrder);
        $criteria->setExcludedNews([$news->id]);
    }
}
