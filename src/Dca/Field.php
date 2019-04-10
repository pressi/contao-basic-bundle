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


class Field
{
    protected $name;


    protected $type         = 'text';

    protected $noType       = false;
    protected $includeLabel = false;


    protected $withoutSQL   = false;


    protected $arrSQL       = array
    (
//        'alias'         => "varchar(##MAXLENGTH##) COLLATE utf8mb4_bin NOT NULL default ''",
        'alias'         => ['type'=>'binary', 'length'=>128, 'default'=>''],

        'text'          => "varchar(##MAXLENGTH##) NOT NULL default ''",
        'textarea'      => "mediumtext NULL",
        'checkbox'      => "char(1) NOT NULL default ''",
        'select'        => "varchar(##MAXLENGTH##) NOT NULL default '##DEFAULT##'",
        'fileTree'      => "binary(16) NULL",
        'pageTree'      => "int(10) unsigned NOT NULL default '0'",
        'imageSize'     => "varchar(64) NOT NULL default ''",
        'trbl'          => "varchar(128) NOT NULL default ''",
        'radioTable'    => "varchar(12) NOT NULL default ''",

        'unit'          => "varchar(64) NOT NULL default ''",
        'inputUnit'     => "varchar(64) NOT NULL default ''",
        'headline'      => "varchar(255) NOT NULL default ''",

        'color'         => "varchar(64) NOT NULL default ''",
        'colorpicker'   => "varchar(64) NOT NULL default ''",

        'checkboxWizard'    => "blob NULL",
        'multiColumnWizard' => "blob NULL",
        'multiColumnEditor' => "blob NULL"
    );


    protected $arrConfig        = array();
    protected $arrRemovedConfig = array();

    protected $arrEval      = array
    (
        'tl_class'  => 'w50'
    );

    protected $strTable;
    protected $strLangTable;

    protected $fieldPrefix  = 'iido([A-Za-z0-9]{0,})_';

    protected $dontUseRTE   = false;

    protected $isSelector   = false;

    protected $addedToSelector  = false;
    protected $selector         = '';

    protected $subpalettePosition       = '';
    protected $subpaletteReplaceField   = '';


    /**
     * Default Table Listener
     *
     * @var string
     */
    protected $defaultTableListener     = 'iido.basic.table.default';



    public function __construct( $strName, $strType = 'text', $withoutSQL = false, $strTable = '')
    {
        $this->name = $strName;
        $this->type = $strType;

        $this->withoutSQL = $withoutSQL;

        if( $strTable )
        {
            $this->strTable = $strTable;
        }

        if( $strType === 'textarea')
        {
            $this->addEval('tl_class', 'clr', true);
        }

        return $this;
    }



    public static function create( $strName, $strType =  'text', $withoutSQL = false, $strTable = '' )
    {
        return new static($strName, $strType, $withoutSQL, $strTable);
    }


    public function __set($name, $value)
    {
        $this->arrConfig[ $name ] = $value;
    }


    public function __get($name)
    {
        $this->arrConfig[ $name ];
    }



    public function setUseRTE( $useRTE )
    {
        $this->dontUseRTE = !$useRTE;

        return $this;
    }




    public function addConfig( $name, $value )
    {
        $this->arrConfig[ $name ] = $value;

        return $this;
    }



    public function removeConfig( $name )
    {
        unset( $this->arrConfig[ $name ] );

        if( !in_array($name, $this->arrRemovedConfig) )
        {
            $this->arrRemovedConfig[] = $name;
        }

        return $this;
    }



    public function addEval( $name, $value, $override = false )
    {
        if( $name === 'tl_class' && $override )
        {
            $this->arrEval[ $name ] = $value;
        }
        elseif( $name === "tl_class" && !$override )
        {
            $this->arrEval[ $name ] = trim($this->arrEval[ $name ] . ' ' . $value);
        }
        else
        {
            $this->arrEval[ $name ] = $value;
        }

        return $this;
    }



    public function getEval( $name = '')
    {
        if( $name )
        {
            return $this->arrEval[ $name ];
        }

        return $this->arrEval;
    }



    public function setFieldPrefix( $prefix )
    {
        $this->fieldPrefix = $prefix;
    }



    public function setLangTable( $strLangTableName )
    {
        $this->strLangTable = $strLangTableName;
    }



    public function isSelector()
    {
        if( isset($this->arrEval['submitOnChange']) && $this->arrEval['submitOnChange'] && $this->isSelector )
        {
            return true;
        }

        return false;
    }



    public function addDefault( $default )
    {
        $this->arrConfig['default'] = $default;

        return $this;
    }




    public function getDefault()
    {
        return $this->arrConfig['default'];
    }



    public function getName()
    {
        return $this->name;
    }



    public function setWithoutSQL( $withoutSQL )
    {
        $this->withoutSQL = $withoutSQL;
        return $this;
    }



    /**
     * @param string $strTable
     *
     * @throws \Exception
     */
    public function createDca($strTable = '' )
    {
        if( $strTable )
        {
            $this->strTable = $strTable;
        }

        if( !$this->strTable )
        {
            throw new \Exception('Tabelle nicht konfiguriert, ein Feld "' . $this->name . ' (' . $this->type . ')" muss einer Tabelle zugewiesen werden!');
        }

        $SQLType = $this->type;

//        switch( $this->type )
//        {
//            case "text":
//                $this->setDefaultTextFieldConfig();
//                break;
//
//            case "alias":
//                $this->setDefaultAliasFieldConfig();
//                break;
//
//            case "textarea":
//                $this->setDefaultTextareaFieldConfig();
//                break;
//
//            case "select":
//                $this->setDefaultSelectFieldConfig();
//                break;
//        }

        $typeFunction = 'setDefault' . ucfirst( $this->type ) . 'FieldConfig';

        if( method_exists($this, $typeFunction) )
        {
            $this->$typeFunction();
        }

        $arrFieldConfig = array
        (
            'label'         => $this->renderLabel(),
            'inputType'     => $this->type,
            'exclude'       => $this->arrConfig['exclude']?:true,
            'eval'          => $this->arrEval,
        );

        if( $this->noType )
        {
            if( !$this->includeLabel )
            {
                unset( $arrFieldConfig['label'] );
            }
            unset( $arrFieldConfig['inputType'] );
            unset( $arrFieldConfig['exclude'] );
            unset( $arrFieldConfig['eval'] );
        }

        foreach($this->arrConfig as $key => $value)
        {
            $arrFieldConfig[ $key ] = $value;
        }

        if( $this->type === "select" && !isset($arrFieldConfig['options']) && isset($GLOBALS['TL_LANG'][ $this->strTable ]['options'][ $this->name ]) && count($GLOBALS['TL_LANG'][ $this->strTable ]['options'][ $this->name ]) )
        {
            $arrFieldConfig['options'] = $GLOBALS['TL_LANG'][ $this->strTable ]['options'][ $this->name ];
        }


        if( !$this->withoutSQL )
        {
            $strSQL = $this->arrSQL[ $SQLType ]?:$this->arrSQL[ $this->type ];

            if( $this->type === "checkbox" && $this->arrEval['multiple'] )
            {
                $arrFieldConfig['sql']      = "blob NULL";
//                $arrFieldConfig['relation'] = array('type'=>'hasMany', 'load'=>'lazy');
            }
            else
            {
                if( $strSQL )
                {
                    $strSQL = str_replace('##MAXLENGTH##', $this->getEval('maxlength'), $strSQL);

                    $arrFieldConfig['sql'] = str_replace('##DEFAULT##', $this->getDefault(), $strSQL);
                }
            }
        }

        $GLOBALS['TL_DCA'][ $this->strTable ]['fields'][ $this->name ] = $arrFieldConfig;
    }



    protected function setDefaultTextFieldConfig()
    {
        if( !$this->getEval('maxlength') )
        {
            $this->addEval('maxlength', 255);
        }

        if( $this->issetEval('decodeEntities') )
        {
            $this->addEval('decodeEntities', true);
        }
    }



    protected function setDefaultCheckboxFieldConfig()
    {
        $this->addEval('tl_class', 'w50 m12');
    }



    protected function setDefaultAliasFieldConfig()
    {
        $this->type = 'text';

        $this->addEval('rgxp', 'alias');
        $this->addEval('maxlength', 128);
        $this->addEval('doNotCopy', true);

        $this->addSaveCallback($this->getTableListener($this->strTable), 'generateAlias');
    }



    protected function setDefaultSelectFieldConfig()
    {
        if( !$this->getEval('maxlength') )
        {
            $this->addEval('maxlength', 32);
        }
    }



    protected function setDefaultColorFieldConfig()
    {
        $this->setDefaultColorpickerFieldConfig();
    }



    protected function setDefaultColorpickerFieldConfig()
    {
        $this->type = 'text';

        $this->addEval('maxlength', 6);
        $this->addEval('multiple', true);
        $this->addEval('size', 2);
        $this->addEval('colorpicker', true);
        $this->addEval('isHexColor', true);
        $this->addEval('decodeEntities', true);
        $this->addEval('tl_class', 'wizard');
    }



    protected function setDefaultUrlFieldConfig()
    {
        $this->type = 'text';

        $this->addEval('rgxp', 'url');
        $this->addEval('decodeEntities', true);
        $this->addEval('dcaPicker', true);
        $this->addEval('tl_class', 'wizard');
        $this->addEval('maxlength', 255);
    }



    protected function setDefaultInputUnitFieldConfig()
    {
        $this->setDefaultUnitFieldConfig();
    }



    protected function setDefaultUnitFieldConfig()
    {
        $this->type = 'inputUnit';

        $this->addEval('includeBlankOption', true);
        $this->addEval('maxlength', 20);
        $this->addEval('tl_class', 'w50');
        $this->addEval('rgxp', 'digit_auto_inherit');

        $this->addConfig('options', $GLOBALS['TL_CSS_UNITS']);
    }



    protected function setDefaultHeadlineFieldConfig()
    {
        $this->type = 'inputUnit';

        $this->addEval('maxlength', 200);
        $this->addEval('tl_class', 'w50 clr');

        $this->addConfig('options', ['h1', 'h2', 'h3', 'h4', 'h5', 'h6']);
    }



    protected function setDefaultFileTreeFieldConfig()
    {
        if( !$this->issetEval('fileType') )
        {
            $this->addEval('fileType', 'radio');
        }

        if( !$this->issetEval('filesOnly') )
        {
            $this->addEval('filesOnly', true);
        }

        if( !$this->issetEval('extensions') || !$this->getEval('extensions') )
        {
            $this->addEval('extensions', \Config::get('validImageTypes'));
        }
    }



    protected function setDefaultImageSizeFieldConfig()
    {
        $this->addConfig('reference', $GLOBALS['TL_LANG']['MSC']);
        $this->addConfig('options_callback', function ()
        {
            return \System::getContainer()->get('contao.image.image_sizes')->getOptionsForUser(\BackendUser::getInstance());
        });

        $this->addEval('rgxp', 'natural');
        $this->addEval('includeBlankOption', true);
        $this->addEval('nospace', true);
        $this->addEval('helpwizard', true);
    }



    protected function setDefaultTrblFieldConfig()
    {
        $this->addConfig('options', $GLOBALS['TL_CSS_UNITS']);

        $this->addEval('includeBlankOption', true);
    }



    protected function setDefaultPageTreeFieldConfig()
    {
        $this->addConfig('foreignKey', 'tl_page.title');

        if( !in_array('relation', $this->arrRemovedConfig) && !key_exists('relation', $this->arrConfig) )
        {
            $this->addConfig('relation', array('type'=>'hasOne', 'load'=>'eager'));
        }

        $this->addEval('fieldType', 'radio');
        $this->addEval('tl_class', 'clr');
    }



    protected function setDefaultTextareaFieldConfig()
    {
        Controller::loadDataContainer('tl_content');

        $arrConfig = $GLOBALS['TL_DCA']['tl_content']['fields']['text'];

        foreach( $arrConfig as $key => $value )
        {
            if( in_array($key, array('label', 'exclude', 'inputType', 'sql', 'search')) || $this->dontUseRTE && $key === 'explanation' )
            {
                continue;
            }

            if( $key === 'eval' )
            {
                foreach( $value as $evalName => $evalValue)
                {
                    if( $evalName === 'mandatory' )
                    {
                        $evalValue = false;
                    }

                    if( $this->dontUseRTE && $evalName === 'rte' )
                    {
                        continue;
                    }

                    $this->addEval($evalName, $evalValue);
                }
            }
            else
            {
                $this->arrConfig[ $key ] = $value;
            }
        }

        if( $this->dontUseRTE )
        {
            $this->addEval('decodeEntities', true);
            $this->addEval('style', 'height:60px');
        }
    }



    protected function setDefaultExplanationFieldConfig()
    {
        $this->type = 'explanation';

        $this->addEval('text', '');
        $this->addEval('class', 'tl_info');
        $this->addEval('tl_class', 'long');
    }



    protected function addSaveCallback( $listenerClass, $functionName)
    {
        if( !isset($this->arrConfig['save_callback']) )
        {
            $this->arrConfig['save_callback'] = array();
        }

        $this->arrConfig['save_callback'][] = array( $listenerClass, $functionName );

    }



    protected function getTableListener( $strTable )
    {
        return $this->defaultTableListener;
    }



    protected function issetEval( $name )
    {
        if( isset($this->arrEval[ $name ]) )
        {
            return true;
        }

        return false;
    }



    /**
     * Render Field Label
     *
     * @return mixed
     */
    protected function renderLabel()
    {
        if( $this->fieldPrefix )
        {
            $fieldName = preg_replace('/^' . $this->fieldPrefix . '/', '', $this->name);
        }

        $strLabel = $GLOBALS['TL_LANG'][ $this->strLangTable?:$this->strTable ][ $fieldName ];

        if( !$strLabel )
        {
            $strLabel = $GLOBALS['TL_LANG']['DEF'][ $fieldName ];
        }

        return $strLabel;
    }


    /**
     * @param \IIDO\BasicBundle\Dca\Table|\IIDO\BasicBundle\Dca\ExistTable $objTable
     *
     * @return \IIDO\BasicBundle\Dca\Field
     */
    public function addFieldToTable( $objTable )
    {
        $objTable->addFields( [ $this ] );

        return $this;
    }


    /**
     * @param \IIDO\BasicBundle\Dca\Table|\IIDO\BasicBundle\Dca\ExistTable $objTable
     *
     * @return \IIDO\BasicBundle\Dca\Field
     */
    public function addToTable( $objTable )
    {
        if( $this->isAddedToSelector() )
        {
            $objTable->addFieldToSubpalette($this->getName(), $this->selector, $this->subpalettePosition, $this->subpaletteReplaceField);
        }

        return $this->addFieldToTable( $objTable );
    }



    public function addSQL( $sql )
    {
        $this->arrSQL[ $this->type ] = $sql;

        return $this;
    }



    public function setSelector( $selector )
    {
        if( $selector )
        {
            $this->isSelector = true;
            $this->addEval('submitOnChange', true);
        }
        else
        {
            $this->isSelector = false;
            $this->addEval('submitOnChange', false);
        }

        return $this;
    }



    public function addToSearch( $addToSearch )
    {
        $this->search = $addToSearch;

        return $this;
    }



    public function addOptions( $arrOptions )
    {
        $this->arrConfig['options'] = $arrOptions;

        return $this;
    }



    public function setNoType( $setNoType )
    {
        $this->noType = $setNoType;

        return $this;
    }



    public function addToSelector( $strSelector, $position = 'end', $replaceField = '' )
    {
        $this->addedToSelector  = true;
        $this->selector         = $strSelector;

        $this->subpalettePosition       = $position;
        $this->subpaletteReplaceField   = $replaceField;

        return $this;
    }



    public function addToSubpalette( $strSubpalette, $position = 'end', $replaceField = '' )
    {
        return $this->addToSelector( $strSubpalette, $position, $replaceField );
    }



    public function isAddedToSelector()
    {
        return $this->addedToSelector;
    }



    public function getSelector()
    {
        return $this->selector;
    }

}