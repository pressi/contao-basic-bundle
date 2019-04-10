<?php

$strTableName   = \IIDO\BasicBundle\Config\BundleConfig::getTableName( __FILE__ );
$objTable       = new \IIDO\BasicBundle\Dca\Table( $strTableName );
//$objTable->setTableListener('prestep.products.table.productsCategory');




/**
 * Table fields
 */

\IIDO\BasicBundle\Dca\Field::create('item_id')
    ->setNoType( true )
    ->addSQL( "int(10) unsigned NOT NULL default '0'" )
    ->addToTable( $objTable );

\IIDO\BasicBundle\Dca\Field::create('category_id')
    ->setNoType( true )
    ->addSQL( "int(10) unsigned NOT NULL default '0'" )
    ->addToTable( $objTable );

\IIDO\BasicBundle\Dca\Field::create('refTable')
    ->addToTable( $objTable );

\IIDO\BasicBundle\Dca\Field::create('refCategory', 'select')
    ->addSQL( "varchar(255) NOT NULL default ''" )
    ->addToTable( $objTable );


$objTable->createDca( true );

//echo "<pre>";
//print_r( $GLOBALS['TL_DCA'][ $strTableName ] );
//exit;