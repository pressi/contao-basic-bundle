<?php
namespace IIDO\BasicBundle\EventListener\DataContainer;


use Contao\Backend;
use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\Database;
use Contao\DataContainer;
use Contao\Input;
use Contao\PageModel;
use Terminal42\ServiceAnnotationBundle\ServiceAnnotationInterface;


class ModuleListener extends Backend implements ServiceAnnotationInterface
{
    protected $strTable = 'tl_module';



    /**
     * Load the database object
     */
    public function __construct()
    {
        parent::__construct();
    }



    /**
     * @Callback(table="tl_module", target="fields.news_order.options")
     */
    public function optionsCallback( DataContainer $dc ): array
    {
        $strClass   = 'contao_newsrelated.listener.news';
        $strMethod  = 'newsOrderOptionsCallback';

        $this->import($strClass);
        $options = $this->$strClass->$strMethod( $dc );

        return array_merge($options, ['order_categoryGroup', 'order_areasOfApplicationGroup']);
    }

}
