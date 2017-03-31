<?php

namespace Oro\Bundle\MagentoBundle\Tests\Functional\Controller;

class CreditMemoControllerTest extends AbstractController
{
    /** @var bool */
    protected $isRealGridRequest = true;

    /**
     * @return int
     */
    protected function getMainEntityId()
    {
        return $this->getReference('creditMemo')->getid();
    }

    public function testView()
    {
        $this->client->request(
            'GET',
            $this->getUrl('oro_magento_credit_memo_view', ['id' => $this->getMainEntityId()])
        );
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $this->assertContains('Credit Memo Items', $result->getContent());
        $this->assertContains('100000307', $result->getContent());
        $this->assertContains('refunded', $result->getContent());
        $this->assertContains('$5.00', $result->getContent());
        $this->assertContains('$120.50', $result->getContent());
        $this->assertContains('John Doe', $result->getContent());
    }

    /**
     * @return array
     */
    public function gridProvider()
    {
        return [
            'Magento credit memo grid'                             => [
                [
                    'gridParameters'      => [
                        'gridName' => 'magento-credit-memo-grid',
                        'magento-credit-memo-grid[_sort_by][incrementId]' => 'ASC',
                    ],
                    'gridFilters'         => [],
                    'asserts' => [
                        [
                            'channelName' => 'Magento channel',
                            'refunded'    => '$120.50',
                        ],
                    ],
                    'expectedResultCount' => 1
                ],
            ],
            'Magento credit memo grid with filters'                => [
                [
                    'gridParameters'      => [
                        'gridName' => 'magento-credit-memo-grid'
                    ],
                    'gridFilters'         => [
                        'magento-credit-memo-grid[_filter][status][value]'    => 'refunded',
                    ],
                    'assert'              => [
                         'channelName' => 'Magento channel',
                         'refunded'    => '$120.50',
                    ],
                    'expectedResultCount' => 1
                ],
            ],
            'Magento credit memo grid with filters without result' => [
                [
                    'gridParameters'      => [
                        'gridName' => 'magento-credit-memo-grid'
                    ],
                    'gridFilters'         => [
                        'magento-credit-memo-grid[_filter][incrementId][value]' => '41241',
                    ],
                    'assert'              => [],
                    'expectedResultCount' => 0
                ],
            ],
            'Magento credit memo item grid'                        => [
                [
                    'gridParameters'      => [
                        'gridName' => 'magento-credit-memo-item-grid',
                        'id' => 'creditMemoId',
                    ],
                    'gridFilters'         => [],
                    'assert'              => [
                        'sku'            => 'some sku',
                        'qty'            => 2,
                        'rowTotal'       => '$400.44',
                        'name'           => 'some name',
                    ],
                    'expectedResultCount' => 1
                ],
            ],
        ];
    }
}
