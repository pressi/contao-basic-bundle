<?php declare(strict_types=1);


namespace IIDO\BasicBundle\View;


use Contao\Controller;
use Contao\FilesModel;
use Contao\FrontendUser;
use Contao\StringUtil;
use Contao\System;
use Contao\Validator;
use IIDO\BasicBundle\Dca\Container;
use PRESTEP\PowerPoolBundle\Helper\FilesHelper;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface;


class BackendFormView
{
    /**
     * Form ID
     *
     * @var string
     */
    protected $formId;


    /**
     * DCA Table
     *
     * @var string
     */
    protected $table;


    /**
     * array of fields
     *
     * @var array
     */
    protected $fields;


    /**
     * array of sub fields (subpalettes)
     *
     * @var array
     */
    protected $subFields;


    /**
     * object model of the value
     *
     * @var null|object
     */
    protected $objValue;


    /**
     * Show only submit save button
     *
     * @var bool
     */
    protected $showOnlySaveSubmitButton = false;


    /**
     * backlink
     *
     * @var string
     */
    protected $backlink;


    protected $jsField = array();



    public function __construct($strTable, $objValueModel = null)
    {
        $this->table    = $strTable;
        $this->formId   = $strTable . '_' . $objValueModel->id;
        $this->objValue = $objValueModel;

        Controller::loadDataContainer( $this->table );
    }



    public function addFields($arrFields): self
    {
        $this->fields = $arrFields;

        return $this;
    }



    public function addSubFields($arrSubFields): self
    {
        $this->subFields = $arrSubFields;

        return $this;
    }



    public function getId(): string
    {
        return $this->formId;
    }



    public function setOnlySaveSubmit()
    {
        $this->showOnlySaveSubmitButton = true;
    }



    public function setBacklink($backlink)
    {
        $this->backlink = $backlink;
    }



    public function folderJS($filedName)
    {
        $this->jsField[] = $filedName;
    }



    public function render( $withoutSubmit = false, $withoutForm = false): string
    {
//        $GLOBALS['TL_JAVASCRIPT'][] = 'bundles/presteppowerpool/js/PS.Backend.js';

        if( !$this->subFields || !count($this->subFields) )
        {
            $this->subFields = [];
        }

        Controller::loadLanguageFile( 'default' );

        Controller::loadLanguageFile( $this->table );
        Controller::loadDataContainer( $this->table );

        $action     = \Environment::get('request');
        $formID     = $this->getId();
        $arrLang    = $GLOBALS['TL_LANG'][ $this->table ];
        $arrDca     = $GLOBALS['TL_DCA'][ $this->table ]['fields'];
        $strForm    = '';


        if( !$withoutForm )
        {
            $strForm = '<form action="' . $action . '" class="tl_form tl_edit_form" id="' . $this->table . '" method="post" enctype="application/x-www-form-urlencoded">
<div class="tl_formbody_edit">
<input type="hidden" name="FORM_SUBMIT" value="' . $formID . '">
<input type="hidden" name="REQUEST_TOKEN" value="' . REQUEST_TOKEN . '">';
        }

        /** @var AttributeBagInterface $objSessionBag */
        $objSessionBag = System::getContainer()->get('session')->getBag('contao_backend');

        $fs = $objSessionBag->get('fieldset_states');

        foreach( $this->fields as $legendName => $arrFields )
        {
            $defLegendName = $legendName;
            $collapsed  = '';

            $legendName = preg_replace('/:(shown|open)/', '', $legendName);

            $state      = $fs[ $this->table ][ $legendName ];
            $onclick    = ' onclick="PS.Backend.toggleFieldset(this,\'' . $legendName . '\',\'' . $this->table . '\')"';

            if( $state == '0' )
            {
                $collapsed = ' collapsed';
            }

            if( false !== strpos($defLegendName, ':shown') || false !== strpos($defLegendName, ':open') )
            {
                $collapsed  = '';
                $onclick    = '';
            }

            $strForm .= '<fieldset id="pal_' . $legendName . '" class="tl_tbox' . $collapsed . '">
<legend' . $onclick . '>' . $arrLang[ $legendName ] . '</legend>';

            foreach($arrFields as $field )
            {
                $arrConfig  = $arrDca[ $field ];
                $strType    = $arrConfig['inputType'];
                $fieldClass = $GLOBALS['BE_FFL'][ $strType ];

                if( !$strType || !$fieldClass )
                {
                    continue;
                }

                $GLOBALS['TL_DCA'][ $this->table ]['fields'][ $field ]['exclude'] = false;

                $objFieldContainer = new Container( $this->table, $field, $this->objValue->$field );

                if( $arrConfig['inputType'] === 'pageTree' )
                {
                    \Input::setGet('id', $this->objValue->id);
                    \Input::setGet('act', 'edit');
                    \Input::setGet('table', $this->table );

                    $objFieldContainer->id = $this->objValue->id;
                }

                $strField = $objFieldContainer->getField();

                if( $arrConfig['inputType'] === 'checkbox' )
                {
                    $strField = preg_replace('/AjaxRequest.toggleSubpalette/', 'PS.Backend.toggleSubpalette', $strField);
                }
                elseif( $arrConfig['inputType'] === 'fileTree' )
                {
                    if( in_array($field, $this->jsField) )
                    {
                        $strField = preg_replace('/Backend.autoSubmit\("([a-zA-Z0-9_]+)"\)/', 'PS.Backend.updateFolderField("' . $field . '", value.join("\t"))', $strField);
                    }
                }

                if( key_exists($field, $this->subFields) )
                {
                    $display = '';

                    if( !$this->objValue->$field )
                    {
                        $display = ' style="display:none;"';

                        $strField = preg_replace('/value="1"/', 'value=""', $strField);
                    }

                    $strField .= '<div id="sub_' . $field . '" class="subpal cf"' . $display . '>';

                    foreach( $this->subFields[ $field ] as $subField )
                    {
                        $GLOBALS['TL_DCA'][ $this->table ]['fields'][ $subField ]['exclude'] = false;

                        $objFieldContainer = new Container( $this->table, $subField, $this->objValue->$subField );

                        if( $arrConfig['inputType'] === 'pageTree' )
                        {
                            \Input::setGet('id', $this->objValue->id);
                            \Input::setGet('act', 'edit');
                            \Input::setGet('table', $this->table );

                            $objFieldContainer->id = $this->objValue->id;
                        }

                        $strSubField = $objFieldContainer->getField();

                        if( !$this->objValue->$field )
                        {
                            $strSubField = preg_replace('/ required/', ' data-required', $strSubField);
                        }

                        $strField .= $strSubField;
                    }

                    $strField .= '</div>';
                }

                $strForm .= $strField;
            }

            $strForm .= '</fieldset>';
        }

        if( !$withoutSubmit )
        {
            $strForm .= '<div class="tl_formbody_submit">
<div class="tl_submit_container">
  <button type="submit" name="save" id="save" class="tl_submit" accesskey="s">Speichern</button> ' . ($this->showOnlySaveSubmitButton ? '' :  '<button type="submit" name="saveNclose" id="saveNclose" class="tl_submit" accesskey="c">Speichern und schließen</button>') . '
</div>
</div>';
        }

        return $strForm . ((!$withoutForm ) ? '</form>' : '');
    }



    public function renderPalette( $strPalette, $includeProcess = false ): string
    {
        $arrFields      = [];
        $arrSubFields   = [];

        $arrSelectors   = $GLOBALS['TL_DCA'][ $this->table ]['palettes']['__selector__'];
        $arrPalettes    = \StringUtil::trimsplit(";", preg_replace('/;$/', '', $GLOBALS['TL_DCA'][ $this->table ]['palettes'][ $strPalette ]));
        $arrSubpalettes = $GLOBALS['TL_DCA'][ $this->table ]['subpalettes'];

        foreach( $arrPalettes as $strPalette )
        {
            $arrSplit   = \StringUtil::trimsplit(',', $strPalette);
            $strLegend  = preg_replace(['/^\{/', '/\}$/'], '', array_shift( $arrSplit ));

            $arrFields[ $strLegend ] = $arrSplit;

            foreach( $arrSplit as $field )
            {
                if( in_array($field, $arrSelectors) && array_key_exists($field, $arrSubpalettes) )
                {
                    $arrSubFields[ $field ] = \StringUtil::trimsplit(',', $arrSubpalettes[ $field ]);
                }
            }
        }

        if( count($arrFields) )
        {
            $this->addFields( $arrFields );
        }

        if( $arrSubFields )
        {
            $this->addSubFields( $arrSubFields );
        }

        if( $includeProcess )
        {
            $this->process();
        }

        return $this->render();
    }



    public function process( $backlink = '' ): void
    {
        Controller::loadDataContainer( $this->table );

        if( !$backlink )
        {
            $backlink = $this->backlink;
        }

        if( \Input::post('FORM_SUBMIT') === $this->getId() )
        {
            $modified = false;

            foreach( $this->fields as $legend => $arrFields )
            {
                foreach( $arrFields as $field )
                {
                    $arrFieldConfig = $GLOBALS['TL_DCA'][ $this->table ]['fields'][ $field ];

                    $fieldValue = $this->objValue->$field;
                    $postValue  = \Input::post( $field );

                    if( $arrFieldConfig['inputType'] === 'fileTree' )
                    {
                        if( $arrFieldConfig['eval']['fieldType'] === 'checkbox' )
                        {
                            $postValue = StringUtil::trimsplit(',', $postValue);

                            foreach( $postValue as $postKey => $postFile )
                            {
                                $postFile = urldecode($postFile);
//                                $postFile  = preg_replace('/%2B/', '+', $postFile);
//                                $postFile  = preg_replace('/%20/', ' ', $postFile);
//                                $postFile  = preg_replace('/%C3%A4/', 'ä', $postFile);

                                $objFile = FilesModel::findByPath( $postFile );

                                if( $objFile )
                                {
                                    $postFile = $objFile->uuid;

                                    if( !Validator::isStringUuid($postFile) )
                                    {
                                        $postFile = StringUtil::binToUuid( $postFile );
                                    }
                                }

                                $postValue[ $postKey ] = $postFile;
                            }
                        }
                        else
                        {
                            $postValue = urldecode($postValue);
//                            $postValue  = preg_replace('/%2B/', '+', $postValue);
//                            $postValue  = preg_replace('/%20/', ' ', $postValue);
//                            $postValue  = preg_replace('/%C3%A4/', 'ä', $postValue);
//echo "<pre>";
//print_r( $field );
//echo "<br>";
//print_r( $postValue );
//                        if( isset($arrFieldConfig['eval']['files']) && !$arrFieldConfig['eval']['files'] )
//                        {
//                            $objFile = FilesModel::findByPath( $postValue );
//                        }
//                        else
//                        {
                            $objFile = FilesModel::findByPath( $postValue );
//                        }

//                        if( !$objFile )
//                        {
//                            $objFile = FilesModel::findByUuid( $postValue );
//                        }
//                            echo "<br>";
//                            print_r( $objFile );
//                            echo "</pre>";
                            if( $objFile )
                            {
                                $postValue = $objFile->uuid;

                                if( !Validator::isStringUuid($postValue) )
                                {
                                    $postValue = StringUtil::binToUuid( $postValue );
                                }
                            }
                        }

                    }
                    elseif( $arrFieldConfig['inputType'] === 'password' )
                    {
                        if( $postValue !== '*****')
                        {
                            $encoder = System::getContainer()->get('security.encoder_factory')->getEncoder(FrontendUser::class);

                            $postValue = $encoder->encodePassword($postValue, null);
                        }
                        else
                        {
                            $postValue = $fieldValue;
                        }
                    }

                    if( ($postValue || $postValue === '') && $postValue !== $fieldValue )
                    {
                        $modified = true;
                        $this->objValue->$field = $postValue;
                    }
                }
            }
//exit;
            foreach( $this->subFields as $legend => $arrSubFields )
            {
                foreach( $arrSubFields as $field )
                {
                    $arrFieldConfig = $GLOBALS['TL_DCA'][ $this->table ]['fields'][ $field ];

                    $fieldValue = $this->objValue->$field;
                    $postValue  = \Input::post( $field );

                    if( $arrFieldConfig['inputType'] === 'fileTree' )
                    {
                        if( $arrFieldConfig['eval']['fieldType'] === 'checkbox' )
                        {
                            if( is_array($postValue) )
                            {
                                foreach( $postValue as $postKey => $postFile )
                                {
                                    $postFile = urldecode($postFile);
//                                    $postFile  = preg_replace('/%2B/', '+', $postFile);
//                                    $postFile  = preg_replace('/%20/', ' ', $postFile);
//                                    $postFile  = preg_replace('/%C3%A4/', 'ä', $postFile);

                                    $objFile = FilesModel::findByPath( $postFile );

                                    if( $objFile )
                                    {
                                        $postFile = $objFile->uuid;

                                        if( !Validator::isStringUuid($postFile) )
                                        {
                                            $postFile = StringUtil::binToUuid( $postFile );
                                        }
                                    }

                                    $postValue[ $postKey ] = $postFile;
                                }
                            }
                        }
                        else
                        {
                            $postValue = urldecode($postValue);
//                            $postValue  = preg_replace('/%2B/', '+', $postValue);
//                            $postValue  = preg_replace('/%20/', ' ', $postValue);
//                            $postValue  = preg_replace('/%C3%A4/', 'ä', $postValue);

//                        if( isset($arrFieldConfig['eval']['files']) && !$arrFieldConfig['eval']['files'] )
//                        {
//                            $objFile = FilesModel::findByPath( $postValue );
//                        }
//                        else
//                        {
                            $objFile = FilesModel::findByPath( $postValue );
//                        }

                            if( $objFile )
                            {
                                $postValue = $objFile->uuid;

                                if( !Validator::isStringUuid($postValue) && $field !== 'homeDir' )
                                {
                                    $postValue = StringUtil::binToUuid( $postValue );
                                }
                            }
                        }

                        if( $field === 'homeDir' && Validator::isStringUuid($postValue) )
                        {
                            $postValue = StringUtil::uuidToBin( $postValue );
                        }
                    }
                    elseif( $arrFieldConfig['inputType'] === 'password' )
                    {
                        if( $postValue !== '*****')
                        {
                            $encoder = System::getContainer()->get('security.encoder_factory')->getEncoder(FrontendUser::class);

                            $postValue = $encoder->encodePassword($postValue, null);
                        }
                        else
                        {
                            $postValue = $fieldValue;

                        }
                    }

                    if( $postValue !== $fieldValue )
                    {
                        $modified = true;
                        $this->objValue->$field = $postValue;
                    }
                }
            }

            if( $this->objValue->status === "abgeschlossen" && $this->objValue->dateRealyComplete <= 0 )
            {
                $this->objValue->dateRealyComplete = time();

                //TODO: TIFF Dateien generieren!! Deaktiviert: 15.01.2020
//                FilesHelper::generateTIFFFiles( $this->objValue );

                $modified = true;
            }

            if( $modified )
            {
//                echo "<pre>"; print_r( $this->objValue ); exit;
                $this->objValue->save();
            }

            if( $backlink && isset($_POST['saveNclose']) )
            {
                Controller::redirect( $backlink );
            }
            else
            {
                Controller::reload();
            }
        }
    }



    public function processNew( $routerString, $router, $routerVar, $modelClass ): void
    {
        if( \Input::post('FORM_SUBMIT') === $this->getId() )
        {
            $objValue = new $modelClass();

            foreach( $this->fields as $legend => $arrFields )
            {
                foreach( $arrFields as $field )
                {
                    $fieldValue = $this->objValue->$field;
                    $postValue  = \Input::post( $field );

                    if( $postValue && $postValue != $fieldValue )
                    {
                        $objValue->$field = $postValue;
                    }
                }
            }

            $objValue = $objValue->save();

            Controller::redirect( $router->generate($routerString, [$routerVar=>$objValue->id]) );
        }
    }
}