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

namespace IIDO\BasicBundle\Dca;


use Contao\Controller;
use IIDO\BasicBundle\Helper\BasicHelper;


/**
 * Class Table
 *
 * @package IIDO\BasicBundle\Dca
 */
class Table
{
    /**
     * Table name "tl_"
     *
     * @var string
     */
    protected $strTable;


    /**
     * Table data Container: Table or File
     *
     * @var string
     */
    protected $dataContainer            = 'Table';


    /**
     * Render $GLOBALS Table array without SQL statements
     *
     * @var bool
     */
    protected $withoutSQL               = false;


    /**
     * Table Listener string in listener.yml or servcies.yml file
     *
     * @var string
     */
    protected $tableListener            = '';


    /**
     * Default Table Listener
     *
     * @var string
     */
    protected $defaultTableListener     = 'iido.basic.table.default';


    /**
     * Add Published Fields to Table
     *
     * @var bool
     */
    protected $addPublishedFields       = false;


    /**
     * published fields config
     *
     * @var array
     */
    protected $publishedFieldsConfig    = array
    (
        'palette'           => 'default',
        'replaceLegend'     => '',
        'replacePosition'   => 'after',
        'addToList'         => true
    );


    /**
     * Table Field Prefix, removed for translation: iidoBasic_title => title
     * @var string
     */
    protected $tableFieldPrefix         = 'iido([A-Za-z0-9]{0,})_';


    /**
     * Translation Table, if language file is not the same as the table name is
     *
     * @var string
     */
    protected $strLangTable             = '';


    /**
     * add sorting to table
     *
     * @var bool
     */
    protected $addSorting               = false;


    /**
     * sorting config
     *
     * @var array
     */
    protected $sortingConfig            = array
    (
        'mode'                  => '',
        'flag'                  => null,
        'fields'                => array(),
        'headerFields'          => array(),
        'panelLayout'           => '',
        'childRecordCallback'   => array(),
        'childRecordClass'      => ''
    );


    /**
     * add global operations to the table
     *
     * @var bool
     */
    protected $addGlobalOperations      = true;


    /**
     * global operations array
     *
     * @var array
     */
    protected $arrGlobalOperations      = array();


    /**
     * operatiosn array
     *
     * @var array
     */
    protected $arrOperations            = array();


    protected $addLabel                 = false;

    protected $arrLabelConfig           = array();



    protected $arrSelectors             = array();
    protected $arrPalettes              = array();
    protected $arrSubpalettes           = array();
    protected $arrFields                = array();
    protected $arrButtonsLabels         = array();

    protected $arrConfig                = array();
    protected $arrOverrideTableConfig   = array();


    protected $arrConfigMultiArray      = array
    (
        'oncreate_version_callback',
        'onsubmit_callback'
    );



    /**
     * Table constructor.
     *
     * @param string $tableName
     * @param bool   $isFile
     * @param bool   $addPublished
     * @param bool   $withoutSQL
     */
    public function __construct( $tableName, $isFile = false, $addPublished = false, $withoutSQL = false )
    {
        $this->strTable             = $tableName;
        $this->withoutSQL           = $withoutSQL;

        if( $isFile )
        {
            $this->dataContainer    = 'File';
        }

        if( is_array($GLOBALS['TL_LANG']['DEF']) && isset($GLOBALS['TL_LANG']['DEF']) && count($GLOBALS['TL_LANG']['DEF']) )
        {
            foreach( $GLOBALS['TL_LANG']['DEF'] as $key => $value )
            {
                $GLOBALS['TL_LANG'][ $tableName ][ $key ] = $value;
            }
        }

        if( $addPublished )
        {
            $this->addPublishedFields();
        }
    }



    public static function create( $tableName, $isFile = false, $addPublished = false, $withoutSQL = false)
    {
        return new static( $tableName, $isFile, $addPublished, $withoutSQL);
    }



    /**
     * set if table $GLOBALS array rendered with or without SQL statements
     *
     * @param bool $withoutSQL
     */
    public function setWithoutSQL( $withoutSQL )
    {
        $this->withoutSQL = $withoutSQL;
    }



    /**
     * set Table data container: Table or File
     *
     * @param bool|string $isFile
     */
    public function isFile( $isFile )
    {
        $dataContainer = 'Table';

        if( (is_bool($isFile) && $isFile) || (is_string($isFile) && $isFile === 'File') )
        {
            $dataContainer = 'File';
        }

        $this->dataContainer = $dataContainer;
    }



    /**
     * Create Table $GLOBALS array
     */
    public function createDca()
    {
        if( count($this->arrButtonsLabels) )
        {
            $strLang            = $GLOBALS['TL_LANGUAGE'];
            $arrButtonLabels    = $this->arrButtonsLabels[ $strLang ];

            $strName            = $arrButtonLabels['name'];

            unset( $arrButtonLabels['name'] );

            if( is_array($arrButtonLabels) && count($arrButtonLabels) )
            {
                foreach( $arrButtonLabels as $key => $label )
                {
                    $strLabelName = $strName;

                    switch( $key )
                    {
                        case "new":
                            $strLabelName = $label . ' ' . $strName;

//                        $value[0] = preg_replace('/#LABEL#/', $strLabelName, $value[0]);
                            break;
                    }

                    $GLOBALS['TL_LANG'][ $this->strTable ][ $key ][0] = preg_replace('/#LABEL#/', $strLabelName, $GLOBALS['TL_LANG'][ $this->strTable ][ $key ][0]);
                }
            }
        }

//        $arrLabelName   = ['name'=>'Item','new'=>'s'];

//        if( is_array($GLOBALS['TL_LANG']['DEF']) && isset($GLOBALS['TL_LANG']['DEF']) && count($GLOBALS['TL_LANG']['DEF']) )
//        {
//            foreach( $GLOBALS['TL_LANG']['DEF'] as $key => $value )
//            {
////                $GLOBALS['TL_LANG'][ $this->strTable ][ $key ] = $value;
//            }
//        }



        $GLOBALS['TL_DCA'][ $this->strTable ] = array
        (
            'config'        => array
            (
                'dataContainer'     => $this->dataContainer
            ),

            'palettes'      => array
            (
                '__selector__'      => array(),
                'default'           => ''
            ),

            'subpalettes'   => array(),
            'fields'        => array()
        );

        if( $this->dataContainer === "File" )
        {
            $GLOBALS['TL_DCA'][ $this->strTable ]['config']['closed'] = true;
        }
        else
        {
            $GLOBALS['TL_DCA'][ $this->strTable ]['config']['switchToEdit']       = true;
            $GLOBALS['TL_DCA'][ $this->strTable ]['config']['enableVersioning']   = true;

            if( !$this->withoutSQL )
            {
                $GLOBALS['TL_DCA'][ $this->strTable ]['config']['sql']['keys']['id']    = 'primary';

                $GLOBALS['TL_DCA'][ $this->strTable ]['fields'] = array
                (
                    'id'        => array( 'sql' => "int(10) unsigned NOT NULL auto_increment" ),
                    'tstamp'    => array( 'sql' => "int(10) unsigned NOT NULL default '0'" )
                );

                if( isset($this->arrConfig['ptable']) )
                {
                    $GLOBALS['TL_DCA'][ $this->strTable ]['fields']['pid'] = array
                    (
                        'foreignKey'    => $this->arrConfig['ptable'] . '.title',
                        'sql'           => "int(10) unsigned NOT NULL default '0'",
                        'relation'      => array('type'=>'belongsTo', 'load'=>'eager')
                    );
                }
            }
//            else
//            {
//                if( isset($this->arrConfig['ptable']) )
//                {
//                    $GLOBALS['TL_DCA'][ $this->strTable ]['fields']['pid'] = array
//                    (
//                        'foreignKey'    => $this->arrConfig['ptable'] . '.title'
//                    );
//                }
//            }
        }

//        if( $this->addSorting )
//        {
//            $this->addSortingToTable( $this->sortingConfig['mode'] );
//        }

//        $this->addLabelToTable();
//        $this->addGlobalOperationsToTable();
//        $this->addOperationsToTable();

//        $this->addPalettesToTable();
//        $this->addSubpalettesToTable();

//        $this->addFieldsToTable();
//        $this->addSelectorsToTable();

        $this->updateDca();

        if( $this->addPublishedFields )
        {
            $this->addPublishedFieldsToTable();
        }
    }



    public function updateDca()
    {
        $this->addTableConfigToTable();

        if( $this->addSorting )
        {
            $this->addSortingToTable( $this->sortingConfig['mode'] );
        }

        $this->addGlobalOperationsToTable();
        $this->addOperationsToTable();

        $this->addPalettesToTable();
        $this->addSubpalettesToTable();

        $this->addLabelToTable();

        $this->addFieldsToTable();
        $this->addSelectorsToTable();
    }



    public function addTableConfigToTable()
    {
        foreach( $this->arrConfig as $key => $value)
        {
            if( in_array($key, $this->arrConfigMultiArray) )
            {
//                $GLOBALS['TL_DCA'][ $this->strTable ]['config'][ $key ][] = $value;
                if( in_array($key, $this->arrOverrideTableConfig) )
                {
                    $GLOBALS['TL_DCA'][ $this->strTable ]['config'][ $key ] = $value;
                }
                else
                {
                    if( is_array($value) )
                    {
                        foreach( $value as $arrValue )
                        {
                            $GLOBALS['TL_DCA'][ $this->strTable ]['config'][ $key ][] = $arrValue;
                        }
                    }
                    else
                    {
                        $GLOBALS['TL_DCA'][ $this->strTable ]['config'][ $key ] = $value;
                    }
                }
            }
            else
            {
                $GLOBALS['TL_DCA'][ $this->strTable ]['config'][ $key ] = $value;
            }
        }
    }



    public function addSorting( $mode, $arrConfig = array() )
    {
        $this->addSorting = true;

        $this->sortingConfig['mode'] = $mode;

        if( count($arrConfig) )
        {
            foreach($arrConfig as $key => $value)
            {
                $this->sortingConfig[ $key ] = $value;
            }
        }
    }



    public function addSortingToTable( $mode, array $fields = array(), $headerFields = array(), $panelLayout = '', $childRecordCallback = array(), $childRecordClass = '' )
    {
        $GLOBALS['TL_DCA'][ $this->strTable ]['list']['sorting']['mode'] = $mode;

        if( count($fields) || count($this->sortingConfig['fields']) )
        {
            $GLOBALS['TL_DCA'][ $this->strTable ]['list']['sorting']['fields'] = $fields?:$this->sortingConfig['fields'];
        }

        if( count($headerFields) || count($this->sortingConfig['headerFields']) )
        {
            $GLOBALS['TL_DCA'][ $this->strTable ]['list']['sorting']['headerFields'] = $headerFields?:$this->sortingConfig['headerFields'];
        }

        if( $panelLayout || $this->sortingConfig['panelLayout'] )
        {
            $GLOBALS['TL_DCA'][ $this->strTable ]['list']['sorting']['panelLayout'] = $panelLayout?:$this->sortingConfig['panelLayout'];
        }

        if( count($childRecordCallback) || count($this->sortingConfig['childRecordCallback']) )
        {
            $GLOBALS['TL_DCA'][ $this->strTable ]['list']['sorting']['child_record_callback'] = $childRecordCallback?:$this->sortingConfig['childRecordCallback'];
        }

        if( $childRecordClass || $this->sortingConfig['childRecordClass'] )
        {
            $GLOBALS['TL_DCA'][ $this->strTable ]['list']['sorting']['child_record_class'] = $childRecordClass?:$this->sortingConfig['childRecordClass'];
        }

        if( $this->sortingConfig['flag'] && $this->sortingConfig['flag'] !== null )
        {
            $GLOBALS['TL_DCA'][ $this->strTable ]['list']['sorting']['flag'] = $this->sortingConfig['flag'];
        }
    }



    public function addLabel( $fields, $format )
    {
        $this->addLabel = true;

        if( !is_array($fields) )
        {
            $fields = array($fields);
        }

        $this->arrLabelConfig['fields'] = $fields;
        $this->arrLabelConfig['format'] = $format;
    }



    public function updateLabelConfig( $strName, $strValue )
    {
        $this->arrLabelConfig[ $strName ] = $strValue;
    }



    protected function addLabelToTable()
    {
        if( $this->addLabel && count($this->arrLabelConfig) )
        {
            foreach($this->arrLabelConfig as $key => $value )
            {
                $GLOBALS['TL_DCA'][ $this->strTable ]['list']['label'][ $key ] = $value;
            }
        }
    }



    public function addTableConfig( $name, $value, $override = false)
    {
        if( in_array($name, $this->arrConfigMultiArray) )
        {
            if( $override )
            {
                $this->arrConfig[ $name ] = array();

                $this->arrOverrideTableConfig[] = $name;
            }

            $this->arrConfig[ $name ][] = $value;
        }
        else
        {
            $this->arrConfig[ $name ] = $value;
        }
    }



    /**
     * set global operations config
     *
     * @param bool  $addAll
     * @param array $arrOperations
     */
    public function addGlobalOperations( $addAll = true, $arrOperations = array() )
    {
        $this->arrGlobalOperations = $arrOperations;

        if( $addAll )
        {
            $this->arrGlobalOperations['all'] = array
            (
                'label'         => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'          => 'act=select',
                'class'         => 'header_edit_all',
                'attributes'    => 'onclick="Backend.getScrollOffset()" accesskey="e"'
            );
        }
    }



    /**
     * add global operations to the table
     */
    protected function addGlobalOperationsToTable()
    {
        if( count($this->arrGlobalOperations) )
        {
            $GLOBALS['TL_DCA'][ $this->strTable ]['list']['global_operations'] = $this->arrGlobalOperations;
        }
    }



    public function addOperations( $strOperations )
    {
        $this->arrOperations = explode(",", $strOperations);
    }



    protected function addOperationsToTable()
    {
        if( count($this->arrOperations) )
        {
            foreach( $this->arrOperations as $operation )
            {
                $arrParts = explode("=", $operation);

                switch( $arrParts[0] )
                {
                    case "edit":
                        $arrEdit = array
                        (
                            'label' => $this->renderDefaultLangLabel('edit'),
                            'href'  => 'act=edit',
                            'icon'  => 'edit.svg'
                        );

                        if( in_array('editHeader', $this->arrOperations) || in_array('editheader', $this->arrOperations) )
                        {
                            $ctable = '';

                            if( is_array($GLOBALS['TL_DCA'][ $this->strTable ]['config']['ctable']) && count($GLOBALS['TL_DCA'][ $this->strTable ]['config']['ctable']) )
                            {
                                $ctable = trim($GLOBALS['TL_DCA'][ $this->strTable ]['config']['ctable'][0]);
                            }

                            $arrEdit['href'] = 'table=' . (strlen($ctable)? $ctable : ((isset($arrParts[1]) && is_string($arrParts[1])) ?  $arrParts[1] : 'tl_content'));
                        }

                        $GLOBALS['TL_DCA'][ $this->strTable ]['list']['operations']['edit'] = $arrEdit;
                        break;

                    case "editheader":
                    case "editHeader":
                        $GLOBALS['TL_DCA'][ $this->strTable ]['list']['operations']['editheader'] = array
                        (
                            'label'               => $this->renderDefaultLangLabel('editmeta'),
                            'href'                => 'act=edit',
                            'icon'                => 'header.svg'
                        );
                        break;

                    case "copy":
                        $GLOBALS['TL_DCA'][ $this->strTable ]['list']['operations']['copy'] = array
                        (
                            'label'               => $this->renderDefaultLangLabel('copy'),
                            'href'                => 'act=paste&amp;mode=copy',
                            'icon'                => 'copy.svg'
                        );
                        break;

                    case "cut":
                        $GLOBALS['TL_DCA'][ $this->strTable ]['list']['operations']['cut'] = array
                        (
                            'label'               => $this->renderDefaultLangLabel('cut'),
                            'href'                => 'act=paste&amp;mode=cut',
                            'icon'                => 'cut.svg'
                        );
                        break;

                    case "delete":
                        $GLOBALS['TL_DCA'][ $this->strTable ]['list']['operations']['delete'] = array
                        (
                            'label'               => $this->renderDefaultLangLabel('delete'),
                            'href'                => 'act=delete',
                            'icon'                => 'delete.svg',
                            'attributes'          => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"'
                        );
                        break;

                    case "toggle":
                        $GLOBALS['TL_DCA'][ $this->strTable ]['list']['operations']['toggle'] = array
                        (
                            'label'               => $this->renderDefaultLangLabel( 'toggle'),
                            'icon'                => 'visible.svg',
                            'attributes'          => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s)"',
                            'button_callback'     => array($this->tableListener?:$this->defaultTableListener, 'toggleIcon')
                        );
                        break;

                    case "show":
                        $GLOBALS['TL_DCA'][ $this->strTable ]['list']['operations']['show'] = array
                        (
                            'label'               => $this->renderDefaultLangLabel('show'),
                            'href'                => 'act=show',
                            'icon'                => 'show.svg'
                        );
                        break;

//                    case "feature":

//                        'featured' => array
//                    (
//                        'label'                   => &$GLOBALS['TL_LANG']['tl_news']['featured'],
//                        'exclude'                 => true,
//                        'filter'                  => true,
//                        'inputType'               => 'checkbox',
//                        'eval'                    => array('tl_class'=>'w50'),
//                        'sql'                     => "char(1) NOT NULL default ''"
//                    )
//                        break;
                }
            }
        }
    }



    /**
     * set add Published fields
     *
     * @param string $toPalette
     * @param string $replaceLegend
     * @param string $replacePosition
     * @param bool   $addToList
     */
    public function addPublishedFields($toPalette = '', $replaceLegend = '', $replacePosition = '', $addToList = true)
    {
        if( !$this->addPublishedFields )
        {
            $this->addPublishedFields = true;

            if( $toPalette )
            {
                $this->publishedFieldsConfig['palette'] = $toPalette;
            }

            if( $replaceLegend )
            {
                $this->publishedFieldsConfig['replaceLegend'] = $replaceLegend;
            }

            if( $replacePosition )
            {
                $this->publishedFieldsConfig['replacePosition'] = $replacePosition;
            }

            if( $addToList || (!$addToList && $this->publishedFieldsConfig['addToList']) )
            {
                $this->publishedFieldsConfig['addToList'] = $addToList;
            }
        }
    }



    /**
     * Add Published Fields to the table
     */
    protected function addPublishedFieldsToTable()
    {
        Controller::loadLanguageFile( $this->strTable );

//        $GLOBALS['TL_DCA'][ $this->strTable ]['palettes']['__selector__'][] = 'published';
//        $GLOBALS['TL_DCA'][ $this->strTable ]['subpalettes']['published'] = 'start,stop';

        $this->addSelector("published");
        $this->addSubpalette("published", 'start,stop');


        // Published Field
        $objPublishedField = new Field('published', 'checkbox', $this->withoutSQL);

//        $objPublishedField->exclude = true;
        $objPublishedField->filter  = true;
        $objPublishedField->flag    = 1;

        $objPublishedField->addEval('doNotCopy', true);
        $objPublishedField->addEval('submitOnChange', true);
//        $objPublishedField->setSelector(true);

        $objPublishedField->createDca( $this->strTable );


        // Start Field
        $objStartField = new Field('start', 'text', $this->withoutSQL);

        $objStartField->addEval('rgxp', 'datim');
        $objStartField->addEval('datepicker', true);
        $objStartField->addEval('tl_class', 'w50 wizard', true);

        $objStartField->createDca( $this->strTable );

        $this->copyField('start', 'stop');


        if( !$this->withoutSQL )
        {
            $strKeys = 'start,stop,published';

            if( isset($GLOBALS['TL_DCA'][ $this->strTable ]['fields']['pid']) )
            {
                unset($GLOBALS['TL_DCA'][ $this->strTable ]['config']['sql']['keys']['pid']);
                $strKeys = 'pid,' .$strKeys;
            }

            $GLOBALS['TL_DCA'][ $this->strTable ]['config']['sql']['keys'][ $strKeys ] = 'index';
        }

        $this->renderTableLegend('{publish_legend},published;', $this->publishedFieldsConfig['palette'], $this->publishedFieldsConfig['replaceLegend'], $this->publishedFieldsConfig['replacePosition']);

        if( $this->publishedFieldsConfig['addToList'] )
        {
            $intIndex = (count($GLOBALS['TL_DCA'][ $this->strTable ]['list']['operations']) - 1);

            if( isset($GLOBALS['TL_DCA'][ $this->strTable ]['list']['operations']['feature']) )
            {
                $intIndex = ($intIndex - 1);
            }

            array_insert($GLOBALS['TL_DCA'][ $this->strTable ]['list']['operations'], $intIndex, array
            (
                'toggle' => array
                (
                    'label'               => $this->renderFieldLabel('toggle'),
                    'icon'                => 'visible.svg',
                    'attributes'          => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s)"',
                    'button_callback'     => array($this->tableListener?:$this->defaultTableListener, 'toggleIcon')
                )
            ));
        }
    }



    public function copyField( $from, $to )
    {
        $GLOBALS['TL_DCA'][ $this->strTable ]['fields'][ $to ] = $GLOBALS['TL_DCA'][ $this->strTable ]['fields'][ $from ];

        $GLOBALS['TL_DCA'][ $this->strTable ]['fields'][ $to ]['label'] = $this->renderFieldLabel( $to );
    }



    public function copyPalette( $from, $to )
    {
        $GLOBALS['TL_DCA'][ $this->strTable ]['palettes'][ $to ] = $GLOBALS['TL_DCA'][ $this->strTable ]['palettes'][ $from ];
    }



    /**
     * Render Field Label
     *
     * @param string $fieldName
     *
     * @return mixed
     */
    protected function renderFieldLabel( $fieldName )
    {
        if( $this->tableFieldPrefix )
        {
            $fieldName = preg_replace('/^' . $this->tableFieldPrefix . '/', '', $fieldName);
        }

        return $GLOBALS['TL_LANG'][ $this->strLangTable?:$this->strTable ][ $fieldName ];
    }



    /**
     * Render Table Legend
     *
     * @param string $strLegend
     * @param string $toPalette
     * @param bool   $replaceLegend
     * @param string $replacePosition
     */
    protected function renderTableLegend( $strLegend, $toPalette = '', $replaceLegend = false, $replacePosition = 'after')
    {
        if( !strlen($toPalette) )
        {
            foreach($GLOBALS['TL_DCA'][ $this->strTable ]['palettes'] as $strPalette => $strFields)
            {
                if( $strPalette === "__selector__" )
                {
                    continue;
                }

                $strFields = $this->renderLegendFields( $strFields, $strLegend, $replaceLegend, $replacePosition);

                $GLOBALS['TL_DCA'][ $this->strTable ]['palettes'][ $strPalette ] = $strFields;
            }
        }
        else
        {
            $strFields = $this->renderLegendFields( $GLOBALS['TL_DCA'][ $this->strTable ]['palettes'][ $toPalette ], $strLegend, $replaceLegend, $replacePosition);

            $GLOBALS['TL_DCA'][ $this->strTable ]['palettes'][ $toPalette ] = $strFields;
        }
    }



    /**
     * Render Legend Fields
     *
     * @param string $strFields
     * @param string $strLegend
     * @param bool   $replaceLegend
     * @param string $replacePosition
     *
     * @return string
     */
    public function renderLegendFields( $strFields, $strLegend, $replaceLegend = false, $replacePosition = 'after' )
    {
        if( $replaceLegend )
        {
            if( !preg_match('/_legend$/', $replaceLegend) )
            {
                $replaceLegend = $replaceLegend . '_legend';
            }

            if( $replacePosition === "after" )
            {
                $strFields = preg_replace('/\{' . preg_quote($replaceLegend, '/') . '([A-Za-z0-9\-:]{0,})\},([A-Za-z0-9\-_,;.:]);/', '{' . $replaceLegend . '$1},$2;' . $strLegend, $strFields);
            }
            else
            {
                $strFields = preg_replace('/\{' . preg_quote($replaceLegend, '/') . '([A-Za-z0-9\-:]{0,})\}/', $strLegend . '{' . $replaceLegend . '$1}', $strFields);
            }
        }
        else
        {
            $strFields = $strFields . $strLegend;
        }

        return $strFields;
    }



    public function addFields( $fields )
    {
        if( !is_array($fields) )
        {
            $fields = array($fields);
        }

        $this->arrFields = array_merge($this->arrFields, $fields);
    }



    protected function addFieldsToTable()
    {
        if( count($this->arrFields) )
        {
            foreach( $this->arrFields as $objField)
            {
                if( $objField instanceof Field)
                {
                    /* @var $objField \IIDO\BasicBundle\Dca\Field */

                    if( $objField->isSelector() )
                    {
                        $this->addSelector( $objField->getName() );
                    }

                    $objField->setWithoutSQL( $this->withoutSQL );

                    $objField->createDca( $this->strTable );
                }
            }
        }
    }



    protected function addSelector( $selectorName )
    {
        $this->arrSelectors[] = $selectorName;
    }


    public function addPalette( $palette, $fields )
    {
        $this->arrPalettes[ $palette ] = $fields;
    }


    public function addSubpalette( $subpalette, $fields )
    {
        $this->arrSubpalettes[ $subpalette ] = $fields;
    }



    protected function addSelectorsToTable()
    {
        $this->arrSelectors = array_unique($this->arrSelectors);

        if( count($this->arrSelectors) )
        {
            if( !isset($GLOBALS['TL_DCA'][ $this->strTable ]['palettes']['__selector__']) || !count($GLOBALS['TL_DCA'][ $this->strTable ]['palettes']['__selector__']) )
            {
                $GLOBALS['TL_DCA'][ $this->strTable ]['palettes']['__selector__'] = $this->arrSelectors;
            }
            else
            {
                foreach( $this->arrSelectors as $selector )
                {
                    if( !in_array($selector, $GLOBALS['TL_DCA'][ $this->strTable ]['palettes']['__selector__']) )
                    {
                        $GLOBALS['TL_DCA'][ $this->strTable ]['palettes']['__selector__'][] = $selector;
                    }
                }
            }
        }
    }



    protected function addPalettesToTable()
    {
        if( count($this->arrPalettes) )
        {
            foreach($this->arrPalettes as $strPalette => $fields )
            {
                $GLOBALS['TL_DCA'][ $this->strTable ]['palettes'][ $strPalette ] = $this->renderPaletteFields( $fields );
            }
        }
    }


    protected function addSubpalettesToTable()
    {
        if( count($this->arrSubpalettes) )
        {
            foreach($this->arrSubpalettes as $strPalette => $fields )
            {
                $GLOBALS['TL_DCA'][ $this->strTable ]['subpalettes'][ $strPalette ] = $this->renderPaletteFields( $fields );
            }
        }
    }



    protected function renderPaletteFields( $fields )
    {
        if( !is_array($fields) )
        {
            return $fields;
        }

        $strFields = '';

        foreach( $fields as $legend => $legendFields )
        {
            if( !is_array($legendFields) )
            {
                $strFields .= '{default_legend},' . $legendFields . ';';
            }
            else
            {
                $strFields .= $this->renderLegendLabel( $legend );

                foreach($legendFields as $field)
                {
                    $strFields .= ',' . $field;
                }

                $strFields .= ';';
            }
        }

        return $strFields;
    }



    protected function renderLegendLabel( $strLegend )
    {
        if( !preg_match('/_legend$/', $strLegend) )
        {
            return $strLegend . '_legend';
        }

        return '{' . $strLegend . '}';
    }


    protected function renderDefaultLangLabel( $fieldName )
    {
        $strLabel = $GLOBALS['TL_LANG'][ $this->strTable ][ $fieldName ];

        if( !$strLabel )
        {
            $strLabel = $GLOBALS['TL_LANG']['DEF'][ $fieldName ];
        }

        return $strLabel;
    }



    public function setTableListener( $listener )
    {
        $this->tableListener = $listener;
    }



    public function getTableListener()
    {
        return $this->tableListener;
    }



    public function replacePaletteFields( $palettes, $replacedField, $replaceFields, $excludes = array())
    {
        if( $palettes === "all" )
        {
            foreach( $GLOBALS['TL_DCA'][ $this->strTable ]['palettes'] as $palette => $fields)
            {
                if( $palette === "__selector__" || in_array($palette, $excludes) )
                {
                    continue;
                }

                $GLOBALS['TL_DCA'][ $this->strTable ]['palettes'][ $palette ] = preg_replace('/' . preg_quote($replacedField, '/') . '/', $replaceFields, $fields);
            }
        }
        else
        {
            if( !is_array($palettes) )
            {
                $palettes = array($palettes);
            }

            if( !is_array($excludes) )
            {
                $excludes = array($excludes);
            }


            foreach($palettes as $palette)
            {
                if( in_array($palette, $excludes) || !isset($GLOBALS['TL_DCA'][ $this->strTable ]['palettes'][ $palette ]) || !in_array($palette, $GLOBALS['TL_DCA'][ $this->strTable ]['palettes']) )
                {
                    continue;
                }

                $GLOBALS['TL_DCA'][ $this->strTable ]['palettes'][ $palette ] = preg_replace('/' . preg_quote($replacedField, '/') . '/', $replaceFields, $GLOBALS['TL_DCA'][ $this->strTable ]['palettes'][ $palette ]);
            }
        }
    }



    public function addImageFields()
    {
        Field::create('addImage', 'checkbox')
            ->setSelector(true)
            ->addToTable( $this );


        Field::create('overwriteMeta', 'checkbox')
            ->setSelector(true)
            ->addEval('tl_class', 'clr')
            ->addToTable( $this );


        Field::create('singleSRC', 'fileTree')
            ->addEval('mandatory', true)
            ->addToTable( $this );

        Field::create('alt')
            ->addConfig('search', true)
            ->addToTable( $this );

        Field::create('imageTitle')
            ->addConfig('search', true)
            ->addToTable( $this );

        Field::create('size', 'imageSize')
            ->addToTable( $this );

        Field::create('imagemargin', 'trbl')
            ->addToTable( $this );

        Field::create('imageUrl', 'url')
            ->addConfig('search', true)
            ->addToTable( $this );





//		'fullsize' => array
//    (
//        'label'                   => &$GLOBALS['TL_LANG']['tl_content']['fullsize'],
//        'exclude'                 => true,
//        'inputType'               => 'checkbox',
//        'eval'                    => array('tl_class'=>'w50 m12'),
//        'sql'                     => "char(1) NOT NULL default ''"
//    ),
//		'caption' => array
//    (
//        'label'                   => &$GLOBALS['TL_LANG']['tl_content']['caption'],
//        'exclude'                 => true,
//        'search'                  => true,
//        'inputType'               => 'text',
//        'eval'                    => array('maxlength'=>255, 'allowHtml'=>true, 'tl_class'=>'w50'),
//        'sql'                     => "varchar(255) NOT NULL default ''"
//    ),
//		'floating' => array
//    (
//        'label'                   => &$GLOBALS['TL_LANG']['tl_content']['floating'],
//        'default'                 => 'above',
//        'exclude'                 => true,
//        'inputType'               => 'radioTable',
//        'options'                 => array('above', 'left', 'right', 'below'),
//        'eval'                    => array('cols'=>4, 'tl_class'=>'w50'),
//        'reference'               => &$GLOBALS['TL_LANG']['MSC'],
//        'sql'                     => "varchar(12) NOT NULL default ''"
//    ),
    }



    public function addTableButtonsLabel( $arrLabels, $strLang )
    {
        foreach($arrLabels as $labelKey => $strLabel)
        {
            $this->arrButtonsLabels[ $strLang ][ $labelKey ] = $strLabel;
        }
    }

}