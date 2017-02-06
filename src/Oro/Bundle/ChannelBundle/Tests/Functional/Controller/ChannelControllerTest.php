<?php

namespace Oro\Bundle\ChannelBundle\Tests\Functional\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Oro\Bundle\MessageQueueBundle\Test\Functional\MessageQueueExtension;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Component\Testing\ResponseExtension;
use Symfony\Component\Form\Form;

/**
 * @group crm
 */
class ChannelControllerTest extends WebTestCase
{
    use ResponseExtension;
    use MessageQueueExtension;

    const CHANNEL_NAME = 'some name';
    const GRID_NAME    = 'oro-channels-grid';

    public function setUp()
    {
        $this->initClient([], array_merge($this->generateBasicAuthHeader(), ['HTTP_X-CSRF-Header' => 1]));
        $this->client->enableReboot();
        $this->client->useHashNavigation(true);
    }

    public function testCreateChannel()
    {
        $crawler      = $this->client->request('GET', $this->getUrl('oro_channel_create'));
        $name         = 'Simple channel';
        $form         = $crawler->selectButton('Save and Close')->form();
        $channelType  = 'custom';

        $form['oro_channel_form[name]']        = $name;
        $form['oro_channel_form[channelType]'] = $channelType;
        $form['oro_channel_form[entities]']    = json_encode(
            ['Oro\Bundle\ChannelBundle\Entity\CustomerIdentity']
        );

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);
        $result  = $this->client->getResponse();

        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $this->assertContains('Channel saved', $crawler->html());

        return compact('name', 'channelType');
    }

    /**
     * @depends testCreateChannel
     */
    public function testView($data)
    {
        $response = $this->client->requestGrid(
            self::GRID_NAME,
            [
                self::GRID_NAME . '[_filter][name][value]' => $data['name']
            ]
        );

        $gridResult = $this->getJsonResponseContent($response, 200);
        $gridResult = reset($gridResult['data']);
        $id         = $gridResult['id'];

        $crawler = $this->client->request(
            'GET',
            $this->getUrl('oro_channel_view', ['id' => $id])
        );

        $result = $this->client->getResponse();

        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $this->assertContains('Channels', $crawler->html());
        $this->assertContains($data['name'], $crawler->html());
        $this->assertContains($data['channelType'], $crawler->html());
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
            $this->getUrl('oro_channel_update', ['id' => $result['id']])
        );
        /** @var Form $form */
        $form                              = $crawler->selectButton('Save and Close')->form();
        $name                              = 'name' . $this->generateRandomString();
        $form['oro_channel_form[name]'] = $name;

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
    public function testDeleteChannel($channel)
    {
        $this->client->request(
            'DELETE',
            $this->getUrl('oro_api_delete_channel', ['id' => $channel['id']])
        );

        $response = $this->client->getResponse();
        $this->assertResponseStatusCodeEquals($response, 204);

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
     * @dataProvider gridProvider
     *
     * @param array $filters
     */
    public function testGrid($filters)
    {
        $this->loadFixtures(['Oro\Bundle\ChannelBundle\Tests\Functional\Fixture\LoadChannel']);

        if (isset($filters['gridParameters']['id'])) {
            $gridId = $filters['gridParameters']['gridName'] . '[' . $filters['gridParameters']['id'] . ']';

            $filters['gridParameters'][$gridId] = $this->getReference('default_channel')->getId();
        }

        $response = $this->client->requestGrid($filters['gridParameters'], $filters['gridFilters']);
        $result   = $this->getJsonResponseContent($response, 200);

        foreach ($result['data'] as $row) {
            if ((isset($filters['gridParameters']['id']))) {
                foreach ($filters['assert'] as $fieldName => $value) {
                    $this->assertEquals($value, $row[$fieldName]);
                }
                break;
            }
        }

        $this->assertCount((int) $filters['expectedResultCount'], $result['data']);
    }

    public function gridProvider()
    {
        return [
            'Channel grid'                => [
                [
                    'gridParameters'      => [
                        'gridName' => self::GRID_NAME
                    ],
                    'gridFilters'         => [],
                    'assert'              => [
                        'name' => self::CHANNEL_NAME,
                    ],
                    'expectedResultCount' => 1
                ],
            ],
            'Channel grid without result' => [
                [
                    'gridParameters'      => [
                        'gridName' => self::GRID_NAME
                    ],
                    'gridFilters'         => [
                        self::GRID_NAME . '[_filter][name][value]' => 'Not found',
                    ],
                    'assert'              => [
                        'name' => self::CHANNEL_NAME,
                    ],
                    'expectedResultCount' => 0
                ],
            ],
        ];
    }

    /**
     * @return EntityManagerInterface
     */
    protected function getEntityManager()
    {
        return $this->getContainer()->get('doctrine.orm.entity_manager');
    }
}
