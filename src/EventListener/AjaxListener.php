<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\EventListener;


use Contao\CoreBundle\Exception\ResponseException;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Monolog\ContaoContext;
use Contao\System;
use Contao\DataContainer;
use Contao\Input;
use Contao\Database;
use IIDO\BasicBundle\Model\GlobalCategoryModel;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;


/**
 * Class Page Hook
 *
 * @package IIDO\Customize\Hook
 */
class AjaxListener
{

    /**
     * @var LoggerInterface
     */
    private $logger;



    protected $framework;



    /**
     * AjaxListener constructor.
     *
     * @param ContaoFramework $framework
     * @param LoggerInterface $logger
     */
    public function __construct(ContaoFramework $framework, LoggerInterface $logger)
    {
        $this->framework    = $framework;
        $this->logger       = $logger;
    }



    public function onExecutePostActions( $strAction, $dc )
    {
        if( 'reloadGlobalCategoriesWidget' === $strAction )
        {
            $this->reloadGlobalCategoriesWidget( $dc );
        }

//        if( $strAction === "toggleFeatured" )
//        {
//            \Controller::log('JEP', __FUNCTION__, \Monolog\Logger::INFO);
//        }
    }



    public function onExecutePreActions( $strAction )
    {
        if( $strAction === "toggleFeatured" )
        {
            $objProductTable = System::importStatic('prestep.products.table.product'); // TODO: dynamic class!!!
            $objProductTable->toggleFeatured(\Input::post('id'), ((\Input::post('state') == 1) ? true : false));
        }
    }



    /**
     * Reload the products categories widget.
     *
     * @param DataContainer $dc
     */
    private function reloadGlobalCategoriesWidget(DataContainer $dc)
    {
        /**
         * @var Database
         * @var Input    $input
         */
        $db     = $this->framework->createInstance(Database::class);
        $input  = $this->framework->getAdapter(Input::class);
        $id     = $input->get('id');
        $field  = $dc->inputName = $input->post('name');

        // Handle the keys in "edit multiple" mode
        if ('editAll' === $input->get('act'))
        {
            $id     = \preg_replace('/.*_([0-9a-zA-Z]+)$/', '$1', $field);
            $field  = \preg_replace('/(.*)_[0-9a-zA-Z]+$/', '$1', $field);
        }

        $dc->field = $field;

        if( $dc->table === 'tl_files' )
        {
            $strAlias   = preg_replace('/^gc_/', '', $field);
            $strAlias   = preg_replace('/_([0-9a-z]{1,})$/', '', $strAlias);

            $objGC      = GlobalCategoryModel::findOneBy('alias', $strAlias);

//            if( $objGC->subCategoriesArePages )
//            {
//                $GLOBALS['TL_DCA'][ $dc->table ]['fields'][ $field ] = array
//                (
//                    'label'             => [$objGC->title, ''],
//                    'inputType'         => 'pageTree',
//                    'eval'              => array('fieldType'=>'checkbox','multiple'=>true, 'tl_class'=>'w50 hauto'),
//                    'input_field_callback' => array('iido_basic.table.global_category', 'renderCategoriesField')
//                );
//
//                if( $objGC->subPagesRoot )
//                {
//                    $GLOBALS['TL_DCA'][ $dc->table ]['fields'][ $field ]['eval']['rootNodes'] = [$objGC->subPagesRoot];
//                }
//            }
//            else
            if( !$objGC->subCategoriesArePages )
            {
                $GLOBALS['TL_DCA'][ $dc->table ]['fields'][ $field ] = array
                (
                    'label'             => [$objGC->title, ''],
                    'foreignKey'        => 'tl_iido_global_category.title',
                    'inputType'         => 'globalCategoriesPicker',
                    'eval'              => array('fieldType'=>'checkbox','rootNodes'=>[$objGC->id], 'tl_class'=>'w50 hauto'),
                    'options_callback'  => array('iido_basic.table.global_category', 'onCategoriesOptionsCallback'),
                );
            }
        }

        // The field does not exist
        if( !isset($GLOBALS['TL_DCA'][ $dc->table ]['fields'][ $field ]) )
        {
            $this->logger->log(
                LogLevel::ERROR,
                \sprintf('Field "%s" does not exist in DCA "%s"', $field, $dc->table),
                ['contao' => new ContaoContext(__METHOD__, TL_ERROR)]
            );

            throw new BadRequestHttpException('Bad request');
        }

        $row    = null;
        $value  = null;

        // Load the value
        if ('overrideAll' !== $input->get('act') && $id > 0 && $db->tableExists($dc->table))
        {
            $row = $db->prepare('SELECT * FROM '.$dc->table.' WHERE id=?')->execute($id);

            // The record does not exist
            if ($row->numRows < 1)
            {
                $this->logger->log(
                    LogLevel::ERROR,
                    \sprintf('A record with the ID "%s" does not exist in table "%s"', $id, $dc->table),
                    ['contao' => new ContaoContext(__METHOD__, TL_ERROR)]
                );

                throw new BadRequestHttpException('Bad request');
            }

            $value = $row->$field;
            $dc->activeRecord = $row;
        }

        // Call the load_callback
        if (\is_array($GLOBALS['TL_DCA'][ $dc->table ]['fields'][ $field ]['load_callback']))
        {
            /** @var System $systemAdapter */
            $systemAdapter = $this->framework->getAdapter(System::class);

            foreach ($GLOBALS['TL_DCA'][ $dc->table ]['fields'][ $field ]['load_callback'] as $callback)
            {
                if (\is_array($callback))
                {
                    $value = $systemAdapter->importStatic($callback[0])->{$callback[1]}($value, $dc);
                }
                elseif (\is_callable($callback))
                {
                    $value = $callback($value, $dc);
                }
            }
        }

        // Set the new value
        $value = $input->post('value', true);

        // Convert the selected values
        if ($value)
        {
            /** @var \StringUtil $stringUtilAdapter */
            $stringUtilAdapter = $this->framework->getAdapter(\StringUtil::class);
            $value = $stringUtilAdapter->trimsplit("\t", $value);
            $value = \serialize($value);
        }

        /** @var \IIDO\BasicBundle\Widget\GlobalCategoriesPickerWidget $strClass */
        $strClass = $GLOBALS['BE_FFL']['globalCategoriesPicker'];

        /** @var \IIDO\BasicBundle\Widget\GlobalCategoriesPickerWidget $objWidget */
        $objWidget = new $strClass($strClass::getAttributesFromDca($GLOBALS['TL_DCA'][$dc->table]['fields'][$field], $dc->inputName, $value, $field, $dc->table, $dc));

        $strWidget = $objWidget->generate();

        if( $dc->table === 'tl_files' )
        {
            $fieldName  = preg_replace('/_([a-z]{1,})$/', '', $field);
            $strLang    = preg_replace('/^gc_([a-z]{1,})_/', '', $field);

            $strWidget = preg_replace('/name="' . $field . '"/', 'name="meta[' . $strLang . '][' . $fieldName . ']"', $strWidget);
        }

        throw new ResponseException(new Response($strWidget));
    }

}
