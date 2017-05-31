<?php
/*******************************************************************
 * (c) 2017 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\Table;


/**
 * Class Content Table
 * @package IIDO\BasicBundle\Table
 */
class ThemeTable extends \Backend
{

    protected $strTable             = 'tl_theme';


    public function saveThemeTable( $dc )
    {
        $activeRecord   = $dc->activeRecord;
        $folderName     = '';
        $folders        = deserialize( $activeRecord->folders, TRUE);
        $vars           = deserialize( $activeRecord->vars, TRUE);

        if( count($vars) )
        {
            foreach( $folders as $strFolder )
            {
                $objFolder = \FilesModel::findByUuid( $strFolder );

                if( $objFolder && !preg_match('/master/', $objFolder->path) )
                {
                    $folderName = $objFolder->name;
                }
            }

            $rootDir    = dirname(\System::getContainer()->getParameter('kernel.root_dir'));
            $filePath   = 'files/' . $folderName . '/css/theme.css';

            if( file_exists($rootDir . '/' . $filePath) )
            {
                touch($rootDir . '/' . $filePath);

//                $objFile = \FilesModel::findByPath( $filePath );
//
//                if( $objFile )
//                {
//                    $objFile->attach();
//
//                    echo "<pre>"; print_r( $objFile ); exit;
//
//                    $objFile->save();
//                }
            }
        }
    }
}
