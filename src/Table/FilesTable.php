<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\Table;


use IIDO\BasicBundle\Helper\GlobalCategoriesHelper;
use IIDO\BasicBundle\Model\GlobalCategoryModel;


/**
 * Class User Table
 *
 * @package IIDO\BasicBundle\Table
 * @author Stephan Preßl <https://github.com/pressi>
 */
class FilesTable extends \Backend
{

    /**
     * Table Name
     */
    protected $strTable = 'tl_files';



    public function onSubmitCallback( &$dc )
    {
        $objGC      = GlobalCategoryModel::findPublishedByPid(0);
        $arrMeta    = \Input::post('meta');
        $arrLangs   = array();
        $update     = false;

        $arrFields  = ['previewImage'];

        foreach( $arrMeta as $strLang => $arrLangMeta )
        {
            $arrLangs[] = $strLang;
        }

        if( $objGC )
        {
            while( $objGC->next() )
            {
                $enableIn = \StringUtil::deserialize($objGC->enableCategoriesIn, TRUE);

                if( count($enableIn) && in_array($this->strTable, $enableIn) )
                {
                    if( $objGC->subCategoriesArePages )
                    {
                        foreach( $arrLangs as $strLang )
                        {
                            $postKey = 'gc_' . $objGC->alias . '_' . $strLang;

                            if( \Input::findPost( $postKey ) )
                            {
                                $varLangValue = \Input::post($postKey);
                                $arrMeta[ $strLang ][ 'gc_' . $objGC->alias ] = $varLangValue;
                            }
                        }

                        $update = true;
                    }
                }
            }
        }

        foreach($arrFields as $strField)
        {
            foreach($arrLangs as $strLang)
            {
                if( \Input::findPost( $strField . '_' . $strLang) )
                {
                    $varLangValue = \Input::post( $strField . '_' . $strLang);

                    $arrMeta[ $strLang ][ $strField ] = $varLangValue;
                }
            }
        }



        if( $update )
        {
            unset($arrMeta['language']);

            $arrSet = array
            (
                'meta' => serialize($arrMeta)
            );

            \Database::getInstance()->prepare('UPDATE ' . $this->strTable . ' %s WHERE id=?')->set( $arrSet )->execute( $dc->activeRecord->id );
        }
    }



    public function onLoadCallback( $dc )
    {
        $objGC = GlobalCategoryModel::findPublishedByPid(0);

        if( $objGC )
        {
            $arrLangs       = array();
            $objRooPages    = \PageModel::findPublishedRootPages(['group'=>'language']);

            if( $objRooPages )
            {
                while( $objRooPages->next() )
                {
                    $arrLangs[] = $objRooPages->language;
                }
            }

            while( $objGC->next() )
            {
                $enableIn = \StringUtil::deserialize($objGC->enableCategoriesIn, TRUE);

                if( count($enableIn) && in_array($this->strTable, $enableIn) )
                {
                    if( $objGC->subCategoriesArePages )
                    {
                        $arrData = array
                        (
                            'label'             => [$objGC->title, ''],
                            'inputType'         => 'pageTree',
                            'eval'              => array('fieldType'=>'checkbox','multiple'=>true, 'tl_class'=>'w50 hauto'),
//                            'load_callback'     => array(get_class($this), 'loadPageField'),
//                            'isMetaField'       => true
                        );

                        if( $objGC->subPagesRoot )
                        {
                            $arrData['eval']['rootNodes'] = [$objGC->subPagesRoot];
                        }

                        foreach($arrLangs as $strLang)
                        {
                            $GLOBALS['TL_DCA'][ $this->strTable ]['fields']['gc_' . $objGC->alias . '_' . $strLang ] = $arrData;
                        }
                    }
                    else
                    {
                        $arrData = array
                        (
                            'label'             => [$objGC->title, ''],
                            'foreignKey'        => 'tl_iido_global_category.title',
                            'inputType'         => 'globalCategoriesPicker',
                            'eval'              => array('fieldType'=>'checkbox','rootNodes'=>[$objGC->id], 'tl_class'=>'w50 hauto'),
                            'options_callback'  => array('iido_basic.table.global_category', 'onCategoriesOptionsCallback'),
                        );

                        foreach($arrLangs as $strLang)
                        {
                            $GLOBALS['TL_DCA'][ $this->strTable ]['fields']['gc_' . $objGC->alias . '_' . $strLang ] = $arrData;
                        }
                    }
                }
            }
        }

        $arrPreviewImageData = array
        (
            'label'     => array('', ''),
            'inputType' => 'fileTree',
            'eval'      => array
            (
                'filesOnly'=>true,
                'fieldType'=>'radio',
                'mandatory'=>true,
                'tl_class'=>'clr',
                'extensions' => \Config::get('validImageTypes')
            )
        );

        foreach( $arrLangs as $strLang )
        {
            $GLOBALS['TL_DCA'][ $this->strTable ]['fields']['previewImage_' . $strLang ] = $arrPreviewImageData;
        }
    }



//    public function loadPageField( $varValue, &$dc )
//    {
//        if( $GLOBALS['TL_DCA'][ $this->strTable ]['fields'][ $dc->inputName ]['isMetaField'] )
//        {
//            $strLang    = preg_replace('/^gc_([a-z]{1,})_/', '', $dc->inputName);
//            $strField   = preg_replace('/_([a-z]{1,})$/', '', $dc->inputName);
//
//            $dc->inputName = 'meta[' . $strLang . ']['. $strField . ']';
//        }
//
//        \Controller::log('JEP > ' . $dc->inputName, __FUNCTION__, \Monolog\Logger::INFO);
//
//        return $varValue;
//    }
}
