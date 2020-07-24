<?php
namespace IIDO\BasicBundle\EventListener\DataContainer;


use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\Database;
use Contao\DataContainer;
use Contao\Input;
use Contao\PageModel;
use Terminal42\ServiceAnnotationBundle\ServiceAnnotationInterface;


class FilesListener implements ServiceAnnotationInterface
{
    protected $strTable = 'tl_files';



    /**
     * @Callback(table="tl_files", target="config.onload")
     */
    public function onloadTable( DataContainer $dc ):void
    {
        $arrLangs       = [];
        $objRooPages    = PageModel::findPublishedRootPages(['group'=>'language']);

        if( $objRooPages )
        {
            while( $objRooPages->next() )
            {
                $arrLangs[] = $objRooPages->language;
            }
        }

        $arrData = $GLOBALS['TL_DCA'][ $this->strTable ]['fields']['meta']['eval']['metaFields']['previewImage'];

        foreach( $arrLangs as $strLang )
        {
            $GLOBALS['TL_DCA'][ $this->strTable ]['fields']['previewImage_' . $strLang ] = $arrData;
        }
    }



    /**
     * @Callback(table="tl_files", target="config.onsubmit")
     */
    public function onsubmitTable( DataContainer $dc ):void
    {
        $arrLangs   = [];
        $arrFields  = ['previewImage'];
        $arrMeta    = Input::post('meta');

        foreach( $arrMeta as $strLang => $arrLangMeta )
        {
            $arrLangs[] = $strLang;
        }

        foreach($arrFields as $strField)
        {
            foreach($arrLangs as $strLang)
            {
                if( Input::findPost( $strField . '_' . $strLang) )
                {
                    $varLangValue = Input::post( $strField . '_' . $strLang);

                    $arrMeta[ $strLang ][ $strField ] = $varLangValue;
                }
            }
        }

        unset($arrMeta['language']);

        $arrSet = array
        (
            'meta' => serialize($arrMeta)
        );

        Database::getInstance()->prepare('UPDATE ' . $this->strTable . ' %s WHERE id=?')->set( $arrSet )->execute( $dc->activeRecord->id );
    }
}
