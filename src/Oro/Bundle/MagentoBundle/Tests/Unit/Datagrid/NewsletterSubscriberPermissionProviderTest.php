<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Datagrid;

use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;
use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;
use Oro\Bundle\MagentoBundle\Datagrid\NewsletterSubscriberPermissionProvider;
use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\MagentoBundle\Entity\NewsletterSubscriber;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class NewsletterSubscriberPermissionProviderTest extends AbstractTwoWaySyncActionPermissionProviderTest
{
    /** @var NewsletterSubscriberPermissionProvider */
    protected $provider;

    /** @var AuthorizationCheckerInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $authorizationChecker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);

        $this->provider = new NewsletterSubscriberPermissionProvider($this->settingsProvider, '\stdClass');
        $this->provider->setAuthorizationChecker($this->authorizationChecker);
    }

    /**
     * @param ResultRecordInterface $record
     * @param array $actions
     * @param array $expected
     * @param bool $isAllowed
     *
     * @dataProvider permissionsDataProvider
     */
    public function testGetActionsPermissions(
        ResultRecordInterface $record,
        array $actions,
        array $expected,
        $isAllowed
    ) {
        $this->settingsProvider
            ->expects($this->any())
            ->method('isChannelApplicable')
            ->will($this->returnValue(true));

        $this->authorizationChecker->expects($this->any())
            ->method('isGranted')
            ->will($this->returnValue($isAllowed));

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
                ['view' => true, 'subscribe' => true, 'unsubscribe' => false],
                true
            ],
            'subscribe not granted' => [
                new ResultRecord(['channelId' => 1, 'customerOriginId' => 1, 'customerId' => 1]),
                ['view' => [], 'subscribe' => [], 'unsubscribe' => []],
                ['view' => true, 'subscribe' => false, 'unsubscribe' => false],
                false
            ],
            'unsubscribe with customer and customer origin id' => [
                new ResultRecord(
                    ['channelId' => 1, 'customerOriginId' => 1, 'customerId' => 1, 'newsletterSubscriberStatusId' => 1]
                ),
                ['view' => [], 'subscribe' => [], 'unsubscribe' => []],
                ['view' => true, 'subscribe' => false, 'unsubscribe' => true],
                true
            ],
            'unsubscribe with customer and customer origin id not granted' => [
                new ResultRecord(
                    ['channelId' => 1, 'customerOriginId' => 1, 'customerId' => 1, 'newsletterSubscriberStatusId' => 1]
                ),
                ['view' => [], 'subscribe' => [], 'unsubscribe' => []],
                ['view' => true, 'subscribe' => false, 'unsubscribe' => false],
                false
            ],
            'subscribe with customer and customer origin id' => [
                new ResultRecord(
                    ['channelId' => 1, 'customerOriginId' => 1, 'customerId' => 1, 'newsletterSubscriberStatusId' => 2]
                ),
                ['view' => [], 'subscribe' => [], 'unsubscribe' => []],
                ['view' => true, 'subscribe' => true, 'unsubscribe' => false],
                true
            ],
            'unsubscribe with customer without customer origin id' => [
                new ResultRecord(
                    ['channelId' => 1, 'customerId' => 1, 'newsletterSubscriberStatusId' => 2]
                ),
                ['view' => [], 'subscribe' => [], 'unsubscribe' => []],
                ['view' => true, 'subscribe' => false, 'unsubscribe' => false],
                true
            ],
            'subscribe with customer without customer origin id' => [
                new ResultRecord(
                    ['channelId' => 1, 'customerId' => 1, 'newsletterSubscriberStatusId' => 2]
                ),
                ['view' => [], 'subscribe' => [], 'unsubscribe' => []],
                ['view' => true, 'subscribe' => false, 'unsubscribe' => false],
                true
            ],
            'unsubscribe without customer id' => [
                new ResultRecord(
                    ['channelId' => 1, 'newsletterSubscriberStatusId' => 1]
                ),
                ['view' => [], 'subscribe' => [], 'unsubscribe' => []],
                ['view' => true, 'subscribe' => false, 'unsubscribe' => true],
                true
            ],
            'subscribe without customer id' => [
                new ResultRecord(
                    ['channelId' => 1, 'newsletterSubscriberStatusId' => 2]
                ),
                ['view' => [], 'subscribe' => [], 'unsubscribe' => []],
                ['view' => true, 'subscribe' => true, 'unsubscribe' => false],
                true
            ],
        ];
    }

    /**
     * @param ResultRecordInterface $record
     * @param array $actions
     * @param array $expected
     * @param bool $isAllowed
     *
     * @dataProvider permissionsChannelDataProvider
     */
    public function testGetActionsPermissionsByChannelPermissions(
        ResultRecordInterface $record,
        array $actions,
        array $expected,
        $isAllowed
    ) {
        $this->settingsProvider
            ->expects($this->any())
            ->method('isChannelApplicable')
            ->will($this->returnValue(true));

        $this->authorizationChecker->expects($this->any())
            ->method('isGranted')
            ->will($this->returnValue($isAllowed));

        $this->assertEquals(
            $expected,
            $this->provider->getActionsPermissions($record, $actions)
        );
    }

    /**
     * @return array
     */
    public function permissionsChannelDataProvider()
    {
        /** @var Integration $integration */
        $integration = $this->createChannelIntegrationEntity(1);

        /** @var Channel $channel */
        $channel = $this->createChannelEntity(1);

        /** @var Customer $customer */
        $customer = new Customer();
        $customer
            ->setId(1)
            ->setChannel($integration)
            ->setDataChannel($channel);

        /** @var NewsletterSubscriber $newsletterSubscriber */
        $newsletterSubscriber = new NewsletterSubscriber();
        $newsletterSubscriber
            ->setCustomer($customer)
            ->setChannel($customer->getChannel())
            ->setDataChannel($customer->getDataChannel());

        return [
            'subscribe with channel permission VIEW is granted' => [
                new ResultRecord(
                    [
                        'channelId'                    => 1,
                        'customerOriginId'             => 1,
                        'customerId'                   => 1,
                        'newsletterSubscriberStatusId' => 2,
                        'customerData'                 => $newsletterSubscriber
                    ]
                ),
                ['view' => [], 'subscribe' => [], 'unsubscribe' => []],
                ['view' => true, 'subscribe' => true, 'unsubscribe' => false],
                true
            ],
            'subscribe with channel permission VIEW is not granted, get channel from customer' => [
                new ResultRecord(
                    [
                        'channelId'                    => null,
                        'customerOriginId'             => 1,
                        'customerId'                   => 1,
                        'newsletterSubscriberStatusId' => 2,
                        'customerData'                 => $newsletterSubscriber
                    ]
                ),
                ['view' => [], 'subscribe' => [], 'unsubscribe' => []],
                ['view' => true, 'subscribe' => true, 'unsubscribe' => false],
                true
            ],
            'customers grid - channel permission VIEW is granted' => [
                new ResultRecord(
                    [
                        'channelId'                    => 1,
                        'customerOriginId'             => 1,
                        'customerId'                   => 1,
                        'customerData'                 => $customer
                    ]
                ),
                ['view' => []],
                ['view' => true],
                true
            ],
            'customers grid - channel permission VIEW is not granted, get channel from customer' => [
                new ResultRecord(
                    [
                        'channelId'                    => null,
                        'customerOriginId'             => 1,
                        'customerId'                   => 1,
                        'customerData'                 => $customer
                    ]
                ),
                ['view' => []],
                ['view' => true],
                true
            ]
        ];
    }
}
