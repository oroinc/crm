<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Functional;

use Symfony\Component\DomCrawler\Form;

use Doctrine\Common\Persistence\ManagerRegistry;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\TestFrameworkBundle\Test\ToolsAPI;
use Oro\Bundle\TestFrameworkBundle\Test\Client;
use OroCRM\Bundle\AccountBundle\Entity\Account;

/**
 * @outputBuffering enabled
 * @db_isolation
 */
class OpportunityControllersTest extends WebTestCase
{
    /**
     * @var Client
     */
    protected $client;

    public function setUp()
    {
        $this->client = static::createClient(
            array(),
            array_merge(ToolsAPI::generateBasicHeader(), array('HTTP_X-CSRF-Header' => 1))
        );
    }

    public function testIndex()
    {
        $this->client->request('GET', $this->client->generate('orocrm_sales_opportunity_index'));
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200, 'text/html; charset=UTF-8');
    }

    public function testCreate()
    {
        $crawler = $this->client->request('GET', $this->client->generate('orocrm_sales_opportunity_create'));
        $account = $this->createAccount();
        /** @var Form $form */
        $form = $crawler->selectButton('Save and Close')->form();
        $name = 'name' . ToolsAPI::generateRandomString();
        $form['orocrm_sales_opportunity_form[name]']         = $name;
        $form['orocrm_sales_opportunity_form[account]']      = $account->getId();
        $form['orocrm_sales_opportunity_form[probability]']  = 50;
        $form['orocrm_sales_opportunity_form[budgetAmount]'] = 10000;
        $form['orocrm_sales_opportunity_form[customerNeed]'] = 10001;
        $form['orocrm_sales_opportunity_form[closeReason]']  = 'cancelled';
        $form['orocrm_sales_opportunity_form[owner]']        = 1;

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);

        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200, 'text/html; charset=UTF-8');
        $this->assertContains("Opportunity saved", $crawler->html());

        return $name;
    }

    /**
     * @return Account
     */
    protected function createAccount()
    {
        /** @var ManagerRegistry $registry */
        $registry = $this->client->getKernel()->getContainer()->get('doctrine');
        $entityManager = $registry->getManagerForClass('OroCRMAccountBundle:Account');

        $account = new Account();
        $account->setName('test account');

        $entityManager->persist($account);
        $entityManager->flush($account);

        return $account;
    }

    /**
     * @param $name
     * @depends testCreate
     *
     * @return string
     */
    public function testUpdate($name)
    {
        $result = ToolsAPI::getEntityGrid(
            $this->client,
            'sales-opportunity-grid',
            array(
                'sales-opportunity-grid[_filter][name][type]' => '1',
                'sales-opportunity-grid[_filter][name][value]' => $name,
            )
        );

        ToolsAPI::assertJsonResponse($result, 200);

        $result = ToolsAPI::jsonToArray($result->getContent());
        $result = reset($result['data']);
        $returnValue = $result;
        $crawler = $this->client->request(
            'GET',
            $this->client->generate('orocrm_sales_opportunity_update', array('id' => $result['id']))
        );

        /** @var Form $form */
        $form = $crawler->selectButton('Save and Close')->form();
        $name = 'name' . ToolsAPI::generateRandomString();
        $form['orocrm_sales_opportunity_form[name]'] = $name;

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);

        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200, 'text/html; charset=UTF-8');
        $this->assertContains("Opportunity saved", $crawler->html());

        $returnValue['name'] = $name;
        return $returnValue;
    }

    /**
     * @param $returnValue
     * @depends testUpdate
     *
     * @return string
     */
    public function testView($returnValue)
    {
        $crawler = $this->client->request(
            'GET',
            $this->client->generate('orocrm_sales_opportunity_view', array('id' => $returnValue['id']))
        );

        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200, 'text/html; charset=UTF-8');
        $this->assertContains("{$returnValue['name']} - Opportunities - Sales", $crawler->html());
    }

    /**
     * @param $returnValue
     * @depends testUpdate
     *
     * @return string
     */
    public function testInfo($returnValue)
    {
        $crawler = $this->client->request(
            'GET',
            $this->client->generate(
                'orocrm_sales_opportunity_info',
                array('id' => $returnValue['id'], '_widgetContainer' => 'block')
            )
        );

        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200, 'text/html; charset=UTF-8');
    }

    /**
     * @param $returnValue
     * @depends testUpdate
     */
    public function testDelete($returnValue)
    {
        $this->client->request(
            'DELETE',
            $this->client->generate('oro_api_delete_opportunity', array('id' => $returnValue['id']))
        );

        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 204);

        $this->client->request(
            'GET',
            $this->client->generate('orocrm_sales_opportunity_view', array('id' => $returnValue['id']))
        );
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 404, 'text/html; charset=UTF-8');
    }
}
