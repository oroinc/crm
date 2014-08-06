<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Functional;

use Doctrine\Common\Persistence\ManagerRegistry;

use Symfony\Component\DomCrawler\Form;

use OroCRM\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

/**
 * @outputBuffering enabled
 * @dbIsolation
 */
class OpportunityControllersTest extends WebTestCase
{
    protected function setUp()
    {
        $this->initClient(
            array(),
            array_merge($this->generateBasicAuthHeader(), array('HTTP_X-CSRF-Header' => 1))
        );
    }

    public function testIndex()
    {
        $this->client->request('GET', $this->getUrl('orocrm_sales_opportunity_index'));
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
    }

    public function testCreate()
    {
        $crawler = $this->client->request('GET', $this->getUrl('orocrm_sales_opportunity_create'));
        $account = $this->createAccount();
        /** @var Form $form */
        $form = $crawler->selectButton('Save and Close')->form();
        $name = 'name' . $this->generateRandomString();
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
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
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
     * @param string $name
     * @depends testCreate
     *
     * @return string
     */
    public function testUpdate($name)
    {
        $response = $this->client->requestGrid(
            'sales-opportunity-grid',
            array(
                'sales-opportunity-grid[_filter][name][type]' => '1',
                'sales-opportunity-grid[_filter][name][value]' => $name,
            )
        );

        $result = $this->getJsonResponseContent($response, 200);
        $result = reset($result['data']);
        $returnValue = $result;
        $crawler = $this->client->request(
            'GET',
            $this->getUrl('orocrm_sales_opportunity_update', array('id' => $result['id']))
        );

        /** @var Form $form */
        $form = $crawler->selectButton('Save and Close')->form();
        $name = 'name' . $this->generateRandomString();
        $form['orocrm_sales_opportunity_form[name]'] = $name;

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $this->assertContains("Opportunity saved", $crawler->html());

        $returnValue['name'] = $name;
        return $returnValue;
    }

    /**
     * @param array $returnValue
     * @depends testUpdate
     *
     * @return string
     */
    public function testView(array $returnValue)
    {
        $crawler = $this->client->request(
            'GET',
            $this->getUrl('orocrm_sales_opportunity_view', array('id' => $returnValue['id']))
        );

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $this->assertContains("{$returnValue['name']} - Opportunities - Sales", $crawler->html());
    }

    /**
     * @param array $returnValue
     * @depends testUpdate
     *
     * @return string
     */
    public function testInfo(array $returnValue)
    {
        $this->client->request(
            'GET',
            $this->getUrl(
                'orocrm_sales_opportunity_info',
                array('id' => $returnValue['id'], '_widgetContainer' => 'block')
            )
        );

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
    }

    /**
     * @param array $returnValue
     * @depends testUpdate
     */
    public function testDelete(array $returnValue)
    {
        $this->client->request(
            'DELETE',
            $this->getUrl('oro_api_delete_opportunity', array('id' => $returnValue['id']))
        );

        $result = $this->client->getResponse();
        $this->assertEmptyResponseStatusCodeEquals($result, 204);

        $this->client->request(
            'GET',
            $this->getUrl('orocrm_sales_opportunity_view', array('id' => $returnValue['id']))
        );
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 404);
    }
}
