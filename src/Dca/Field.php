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


    protected $type = 'text';


    protected $withoutSQL = false;


    protected $arrSQL = array
    (
        'alias'         => "varchar(##MAXLENGTH##) COLLATE utf8mb4_bin NOT NULL default ''",
        'text'          => "varchar(##MAXLENGTH##) NOT NULL default ''",
        'textarea'      => "mediumtext NULL",
        'checkbox'      => "char(1) NOT NULL default ''",
        'select'        => "varchar(##MAXLENGTH##) NOT NULL default ''",
        'fileTree'      => "binary(16) NULL",
        'pageTree'      => "int(10) unsigned NOT NULL default '0'",
        'imageSize'     => "varchar(64) NOT NULL default ''",
        'trbl'          => "varchar(128) NOT NULL default ''",

        'colorpicker'   => "varchar(64) NOT NULL default ''"
    );

    protected $arrConfig = array();

    protected $arrEval  = array
    (
        'tl_class'  => 'w50'
    );

    protected $strTable;
    protected $strLangTable;

    protected $fieldPrefix = 'iido([A-Za-z0-9]{0,})_';

    protected $dontUseRTE = false;

    protected $isSelector = false;


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

        foreach($this->arrConfig as $key => $value)
        {
            $arrFieldConfig[ $key ] = $value;
        }

        if( !$this->withoutSQL )
        {
            $arrFieldConfig['sql'] = str_replace('##MAXLENGTH##', $this->getEval('maxlength'), $this->arrSQL[ $SQLType ]?:$this->arrSQL[ $this->type ]);
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



    protected function setDefaultFileTreeFieldConfig()
    {
        $this->addEval('fileType', 'radio');
        $this->addEval('filesOnly', true);
        $this->addEval('extensions', \Config::get('validImageTypes'));
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
        $this->addConfig('relation', array('type'=>'hasOne', 'load'=>'eager'));

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

}