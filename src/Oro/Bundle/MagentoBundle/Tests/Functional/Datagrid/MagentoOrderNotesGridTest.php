<?php

namespace Oro\Bundle\MagentoBundle\Tests\Functional\Datagrid;

use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\MagentoBundle\Entity\Order;
use Oro\Bundle\MagentoBundle\Tests\Functional\Fixtures\LoadOrderNotesData;
use Oro\Bundle\UserBundle\Tests\Functional\DataFixtures\LoadUserACLData;

/**
 * @SuppressWarnings(PHPMD)
 */
class MagentoOrderNotesGridTest extends AbstractMagentoGridTest
{
    protected function setUp(): void
    {
        $this->initClient(['debug' => false], $this->generateBasicAuthHeader());

        $this->client->useHashNavigation(true);

        $this->loadFixtures([LoadOrderNotesData::class, LoadUserACLData::class]);
    }

    public function testCustomerOrderNotesWidgetAction()
    {
        /** @var Customer $customer */
        $customer = $this->getCustomer();

        $this->client->request(
            'GET',
            $this->getUrl(
                'oro_magento_customer_order_notes_widget',
                [
                    'customerId' => $customer->getId(),
                    'channelId'  => $this->getChannel()->getId(),
                    '_widgetContainer' => 'block'
                ]
            )
        );

        $result = $this->client->getResponse();

        $this->assertResponseStatusCodeEquals($result, 200);
    }

    public function testOrderNotesWidgetAction()
    {
        /** @var Order $order */
        $order = $this->getReference(LoadOrderNotesData::DEFAULT_ORDER_REFERENCE_ALIAS);

        $this->client->request(
            'GET',
            $this->getUrl(
                'oro_magento_order_notes_widget',
                [
                    'orderId' => $order->getId(),
                    '_widgetContainer' => 'block'
                ]
            )
        );

        $result = $this->client->getResponse();

        $this->assertResponseStatusCodeEquals($result, 200);
    }

    public function testCustomerOrderNotesAction()
    {
        /** @var Customer $customer */
        $customer = $this->getCustomer();

        $this->client->request(
            'GET',
            $this->getUrl(
                'oro_magento_widget_customer_order_notes',
                [
                    'customerId' => $customer->getId(),
                    'channelId'  => $this->getChannel()->getId(),
                    '_widgetContainer' => 'block'
                ]
            )
        );

        $result = $this->client->getResponse();

        $this->assertResponseStatusCodeEquals($result, 200);
    }

    public function gridAclDataProvider()
    {
        return [
            "Test ACL on widget 'magento-order-notes-widget-grid'" => [
                'gridName' => 'magento-order-notes-widget-grid',
                'user' => LoadUserACLData::SIMPLE_USER_2_ROLE_LOCAL
            ],
            "Test ACL on widget 'magento-account-order-notes-widget-grid'" => [
                'gridName' => 'magento-account-order-notes-widget-grid',
                'user' => LoadUserACLData::SIMPLE_USER_2_ROLE_LOCAL
            ],
            "Test ACL on widget 'magento-customer-order-notes-widget-grid'" => [
                'gridName' => 'magento-customer-order-notes-widget-grid',
                'user' => LoadUserACLData::SIMPLE_USER_2_ROLE_LOCAL
            ]
        ];
    }

    /**
     * @return array
     */
    public function gridDataProvider()
    {
        return [
            'Magento order notes grid' => [
                'gridName'  => 'magento-order-notes-widget-grid',
                'gridParameters' => [
                    'orderIncrementId' => LoadOrderNotesData::DEFAULT_ORDER_INCREMENT_ID
                ],
                'channelId' => false,
                'customerId' => false,
                'asserts' => [
                    [
                        'message'  => LoadOrderNotesData::DEFAULT_MESSAGE
                    ]
                ],
                'expectedResultCount' => 1
            ],
            'Magento account order notes widget grid' => [
                'gridName'  => 'magento-account-order-notes-widget-grid',
                'gridParameters' => [],
                'channelId' => true,
                'customerId' => true,
                'asserts' => [
                    [
                        'message'  => LoadOrderNotesData::DEFAULT_MESSAGE
                    ],
                    [
                        'message'  => LoadOrderNotesData::OTHER_ORDER_NOTE_MESSAGE
                    ],
                ],
                'expectedResultCount' => 2
            ],
            'Magento customer order notes widget grid' => [
                'gridName'  => 'magento-customer-order-notes-widget-grid',
                'gridParameters' => [],
                'channelId' => true,
                'customerId' => true,
                'asserts' => [
                    [
                        'message'  => LoadOrderNotesData::DEFAULT_MESSAGE
                    ],
                    [
                        'message'  => LoadOrderNotesData::OTHER_ORDER_NOTE_MESSAGE
                    ],
                ],
                'expectedResultCount' => 2
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
