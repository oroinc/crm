<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\DependencyInjection;

use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;
use OroCRM\Bundle\MagentoBundle\Datagrid\NewsletterSubscriberPermissionProvider;

class NewsletterSubscriberPermissionProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var NewsletterSubscriberPermissionProvider
     */
    protected $provider;

    protected function setUp()
    {
        $this->provider = new NewsletterSubscriberPermissionProvider();
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
            [
                new ResultRecord([]),
                ['view' => [], 'subscribe' => [], 'unsubscribe' => []],
                ['view' => true, 'subscribe' => true, 'unsubscribe' => false]
            ],
            [
                new ResultRecord(['newsletterSubscriberStatusId' => 2]),
                ['view' => [], 'subscribe' => [], 'unsubscribe' => []],
                ['view' => true, 'subscribe' => true, 'unsubscribe' => false]
            ],
            [
                new ResultRecord(['newsletterSubscriberStatusId' => 1]),
                ['view' => [], 'subscribe' => [], 'unsubscribe' => []],
                ['view' => true, 'subscribe' => false, 'unsubscribe' => true]
            ]
        ];
    }
}
