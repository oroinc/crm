<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Datagrid;

use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;

use OroCRM\Bundle\MagentoBundle\Datagrid\NewsletterSubscriberPermissionProvider;

class NewsletterSubscriberPermissionProviderTest extends AbstractTwoWaySyncActionPermissionProviderTest
{
    /**
     * @var NewsletterSubscriberPermissionProvider
     */
    protected $provider;

    protected function setUp()
    {
        parent::setUp();

        $this->provider = new NewsletterSubscriberPermissionProvider($this->settingsProvider, '\stdClass');
    }

    /**
     * @param ResultRecordInterface $record
     * @param array $actions
     * @param array $expected
     *
     * @dataProvider permissionsDataProvider
     */
    public function testGetActionsPermissions(ResultRecordInterface $record, array $actions, array $expected)
    {
        $this->settingsProvider
            ->expects($this->any())
            ->method('isChannelApplicable')
            ->will($this->returnValue(true));

        $this->assertEquals(
            $expected,
            $this->provider->getActionsPermissions($record, $actions)
        );
    }

    /**
     * @return array
     */
    public function permissionsDataProvider()
    {
        return [
            'subscribe' => [
                new ResultRecord(['channelId' => 1, 'customerOriginId' => 1, 'customerId' => 1]),
                ['view' => [], 'subscribe' => [], 'unsubscribe' => []],
                ['view' => true, 'subscribe' => true, 'unsubscribe' => false]
            ],
            'unsubscribe with customer and customer origin id' => [
                new ResultRecord(
                    ['channelId' => 1, 'customerOriginId' => 1, 'customerId' => 1, 'newsletterSubscriberStatusId' => 1]
                ),
                ['view' => [], 'subscribe' => [], 'unsubscribe' => []],
                ['view' => true, 'subscribe' => false, 'unsubscribe' => true]
            ],
            'subscribe with customer and customer origin id' => [
                new ResultRecord(
                    ['channelId' => 1, 'customerOriginId' => 1, 'customerId' => 1, 'newsletterSubscriberStatusId' => 2]
                ),
                ['view' => [], 'subscribe' => [], 'unsubscribe' => []],
                ['view' => true, 'subscribe' => true, 'unsubscribe' => false]
            ],
            'unsubscribe with customer without customer origin id' => [
                new ResultRecord(
                    ['channelId' => 1, 'customerId' => 1, 'newsletterSubscriberStatusId' => 2]
                ),
                ['view' => [], 'subscribe' => [], 'unsubscribe' => []],
                ['view' => true, 'subscribe' => false, 'unsubscribe' => false]
            ],
            'subscribe with customer without customer origin id' => [
                new ResultRecord(
                    ['channelId' => 1, 'customerId' => 1, 'newsletterSubscriberStatusId' => 2]
                ),
                ['view' => [], 'subscribe' => [], 'unsubscribe' => []],
                ['view' => true, 'subscribe' => false, 'unsubscribe' => false]
            ],
            'unsubscribe without customer id' => [
                new ResultRecord(
                    ['channelId' => 1, 'newsletterSubscriberStatusId' => 1]
                ),
                ['view' => [], 'subscribe' => [], 'unsubscribe' => []],
                ['view' => true, 'subscribe' => false, 'unsubscribe' => true]
            ],
            'subscribe without customer id' => [
                new ResultRecord(
                    ['channelId' => 1, 'newsletterSubscriberStatusId' => 2]
                ),
                ['view' => [], 'subscribe' => [], 'unsubscribe' => []],
                ['view' => true, 'subscribe' => true, 'unsubscribe' => false]
            ]
        ];
    }
}
