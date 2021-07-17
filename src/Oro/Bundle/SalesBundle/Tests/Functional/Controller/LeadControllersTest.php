<?php

namespace Oro\Bundle\SalesBundle\Tests\Functional\Controller;

use Oro\Bundle\DataGridBundle\Tests\Functional\AbstractDatagridTestCase;
use Symfony\Component\DomCrawler\Field\ChoiceFormField;
use Symfony\Component\DomCrawler\Field\InputFormField;
use Symfony\Component\DomCrawler\Form;

class LeadControllersTest extends AbstractDatagridTestCase
{
    /** @var bool */
    protected $isRealGridRequest = false;

    protected function setUp(): void
    {
        $this->initClient(
            [],
            $this->generateBasicAuthHeader()
        );
        $this->client->useHashNavigation(true);
        $this->loadFixtures(['Oro\Bundle\SalesBundle\Tests\Functional\Fixture\LoadSalesBundleFixtures']);
    }

    public function testIndex()
    {
        $this->client->request('GET', $this->getUrl('oro_sales_lead_index'));
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
    }

    /**
     * @return string
     */
    public function testCreate()
    {
        $crawler = $this->client->request('GET', $this->getUrl('oro_sales_lead_create'));
        /** @var Form $form */
        $form = $crawler->selectButton('Save and Close')->form();
        $name = 'name' . $this->generateRandomString();
        $form['oro_sales_lead_form[name]']                = $name;
        $form['oro_sales_lead_form[firstName]']           = 'firstName';
        $form['oro_sales_lead_form[lastName]']            = 'lastName';
        $form['oro_sales_lead_form[companyName]']         = 'Company';
        $form['oro_sales_lead_form[emails][0][email]']    = 'test@example.test';
        $form['oro_sales_lead_form[owner]']               = 1;
        $form['oro_sales_lead_form[website]']             = 'http://example.com';
        //Add address fields to form as they are rendered with javascript
        $doc = new \DOMDocument("1.0");
        $addressInputs = ['city', 'label', 'postalCode', 'street', 'street2'];
        foreach ($addressInputs as $addressInput) {
            $input = $doc->createElement('input');
            $input->setAttribute('name', sprintf('oro_sales_lead_form[addresses][0][%s]', $addressInput));
            $field = new InputFormField($input);
            $form->set($field);
        }
        $doc->loadHTML(
            '<select name="oro_sales_lead_form[addresses][0][country]" ' .
            'id="oro_sales_lead_form_address_country" ' .
            'tabindex="-1" class="select2-offscreen"> ' .
            '<option value="" selected="selected"></option> ' .
            '<option value="US">United States</option> </select>'
        );
        $field = new ChoiceFormField($doc->getElementsByTagName('select')->item(0));
        $form->set($field);
        $doc->loadHTML(
            '<select name="oro_sales_lead_form[addresses][0][region]" id="oro_sales_lead_form_address_region" ' .
            'tabindex="-1" class="select2-offscreen"> ' .
            '<option value="" selected="selected"></option> ' .
            '<option value="US-CA">California</option> </select>'
        );
        $field = new ChoiceFormField($doc->getElementsByTagName('select')->item(0));
        $form->set($field);
        $form['oro_sales_lead_form[addresses][0][city]']       = 'City Name';
        $form['oro_sales_lead_form[addresses][0][label]']      = 'Main Address';
        $form['oro_sales_lead_form[addresses][0][postalCode]'] = '10000';
        $form['oro_sales_lead_form[addresses][0][street2]']    = 'Second Street';
        $form['oro_sales_lead_form[addresses][0][street]']     = 'Main Street';
        $form['oro_sales_lead_form[addresses][0][country]'] = 'US';
        $form['oro_sales_lead_form[addresses][0][region]'] = 'US-CA';

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        static::assertStringContainsString("Lead saved", $crawler->html());

        return $name;
    }

    /**
     * @dataProvider updateWithInvalidWebsiteDataProvider
     *
     * @depends      testCreate
     */
    public function testUpdateWithInvalidWebsite(string $website, string $name)
    {
        $response = $this->client->requestGrid(
            'sales-lead-grid',
            ['sales-lead-grid[_filter][name][value]' => $name]
        );

        $result = $this->getJsonResponseContent($response, 200);
        $result = reset($result['data']);
        $crawler = $this->client->request(
            'GET',
            $this->getUrl('oro_sales_lead_update', ['id' => $result['id']])
        );

        /** @var Form $form */
        $form = $crawler->selectButton('Save and Close')->form();
        $form['oro_sales_lead_form[website]'] = $website;

        $crawler = $this->client->submit($form);

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        static::assertStringContainsString(
            'This value is not a valid URL. Allowed URL protocols are: http, https.',
            $crawler->html()
        );
    }

    public function updateWithInvalidWebsiteDataProvider(): array
    {
        return [
            ['website' => 'sample-string'],
            ['website' => 'unsupported-protocol://sample-site'],
            ['website' => 'javascript:alert(1)'],
            ['website' => 'jAvAsCrIpt:alert(1)'],
        ];
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
            'sales-lead-grid',
            ['sales-lead-grid[_filter][name][value]' => $name]
        );

        $result = $this->getJsonResponseContent($response, 200);
        $result = reset($result['data']);
        $returnValue = $result;
        $crawler = $this->client->request(
            'GET',
            $this->getUrl('oro_sales_lead_update', ['id' => $result['id']])
        );

        /** @var Form $form */
        $form = $crawler->selectButton('Save and Close')->form();
        $name = 'name' . $this->generateRandomString();
        $form['oro_sales_lead_form[name]'] = $name;

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        static::assertStringContainsString("Lead saved", $crawler->html());

        $returnValue['name'] = $name;

        return $returnValue;
    }

    /**
     * @param array $returnValue
     * @depends testUpdate
     *
     * @return string
     */
    public function testView($returnValue)
    {
        $crawler = $this->client->request(
            'GET',
            $this->getUrl('oro_sales_lead_view', ['id' => $returnValue['id']])
        );

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        static::assertStringContainsString("{$returnValue['name']} - Leads - Sales", $crawler->html());
    }

    /**
     * @param array $returnValue
     * @depends testUpdate
     *
     * @return string
     */
    public function testInfo($returnValue)
    {
        $crawler = $this->client->request(
            'GET',
            $this->getUrl(
                'oro_sales_lead_info',
                ['id' => $returnValue['id'], '_widgetContainer' => 'block']
            )
        );

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        static::assertStringContainsString($returnValue['firstName'], $crawler->html());
        static::assertStringContainsString($returnValue['lastName'], $crawler->html());
    }

    /**
     * @param array $returnValue
     * @depends testUpdate
     */
    public function testDelete($returnValue)
    {
        $this->ajaxRequest(
            'DELETE',
            $this->getUrl('oro_api_delete_lead', ['id' => $returnValue['id']])
        );

        $result = $this->client->getResponse();
        $this->assertEmptyResponseStatusCodeEquals($result, 204);

        $this->client->request(
            'GET',
            $this->getUrl('oro_sales_lead_view', ['id' => $returnValue['id']])
        );

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 404);
    }

    /**
     * @return array
     */
    public function gridProvider()
    {
        return [
            'Lead grid'                => [
                [
                    'gridParameters'      => [
                        'gridName' => 'sales-lead-grid'
                    ],
                    'gridFilters'         => [
                        'sales-lead-grid[_sort_by][name][value]' => 'ASC',
                    ],
                    'assert'              => [
                        'name'        => 'Lead name',
                        'firstName'   => 'fname',
                        'lastName'    => 'lname',
                        'email'       => 'email@email.com'
                    ],
                    'expectedResultCount' => 3
                ],
            ],
            'Lead grid with filters'   => [
                [
                    'gridParameters'      => [
                        'gridName' => 'sales-lead-grid'
                    ],
                    'gridFilters'         => [
                        'sales-lead-grid[_filter][name][value]' => 'Lead name',
                    ],
                    'assert'              => [
                        'name'        => 'Lead name',
                        'firstName'   => 'fname',
                        'lastName'    => 'lname',
                        'email'       => 'email@email.com'
                    ],
                    'expectedResultCount' => 1
                ],
            ],
            'Lead grid without result' => [
                [
                    'gridParameters'      => [
                        'gridName' => 'sales-lead-grid'
                    ],
                    'gridFilters'         => [
                        'sales-lead-grid[_filter][name][value]' => 'some name',
                    ],
                    'assert'              => [],
                    'expectedResultCount' => 0
                ],
            ],
        ];
    }
}
