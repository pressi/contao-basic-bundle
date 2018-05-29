<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\Table;


//use IIDO\BasicBundle\Config\BundleConfig;
use IIDO\BasicBundle\Helper\BasicHelper;


/**
 * Class Website Config Table
 */
class WebsiteStylesTable
{

    /**
     * Table name
     *
     * @var string
     */
    protected $strTable             = 'tl_iido_website_styles';



    public function saveStyleseditor( $dc )
    {
//        \Config::persist(BundleConfig::getTableFieldPrefix() . 'stylesEditorSaved', time());
        touch(BasicHelper::getRootDir() . '/files/master/css/core.css');
    }



    public function checkHeadlineStylesNameIfUnique( $varValue, $dc )
    {
echo "<pre>";
print_r( $dc->activeRecord );
//echo "<br>";
//print_r( $var );
exit;
    }
}
