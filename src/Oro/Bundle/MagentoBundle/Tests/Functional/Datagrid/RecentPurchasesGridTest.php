<?php

namespace Oro\Bundle\MagentoBundle\Tests\Functional\Datagrid;

use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\MagentoBundle\Tests\Functional\Fixture\LoadRecentPurchasesData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\UserBundle\Tests\Functional\DataFixtures\LoadUserACLData;

/**
 * @dbIsolationPerTest
 *
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
class RecentPurchasesGridTest extends WebTestCase
{
    const CUSTOMER_REFERENCE = 'customer';

    protected function setUp(): void
    {
        $this->initClient(['debug' => false], $this->generateBasicAuthHeader());
        $this->client->useHashNavigation(true);

        $this->loadFixtures([LoadRecentPurchasesData::class, LoadUserACLData::class]);
    }

    /**
     * @dataProvider gridAclDataProvider
     *
     * @param string    $gridName
     */
    public function testGridIfUserAclNotAllowed($gridName)
    {
        $this->loginUser(LoadUserACLData::SIMPLE_USER_2_ROLE_LOCAL);

        $channelId = $this->getChannel()->getId();
        $customerId = $this->getCustomer()->getId();

        $gridParameters = $this->createGridParameters(
            $gridName,
            $channelId,
            $customerId
        );

        $response = $this->client->requestGrid(
            $gridParameters,
            [],
            true
        );

        $this->getJsonResponseContent($response, 403);
    }

    /**
     * @dataProvider gridDataProvider
     *
     * @param string    $gridName
     * @param bool      $hasChannel
     * @param bool      $hasCustomer
     * @param array     $asserts
     * @param int       $expectedResultCount
     */
    public function testGrid(
        $gridName,
        $hasChannel,
        $hasCustomer,
        array $asserts,
        $expectedResultCount
    ) {
        $channelId  = !$hasChannel ? null : $this->getChannel()->getId();
        $customerId = !$hasCustomer ? null : $this->getCustomer()->getId();

        $gridParameters = $this->createGridParameters(
            $gridName,
            $channelId,
            $customerId
        );

        $response = $this->client->requestGrid(
            $gridParameters,
            [],
            true
        );
        $result = $this->getJsonResponseContent($response, 200);

        $this->assertCount($expectedResultCount, $result['data']);

        foreach ($asserts as $assertKey => $assert) {
            $gridData = $result['data'][$assertKey];
            foreach ($assert as $key => $value) {
                $this->assertEquals($gridData[$key], $value);
            }
        }
    }

    public function testCustomerRecentPurchasesAction()
    {
        /** @var Customer $customer */
        $customer = $this->getReference(self::CUSTOMER_REFERENCE);

        $this->client->request(
            'GET',
            $this->getUrl(
                'oro_magento_widget_customer_recent_purchases',
                [
                    'customerId' => $customer->getId(),
                    'channelId'  => $customer->getChannel()->getId(),
                    '_widgetContainer' => 'block'
                ]
            )
        );

        $result = $this->client->getResponse();

        $this->assertResponseStatusCodeEquals($result, 200);
    }

    public function testCustomerRecentPurchasesWidgetAction()
    {
        /** @var Customer $customer */
        $customer = $this->getReference(self::CUSTOMER_REFERENCE);

        $this->client->request(
            'GET',
            $this->getUrl(
                'oro_magento_customer_recent_purchases_widget',
                [
                    'customerId' => $customer->getId(),
                    'channelId'  => $customer->getChannel()->getId(),
                    '_widgetContainer' => 'block'
                ]
            )
        );

        $result = $this->client->getResponse();

        $this->assertResponseStatusCodeEquals($result, 200);
    }

    /**
     * @return array
     */
    public function gridDataProvider()
    {
        return [
            'Magento recent purchases grid' => [
                'gridName'  => 'magento-customer-recent-purchases-grid',
                'channelId' => true,
                'customerId' => true,
                'asserts' => [
                    [
                        'orderOriginId' => '100000307',
                        'sku' => 'some sku',
                        'name' => 'some order item',
                        'qty' => 1,
                        'price' => '$75.00',
                        'originalPrice' => '$0.00',
                        'discountPercent' => 4,
                        'discountAmount' => '$0.00',
                        'taxPercent' => 2,
                        'taxAmount' => '$1.50',
                        'rowTotal' => '$234.00',
                    ],
                    [
                        'orderOriginId' => '100000505',
                        'sku' => 'some sku2',
                        'name' => 'some order item',
                        'qty' => 1,
                        'price' => '$75.00',
                        'originalPrice' => '$0.00',
                        'discountPercent' => 4,
                        'discountAmount' => '$0.00',
                        'taxPercent' => 2,
                        'taxAmount' => '$1.50',
                        'rowTotal' => '$234.00',
                    ]
                ],
                'expectedResultCount' => 2
            ],
            'Magento recent purchases grid without channel' => [
                'gridName'  => 'magento-customer-recent-purchases-grid',
                'channelId' => false,
                'customerId' => true,
                'asserts' => [],
                'expectedResultCount' => 0
            ],
            'Magento recent purchases grid without customer' => [
                'gridName'  => 'magento-customer-recent-purchases-grid',
                'channelId' => true,
                'customerId' => false,
                'asserts' => [],
                'expectedResultCount' => 0
            ],
            'Magento recent purchases grid without customer and channel' => [
                'gridName'  => 'magento-customer-recent-purchases-grid',
                'channelId' => false,
                'customerId' => false,
                'asserts' => [],
                'expectedResultCount' => 0
            ],
            'Account recent purchases grid' => [
                'gridName'  => 'magento-customer-recent-purchases-widget-grid',
                'channelId' => true,
                'customerId' => true,
                'asserts' => [
                    [
                        'orderOriginId' => '100000307',
                        'sku' => 'some sku',
                        'name' => 'some order item',
                        'qty' => 1,
                        'price' => '$75.00',
                        'originalPrice' => '$0.00',
                        'discountPercent' => 4,
                        'discountAmount' => '$0.00',
                        'taxPercent' => 2,
                        'taxAmount' => '$1.50',
                        'rowTotal' => '$234.00',
                    ],
                    [
                        'orderOriginId' => '100000505',
                        'sku' => 'some sku2',
                        'name' => 'some order item',
                        'qty' => 1,
                        'price' => '$75.00',
                        'originalPrice' => '$0.00',
                        'discountPercent' => 4,
                        'discountAmount' => '$0.00',
                        'taxPercent' => 2,
                        'taxAmount' => '$1.50',
                        'rowTotal' => '$234.00',
                    ]
                ],
                'expectedResultCount' => 2
            ],
            'Account recent purchases grid without channel' => [
                'gridName'  => 'magento-customer-recent-purchases-widget-grid',
                'channelId' => false,
                'customerId' => true,
                'asserts' => [],
                'expectedResultCount' => 0
            ],
            'Account recent purchases grid without customer' => [
                'gridName'  => 'magento-customer-recent-purchases-widget-grid',
                'channelId' => true,
                'customerId' => false,
                'asserts' => [],
                'expectedResultCount' => 0
            ],
            'Account recent purchases grid without customer and channel' => [
                'gridName'  => 'magento-customer-recent-purchases-widget-grid',
                'channelId' => true,
                'customerId' => false,
                'asserts' => [],
                'expectedResultCount' => 0
            ],
        ];
    }

    /**
     * @return string[]
     */
    public function gridAclDataProvider()
    {
        return [
            ['gridName' => 'magento-customer-recent-purchases-widget-grid'],
            ['gridName' => 'magento-customer-recent-purchases-grid']
        ];
    }

    /**
     * @param string    $gridName
     * @param int|null  $channelId
     * @param int|null  $customerId
     * @return array
     */
    protected function createGridParameters($gridName, $channelId, $customerId)
    {
        return [
            'gridName' => $gridName,
            $gridName => [
                'channelId' => $channelId,
                'customerId' => $customerId
            ]
        ];
    }


    /** @return Channel */
    protected function getChannel()
    {
        return $this->getCustomer()->getChannel();
    }

    /** @return Customer */
    protected function getCustomer()
    {
        return $this->getReference(self::CUSTOMER_REFERENCE);
    }
}
