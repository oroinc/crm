<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Datagrid;

use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;
use Oro\Bundle\SecurityBundle\SecurityFacade;

use OroCRM\Bundle\MagentoBundle\Datagrid\NewsletterSubscriberPermissionProvider;

class NewsletterSubscriberPermissionProviderTest extends AbstractTwoWaySyncActionPermissionProviderTest
{
    /**
     * @var NewsletterSubscriberPermissionProvider
     */
    protected $provider;

    /**
     * @var SecurityFacade|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $securityFacade;

    protected function setUp()
    {
        parent::setUp();

        $this->securityFacade = $this->getMockBuilder('Oro\Bundle\SecurityBundle\SecurityFacade')
            ->disableOriginalConstructor()
            ->getMock();

        $this->provider = new NewsletterSubscriberPermissionProvider($this->settingsProvider, '\stdClass');
        $this->provider->setSecurityFacade($this->securityFacade);
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

        $this->securityFacade->expects($this->any())
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
}
