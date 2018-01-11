<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle;

use Contao\Backend;
use Contao\BackendUser;
use Contao\Config;
use Contao\System;
use Contao\Encryption;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use IIDO\BasicBundle\Config\BundleConfig;

/**
 * Provides connection related methods.
 *
 * @author Stephan Preßl <https://github.com/pressi>
 */
class ConnectTool
{
    /**
     * @var Connection
     */
    private $connection;



    /**
     * @var ContainerInterface
     */
    private $container;


    /**
     * @var string
     */
    private $rootDir;


    /**
     * @var string
     */
    private $configFile      = 'Resources/config/master-connection.json';


    /**
     * @var string
     */
    private $bundlePath;


    /**
     * @var BackendUser
     */
    private $User;



    /**
     * @var \UserModel
     */
    private $backendUser            = null;



    /**
     * @var \UserGroupModel
     */
    private $backendUserGroup       = null;




    /**
     * Constructor.
     *
     * @param Connection $connection
     * @param string     $rootDir
     */
    public function __construct(Connection $connection, $rootDir)
    {
        $this->bundlePath   = BundleConfig::getBundlePath();

        $this->connection   = $connection;
        $this->rootDir      = $rootDir;

        $this->container    = System::getContainer();

        $this->User         = BackendUser::getInstance();
    }



    /**
     * set password in tool user session
     *
     * @param string $strPassword
     */
    public function setPassword( $strPassword )
    {
        $this->container->get('contao.connect_tool_user')->setPassword( $strPassword );
    }



    /**
     * set backend user (redakteur)
     *
     * @param \UserModel $objBackendUser
     */
    public function setBackendUser( $objBackendUser )
    {
        $this->backendUser = $objBackendUser;
    }



    /**
     * set backend user group (redakteur group)
     *
     * @param \UserGroupModel $objBackendUserGroup
     */
    public function setBackendUserGroup( $objBackendUserGroup )
    {
        $this->backendUserGroup = $objBackendUserGroup;
    }



    public function checkPassword( $strPassword )
    {
        return $this->getActionData('checkPassword', array('pwd'=>$strPassword), false, true);
    }



    public function testConnection()
    {
        return $this->getActionData("testConnection", array(), true);
    }



    public function connectionLost()
    {
        $arrData = $this->testConnection();

        if( is_array($arrData) && key_exists("ERROR", $arrData) )
        {
            if( $arrData[ 'ERROR' ] === $this->container->get("translator")->trans("connection_failed") )
            {
                return true;
            }
        }

        if( !is_array($arrData) )
        {
            return $arrData;
        }

        return false;
    }



    /**
     * Returns true if the install tool has been locked.
     *
     * @return bool
     */
    public function isLocked()
    {
        $cache = \System::getContainer()->get('contao.cache');

        if ($cache->contains('login-count'))
        {
            return intval($cache->fetch('login-count')) >= 3;
        }

        return false;
    }



    /**
     * Returns true if the install tool can write files.
     *
     * @return bool
     */
    public function canWriteFiles()
    {
        return is_writable(__FILE__);
    }



    /**
     * Checks if the license has been accepted.
     *
     * @return bool
     */
    public function shouldAcceptLicense()
    {
        return !Config::get('licenseAccepted');
    }



    /**
     * Increases the login count.
     */
    public function increaseLoginCount()
    {
        $cache = \System::getContainer()->get('contao.cache');

        if ($cache->contains('login-count'))
        {
            $count = intval($cache->fetch('login-count')) + 1;
        }
        else
        {
            $count = 1;
        }

        $cache->save('login-count', $count);
    }



    /**
     * Resets the login count.
     */
    public function resetLoginCount()
    {
        \File::putContent('system/tmp/login-count.txt', 0);
    }



    /**
     * Sets a database connection object.
     *
     * @param Connection $connection
     */
    public function setConnection(Connection $connection)
    {
        $this->connection = $connection;
    }



    /**
     * Checks if a table exists.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasTable($name)
    {
        return $this->connection->getSchemaManager()->tablesExist([$name]);
    }



    /**
     * Checks if the installation is fresh.
     *
     * @return bool
     */
    public function isFreshInstallation()
    {
        if (!$this->hasTable('tl_module'))
        {
            return true;
        }

        $statement = $this->connection->query('SELECT COUNT(*) AS count FROM tl_page');

        return $statement->fetch(\PDO::FETCH_OBJ)->count < 1;
    }



    /**
     * Handles executing the runonce files.
     */
    public function handleRunOnce()
    {
        // Wait for the tables to be created (see #5061)
        if (!$this->hasTable('tl_log'))
        {
            return;
        }

        Backend::handleRunOnce();
    }



    /**
     * Returns a Contao parameter.
     *
     * @param string $key
     *
     * @return mixed|null
     */
    public function getConfig($key)
    {
        return Config::get($key);
    }



    /**
     * Sets a Contao parameter.
     *
     * @param string $key
     * @param mixed  $value
     */
    public function setConfig($key, $value)
    {
        Config::set($key, $value);
    }



    /**
     * Persists a Contao parameter.
     *
     * @param string $key
     * @param mixed  $value
     */
    public function persistConfig($key, $value)
    {
        $config = Config::getInstance();
        $config->persist($key, $value);
        $config->save();
    }



    /**
     * Logs an exception in the current log file.
     *
     * @param \Exception $e
     */
    public function logException(\Exception $e)
    {
        error_log(
            sprintf(
                "PHP Fatal error: %s in %s on line %s\n%s\n",
                $e->getMessage(),
                $e->getFile(),
                $e->getLine(),
                $e->getTraceAsString()
            ),
            3,
            $this->rootDir.'/../var/logs/prod-'.date('Y-m-d').'.log'
        );
    }



    public function getData($returnAsArray = false)
    {
        $connectionUrl  = $this->getConnectionUrl();
        $arrData        = file_get_contents( $connectionUrl );

        if( !$arrData )
        {
            return array("ERROR" => $this->container->get("translator")->trans("connection_failed") );
        }

        $objData        = json_decode($arrData, $returnAsArray);
        $clientID       = $this->getConfig( "clientID" );

        if( !$clientID )
        {
            $this->persistConfig("clientID", (($returnAsArray) ? $objData['clientID'] : $objData->clientID) );
        }

        return $objData;
    }



    public function getActionData( $actionName, array $actionParams = array(), $returnAsArray = false, $returnBoolean = false)
    {
        if( $actionName == "checkPassword" )
        {
            $this->setPassword( $actionParams['pwd'] );
            unset( $actionParams['pwd'] );
        }

//        $opts = array
//        (
//            'http'=>array
//            (
//                'method'=>"GET",
//                'header'=>"Cache-Control: no-store, no-cache, must-revalidate, max-age=0\r\n" .
//                    "Cache-Control: post-check=0, pre-check=0\r\n" .
//                    "Pragma: no-cache\r\n" .
//                    "Content-Type: application/json;charset=utf-8"
//            )
//        );

//        $context = stream_context_create($opts);

        $arrParams = array();

        if( count($actionParams) )
        {
            foreach($actionParams as $paramKey => $paramValue)
            {
                $param = $paramKey . '=' . $paramValue;

                if( is_numeric($paramKey) )
                {
                    $param = $paramValue;
                }

                $arrParams[] = $param;
            }
        }

        $connectionUrl  = $this->getConnectionUrl() . '&act=' . $actionName . (count($arrParams)?'&':'') . implode('&', $arrParams);
//        $arrData        = trim(file_get_contents( $connectionUrl, false, $context ), "\xEF\xBB\xBF");
        $arrData        = file_get_contents( $connectionUrl );
        $return         = false;
//echo "<pre>";
//print_r( $connectionUrl );
//        print_r( $arrData );
//        echo "<br>B: ";
//        print_r( $returnBoolean );
//        echo "<br>A: ";
//        print_r( $returnAsArray );
//echo "</pre>";

//print_r( parse)

        if( !$arrData )
        {
            if( !$returnBoolean )
            {
                $return = array("ERROR" => $this->container->get("translator")->trans("connection_failed") );
            }
        }
        else
        {
            $return = json_decode($arrData, $returnAsArray);
//            echo "<pre>2A: "; print_r( $arrData ); echo "</pre>";
//            echo "<pre>2: "; print_r( $return ); echo "</pre>";
            if( !is_array($return) && !is_object($return) )
            {
                $return = json_decode($return, $returnAsArray);
            }
        }

        if( $returnBoolean )
        {
            if( key_exists('ERROR', $return) )
            {
                return false;
            }
        }
//echo "<pre>3: "; print_r( $return ); echo "</pre>";
        return $returnBoolean ? true : $return;
    }



    protected function getConnectionUrl()
    {
        $configData = $this->getConfigData();
        return $configData->domain . $configData->connection->publicPath . $configData->connection->file . '?pwd=' . $this->getPassword() . $this->getConnectionUrlVars( $configData );
    }



    public function getConfigData()
    {
        return json_decode(file_get_contents($this->rootDir . '/../' . $this->bundlePath . '/' . $this->configFile));
    }



    protected function getConnectionUrlVars( $configData = NULL )
    {
        $requestVars = "";

        if( $configData === NULL )
        {
            $configData = $this->getConfigData();
        }

        foreach( (array) $configData->vars as $varName => $varData )
        {
            $varDataParsed  = "";
            $varData        = \StringUtil::deserialize( $varData );

            if( is_string($varData) )
            {
                $varDataParsed = $varData;
            }
            else
            {
                $arrVarData = array();

                foreach( (array) $varData as $key => $value )
                {
                    $arrVarData[ $key ] = $this->replaceVars( $value );
                }

                if( count($arrVarData) )
                {
                    $varDataParsed = json_encode( $arrVarData );
                }
            }

            if( $varDataParsed )
            {
                $requestVars .= '&' . $varName . '=' . $varDataParsed;
            }
        }

        return $requestVars;
    }



    protected function getPassword()
    {
        return $this->container->get('contao.connect_tool_user')->getPassword();
    }



    public function setUpFilesFolder( $arrFiles )
    {
        $arrFolders = array();

        foreach( $arrFiles as $strFolder => $arrSubfolders)
        {
            $strFolderPath  = "files/" . $this->replaceVars($strFolder);

            if( is_dir($this->rootDir . '/../' . $strFolderPath) )
            {
                $objFolder      = \FilesModel::findByPath( $strFolderPath );
            }
            else
            {
                $objFolder      = new \Folder( $strFolderPath );
            }

//            if( $objFolder )
//            {
//                $objFolder->protected =
//            }

            $arrFolders[] = $objFolder->path;

            if( is_array($arrSubfolders) && count($arrSubfolders) && $objFolder && is_dir( $this->rootDir . '/../' . $objFolder->path) )
            {
                foreach($arrSubfolders as $strSubFolder)
                {
                    $strSubFolder = $this->replaceVars( $strSubFolder );

                    if( is_dir($this->rootDir . '/../' . $objFolder->path . $strSubFolder) )
                    {
                        $objSubFolder = \FilesModel::findByPath( $objFolder->path . '/' . $strSubFolder );
                    }
                    else
                    {
                        $objSubFolder = new \Folder( $objFolder->path . '/' . $strSubFolder );
                    }

                    if( $strSubFolder === "Uploads" || $strSubFolder === "images" )
                    {
                        $objSubFolder->protected = TRUE;

//                        $objSubFolder->save();
                        $objSubFolder->unprotect();
                    }

                    $arrFolders[] = $objSubFolder->path;
                }
            }
        }

        if( count($arrFolders) )
        {
            \Dbafs::updateFolderHashes($arrFolders);
        }
    }



    public function setUpTemplates( $arrTemplates )
    {
        foreach( $arrTemplates as $strTemplateFolder)
        {
            $strFolderPath  = "templates/" . $this->replaceVars( $strTemplateFolder );

            if( !is_dir($this->rootDir . '/../' . $strFolderPath) )
            {
                mkdir( $this->rootDir . '/../' . $strFolderPath );
            }
        }
    }



    public function createNewOneModelEntry($modelName, array $arrModelValue, array $arrAddToModel = array())
    {
        if( is_array($arrModelValue) && count($arrModelValue) )
        {
            $request        = $this->container->get('request_stack')->getCurrentRequest();
            $customerAlias  = $request->request->get('customer_alias');

            $modelClass = '\\' . $modelName . 'Model';
            $objModel   = new $modelClass();

            foreach($arrModelValue as $valueVar => $valueVarValue)
            {
                if( in_array($valueVar, array('id')) )
                {
                    continue;
                }

                $valueVarValue = $this->replaceVars( $valueVarValue);

                if( $valueVar === "password" )
                {
                    if( !preg_match('/^[a-f0-9]{32}$/', $valueVarValue) )
                    {
                        $valueVarValue = Encryption::hash( $valueVarValue );
                    }
                }

                $objModel->$valueVar = $valueVarValue;
            }

            if( count($arrAddToModel) )
            {
                foreach($arrAddToModel as $key => $value)
                {
                    if( preg_match('/^field_/', $value) )
                    {
                        $fieldName  = preg_replace('/^field_/', '', $value);
                        $value      = $arrModelValue[ $fieldName ];
                    }

                    $value = $this->replaceVars( $value);

                    if( $key == "password" )
                    {
//                        if( !preg_match('/^[a-f0-9]{32}$/', $value) )
                        if( !Encryption::test( $value) )
                        {
                            $value = Encryption::hash( $value );
                        }
                    }

                    if( is_bool($value) || is_numeric($value) || strlen($value) )
                    {
                        $objModel->$key = $value;
                    }
                }
            }

            if( $modelName === "Theme" )
            {
                $objModel->templates        = 'templates/' . $customerAlias;

                $arrFolders         = array();
                $objMasterFolder    = \FilesModel::findByPath("files/master");
                $objCustomerFolder  = \FilesModel::findByPath("files/" . $customerAlias );

                if( $objMasterFolder )
                {
                    $arrFolders[] = $objMasterFolder->uuid;
                }

                if( $objCustomerFolder )
                {
                    $arrFolders[] = $objCustomerFolder->uuid;
                }

                if( count($arrFolders) )
                {
                    $objModel->folders = serialize($arrFolders);
                }
            }

            return $objModel->save();
        }

        return false;
    }



    public function createNewModelEntry($modelName, array $arrModelValue, array $arrAddToModel = array())
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
                        if( preg_match('/^field_/', $value) )
                        {
                            $fieldName  = trim( preg_replace('/^field_/', '', $value));
                            $value      = $arrValue->$fieldName;
                        }

                        $value = $this->replaceVars( $value);

                        if( $key == "password" )
                        {
                            if( !Encryption::test( $value) )
                            {
                                $value = Encryption::hash( $value );
                            }
                        }

                        if( is_bool($value) || is_numeric($value) || strlen($value) )
                        {
                            $objModel->$key = $value;
                        }
                    }
                }

                $objModel->save();
            }
        }
    }



    public function createPages( array $arrPages, array $arrAddToPage = array() )
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
                        $arrAddToArticle = array('pid' => $objInsertPage->id);
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



    public function replaceVars( $varValue )
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();
//echo "<pre>"; print_r( $this->container->get('') ); exit;
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
                    $varValue = str_replace($strChunk, $request->request->get("customer_name"), $varValue);
                    break;

                case "customer_alias":
                case "customerAlias":
                case "customeralias":
                    $varValue = str_replace($strChunk, $request->request->get("customer_alias"), $varValue);
                    break;

                case "customer_email":
                case "customerEmail":
                case "customeremail":
                    $varValue = str_replace($strChunk, $request->request->get("customer_email"), $varValue);
                    break;

                case "customer_username":
                case "customerUsername":
                case "customerusername":
                    $varValue = str_replace($strChunk, $request->request->get("customer_username")?:'user', $varValue);
                    break;

                case "admin_email":
                case "adminEmail":
                case "adminemail":
                    $adminMail = $request->request->get("adminEmail")?:$request->request->get("admin_email");

                    if( $adminMail )
                    {
                        $varValue = str_replace($strChunk, $adminMail, $varValue);
                    }
                    else
                    {
                        $varValue = str_replace($strChunk, $this->User->email, $varValue);
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
                    $varValue = str_replace($strChunk, $this->User->id, $varValue);
                    break;

                case "editor_user_id":
                case "editorUserID":
                case "editorUserId":
                case "editoruserid":
                    if( $this->backendUser instanceof \UserModel )
                    {
                        $varValue = str_replace($strChunk, $this->backendUser->id, $varValue);
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
                    if( $this->backendUserGroup instanceof \UserGroupModel )
                    {
                        $varValue = str_replace($strChunk, $this->backendUserGroup->id, $varValue);
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
                        elseif( $arrParts[0] == "env" )
                        {
                            $varValue = \Environment::get( $arrParts[1] );
                        }
                        elseif( $arrParts[0] == "config" )
                        {
                            $varValue = \Config::get( $arrParts[1] );

                            if( $varValue == "Contao Open Source CMS" )
                            {
                                $varValue = $request->request->get("customer_name");
                            }

                            if( $arrParts[1] === "websiteTitle" )
                            {
                                $varValue = $this->renderTitleString( $varValue );
                            }
                        }
                        elseif( $arrParts[0] == "cms" )
                        {
                            switch( $arrParts[1] )
                            {
                                case "version":
                                    $packages = $this->container->getParameter('kernel.packages');
                                    $varValue = $packages['contao/core-bundle'];
                                    break;

                                case "bundles":
                                    $varValue = array_keys($this->container->getParameter('kernel.bundles') );
                                    break;

                                case "packages":
                                    $varValue = $this->container->getParameter('kernel.packages');
                                    break;
                            }
                        }
                    }
                    else
                    {
                        echo "<pre>KEY<br>";
                        print_r( $strKey );
                        exit;
                    }

            }
        }

        return $varValue;
    }



    public function isConnectToolInitialized()
    {
        return $this->getConfig("iido_initSystem");
    }



    protected function renderTitleString( $strTitle )
    {
        $strTitle = preg_replace(array('/ä/', '/ö/', '/ü/', '/Ä/', '/Ö/', '/Ü/', '/ & /'), array(';ae;', ';oe;', ';ue;', ';AE;', ';OE;', ';UE;', ';and;'), $strTitle);
        $strTitle = preg_replace('/ß/', ';ss;', $strTitle);
        $strTitle = preg_replace('/ /', '+', $strTitle);

        return $strTitle;
    }



    public function getFoldersFromMaster( $arrFolders )
    {
        $arrData = $this->getActionData("getFolderFiles", array('foldersPath'=>implode(",", $arrFolders)));

        echo "<pre>";
        print_r( $arrData->files );
        exit;
    }



    public function getFilesFromMaster( $arrFiles, $customerAlias = '' )
    {
//        $arrData = $this->getActionData("getFiles", array('files'=>implode(",", $arrFiles)));

        $arrFolders     = array();
        $ftpSettings    = $this->getActionData("ftpSettings");

        $conn_id        = ftp_connect($ftpSettings->host);

        if( ftp_login($conn_id, $ftpSettings->username, $ftpSettings->password) )
        {
            foreach($arrFiles as $strFile)
            {
                $arrFolder      = explode('/', $strFile);
                $strMasterFile  = $strFile;

                if( preg_match('/^templates/', $strMasterFile) )
                {
                    $strMasterFile = preg_replace('/templates\/' . $customerAlias . '\//', 'templates/global/', $strMasterFile);
                }
                else
                {
                    array_pop( $arrFolder);

                    $arrFolders[] = $strFolder = implode('/', $arrFolder);

                    if( preg_match('/files\/master\/css/', $strFolder) && !preg_match('/css$/', $strFolder) )
                    {
                        $this->createFolder( $strFolder );
                    }
                }

                ftp_get( $conn_id, $this->rootDir . '/../' . $strFile, $strMasterFile, FTP_BINARY);
            }
        }

        ftp_close($conn_id);

        if( count($arrFolders) )
        {
            $arrFolders = array_unique($arrFolders);
            $arrFolders = array_values($arrFolders);

            \Dbafs::updateFolderHashes($arrFolders);
        }
    }



    protected function createFolder( $strFolderPath )
    {
        $arrFolders = array();

        if( !is_dir($this->rootDir . '/../' . $strFolderPath) )
        {
            $objFolder = new \Folder( $strFolderPath );

            $arrFolders[] = $objFolder->path;
        }

        if( count($arrFolders) )
        {
            $arrFolders = array_unique($arrFolders);
            $arrFolders = array_values($arrFolders);

            \Dbafs::updateFolderHashes($arrFolders);
        }
    }
}
