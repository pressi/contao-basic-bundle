<?
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\Maintenance;


use IIDO\BasicBundle\Config\BundleConfig;
use IIDO\BasicBundle\Helper\BasicHelper;
use IIDO\BasicBundle\Helper\TwigHelper;


/**
 *
 */
class WebsiteConfigMaintenance extends \Backend implements \executable
{

    /**
     * Return true if the module is active
     *
     * @return boolean
     */
    public function isActive()
    {
        return \Input::get('act') == 'iido_maintenance';
    }



    /**
     * Generate the module
     *
     * @return string
     */
    public function run()
    {
        $objUser        = \BackendUser::getInstance();
        $strUsername    = $objUser->username;

        if( $strUsername !== "zomedia" && $strUsername !== "develop" && $strUsername !== "stephan" )
        {
            return '';
        }

        /** @var \BackendTemplate|object $objTemplate */
        $objTemplate            = new \BackendTemplate('be_iido_maintenance');

        $objTemplate->action    = ampersand(\Environment::get('request'));
        $objTemplate->isActive  = $this->isActive();

        $objTemplate->headline      = $GLOBALS['TL_LANG']['tl_maintenance']['iido']['maintenanceHeadline'];
        $objTemplate->label         = $GLOBALS['TL_LANG']['tl_maintenance']['iido']['label'];

        if( \Input::get('act') === 'iido_maintenance' )
        {
            $this->runImprint( $objTemplate );
        }

        return $objTemplate->parse();
    }



    /**
     * Generate the module
     *
     * @return string
     */
    public function runImprint( &$objTemplate )
    {
        $rootDir    = BasicHelper::getRootDir( true );
        $bundlePath = BundleConfig::getBundlePath();
        $folderPath = $rootDir . $bundlePath . '/Resources/views/Website/';

        $arrTexts   = array();
        $arrFiles   = scan( $folderPath );

        if( count($arrFiles) )
        {
            $num = 1;
            foreach($arrFiles as $strFile)
            {
                if( preg_match('/^imprint_/', $strFile) )
                {
                    $keyNum     = $num;

                    $strName    = preg_replace(array('/^imprint_/', '/.html.twig$/'), '', $strFile);
                    $strLabel   = $this->renderLabel( $strName );
                    $strContent = file_get_contents( $folderPath . $strFile );

//                    $strContent = TwigHelper::render( 'Website/' . $strFile );
//                    $strContent = \Frontend::replaceInsertTags( $strContent );

                    if( preg_match('/imprint_intro/', $strFile) )
                    {
                        $keyNum = 0;
                    }

                    $arrTexts[ $keyNum ] = array
                    (
                        'name'      => $strName,
                        'label'     => $strLabel,
                        'value'     => $strContent
                    );

                    $num++;
                }
            }
        }

        ksort($arrTexts);

        if( \Input::post("FORM_SUBMIT") === "iido_maintenance_imprint" )
        {
            foreach( $arrTexts as $key => $arrText )
            {
                $strPost = \Input::postRaw( $arrText['name'] );

                if( $strPost !== $arrText['value'] )
                {
                    $this->writeTextToFile( $strPost, $arrText['name'] );

                    $arrTexts[ $key ]['value'] = $strPost;
                }
            }
        }

        $objTemplate->texts = $arrTexts;
    }



    protected function renderLabel( $strName )
    {
        \Controller::loadLanguageFile("tl_content");

        $strLabel   = $GLOBALS['TL_LANG']['tl_content']['options']['imprintText'][ $strName ];

        if( $strName === "intro" )
        {
            $strLabel   = $GLOBALS['TL_LANG']['tl_content']['options']['imprint'][ $strName ];
        }

        if( !strlen($strLabel) )
        {
            $arrName    = explode("_", $strName);

            foreach($arrName as $key => $name)
            {
                $arrSubName = explode("-", $name);

                if( $key > 0 )
                {
                    $strLabel .= ' (';
                }

                foreach($arrSubName as $subname)
                {
                    $strLabel .= ((strlen($strLabel) && !preg_match('/\($/', $strLabel)) ? ' ' : '') . $subname;
                }

                if( $key > 0 )
                {
                    $strLabel .= ')';
                }
            }
        }

        return $strLabel;
    }



    protected function writeTextToFile( $strText, $strFileName )
    {
        $rootDir    = BasicHelper::getRootDir( true );
        $bundlePath = BundleConfig::getBundlePath();
        $folderPath = $rootDir . $bundlePath . '/Resources/views/Website/';
        $sfileName  = 'imprint_' . $strFileName . '.html.twig';

        if( file_exists($folderPath . $sfileName ) )
        {
            $handle = fopen( $folderPath . $sfileName, 'w' );

            if( $handle )
            {
                $write = fwrite( $handle, $strText);
                fclose( $handle );
            }
        }
    }
}