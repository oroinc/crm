<?php

namespace OroCRM\Bundle\ChannelBundle\Tests\Functional\Controller;

use Symfony\Component\Form\Form;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\OrganizationBundle\Migrations\Data\ORM\LoadOrganizationAndBusinessUnitData;

/**
 * @outputBuffering enabled
 * @dbIsolation
 */
class ChannelControllerTest extends WebTestCase
{
    const CHANNEL_NAME = 'some name';
    const GRID_NAME    = 'channels-grid';

    public function setUp()
    {
        $this->initClient(
            ['debug' => false],
            array_merge($this->generateBasicAuthHeader(), ['HTTP_X-CSRF-Header' => 1])
        );
    }

    public function testCreateChannel()
    {
        $organization = $this->getOrganization();
        $crawler      = $this->client->request('GET', $this->getUrl('orocrm_channel_create'));
        $form         = $crawler->selectButton('Save and Close')->form();

        $name                                     = 'Simple channel';
        $form['orocrm_channel_form[name]']        = $name;
        $form['orocrm_channel_form[description]'] = 'some description';
        $form['orocrm_channel_form[owner]']       = $organization->getId();

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);
        $result  = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $this->assertContains('Channel saved', $crawler->html());

        return compact('name', 'organization');
    }

    /**
     * @param array $data
     *
     * @depends testCreateChannel
     *
     * @return array
     */
    public function testUpdateChannel($data)
    {
        $response = $this->client->requestGrid(
            self::GRID_NAME,
            [
                self::GRID_NAME . '[_filter][name][value]' => $data['name']
            ]
        );

        $result  = $this->getJsonResponseContent($response, 200);
        $result  = reset($result['data']);
        $channel = $result;

        $crawler = $this->client->request(
            'GET',
            $this->getUrl('orocrm_channel_update', ['id' => $result['id']])
        );
        /** @var Form $form */
        $form                              = $crawler->selectButton('Save and Close')->form();
        $name                              = 'name' . $this->generateRandomString();
        $form['orocrm_channel_form[name]'] = $name;

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200, 'text/html; charset=UTF-8');
        $this->assertContains('Channel saved', $crawler->html());

        $channel['name'] = $name;

        return $channel;
    }

    /**
     * @depends testUpdateChannel
     *
     * @param $channel
     */
    public function testChangeStatusChannel($channel)
    {
        $crawler = $this->client->request(
            'GET',
            $this->getUrl('orocrm_channel_change_status', ['id' => $channel['id']])
        );

        $this->client->getResponse();
        $this->assertContains('Channel activated', $crawler->html());

        return $channel;
    }

    /**
     * @depends testChangeStatusChannel
     *
     * @param $channel
     */
    public function testDeleteChannel($channel)
    {
        $this->client->request(
            'DELETE',
            $this->getUrl('orocrm_api_delete_channel', ['id' => $channel['id']])
        );

        $response = $this->client->getResponse();
        $this->assertJsonResponseStatusCodeEquals($response, 204);

        $response = $this->client->requestGrid(
            self::GRID_NAME,
            [
                self::GRID_NAME . '[_filter][name][value]' => $channel['name']
            ]
        );

        $result = $this->getJsonResponseContent($response, 200);

        $this->assertEmpty($result['data']);
        $this->assertEmpty($result['options']['totalRecords']);
    }

    /**
     * @return Organization
     */
    protected function getOrganization()
    {
        return $this->getContainer()
            ->get('doctrine')
            ->getRepository('OroOrganizationBundle:Organization')
            ->findOneByName(LoadOrganizationAndBusinessUnitData::MAIN_ORGANIZATION);
    }
}
