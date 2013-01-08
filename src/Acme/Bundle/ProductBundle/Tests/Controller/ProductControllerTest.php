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
class ProductControllerTest extends AbstractControllerTest
{
    /**
     * Test related method
     */
    public function testIndex()
    {
        $this->client->request('GET', '/en/product/product/index');

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Test related method
     */
    public function testInsert()
    {
        $this->client->request('GET', '/en/product/product/insert');

        $this->assertEquals(302, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Test related method
     */
    public function testTranslate()
    {
        $this->client->request('GET', '/en/product/product/translate');

        $this->assertEquals(302, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Test query actions
     */
    public function testQueries()
    {
        // insert attributes data then products data
        $this->client->request('GET', '/en/product/attribute/insert');
        $this->assertEquals(302, $this->client->getResponse()->getStatusCode());
        $this->client->request('GET', '/en/product/product/insert');
        $this->assertEquals(302, $this->client->getResponse()->getStatusCode());

        $actions = array(
            '/en/product/product/querylazyload',
            '/en/product/product/queryonlyname',
            '/en/product/product/querynameanddesc',
            '/en/product/product/querynameanddescforcelocale',
            '/en/product/product/queryfilterskufield',
            '/en/product/product/querynamefilterskufield',
            '/en/product/product/queryfiltersizeattribute',
            '/en/product/product/queryfiltersizeanddescattributes',
            '/en/product/product/querynameanddesclimit',
            '/en/product/product/querynameanddescorderby',
        );
        foreach ($actions as $action) {
            $this->client->request('GET', $action);
            $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        }
    }

    /**
     * Test query actions without data
     */
    public function testQueriesWithoutData()
    {
        // actions returning code 200
        $actions = array(
            '/en/product/product/querylazyload',
            '/en/product/product/queryfilterskufield',
        );
        foreach ($actions as $action) {
            $this->client->request('GET', $action);
            $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        }

        // actions returning exception
        $actions = array(
            '/en/product/product/queryonlyname',
            '/en/product/product/querynameanddesc',
            '/en/product/product/querynameanddescforcelocale',
            '/en/product/product/querynamefilterskufield',
            '/en/product/product/queryfiltersizeattribute',
            '/en/product/product/queryfiltersizeanddescattributes',
            '/en/product/product/querynameanddesclimit',
            '/en/product/product/querynameanddescorderby',
        );
        foreach ($actions as $action) {
            $this->client->request('GET', $action);
            $this->assertEquals(500, $this->client->getResponse()->getStatusCode());
        }
    }

}
