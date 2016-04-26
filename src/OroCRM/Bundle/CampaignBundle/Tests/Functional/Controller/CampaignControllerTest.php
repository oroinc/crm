<?php

namespace OroCRM\Bundle\CampaignBundle\Tests\Functional\Controller;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

/**
 * @outputBuffering enabled
 * @dbIsolation
 */
class CampaignControllerTest extends WebTestCase
{
    const TEST_CODE         = 'code-1234';
    const UPDATED_TEST_CODE = 'updated-code-1234';

    public function setUp()
    {
        $this->initClient(['debug' => false], $this->generateBasicAuthHeader());
        $this->client->useHashNavigation(true);
    }

    public function testCreate()
    {
        $crawler                                   = $this->client->request(
            'GET',
            $this->getUrl('orocrm_campaign_create')
        );
        $form                                      = $crawler->selectButton('Save and Close')->form();
        $form['orocrm_campaign_form[name]']        = 'new name';
        $form['orocrm_campaign_form[code]']        = self::TEST_CODE;
        $form['orocrm_campaign_form[description]'] = 'some description';
        $form['orocrm_campaign_form[budget]']      = 154.54;

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);
        $result  = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $this->assertContains("Campaign saved", $crawler->html());
    }

    /**
     * @depends testCreate
     */
    public function testUpdate()
    {
        $response = $this->client->requestGrid('orocrm-campaign-grid');
        $result   = $this->getJsonResponseContent($response, 200);
        $result   = reset($result['data']);
        $crawler  = $this->client->request(
            'GET',
            $this->getUrl('orocrm_campaign_update', ['id' => $result['id']])
        );

        $form                                 = $crawler->selectButton('Save and Close')->form();
        $form['orocrm_campaign_form[name]']   = 'new name';
        $form['orocrm_campaign_form[budget]'] = 177;
        $form['orocrm_campaign_form[code]']   = self::UPDATED_TEST_CODE;

        $this->client->followRedirects(true);

        $crawler = $this->client->submit($form);
        $result  = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $this->assertContains("Campaign saved", $crawler->html());
    }

    /**
     * @depends testUpdate
     */
    public function testGrid()
    {
        $response = $this->client->requestGrid('orocrm-campaign-grid');
        $result   = $this->getJsonResponseContent($response, 200);
        $result   = reset($result['data']);
        $this->assertEquals('new name', $result['name']);
        $this->assertEquals('177.0000', $result['budget']);
        $this->assertEquals(self::UPDATED_TEST_CODE, $result['code']);
    }
}
