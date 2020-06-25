<?php

namespace Oro\Bundle\MagentoBundle\Tests\Functional\Api\RestJsonApi;

use Oro\Bundle\ApiBundle\Tests\Functional\RestJsonApiTestCase;
use Oro\Bundle\MagentoBundle\Entity\CreditMemoItem;
use Oro\Bundle\MagentoBundle\Tests\Functional\Fixture\LoadMagentoChannel;

class CreditMemoItemTest extends RestJsonApiTestCase
{
    protected function setUp(): void
    {
        $this->markTestSkipped('Magento integration is disabled in CRM-9202');
        parent::setUp();
        $this->loadFixtures([LoadMagentoChannel::class]);
    }

    public function testGetList()
    {
        $response = $this->cget(['entity' => 'magentocreditmemoitems']);
        $this->assertResponseContains('get_credit_memo_items.yml', $response);
    }

    public function testGet()
    {
        $response = $this->get(
            ['entity' => 'magentocreditmemoitems', 'id' => '<toString(@creditMemoItem->id)>']
        );
        $this->assertResponseContains('get_credit_memo_item.yml', $response);
    }

    public function testCreate()
    {
        $response = $this->post(
            ['entity' => 'magentocreditmemoitems'],
            'create_credit_memo_item.yml'
        );

        $creditMemoItemId = (int)$this->getResourceId($response);
        $responseContent = $this->updateResponseContent('create_credit_memo_item.yml', $response);
        $this->assertResponseContains($responseContent, $response);

        /** @var CreditMemoItem $creditMemoItem */
        $creditMemoItem = $this->getEntityManager()->find(CreditMemoItem::class, $creditMemoItemId);
        $this->assertEquals('sku', $creditMemoItem->getSku());
        $this->assertSame($this->getReference('creditMemo')->getId(), $creditMemoItem->getParent()->getId());
    }
}
