<?php


$strTableName   = \IIDO\BasicBundle\Config\BundleConfig::getTableName( __FILE__ );
//$objTable       = new \IIDO\BasicBundle\Dca\Table( $strTableName );
$objTable       = new \IIDO\BasicBundle\Dca\Table( $strTableName, false, true );
//$objTable->setTableListener('prestep.products.table.productsCategory');



/**
 * Table config
 */

$objTable->addTableConfig('label', 'Globale-Kategorien');
$objTable->addTableConfig('backlink', 'do=' . \Input::get("do"));
$objTable->addTableConfig('enableVersioning', true);

//$objTable->addTableConfig('onload_callback', [array( 'tl_prestep_products_category', 'onLoadCallback')]);

$objTable->addTableButtonsLabel([
    'name'          => 'Globale-Kategorie',

    'new'           => '',
    'details'       => 'der',

], 'de');



/**
 * Table list
 */

$objTable->addSorting(5, array
(
//    'pasteButtonCallback'   => ['tl_prestep_products_category', 'onPasteButtonCallback'],
    'icon'                  => \PRESTEP\ProductsBundle\Config\BundleConfig::getBundlePath( true, false ) . '/images/icons/categories.png',
    'panelLayout'           => 'filter;search'
));

$objTable->addLabel(['title', 'frontendTitle'], '%s <span style="padding-left:3px;color:#b3b3b3;">[%s]</span>');
//$objTable->addLabel(['title', 'frontendTitle'], '%s <span style="padding-left:3px;color:#b3b3b3;">[%s]</span>', ['tl_prestep_products_category', 'onLabelCallback']);

$objTable->addGlobalOperations(true );

$objTable->addOperations('edit,copy,copyChilds,cut,delete,show,toggle');



/**
 * Table palettes
 */

$objTable->addPalette('default', '{title_legend},title,alias,frontendTitle,cssClass;{enable_legend},enableCategoriesIn;{details_legend:hide},description,singleSRC;{additional_legend},subCategoriesArePages;{modules_legend:hide},hideInList,hideInReader,excludeInRelated;{redirect_legend:hide},jumpTo;{language_legend},master;');



/**
 * Table subpalettes
 */

//$objTable->addSubpalette('protected', 'groups');



/**
 * Table fields
 */

\IIDO\BasicBundle\Dca\Field::create('title', 'text')
    ->addToSearch( true )
    ->addToTable( $objTable );

\IIDO\BasicBundle\Dca\Field::create('frontendTitle', 'text')
    ->addToSearch( true )
    ->addToTable( $objTable );

\IIDO\BasicBundle\Dca\Field::create('alias', 'alias')
    ->addToSearch( true )
    ->addToTable( $objTable );

\IIDO\BasicBundle\Dca\Field::create('cssClass', 'text')
    ->addToTable( $objTable );

\IIDO\BasicBundle\Dca\Field::create('description', 'textarea')
    ->setUseRTE( true )
    ->addToTable( $objTable );

\IIDO\BasicBundle\Dca\Field::create('singleSRC', 'fileTree')
    ->addToTable( $objTable );

\IIDO\BasicBundle\Dca\Field::create('jumpTo', 'pageTree')
    ->addConfig('relation', array('type'=>'belongsTo', 'load'=>'lazy'))
    ->addToTable( $objTable );

$objSubpalette1 = \IIDO\BasicBundle\Dca\Field::create('subCategoriesArePages', 'checkbox')
    ->setSelector( true )
    ->addToTable( $objTable );

\IIDO\BasicBundle\Dca\Field::create('subPagesRoot', 'pageTree')
    ->removeConfig('relation')
    ->addToSelector( $objSubpalette1 )
    ->addToTable( $objTable );

\IIDO\BasicBundle\Dca\Field::create('enableCategoriesIn', 'checkbox')
    ->addEval("multiple", TRUE)
    ->addOptions( $GLOBALS['IIDO']['GLOBAL_CATEGORIES']['ENABLE'] )
    ->addToTable( $objTable );


//\IIDO\BasicBundle\Dca\Field::create('protected', 'checkbox')
//    ->setSelector( true )
//    ->addToTable( $objTable );

//\IIDO\BasicBundle\Dca\Field::create('groups', 'checkbox')
//    ->addConfig('foreignKey', 'tl_member_group.name')
//    ->addEval('mandatory', true)
//    ->addEval('multiple', true)
//    ->addSQL("blob NULL")
//    ->addConfig('relation', array('type'=>'hasMany', 'load'=>'lazy'))
//    ->addToTable( $objTable );

if( \IIDO\BasicBundle\Config\BundleConfig::isActiveBundle('terminal42/contao-changelanguage') )
{
    \IIDO\BasicBundle\Dca\Field::create('master', 'select')
        ->addEval('includeBlankOption', TRUE)
        ->addEval('blankOptionLabel', $GLOBALS['TL_LANG'][ $strTableName ]['isMaster'])

        ->addSQL("int(10) unsigned NOT NULL default '0'")
        ->addConfig('relation', ['type' => 'hasOne', 'table' => $strTableName])

        ->addConfig('options_callback', function( $dc )
        {
            $tableListener = new \Terminal42\ChangeLanguage\EventListener\DataContainer\ParentTableListener( $dc->table );

            return $tableListener->onMasterOptions( $dc );
        })

        ->addConfig('save_callback', [function($value, DataContainer $dc )
        {
            if (!$value)
            {
                return $value;
            }

            $result = Database::getInstance()
                ->prepare('
                SELECT title 
                FROM '.$dc->table.' 
                WHERE jumpTo=? AND master=? AND id!=?
            ')
                ->limit(1)
                ->execute($dc->activeRecord->jumpTo, $value, $dc->id);

            if ($result->numRows > 0) {
                throw new \RuntimeException(sprintf($GLOBALS['TL_LANG'][$dc->table]['master'][2], $result->title));
            }

            return $value;
        }])

        ->addToTable( $objTable );
}


$objTable->createDca();
//echo "<pre>"; print_r( $GLOBALS['TL_DCA'][ $strTableName ] ); exit;