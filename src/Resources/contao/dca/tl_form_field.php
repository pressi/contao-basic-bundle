<?php
$edit       = \Input::get("act");
$objElement = false;

$strFileName   = \IIDO\BasicBundle\Config\BundleConfig::getFileTable( __FILE__ );

if( $edit === "edit" )
{
    $objElement = \Database::getInstance()->prepare("SELECT * FROM " . $strFileName . " WHERE id=?")->execute( \Input::get("id") );
}



/**
 * Palettes
 */

$GLOBALS['TL_DCA'][ $strFileName ]['palettes']['radioTable']           = str_replace(',options', ',tableHeader,tableOptions', $GLOBALS['TL_DCA'][ $strFileName ]['palettes']['radio']);
$GLOBALS['TL_DCA'][ $strFileName ]['palettes']['databaseSelect']       = str_replace(',options', ',optionsBlankLabel,optionsFrom,options', $GLOBALS['TL_DCA'][ $strFileName ]['palettes']['select']);
$GLOBALS['TL_DCA'][ $strFileName ]['palettes']['pickdate']             = str_replace(array(',placeholder', ',value,minlength,maxlength'), array(',placeholder,dateFormat,dateDirection,dateIncludeCSS,dateImage,dateDisabledWeekdays,dateDisabledDays', ',value,dateParseValue'), $GLOBALS['TL_DCA'][ $strFileName ]['palettes']['text']);


$GLOBALS['TL_DCA'][ $strFileName ]['palettes']['__selector__'][] = 'dateImage';
$GLOBALS['TL_DCA'][ $strFileName ]['palettes']['__selector__'][] = 'dateIncludeCSS';



/**
 * Subpalettes
 */

\IIDO\BasicBundle\Helper\DcaHelper::addSubpalette('optionsFrom_news', 'newsArchives', $strFileName);
\IIDO\BasicBundle\Helper\DcaHelper::addSubpalette('optionsFrom_events', 'eventsArchives', $strFileName);

\IIDO\BasicBundle\Helper\DcaHelper::addSubpalette('dateIncludeCSS', 'dateIncludeCSSTheme', $strFileName);
\IIDO\BasicBundle\Helper\DcaHelper::addSubpalette('dateImage', 'dateImageSRC', $strFileName);



/**
 * Fields
 */

$GLOBALS['TL_DCA'][ $strFileName ]['fields']['tableHeader'] = array
(
    'label'                   => &$GLOBALS['TL_LANG'][ $strFileName ]['tableHeader'],
    'exclude'                 => true,
    'inputType'               => 'text',
    'eval'                    => array
    (
        'multiple'              => true,
        'size'                  => 4
    ),
    'sql'                     => "varchar(255) NOT NULL default ''"
);


$GLOBALS['TL_DCA'][ $strFileName ]['fields']['tableOptions'] = array
(
    'label'                   => &$GLOBALS['TL_LANG'][ $strFileName ]['options'],
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
                'label'                 => &$GLOBALS['TL_LANG'][ $strFileName ]['tableOptions']['rowTitle'],
                'exclude'               => true,
                'inputType'             => 'text',
                'eval'                  => array('style'=>'width:190px')
            ),

            'col1_field'              => array
            (
                'label'                 => &$GLOBALS['TL_LANG'][ $strFileName ]['tableOptions']['col1_field'],
                'exclude'               => true,
                'inputType'             => 'text',
                'eval'                  => array('multiple'=>true,'size'=>2,'style'=>'width:160px')
            ),

            'col2_field'              => array
            (
                'label'                 => &$GLOBALS['TL_LANG'][ $strFileName ]['tableOptions']['col2_field'],
                'exclude'               => true,
                'inputType'             => 'text',
                'eval'                  => array('multiple'=>true,'size'=>2,'style'=>'width:160px')
            ),

            'col3_field'              => array
            (
                'label'                 => &$GLOBALS['TL_LANG'][ $strFileName ]['tableOptions']['col3_field'],
                'exclude'               => true,
                'inputType'             => 'text',
                'eval'                  => array('multiple'=>true,'size'=>2,'style'=>'width:160px')
            )
        )
    ),
    'sql'                     => "blob NULL"
);

\IIDO\BasicBundle\Helper\DcaHelper::addSelectField('optionsFrom', $strFileName, array('includeBlankOption'=>true), 'clr', false, '', false, true);
\IIDO\BasicBundle\Helper\DcaHelper::addTextField('optionsBlankLabel', $strFileName);

if( \IIDO\BasicBundle\Helper\BasicHelper::isActiveBundle('contao/news-bundle') )
{
    \IIDO\BasicBundle\Helper\DcaHelper::copyFieldFromTable('newsArchives', $strFileName, 'news_archives', 'tl_module');
}

if( \IIDO\BasicBundle\Helper\BasicHelper::isActiveBundle('contao/calendar-bundle') )
{
    \IIDO\BasicBundle\Helper\DcaHelper::copyFieldFromTable('eventsArchives', $strFileName, 'cal_calendar', 'tl_module');
}


if( $objElement && $objElement->type === "databaseSelect" )
{
    $GLOBALS['TL_DCA'][ $strFileName ]['fields']['options']['eval']['mandatory'] = false;
}



// PickDate

$GLOBALS['TL_DCA'][ $strFileName ]['fields']['dateFormat'] = array
(
    'label'                   => &$GLOBALS['TL_LANG'][ $strFileName ]['dateFormat'],
    'exclude'                 => true,
    'inputType'               => 'text',
    'eval'                    => array('helpwizard'=>true, 'tl_class'=>'clr w50'),
    'explanation'             => 'dateFormat',
    'sql'                     => "varchar(32) NOT NULL default ''"
);
$GLOBALS['TL_DCA'][ $strFileName ]['fields']['dateDirection'] = array
(
    'label'                   => &$GLOBALS['TL_LANG'][ $strFileName ]['dateDirection'],
    'exclude'                 => true,
    'inputType'               => 'select',
    'options'                 => array('all', 'ltToday', 'leToday', 'geToday', 'gtToday'),
    'reference'               => &$GLOBALS['TL_LANG'][ $strFileName ]['dateDirection_ref'],
    'eval'                    => array('tl_class'=>'w50'),
    'sql'                     => "varchar(10) NOT NULL default ''"
);
$GLOBALS['TL_DCA'][ $strFileName ]['fields']['dateParseValue'] = array
(
    'label'                   => &$GLOBALS['TL_LANG'][ $strFileName ]['dateParseValue'],
    'exclude'                 => true,
    'inputType'               => 'checkbox',
    'eval'                    => array('tl_class'=>'w50 m12'),
    'sql'                     => "char(1) NOT NULL default ''"
);
$GLOBALS['TL_DCA'][ $strFileName ]['fields']['dateIncludeCSS'] = array
(
    'label'                   => &$GLOBALS['TL_LANG'][ $strFileName ]['dateIncludeCSS'],
    'exclude'                 => true,
    'default'                 => '1',
    'inputType'               => 'checkbox',
    'eval'                    => array('submitOnChange'=>true, 'tl_class'=>'clr w50 m12'),
    'sql'                     => "char(1) NOT NULL default '1'"
);
$GLOBALS['TL_DCA'][ $strFileName ]['fields']['dateIncludeCSSTheme'] = array
(
    'label'                   => &$GLOBALS['TL_LANG'][ $strFileName ]['dateIncludeCSSTheme'],
    'exclude'                 => true,
    'default'                 => 'smoothness',
    'inputType'               => 'select',
    'options'                 => array("black-tie", "blitzer", "cupertino", "dark-hive", "dot-luv", "eggplant", "excite-bike", "flick", "hot-sneaks", "humanity", "le-frog", "mint-choc", "overcast", "pepper-grinder", "redmond", "smoothness", "south-street", "start", "sunny", "swanky-purse", "trontastic", "ui-darkness", "ui-lightness", "vader"),
    'eval'                    => array('tl_class'=>'w50', 'includeBlankOption'=>true),
    'sql'                     => "varchar(64) NOT NULL default 'smoothness'"
);
$GLOBALS['TL_DCA'][ $strFileName ]['fields']['dateImage'] = array
(
    'label'                   => &$GLOBALS['TL_LANG'][ $strFileName ]['dateImage'],
    'exclude'                 => true,
    'default'                 => '1',
    'inputType'               => 'checkbox',
    'eval'                    => array('submitOnChange'=>true, 'tl_class'=>'clr'),
    'sql'                     => "char(1) NOT NULL default '1'"
);
$GLOBALS['TL_DCA'][ $strFileName ]['fields']['dateImageSRC'] = array
(
    'label'                   => &$GLOBALS['TL_LANG'][ $strFileName ]['dateImageSRC'],
    'exclude'                 => true,
    'inputType'               => 'fileTree',
    'eval'                    => array('files'=>true,'fieldType'=>'radio','filesOnly'=>true,'tl_class'=>'clr'),
    'sql'                     => "binary(16) NULL"
);
$GLOBALS['TL_DCA'][ $strFileName ]['fields']['dateDisabledWeekdays'] = array
(
    'label'                   => &$GLOBALS['TL_LANG'][ $strFileName ]['dateDisabledWeekdays'],
    'exclude'                 => true,
    'inputType'               => 'checkbox',
    'options'                 => array("1", "2", "3", "4", "5", "6", "0"),
    'reference'               => &$GLOBALS['TL_LANG']['DAYS'],
    'eval'                    => array('tl_class'=>'w50 clr', 'multiple'=>true),
    'sql'                     => "blob NULL"
);
$GLOBALS['TL_DCA'][ $strFileName ]['fields']['dateDisabledDays'] = array
(
    'label'                   => &$GLOBALS['TL_LANG'][ $strFileName ]['dateDisabledDays'],
    'inputType'               => 'multiColumnWizard',
    'eval'                    => array
    (
        'style'        => 'min-width: 100%;',
        'tl_class'     =>'clr',
        'minCount'        => 0,
        'columnFields' => array
        (
            'date' => array
            (
                'label'            => &$GLOBALS['TL_LANG'][ $strFileName ]['dateDisabledDaysDate'],
                'exclude'          => true,
                'inputType'        => 'text',
                'eval'             => array('rgxp'=>'date', 'datepicker'=>true, 'tl_class'=>'wizard'),
            ),
            'active' => array
            (
                'label'            => &$GLOBALS['TL_LANG'][ $strFileName ]['dateDisabledDaysActive'],
                'exclude'          => true,
                'inputType'        => 'checkbox'
            )
        )
    ),
    'sql'            => "blob NULL"
);