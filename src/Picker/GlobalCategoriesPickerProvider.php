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

namespace IIDO\BasicBundle\Picker;


use IIDO\BasicBundle\Permission\PermissionChecker;
use Contao\CoreBundle\Picker\AbstractPickerProvider;
use Contao\CoreBundle\Picker\DcaPickerProviderInterface;
use Contao\CoreBundle\Picker\PickerConfig;


class GlobalCategoriesPickerProvider extends AbstractPickerProvider implements DcaPickerProviderInterface
{
    /**
     * @var PermissionChecker
     */
    private $permissionChecker;



    protected $strDoRoute           = ''; //'prestepProducts';

    protected $userCategoryManage   = 'global'; //'products';

    protected $userCategoryTable    = ''; //'tl_prestep_product';



    /**
     * @param PermissionChecker $permissionChecker
     */
    public function setPermissionChecker(PermissionChecker $permissionChecker)
    {
        $this->permissionChecker = $permissionChecker;
    }



    /**
     * {@inheritdoc}
     */
    public function getDcaTable()
    {
        return 'tl_iido_global_category';
    }



    /**
     * {@inheritdoc}
     */
    public function getDcaAttributes(PickerConfig $config)
    {
        $attributes = ['fieldType' => 'checkbox'];

        if ($fieldType = $config->getExtra('fieldType'))
        {
            $attributes['fieldType'] = $fieldType;
        }

        if ($this->supportsValue($config))
        {
            $attributes['value'] = \array_map('intval', \explode(',', $config->getValue()));
        }

        if (\is_array($rootNodes = $config->getExtra('rootNodes')))
        {
            $attributes['rootNodes'] = $rootNodes;
        }

        return $attributes;
    }



    /**
     * {@inheritdoc}
     */
    public function getUrl(PickerConfig $config)
    {
        // Set the news categories root in session for further reference in onload_callback (see #137)
        if (\is_array($rootNodes = $config->getExtra('rootNodes')))
        {
            $_SESSION['IIDO_GLOBAL_CATEGORIES_ROOT'] = $rootNodes;
        }
        else
        {
            unset($_SESSION['IIDO_GLOBAL_CATEGORIES_ROOT']);
        }

        return parent::getUrl($config);
    }



    /**
     * {@inheritdoc}
     */
    public function convertDcaValue(PickerConfig $config, $value)
    {
        return (int) $value;
    }



    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'globalCategoriesPicker';
    }



    /**
     * {@inheritdoc}
     */
    public function supportsContext($context)
    {
        if ($this->permissionChecker === null)
        {
            return false;
        }

        return 'globalCategories' === $context && ($this->permissionChecker->canUserManageCategories( $this->userCategoryManage ) || $this->permissionChecker->canUserAssignCategories( $this->userCategoryTable ));
    }



    /**
     * {@inheritdoc}
     */
    public function supportsValue(PickerConfig $config)
    {
        foreach (\explode(',', $config->getValue()) as $id)
        {
            if (!\is_numeric($id))
            {
                return false;
            }
        }

        return true;
    }



    /**
     * {@inheritdoc}
     */
    protected function getRouteParameters(PickerConfig $config = null)
    {
//        return ['do' => 'iidoWebsiteConfig', 'table' => $this->getDcaTable(), 'config'=>'global_categories'];
        return ['do' => 'iidoWebsiteConfig', 'config'=>'global_categories'];
    }
}