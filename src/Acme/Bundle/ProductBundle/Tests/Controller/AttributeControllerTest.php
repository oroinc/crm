<?php

namespace Acme\Bundle\ProductBundle\Tests\Controller;

use Oro\Bundle\FlexibleEntityBundle\Tests\Controller\AbstractControllerTest;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
class AttributeControllerTest extends AbstractControllerTest
{
    /**
     * Test related method
     */
    public function testInsert()
    {
        $this->client->request('GET', '/en/product/attribute/insert');

        $this->assertEquals(302, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Test related method
     */
    public function testIndex()
    {
        $this->client->request('GET', '/en/product/attribute/index');

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

}
