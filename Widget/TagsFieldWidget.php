<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/


namespace IIDO\BasicBundle\Widget;


/**
 * Provide methods to handle text fields.
 *
 * @property integer $maxlength
 * @property boolean $mandatory
 * @property string  $placeholder
 * @property boolean $multiple
 * @property boolean $hideInput
 * @property integer $size
 *
 * @author Stephan Preßl <development@prestep.at>
 */
class TagsFieldWidget extends \TextField
{

    /**
     * Generate the widget and return it as string
     *
     * @return string
     */
    public function generate()
    {
        $this->varValue = preg_replace('/,([^!\s]{1})/', ', $1', $this->varValue);

        $objElement     = \ContentModel::findByPk( $this->currentRecord );
        $strField       = parent::generate();
        $strLabels      = '';
        $arrFilters     = array();
        $varValue       = $this->varValue;
        $strName        = $strFilterName = $this->strName;
        $fieldName      = preg_replace('/^rsce_field_/', '', $strName);
        $filterMode     = 'Main';

        $arrCols        = array('pid=?', 'invisible=?');
        $arrColsValue   = array($objElement->pid, '');

        if( $this->fieldType )
        {
            $arrCols[]          = 'type=?';
            $arrColsValue[]     = $this->fieldType;
        }

        if( $this->multipleTags )
        {
            $filterMode     = 'Sub';
        }

        if( $this->strTable === "tl_files" )
        {
            $strLang = preg_replace('/meta\[/', '', $strName);
            $strLang = preg_replace('/([a-z]{2})\]\[([A-Za-z0-9\s\-_]{0,})\]/', '$1', $strLang);

            $strMetaField = preg_replace('/_([0-9]{1,})$/', '', $this->strId);
//            echo "<pre>";
//            print_r( $strLang );
//            echo "<br>";
//            exit;
            $objFiles = \FilesModel::findByExtension(array('jpg', 'jpeg', 'JPG', 'JPEG', 'svg', 'svgz', 'png', 'gif'));

            if( $objFiles )
            {
                while( $objFiles->next() )
                {
                    $arrMeta = deserialize($objFiles->meta, TRUE);

                    if( count($arrMeta) )
                    {
                        $strFieldValue = $arrMeta[ $strLang ][ $strMetaField ];

                        if( $this->multipleTags )
                        {
                            $arrFilter      = array_map('trim', explode(',', $strFieldValue)); //explode(",", $strFieldValue);

                            foreach($arrFilter as $strFilter)
                            {
                                $arrFilters[] = trim($strFilter);
                            }
                        }
                        else
                        {
                            $arrFilters[] = $strFieldValue;
                        }
                    }
                }
            }

            $strFilterName = $this->strId;
        }
        else
        {
            $objElements = \ContentModel::findBy($arrCols, $arrColsValue);

            if( $objElements )
            {
                while( $objElements->next() )
                {
//                if( $objElements->id != $objElement->id )
//                {
                    $objData = json_decode($objElements->rsce_data);

                    if( $this->multipleTags )
                    {
                        $arrFilter      = array_map('trim', explode(',', $objData->$fieldName)); //explode(",", $objData->$fieldName);

                        foreach($arrFilter as $strFilter)
                        {
                            $arrFilters[] = trim($strFilter);
                        }
                    }
                    else
                    {
                        $arrFilters[] = $objData->$fieldName;
                    }
//                }
                }
            }
        }

        if( count($arrFilters) )
        {
            $arrFilters = array_unique($arrFilters);
            asort($arrFilters);

            foreach($arrFilters as $strFilter)
            {
                $labelClass = '';

                if( $this->multipleTags )
                {
                    if( in_array($strFilter, array_map('trim', explode(',', $varValue))) ) //explode(",", $varValue)) )
                    {
                        $labelClass = 'active';
                    }
                }
                else
                {
                    if( $strFilter == $varValue )
                    {
                        $labelClass = 'active';
                    }
                }

                if( strlen(trim($strFilter)) )
                {
                    $strLabels .= '<a class="label-link ' . $labelClass . '" href="javascript:void(0)" onclick="IIDO.Backend.set' . $filterMode . 'FilterLabel(\'' . $strFilter . '\', \'' . $strFilterName . '\', this)">' . $strFilter . '</a>';
                }
            }
        }

        if( strlen($strLabels) )
        {
            $strLabels = '<div class="labels">' . $strLabels . '</div>';
        }

        return $strLabels . $strField;
    }
}
