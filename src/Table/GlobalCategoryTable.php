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

namespace IIDO\BasicBundle\Table;


use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Framework\FrameworkAwareInterface;
use Contao\CoreBundle\Framework\FrameworkAwareTrait;
use Contao\DataContainer;
use Contao\Input;
use Contao\StringUtil;
use Doctrine\DBAL\Connection;
use IIDO\BasicBundle\Helper\GlobalCategoriesHelper;
use IIDO\BasicBundle\Model\GlobalCategoryModel;
use IIDO\BasicBundle\Permission\PermissionChecker;
use IIDO\BasicBundle\Widget\MetaWizardWidget;
use Haste\Model\Relations;


class GlobalCategoryTable implements FrameworkAwareInterface
{
    use FrameworkAwareTrait;


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
     * @param ContaoFramework   $framework
     * @param Connection        $db
     * @param PermissionChecker $permissionChecker
     */
    public function __construct(ContaoFramework $framework, Connection $db, PermissionChecker $permissionChecker)
    {
        $this->framework            = $framework;
        $this->db                   = $db;
        $this->permissionChecker    = $permissionChecker;
    }



    /**
     * On data container load. Limit the categories set in the news archive settings.
     *
     * @param DataContainer|MetaWizardWidget $dc
     */
    public function onCategoriesTableLoadCallback( $dc )
    {
        if (!$dc->id)
        {
            return;
        }

        $objGlobalCategories = GlobalCategoryModel::findBy('pid', '0');

        if( !$objGlobalCategories )
        {
            return;
        }

        while( $objGlobalCategories->next() )
        {
            if( $objGlobalCategories->subCategoriesArePages )
            {
                continue;
            }

            $arrTables = StringUtil::deserialize($objGlobalCategories->enableCategoriesIn, TRUE);

            if( count($arrTables) && in_array($dc->table, $arrTables) )
            {
                $GLOBALS['TL_DCA'][ $dc->table ]['fields'][ 'gc_' . $objGlobalCategories->alias ]['eval']['rootNodes'] = [$objGlobalCategories->id];
            }
        }

//        /** @var Input $input */
//        $input = $this->framework->getAdapter(Input::class);

        // Handle the edit all modes differently
//        if ($input->get('act') === 'editAll' || $input->get('act') === 'overrideAll')
//        {
//            $categories = $this->db->fetchColumn('SELECT categories FROM tl_news_archive WHERE limitCategories=1 AND id=?', [$dc->id]);
//        }
//        else
//        {
//            $categories = $this->db->fetchColumn('SELECT categories FROM tl_news_archive WHERE limitCategories=1 AND id=(SELECT pid FROM tl_news WHERE id=?)', [$dc->id]);
//        }

//        if (!$categories || 0 === \count($categories = StringUtil::deserialize($categories, true)))
//        {
//            return;
//        }
    }



    /**
     * On submit record. Update the category relations.
     *
     * @param DataContainer|MetaWizardWidget $dc
     */
    public function onCategoriesTableSubmitCallback( $dc )
    {
        // Return if the user is allowed to assign categories or the record is not new
        if( $this->permissionChecker->canUserAssignCategories( $dc->table ) || $dc->activeRecord->tstamp > 0 )
        {
            return;
        }

//        echo "<pre>"; print_r( $dc->activeRecord ); exit;

//        $dc->field = 'categories';

//        $relations = new Relations();
//        $relations->updateRelatedRecords($this->permissionChecker->getUserDefaultCategories('global'), $dc);

        // Reset back the field property
//        $dc->field = null;
    }



    /**
     * On categories options callback
     *
     * @param DataContainer|MetaWizardWidget $dc
     *
     * @return array
     */
    public function onCategoriesOptionsCallback( $dc )
    {
        /** @var Input $input */
        $input = $this->framework->getAdapter(Input::class);

        // Do not generate the options for other views than listings
        if( $input->get('act') && $input->get('act') !== 'select' )
        {
            return [];
        }

        return $this->generateOptionsRecursively();
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
//        $records = $this->db->fetchAll('SELECT * FROM tl_news_category WHERE pid=? ORDER BY sorting', [$pid]);

//        foreach ($records as $record)
//        {
//            $options[$record['id']] = $prefix . $record['title'];
//
//            foreach ($this->generateOptionsRecursively($record['id'], $record['title'] . ' / ') as $k => $v)
//            {
//                $options[$k] = $v;
//            }
//        }

        return $options;
    }



    public function renderCategoriesField( $dc, $xlabel )
    {
        if( \Input::post("FORM_SUBMIT") )
        {
            GlobalCategoriesHelper::addValueToTable(\Input::post($dc->field), $dc->field, $dc->activeRecord->id, $dc->table );
        }

        $fieldValue = GlobalCategoriesHelper::loadValueFromTable( $dc->field, $dc->activeRecord->id, $dc->table );
        $arrData    = $GLOBALS['TL_DCA'][ $dc->table ]['fields'][ $dc->field ];

        $strClass   = $GLOBALS['BE_FFL'][ $arrData['inputType'] ];
        $objWidget  = new $strClass( $strClass::getAttributesFromDca($arrData, $dc->field, $fieldValue, $dc->field, $dc->table, $dc) );

        $objWidget->xlabel = $xlabel;

        $strHelpClass   = '';
        $updateMode     = '';

        return '
<div' . ($arrData['eval']['tl_class'] ? ' class="widget ' . trim($arrData['eval']['tl_class']) . '"' : '') . '>' . $objWidget->parse() . $updateMode . (!$objWidget->hasErrors() ? $dc->help($strHelpClass) : '') . '
</div>';
    }
}