<?php

namespace Acme\Bundle\CustomerBundle\Tests\Controller;

use Oro\Bundle\FlexibleEntityBundle\Tests\Controller\AbstractControllerTest;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
class CustomerControllerTest extends AbstractControllerTest
{
    /**
     * Test related method
     */
    public function testIndex()
    {
        $this->client->request('GET', '/en/customer/customer/index');

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Test related method
     */
    public function testInsert()
    {
        $this->client->request('GET', '/en/customer/customer/insert');

        $this->assertEquals(302, $this->client->getResponse()->getStatusCode());
    }
}
