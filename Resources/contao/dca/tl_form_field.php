<?
$edit       = \Input::get("act");
$objElement = false;

$strTable   = \IIDO\ShopBundle\Config\BundleConfig::getFileTable( __FILE__ );

if( $edit === "edit" )
{
    $objElement = \Database::getInstance()->prepare("SELECT * FROM " . $strTable . " WHERE id=?")->execute( \Input::get("id") );
}



/**
 * Palettes
 */

$GLOBALS['TL_DCA'][ $strTable ]['palettes']['radioTable']           = str_replace(',options', ',tableHeader,tableOptions', $GLOBALS['TL_DCA'][ $strTable ]['palettes']['radio']);
$GLOBALS['TL_DCA'][ $strTable ]['palettes']['databaseSelect']       = str_replace(',options', ',optionsBlankLabel,optionsFrom,options', $GLOBALS['TL_DCA'][ $strTable ]['palettes']['select']);



/**
 * Subpalettes
 */

\IIDO\BasicBundle\Helper\DcaHelper::addSubpalette('optionsFrom_news', 'newsArchives', $strTable);
\IIDO\BasicBundle\Helper\DcaHelper::addSubpalette('optionsFrom_events', 'eventsArchives', $strTable);



/**
 * Fields
 */

$GLOBALS['TL_DCA'][ $strTable ]['fields']['tableHeader'] = array
(
    'label'                   => &$GLOBALS['TL_LANG'][ $strTable ]['tableHeader'],
    'exclude'                 => true,
    'inputType'               => 'text',
    'eval'                    => array
    (
        'multiple'              => true,
        'size'                  => 4
    ),
    'sql'                     => "varchar(255) NOT NULL default ''"
);


$GLOBALS['TL_DCA'][ $strTable ]['fields']['tableOptions'] = array
(
    'label'                   => &$GLOBALS['TL_LANG'][ $strTable ]['options'],
    'exclude'                 => true,
    'inputType'               => 'multiColumnWizard',
    'eval'                    => array
    (
        'mandatory'             => true,
        'allowHtml'             => true,
        'tl_class'              => 'clr mcw-radio-table',
        'columnFields'          => array
        (
            'rowTitle'              => array
            (
                'label'                 => &$GLOBALS['TL_LANG'][ $strTable ]['tableOptions']['rowTitle'],
                'exclude'               => true,
                'inputType'             => 'text',
                'eval'                  => array('style'=>'width:190px')
            ),

            'col1_field'              => array
            (
                'label'                 => &$GLOBALS['TL_LANG'][ $strTable ]['tableOptions']['col1_field'],
                'exclude'               => true,
                'inputType'             => 'text',
                'eval'                  => array('multiple'=>true,'size'=>2,'style'=>'width:160px')
            ),

            'col2_field'              => array
            (
                'label'                 => &$GLOBALS['TL_LANG'][ $strTable ]['tableOptions']['col2_field'],
                'exclude'               => true,
                'inputType'             => 'text',
                'eval'                  => array('multiple'=>true,'size'=>2,'style'=>'width:160px')
            ),

            'col3_field'              => array
            (
                'label'                 => &$GLOBALS['TL_LANG'][ $strTable ]['tableOptions']['col3_field'],
                'exclude'               => true,
                'inputType'             => 'text',
                'eval'                  => array('multiple'=>true,'size'=>2,'style'=>'width:160px')
            )
        )
    ),
    'sql'                     => "blob NULL"
);

\IIDO\BasicBundle\Helper\DcaHelper::addSelectField('optionsFrom', $strTable, array('includeBlankOption'=>true), 'clr', false, '', false, true);
\IIDO\BasicBundle\Helper\DcaHelper::addTextField('optionsBlankLabel', $strTable);

\IIDO\BasicBundle\Helper\DcaHelper::copyFieldFromTable('newsArchives', $strTable, 'news_archives', 'tl_module');
\IIDO\BasicBundle\Helper\DcaHelper::copyFieldFromTable('eventsArchives', $strTable, 'cal_calendar', 'tl_module');


if( $objElement && $objElement->type === "databaseSelect" )
{
    $GLOBALS['TL_DCA'][ $strTable ]['fields']['options']['eval']['mandatory'] = false;
}