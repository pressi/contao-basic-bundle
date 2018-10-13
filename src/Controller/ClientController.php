<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\Controller;


//use Contao\Encryption;
use Contao\Environment;
use IIDO\BasicBundle\ConnectTool;
//use Doctrine\DBAL\DBALException;
//use Patchwork\Utf8;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
//use Symfony\Component\Filesystem\Filesystem;
//use Symfony\Component\Finder\Finder;
//use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;


/**
 * Handles the iido client.
 *
 * @author Stephan Preßl <development@prestep.at>
 *
 * @Route("/iido", defaults={"_scope" = "frontend", "_token_check" = true})
 */
class ClientController implements ContainerAwareInterface
{
    use ContainerAwareTrait;


    /**
     * @var array
     */
    private $context = [
        'initialized'   => false
    ];



    /**
     * Handles the installation process.
     *
     * @return Response
     *
     * @Route("/client", name="iido_client")
     */
    public function indexAction()
    {
        $arrReturn = array
        (
            'error'     => true,
            'message'   => '',
        );
        exit;

//        if( !$this->authenticate() )
//        {
//            $arrReturn;
//        }

//        echo "<pre>";
//        print_r( "CLIENT" );
//        echo "<br><br>";
//        print_r( \Environment::get("base") );
//        echo "<br>";
//        print_r( \Environment::get("request") );
//        echo "<br>";
//        print_r( \Environment::get("httpXForwardedHost") );
//        exit;

//        $connectTool = $this->container->get('contao.connect_tool');
//
//        if( $connectTool->connectionLost() )
//        {
//            return $this->render('noConection.html.twig');
//        }
//
//        if( $connectTool->isLocked() )
//        {
//            return $this->render('locked.html.twig');
//        }
//
//        if( !$this->container->get('contao.connect_tool_user')->isAuthenticated() )
//        {
//            return $this->login( $connectTool );
//        }
//
//        if (null !== ($response = $this->setUpClient( $connectTool )))
//        {
//            return $response;
//        }
//
//        $this->setConnectVarsToContext( $connectTool );
//
//        return $this->render('main.html.twig', $this->context);
    }



    /**
     * Renders a form to log in.
     *
     * @return Response|RedirectResponse
     */
    private function login( $connectTool )
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();

        if( 'tl_login' !== $request->request->get('FORM_SUBMIT') )
        {
            return $this->render('login.html.twig');
        }

        $verified       = $connectTool->checkPassword( $request->request->get('password') );

        if (!$verified)
        {
            $connectTool->increaseLoginCount();

            return $this->render('login.html.twig', [
                'error' => $this->trans('invalid_password'),
            ]);
        }

        $connectTool->resetLoginCount();
        $this->container->get('contao.connect_tool_user')->setAuthenticated(true);
        $this->container->get('contao.connect_tool_user')->setPassword( $request->request->get('password') );

        return $this->getRedirectResponse();
    }



    /**
     * Renders a template.
     *
     * @param string $name
     * @param array  $context
     *
     * @return Response
     */
    private function render($name, $context = [])
    {
        return new Response(
            $this->container->get('twig')->render(
                '@IIDOBasic/'.$name,
                $this->addDefaultsToContext($context)
            )
        );
    }



    /**
     * Translate a key.
     *
     * @param string $key
     *
     * @return string
     */
    private function trans($key)
    {
        return $this->container->get('translator')->trans($key);
    }



    /**
     * Returns a redirect response to reload the page.
     *
     * @return RedirectResponse
     */
    private function getRedirectResponse()
    {
        return new RedirectResponse($this->container->get('request_stack')->getCurrentRequest()->getRequestUri());
    }



    /**
     * Adds the default values to the context.
     *
     * @param array $context
     *
     * @return array
     */
    private function addDefaultsToContext(array $context)
    {
        $context = array_merge($this->context, $context);

        if (!isset($context['request_token']))
        {
            $context['request_token'] = $this->getRequestToken();
        }

        if (!isset($context['language']))
        {
            $context['language'] = $this->container->get('translator')->getLocale();
        }

        if (!isset($context['ua']))
        {
            $context['ua'] = Environment::get('agent')->class;
        }

        if (!isset($context['path']))
        {
            $context['path'] = $this->container->get('request_stack')->getCurrentRequest()->getBasePath();
        }

        return $context;
    }



    /**
     * Returns the request token.
     *
     * @return string
     */
    private function getRequestToken()
    {
        if (!$this->container->hasParameter('contao.csrf_token_name'))
        {
            return '';
        }

        return $this->container
            ->get('security.csrf.token_manager')
            ->getToken($this->getContainerParameter('contao.csrf_token_name'))
            ->getValue();
    }



    /**
     * Returns a parameter from the container.
     *
     * @param string $name
     *
     * @return mixed
     */
    private function getContainerParameter($name)
    {
        if ($this->container->hasParameter($name))
        {
            return $this->container->getParameter($name);
        }

        return null;
    }



    /**
     * Set connect variables to context
     *
     * @var ConnectTool $connectTool
     */
    private function setConnectVarsToContext( $connectTool )
    {
        $arrData = $connectTool->getData();

        $this->context['themes']            = (array) $arrData->themes;
        $this->context['templates']         = (array) $arrData->rsce_templates;

        $this->context['masterStylesheets'] = $this->renderObjectToArray( $arrData->stylesheets );

        if( !$connectTool->getConfig("clientID") )
        {
            $connectTool->persistConfig("clientID", $arrData->clientID);
        }
    }



    /**
     * Set up the client.
     *
     * @var ConnectTool $connectTool
     *
     * @return Response|RedirectResponse|null
     */
    private function setUpClient( $connectTool )
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();

        if ( $connectTool->isConnectToolInitialized() )
        {
            $this->context['initialized'] = true;

            if( $request->request->get('FORM_SUBMIT') === "tl_add_config")
            {
                return $this->addToClient( $connectTool, $request->request );
            }

            return null;
        }

        if ('tl_config' !== $request->request->get('FORM_SUBMIT'))
        {
            return null;
        }

        $customerName       = $request->request->get('customer_name');
        $customerEmail      = $request->request->get('customer_email');
        $customerAlias      = $request->request->get('customer_alias');
        $customerUsername   = $request->request->get('customer_username');

        $themeID            = $request->request->get('theme');
        $RSCE_Templates     = $request->request->get('rsce_templates');

        $webfont            = $request->request->get("webfont");

        // All fields are mandatory
        if( '' === $customerName || '' === $customerEmail || '' === $customerAlias || '' === $themeID )
        {
            $this->context['config_error'] = $this->trans('config_error');

            return null;
        }

        if( $customerUsername === '' )
        {
            $customerUsername = 'user';
        }

        $arrData = $connectTool->getActionData("getContaoInit", array('themeID=' . $themeID) );

        $connectTool->setUpFilesFolder( $arrData->folders );
        $connectTool->setUpTemplates( $arrData->templates );


        $arrTheme   = (array) $arrData->theme;
        unset($arrTheme['layouts']);
        unset($arrTheme['modules']);
        unset($arrTheme['imageSizes']);
        unset($arrTheme['imageSizeItems']);

        $connectTool->createNewOneModelEntry("Theme", $arrTheme, array('master_ID' => $themeID));

        // Create Layouts
        $connectTool->createNewModelEntry("Layout", (array) $arrData->theme->layouts, array('master_ID' => 'field_id', 'webfonts' => $webfont));

        // Create Modules
        $connectTool->createNewModelEntry("Module", (array) $arrData->theme->modules, array('master_ID' => 'field_id'));

        // Create Image Sizes
        $connectTool->createNewModelEntry("ImageSize", (array) $arrData->theme->imageSizes);

        // Create Image Size Items
        $connectTool->createNewModelEntry("ImageSizeItem", (array) $arrData->theme->imageSizeItems);


        // Create Pages
        $connectTool->createPages( (array) $arrData->pages );


        // Backend Users
        foreach((array) $arrData->users as $username => $arrUser)
        {
            $arrUser = (array) $arrUser;
//            $arrUser['password'] = Encryption::hash($arrUser['password']);

            if( $arrUser['username'] === "##customer_username##" )
            {
                $arrUser['username'] = $customerUsername;
            }

            $connectTool->createNewOneModelEntry("User", $arrUser);
        }


        // Backend User & Group (Redakteur)
        $arrUser = (array) $arrData->user;
//        $arrUser['password'] = Encryption::hash($arrUser['password']);

        $objUserGroup   = $connectTool->createNewOneModelEntry("UserGroup", (array) $arrData->user_group);
        $this->container->get("contao.connect_tool")->setBackendUserGroup( $objUserGroup );

        $objUser        = $connectTool->createNewOneModelEntry("User", $arrUser);
        $this->container->get("contao.connect_tool")->setBackendUser( $objUser );


        // Get RSCE Templates
        if( is_array($RSCE_Templates) && count($RSCE_Templates) )
        {
            $arrTemplateFiles = array();

            foreach($RSCE_Templates as $fileName)
            {
                $arrTemplateFiles[] = 'templates/' . $customerAlias . '/' . $fileName;

                if( preg_match('/^rsce_/', $fileName) && preg_match('/.html5$/', $fileName) )
                {
                    $arrTemplateFiles[] = 'templates/' . $customerAlias . '/' . preg_replace('/.html5$/', '_config.php', $fileName);
                }
            }

            if( count($arrTemplateFiles) )
            {
                $connectTool->getFilesFromMaster( $arrTemplateFiles, $customerAlias );
            }
        }


        // Set Contao Settings
        foreach( (array) $arrData->settings as $varName => $varValue)
        {
            if( is_array($varValue) )
            {
                $varValue = serialize($varValue);
            }

            $varValue = $connectTool->replaceVars( $varValue );

            $connectTool->persistConfig( $varName, $varValue);
        }

        $connectTool->persistConfig( 'iido_initSystem', TRUE);
        $connectTool->persistConfig( 'clientID', $arrData->clientID);

        return $this->getRedirectResponse();
    }


    /**
     * Set up the client.
     *
     * @var ConnectTool $connectTool
     * @var ParameterBag $request
     *
     * @return Response|RedirectResponse|null
     */
    protected function addToClient( $connectTool, $request )
    {
        $arrFiles       = array();
        $arrStylesheets = $request->get("master_stylessheets");

        if( $arrStylesheets )
        {
            foreach($arrStylesheets as $strStylesheet)
            {
                $arrFiles[] = 'files/master/css/' . $strStylesheet;
            }
        }

        $connectTool->getFilesFromMaster( $arrFiles );

        return $this->getRedirectResponse();
    }



    protected function renderObjectToArray( $object )
    {
        if( !is_object($object) && !is_array($object) )
        {
            $array = $object;
            return $array;
        }

        $array = (array) $object;

        foreach($array as $key => $value )
        {
            if( !empty($value) )
            {
                $array[ $key ] = $this->renderObjectToArray($value );
            }
            else
            {
                $array[ $key ] = $value;
            }
        }

        return $array;
    }

}
