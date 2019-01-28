<?php

namespace Oro\Bundle\MagentoBundle\Tests\Functional\Datagrid;

use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

abstract class AbstractMagentoGridTest extends WebTestCase
{
    const CUSTOMER_REFERENCE = 'customer';

    /**
     * Returned list of grids name
     *
     * @return string[]
     */
    abstract public function gridAclDataProvider();

    /**
     * @return mixed[]
     */
    abstract public function gridDataProvider();

    /**
     * @dataProvider gridDataProvider
     *
     * @param string    $gridName
     * @param mixed     $gridParameters
     * @param bool      $hasChannel
     * @param bool      $hasCustomer
     * @param array     $asserts
     * @param int       $expectedResultCount
     */
    public function testGrid(
        $gridName,
        array $gridParameters,
        $hasChannel,
        $hasCustomer,
        array $asserts,
        $expectedResultCount
    ) {
        $channelId  = !$hasChannel ? null : $this->getChannel()->getId();
        $customerId = !$hasCustomer ? null : $this->getCustomer()->getId();

        if (isset($gridParameters['orderIncrementId'])) {
            $order = $this->getReference($gridParameters['orderIncrementId']);
            $gridParameters['orderId'] = $order->getId();
            unset($gridParameters['orderIncrementId']);
        }

        if (null !== $channelId) {
            $gridParameters['channelId'] = $channelId;
        }

        if (null !== $customerId) {
            $gridParameters['customerId'] = $customerId;
        }

        $gridParameters = $this->createGridParameters(
            $gridName,
            $gridParameters
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

    /**
     * @dataProvider gridAclDataProvider
     *
     * @param string    $gridName
     * @param string    $user
     */
    public function testGridIfUserAclNotAllowed($gridName, $user)
    {
        $this->loginUser($user);

        $channelId = $this->getChannel()->getId();
        $customerId = $this->getCustomer()->getId();

        $gridParameters = [];

        if (null !== $channelId) {
            $gridParameters['channelId'] = $channelId;
        }

        if (null !== $customerId) {
            $gridParameters['customerId'] = $customerId;
        }

        $gridParameters = $this->createGridParameters(
            $gridName,
            $gridParameters
        );

        $response = $this->client->requestGrid(
            $gridParameters,
            [],
            true
        );

        $this->getJsonResponseContent($response, 403);
    }

    /** @return Channel */
    abstract protected function getChannel();

    /** @return Customer */
    abstract protected function getCustomer();

    /**
     * $params = [
     *      'param1' => 'value',
     *      'param2' => 'value'
     * ]
     *
     * @param string    $gridName
     * @param mixed[]
     * @return mixed[]
     */
    protected function createGridParameters($gridName, array $params)
    {
        return [
            'gridName' => $gridName,
            $gridName => $params
        ];
    }
}
