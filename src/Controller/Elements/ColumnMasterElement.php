<?php


namespace IIDO\BasicBundle\Controller\Elements;


use Contao\ContentModel;
use Contao\Controller;
use Contao\CoreBundle\Controller\ContentElement\AbstractContentElementController;
use Contao\CoreBundle\ServiceAnnotation\ContentElement;
use Contao\System;
use Contao\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


/**
 * Class ColumnMaster
 *
 * @package IIDO\BasicBundle\Controller\Elements
 *
 * @ContentElement("iido_column_master",
 *     category="texts",
 *     template="ce_iido_config_column-master",
 *     renderer="forward"
 * )
 *
 * @deprecated
 */
class ColumnMasterElement extends AbstractContentElementController
{
    protected function getResponse(Template $template, ContentModel $model, Request $request): ?Response
    {
        Controller::loadDataContainer('tl_iido_content');

        $elements = System::getContainer()->get('huh.fieldpalette.manager')->createModelByTable('tl_iido_content')->findByPidAndTableAndField($model->id, 'tl_content', 'iido_elements');

        $template->elements = $this->renderElements( $elements );

        return $template->getResponse();
    }



    protected function renderElements( $elements )
    {
        $arrElements = [];

        while( $elements->next() )
        {
            $element = $elements->current();

            $objElement = new ContentModel();
            $objElement->setRow( $element->row() );

            $objElement->isSubElement = true;

            $arrElements[] = Controller::getContentElement( $objElement );
//            $arrElements[] = $this->parseElement( $element );
        }

        return $arrElements;
    }



    /**
     * COPY of Controller::getContentElement
     *
     * @param $intId
     *
     * @return string
     */
    protected function parseElement( $intId )
    {
        if (\is_object($intId))
        {
            $objRow = $intId;
        }
        else
        {
            if ($intId < 1 || !\strlen($intId))
            {
                return '';
            }

            $objRow = ContentModel::findByPk($intId);

            if ($objRow === null)
            {
                return '';
            }
        }

        // Check the visibility (see #6311)
        if (!static::isVisibleElement($objRow))
        {
            return '';
        }

        $strClass = ContentElement::findClass($objRow->type);

        // Return if the class does not exist
        if (!class_exists($strClass))
        {
            static::log('Content element class "' . $strClass . '" (content element "' . $objRow->type . '") does not exist', __METHOD__, TL_ERROR);

            return '';
        }

        $objRow->typePrefix = 'ce_';

        /** @var ContentElement $objElement */
        $objElement = new $strClass($objRow, $strColumn);
        $strBuffer = $objElement->generate();

        // HOOK: add custom logic
        if (isset($GLOBALS['TL_HOOKS']['getContentElement']) && \is_array($GLOBALS['TL_HOOKS']['getContentElement']))
        {
            foreach ($GLOBALS['TL_HOOKS']['getContentElement'] as $callback)
            {
                $strBuffer = static::importStatic($callback[0])->{$callback[1]}($objRow, $strBuffer, $objElement);
            }
        }

        // Disable indexing if protected
        if ($objElement->protected && !preg_match('/^\s*<!-- indexer::stop/', $strBuffer))
        {
            $strBuffer = "\n<!-- indexer::stop -->" . $strBuffer . "<!-- indexer::continue -->\n";
        }

        return $strBuffer;
    }
}