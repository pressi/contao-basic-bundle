<?php
/*******************************************************************
 *
 * (c) 2019 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 *
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *
 *******************************************************************/

namespace IIDO\BasicBundle\Criteria;


use Contao\CoreBundle\Framework\FrameworkAwareInterface;
use Contao\CoreBundle\Framework\FrameworkAwareTrait;
use IIDO\BasicBundle\Manager\BasicManager;
use Doctrine\DBAL\Connection;


class CriteriaBuilder implements FrameworkAwareInterface
{
    use FrameworkAwareTrait;


    /**
     * @var Connection
     */
    private $db;


    /**
     * @var BasicManager
     */
    private $manager;



    /**
     * CriteriaBuilder constructor.
     *
     * @param Connection   $db
     * @param BasicManager $manager
     */
    public function __construct(Connection $db, BasicManager $manager)
    {
        $this->db       = $db;
        $this->manager  = $manager;
    }

}