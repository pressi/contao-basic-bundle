<?php

\Contao\Controller::loadLanguageFile('tl_fieldpalette');
\Contao\Controller::loadDataContainer('tl_fieldpalette');

\Contao\Controller::loadLanguageFile('tl_content');
\Contao\Controller::loadDataContainer('tl_content');



$strContentFileName = \IIDO\BasicBundle\Config\BundleConfig::getFileTable( __FILE__ );
//$objContentTable    = new \IIDO\BasicBundle\Dca\Cr( $strContentFileName );

//$objContentTable->setTableListener( 'iido.basic.dca.iido_content' );

//$config = \Contao\System::getContainer()->get('iido.basic.config');

$GLOBALS['TL_DCA'][ $strContentFileName ]   = $GLOBALS['TL_DCA']['tl_fieldpalette'];
$dca                                        = &$GLOBALS['TL_DCA'][ $strContentFileName ];



/**
 * Palettes
 */

foreach( $GLOBALS['TL_DCA']['tl_content']['palettes'] as $paletteName => $paletteFields )
{
    if( $paletteName === '__selector__' )
    {
        foreach( $paletteFields as $paletteField )
        {
            $dca['palettes']['__selector__'][] = $paletteField;
        }
    }
    else
    {
        $dca['palettes'][ $paletteName ] = '{intern_legend},internName;' . $paletteFields;
    }
}



/**
 * Fields
 */

$fields = [];

foreach( $GLOBALS['TL_DCA']['tl_content']['fields'] as $fieldName => $fieldConfig )
{
    $fields[ $fieldName ] = $fieldConfig;
}

$dca['fields']['internName'] = \IIDO\BasicBundle\Dca\Field::create('internName')->getConfigAsArray();


unset( $fields['iido_elements'] );

$dca['fields'] = array_merge($dca['fields'], $fields);


//$objContentTable->updateDca();
