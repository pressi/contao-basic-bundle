<?php
/*******************************************************************
 * (c) 2020 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\EventListener\DataContainer;


use Contao\Backend;
use Contao\Database;
use Contao\DataContainer;
use Contao\CoreBundle\ServiceAnnotation\Callback;
use Terminal42\ServiceAnnotationBundle\ServiceAnnotationInterface;


class PageListener implements ServiceAnnotationInterface
{
    /**
     * Callback(table="tl_page", target="list.label.label")
     */
    public function loadLabel( array $row, string $label, DataContainer $dc = null, $imageAttribute = '', $blnReturnImage = false, $blnProtected = false ): string
    {
        return Backend::addPageIcon($row, $label, $dc, $imageAttribute, $blnReturnImage, $blnProtected);
    }



    /**
     * Callback(table="tl_page", target="config.onsubmit")
     */
    public function onSubmit( DataContainer $dc ): void
    {
    }
}