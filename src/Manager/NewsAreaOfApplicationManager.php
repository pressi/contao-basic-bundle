<?php


namespace IIDO\BasicBundle\Manager;

use IIDO\BasicBundle\Model\NewsAreaOfApplicationModel;
use Contao\CoreBundle\Framework\FrameworkAwareInterface;
use Contao\CoreBundle\Framework\FrameworkAwareTrait;
use Contao\Database;
use Contao\Module;
use Contao\ModuleNewsArchive;
use Contao\ModuleNewsList;
use Contao\ModuleNewsReader;
use Contao\PageModel;
use Terminal42\DcMultilingualBundle\Model\Multilingual;

class NewsAreaOfApplicationManager implements FrameworkAwareInterface
{
    use FrameworkAwareTrait;

    /**
     * Generate the company URL.
     *
     * @param NewsAreaOfApplicationModel $company
     * @param PageModel         $page
     * @param bool              $absolute
     *
     * @return string
     */
    public function generateUrl(NewsAreaOfApplicationModel $company, PageModel $page, $absolute = false)
    {
        $page->loadDetails();

        $params = '/'.$this->getParameterName($page->rootId).'/'.$this->getCompanyAlias($company, $page);

        return $absolute ? $page->getAbsoluteUrl($params) : $page->getFrontendUrl($params);
    }

    /**
     * Get the image.
     *
     * @param NewsAreaOfApplicationModel $company
     *
     * @return \Contao\FilesModel|null
     */
    public function getImage(NewsAreaOfApplicationModel $company)
    {
        if (null === ($image = $company->getImage()) || !\is_file(TL_ROOT.'/'.$image->path)) {
            return null;
        }

        return $image;
    }

    /**
     * Get the map image.
     *
     * @param NewsAreaOfApplicationModel $company
     *
     * @return \Contao\FilesModel|null
     */
    public function getMapImage(NewsAreaOfApplicationModel $company)
    {
        if (null === ($image = $company->getMapImage()) || !\is_file(TL_ROOT.'/'.$image->path)) {
            return null;
        }

        return $image;
    }

    /**
     * Get the company alias
     *
     * @param NewsAreaOfApplicationModel $company
     * @param PageModel         $page
     *
     * @return string
     */
    public function getCompanyAlias(NewsAreaOfApplicationModel $company, PageModel $page)
    {
        if ($company instanceof Multilingual) {
            return $company->getAlias($page->language);
        }

        return $company->alias;
    }

    /**
     * Get the parameter name.
     *
     * @param int|null $rootId
     *
     * @return string
     */
    public function getParameterName($rootId = null)
    {
        $rootId = $rootId ?: $GLOBALS['objPage']->rootId;

        if (!$rootId || null === ($rootPage = PageModel::findByPk($rootId))) {
            return '';
        }

        return $rootPage->newsAreaOfApplication_param ?: 'areaOfApplication';
    }

    /**
     * Get the company target page.
     *
     * @param NewsAreaOfApplicationModel $company
     *
     * @return PageModel|null
     */
    public function getTargetPage(NewsAreaOfApplicationModel $company)
    {
        $pageId = $company->jumpTo;

        // Inherit the page from parent if there is none set
        if (!$pageId) {
            $pid = $company->pid;

            do {
                /** @var NewsAreaOfApplicationModel $parent */
                $parent = $company->findByPk($pid);

                if (null !== $parent) {
                    $pid = $parent->pid;
                    $pageId = $parent->jumpTo;
                }
            } while ($pid && !$pageId);
        }

        // Get the page model
        if ($pageId) {
            /** @var PageModel $pageAdapter */
            $pageAdapter = $this->framework->getAdapter(PageModel::class);

            return $pageAdapter->findPublishedById($pageId);
        }

        return null;
    }

    /**
     * Get the company trail IDs.
     *
     * @param NewsAreaOfApplicationModel $company
     *
     * @return array
     */
    public function getTrailIds(NewsAreaOfApplicationModel $company)
    {
        static $ids;

        if (!\is_array($ids)) {
            /** @var Database $db */
            $db = $this->framework->createInstance(Database::class);

            $ids = $db->getParentRecords($company->id, $company->getTable());
            $ids = \array_map('intval', \array_unique($ids));

            // Remove the current company
            unset($ids[\array_search($company->id, $ids, true)]);
        }

        return $ids;
    }

    /**
     * Return true if the company is visible for module.
     *
     * @param NewsAreaOfApplicationModel $company
     * @param Module            $module
     *
     * @return bool
     */
    public function isVisibleForModule(NewsAreaOfApplicationModel $company, Module $module)
    {
        // List or archive module
        if ($company->hideInList && ($module instanceof ModuleNewsList || $module instanceof ModuleNewsArchive)) {
            return false;
        }

        // Reader module
        if ($company->hideInReader && $module instanceof ModuleNewsReader) {
            return false;
        }

        return true;
    }
}
