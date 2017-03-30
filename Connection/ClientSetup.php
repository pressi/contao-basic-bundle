<?php
/*******************************************************************
 * (c) 2017 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\Connection;

use Contao\Controller;
use Contao\System;
use IIDO\BasicBundle\Config\BundleConfig;


class ClientSetup
{

    protected $objBackendMember;

    protected $objBackendEditor         = false;
    protected $objBackendEditorGroup    = false;

    protected $arrPages                 = array();



    public static function initClient()
    {
        if( !\Config::get("iido_initSystem") )
        {
            if( !\Input::get("do") == "iidoConfigContao" )
            {
                Controller::redirect( Controller::addToUrl("do=iidoConfigContao") );
            }
            else
            {
                if( \Input::post("FORM_SUBMIT") == "tl_iido_configContao" )
                {
                    if( MasterConnection::getInstance()->isPasswordValid() )
                    {
                        $self = new self();
                        $self->setUpContao();
                    }
                }
            }
        }
    }



    protected function setUpContao()
    {
        $this->Config               = \Config::getInstance();
        $this->objBackendMember     = \BackendUser::getInstance();

        $bundlePath         = BundleConfig::getBundlePath();
        $rootDir            = dirname(System::getContainer()->getParameter('kernel.root_dir'));

        $connectionFile     = $rootDir . '/' . $bundlePath . '/Resources/config/master-connection.json';
        $cmsConfig          = MasterConnection::getInstance()->getActionData('getContaoInit', array('themeID=' . \Input::post("theme")) );
//        echo "<pre>"; print_r( $cmsConfig );
//        echo "<br>"; print_r( $objBackendMember );
//        exit;
        $arrFolders         = array();

        foreach( $cmsConfig->files as $strFolder => $arrSubfolders)
        {
            
            $strFolderPath  = "files/" . $this->replaceVars($strFolder);

            if( is_dir($rootDir . '/' . $strFolderPath) )
            {
                $objFolder      = \FilesModel::findByPath( $strFolderPath );
            }
            else
            {
                $objFolder      = new \Folder( $strFolderPath );
            }

            $arrFolders[] = $objFolder->path;

            if( is_array($arrSubfolders) && count($arrSubfolders) && $objFolder && is_dir( $rootDir . '/' . $objFolder->path) )
            {
                foreach($arrSubfolders as $strSubFolder)
                {
                    $strSubFolder = $this->replaceVars( $strSubFolder );

                    if( is_dir($rootDir . '/' . $objFolder->path . $strSubFolder) )
                    {
                        $objSubFolder = \FilesModel::findByPath( $objFolder->path . '/' . $strSubFolder );
                    }
                    else
                    {
                        $objSubFolder = new \Folder( $objFolder->path . '/' . $strSubFolder );
                    }

                    $arrFolders[] = $objSubFolder->path;
                }
            }
        }

        if( count($arrFolders) )
        {
            \Dbafs::updateFolderHashes($arrFolders);
        }

        foreach( $cmsConfig->templates as $strTemplateFolder)
        {
            $strFolderPath  = "templates/" . $this->replaceVars( $strTemplateFolder );

            if( !is_dir($rootDir . '/' . $strFolderPath) )
            {
                mkdir( $rootDir . '/' . $strFolderPath );
            }
        }



        // Create Theme
        $arrTheme   = (array) $cmsConfig->theme;
        unset($arrTheme['layouts']);
        unset($arrTheme['modules']);
        unset($arrTheme['imageSizes']);
        unset($arrTheme['imageSizeItems']);

        $this->createNewOneModelEntry("Theme", $arrTheme);

        // Create Layouts
        $this->createNewModelEntry("Layout", (array) $cmsConfig->theme->layouts);

        // Create Modules
        $this->createNewModelEntry("Module", (array) $cmsConfig->theme->modules);

        // Create Image Sizes
        $this->createNewModelEntry("ImageSize", (array) $cmsConfig->theme->imageSizes);

        // Create Image Size Items
        $this->createNewModelEntry("ImageSizeItem", (array) $cmsConfig->theme->imageSizeItems);
        
        
        
        // Create Pages
        $this->createPages( (array) $cmsConfig->pages );



        // Backend User & Group
        $arrUser = (array) $cmsConfig->user;
        $arrUser['password'] = md5($arrUser['password']);

        $this->createNewOneModelEntry("UserGroup", (array) $cmsConfig->user_group);
        $this->createNewOneModelEntry("User", $arrUser);



        // Get RSCE Templates
        $arrRSCEfiles = \Input::post("rsce_templates");

        if( is_array($arrRSCEfiles) && count($arrRSCEfiles) )
        {
            foreach($arrRSCEfiles as $fileName)
            {
                echo "<pre>";
                print_r( $fileName );
                exit;
            }
        }


        // Set Contao Settings
        foreach( (array) $cmsConfig->settings as $varName => $varValue)
        {
            if( is_array($varValue) )
            {
                $varValue = serialize($varValue);
            }

            $varValue = $this->replaceVars( $varValue );

            $this->Config->update( $varName, $varValue);
        }
    }


    protected function createNewModelEntry($modelName, array $arrModelValue, array $arrAddToModel = array())
    {
        if( is_array($arrModelValue) && count($arrModelValue) )
        {
            $modelClass = '\\' . $modelName . 'Model';

            foreach( $arrModelValue as $arrValue)
            {
                $objModel = new $modelClass();

                foreach($arrValue as $valueVar => $valueVarValue)
                {
                    $valueVarValue = $this->replaceVars( $valueVarValue);

                    $objModel->$valueVar = $valueVarValue;
                }

                if( count($arrAddToModel) )
                {
                    foreach($arrAddToModel as $key => $value)
                    {
                        $value = $this->replaceVars( $value);

                        $objModel->$key = $value;
                    }
                }

//                echo "<pre>"; print_r($objModel); exit;
                $objModel->save();
            }
        }
    }

    protected function createNewOneModelEntry($modelName, array $arrModelValue)
    {
        if( is_array($arrModelValue) && count($arrModelValue) )
        {
            $modelClass = '\\' . $modelName . 'Model';
            $objModel   = new $modelClass();

            foreach($arrModelValue as $valueVar => $valueVarValue)
            {
                $valueVarValue = $this->replaceVars( $valueVarValue);

                $objModel->$valueVar = $valueVarValue;
            }
//            echo "<pre>"; print_r($objModel); exit;
            return $objModel->save();
        }

        return false;
    }



    protected function replaceVars( $varValue )
    {
        if( is_array($varValue) )
        {
            $varValue = serialize($varValue);
        }

        preg_match_all('/##[^#]+##/', $varValue, $arrChunks);

        foreach ($arrChunks[0] as $strChunk)
        {
            $strOriginKey   = substr($strChunk, 2, -2);
            $strKey         = strtolower($strOriginKey);

            switch( $strKey )
            {
                case "customer_name":
                case "customer":
                case "customerName":
                case "customername":
                    $varValue = str_replace($strChunk, \Input::post("customer_name"), $varValue);
                    break;

                case "customer_alias":
                case "customerAlias":
                case "customeralias":
                    $varValue = str_replace($strChunk, \Input::post("customer_alias"), $varValue);
                    break;

                case "customer_email":
                case "customerEmail":
                case "customeremail":
                    $varValue = str_replace($strChunk, \Input::post("customer_email"), $varValue);
                    break;

                case "admin_email":
                case "adminEmail":
                case "adminemail":
                    $adminMail = \Input::post("adminEmail")?:\Input::post("admin_email");

                    if( $adminMail )
                    {
                        $varValue = str_replace($strChunk, $adminMail, $varValue);
                    }
                    else
                    {
                        $varValue = str_replace($strChunk, $this->objBackendMember->email, $varValue);
                    }
                    break;

                case "admin_id":
                case "adminID":
                case "adminId":
                case "adminid":
                case "admin_user_id":
                case "adminUserID":
                case "adminUserId":
                case "adminuserid":
                    $varValue = str_replace($strChunk, $this->objBackendMember->id, $varValue);
                    break;

                case "editor_user_id":
                case "editorUserID":
                case "editorUserId":
                case "editoruserid":
                    if( $this->objBackendEditor )
                    {
                        $varValue = str_replace($strChunk, $this->objBackendEditor->id, $varValue);
                    }
                    else
                    {
                        $varValue = str_replace($strChunk, '', $varValue);
                    }
                    break;

                case "editor_group_id":
                case "editorGroupID":
                case "editorGroupId":
                case "editorgroupid":
                    if( $this->objBackendEditorGroup )
                    {
                        $varValue = str_replace($strChunk, $this->objBackendEditorGroup->id, $varValue);
                    }
                    else
                    {
                        $varValue = str_replace($strChunk, '', $varValue);
                    }
                    break;

                case "layout_id":
                case "layoutID":
                case "layoutId":
                case "layoutid":
                    $varValue = 1;
                    break;

                default:
                    if( preg_match('/__/', $strKey) )
                    {
                        $arrParts = explode("__", $strOriginKey);

                        if( $arrParts[0] == "page" )
                        {
                            $arrObjectParts = explode("_", $arrParts[1]);
                            $objectKey      = $arrObjectParts[1];
                            $object         = $this->arrPages[ $arrObjectParts[0] ];

                            $varValue = str_replace($strChunk, $object->$objectKey, $varValue);
                        }
                        elseif( $arrParts[0] == "files" )
                        {
                            $folderPath = 'files/' . $this->replaceVars( str_replace(array('{', '}'), array('##', '##'), $arrParts[1]) );
                            $objFolder  = \FilesModel::findByPath( $folderPath );

                            if( $objFolder )
                            {
                                $varValue = str_replace($strChunk, $objFolder->uuid, $varValue);
                            }
                        }
                        elseif( $arrParts[0] == "func" || $arrParts[0] == "function" )
                        {
                            $funcName = $arrParts[1];
                            $varValue = str_replace($strChunk, $funcName(), $varValue);
                        }
                    }
                    else
                    {
                        echo "<pre>";
                        print_r( $strKey );
                        exit;
                    }

            }
        }

        return $varValue;
    }



    protected function createPages( array $arrPages, array $arrAddToPage = array() )
    {
        if( is_array($arrPages) && count($arrPages) )
        {
            foreach($arrPages as $pageName => $arrPage)
            {
                $arrPage        = (array) $arrPage;
                $arrSubPages    = (array) $arrPage['subpages'];
                $arrArticles    = (array) $arrPage['articles'];

                unset($arrPage['subpages']);
                unset($arrPage['articles']);

                if( count($arrAddToPage) )
                {
                    $arrPage = array_merge($arrPage, $arrAddToPage);
                }

//                $objInsertPage = false;
                $objInsertPage = $this->createNewOneModelEntry("Page", $arrPage);

                if( $objInsertPage )
                {
                    $this->arrPages[ $pageName ] = $objInsertPage;
                }

                if( is_array($arrArticles) && count($arrArticles) )
                {
                    $arrAddToArticle = array();

                    if( $objInsertPage )
                    {
                        $arrAddToArticle = array('pid'=>$objInsertPage->id);
                    }

                    $this->createNewModelEntry("Article", $arrArticles, $arrAddToArticle);
                }

                if( is_array($arrSubPages) && count($arrSubPages) )
                {
                    $arrAddToPage = array
                    (
                        'pid' => $objInsertPage->id
                    );

                    $this->createPages( $arrSubPages, $arrAddToPage );
                }

            }
        }
    }

}