<?php
/*******************************************************************
 * (c) 2020 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\Tests;


use IIDO\BasicBundle\IIDOBasicBundle;
use PHPUnit\Framework\TestCase;


class IIDOBasicBundleTest extends TestCase
{

    public function testCanBeInstantiated()
    {
        $bundle = new IIDOBasicBundle();
        $this->assertInstanceOf('IIDO\BasicBundle\IIDOBasicBundle', $bundle);
    }
}
