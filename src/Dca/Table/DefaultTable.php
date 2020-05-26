<?php
declare (strict_types=1);
/*******************************************************************
 * (c) 2020 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\Dca\Table;


use Contao\DataContainer;
use Contao\Exception;
use Contao\Image;
use Contao\Input;
use Contao\StringUtil;
use Contao\System;
use Contao\Backend;
use Contao\Versions;
use Contao\CoreBundle\Framework\ContaoFramework;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;


/**
 * Class DefaultTable
 *
 * @package IIDO\BasicBundle\Dca\Table
 *
 * @todo still needed?? eine schönere lösung finden, die global einsetzbar ist
 */
class DefaultTable
{
    /**
     * @var ContaoFramework
     */
    private $framework;


    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;


    /**
     * @var SessionInterface
     */
    private $session;


    private $entityManager;


    private $User;


    private $Database = null;

    protected $strTableName;



    // Dependency Injection: Die Klasse werden in der Datei /src/Resources/config/services.yml definiert
    public function __construct(ContaoFramework $framework, TokenStorageInterface $tokenStorage, SessionInterface $session, $entityManager)
    {
        $this->framework        = $framework;
        $this->tokenStorage     = $tokenStorage;
        $this->session          = $session;
        $this->entityManager    = $entityManager;

        $token                  = $this->tokenStorage->getToken();
        $this->User             = $token->getUser();
        $this->Database         = System::importStatic('Database');
    }



    public function editItem($row, $href, $label, $title, $icon, $attributes)
    {
        return '<a href="' . Backend::addToUrl($href . '&amp;id=' . $row['id']) . '" title="' . StringUtil::specialchar($title) . '"' . $attributes . '>' . Image::getHtml($icon, $label) . '</a> ';
    }
}