<?php

namespace Oro\Bundle\MagentoBundle\Tests\Functional\Api;

use Symfony\Component\HttpFoundation\Response;

use Oro\Bundle\ApiBundle\Tests\Functional\RestJsonApiTestCase;
use Oro\Bundle\MagentoBundle\Entity\CreditMemo;
use Oro\Bundle\MagentoBundle\Entity\CreditMemoItem;
use Oro\Bundle\MagentoBundle\Tests\Functional\Fixture\LoadMagentoChannel;

class CreditMemoItemApiTest extends RestJsonApiTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
        $this->loadFixtures([LoadMagentoChannel::class]);
    }

    public function testGetCreditMemoItems()
    {
        $entityType = $this->getEntityType(CreditMemoItem::class);
        $response = $this->cget(['entity' => $entityType]);
        $this->assertResponseContains(__DIR__.'/responses/get_credit_memo_items.yml', $response);
    }

    public function testGetCreditMemoItem()
    {
        $response = $this->get([
            'entity' => $this->getEntityType(CreditMemoItem::class),
            'id' => '<toString(@creditMemoItem->id)>',
        ]);
        $this->assertResponseContains(__DIR__.'/responses/get_credit_memo_item.yml', $response);
    }

    public function testCreateCreditMemoItem()
    {
        $entityType = $this->getEntityType(CreditMemoItem::class);

        $response = $this->post(
            ['entity' => $entityType],
            __DIR__.'/requests/create_credit_memo_item.yml'
        );

        /** @var CreditMemoItem $creditMemoItem */
        $creditMemoItem = $this->doctrineHelper->getEntityRepository(CreditMemoItem::class)->findOneByOriginId('141');
        $this->assertResponseContains(__DIR__.'/responses/create_credit_memo_item.yml', $response, $creditMemoItem);
        $this->assertEquals('sku', $creditMemoItem->getSku());
        $this->assertSame($this->getReference('creditMemo')->getId(), $creditMemoItem->getParent()->getId());
    }
}
