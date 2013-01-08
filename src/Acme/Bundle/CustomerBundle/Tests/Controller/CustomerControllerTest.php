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

    /**
     * Test related method
     */
    public function testView()
    {
        // insert attributes data then customers data
        $this->client->request('GET', '/en/customer/attribute/insert');
        $this->assertEquals(302, $this->client->getResponse()->getStatusCode());
        $this->client->request('GET', '/en/customer/customer/insert');
        $this->assertEquals(302, $this->client->getResponse()->getStatusCode());

        // call and assert view
        $this->client->request('GET', '/en/customer/customer/view/1');

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Test query actions
     */
    public function testQueries()
    {
        // insert attributes data then customers data
        $this->client->request('GET', '/en/customer/attribute/insert');
        $this->assertEquals(302, $this->client->getResponse()->getStatusCode());
        $this->client->request('GET', '/en/customer/customer/insert');
        $this->assertEquals(302, $this->client->getResponse()->getStatusCode());

        $actions = array(
            '/en/customer/customer/querylazyload',
            '/en/customer/customer/queryonlydob',
            '/en/customer/customer/queryonlydobandgender',
            '/en/customer/customer/queryfilterfirstname',
            '/en/customer/customer/queryfilterfirstnameandcompany',
            '/en/customer/customer/queryfilterfirstnameandlimit',
            '/en/customer/customer/queryfilterfirstnameandorderbirthdatedesc'
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
            '/en/customer/customer/querylazyload',
            '/en/customer/customer/queryfilterfirstname',
            '/en/customer/customer/queryfilterfirstnameandlimit',
        );
        foreach ($actions as $action) {
            $this->client->request('GET', $action);
            $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        }

        // actions returning exception
        $actions = array(
            '/en/customer/customer/queryonlydob',
            '/en/customer/customer/queryonlydobandgender',
            '/en/customer/customer/queryfilterfirstnameandcompany',
            '/en/customer/customer/queryfilterfirstnameandorderbirthdatedesc'
        );
        foreach ($actions as $action) {
            $this->client->request('GET', $action);
            $this->assertEquals(500, $this->client->getResponse()->getStatusCode());
        }
    }
}
