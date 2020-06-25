<?php

namespace Oro\Bundle\MagentoBundle\Tests\Functional\Api\RestJsonApi;

use Oro\Bundle\ApiBundle\Tests\Functional\RestJsonApiTestCase;
use Oro\Bundle\MagentoBundle\Entity\CreditMemo;
use Oro\Bundle\MagentoBundle\Tests\Functional\Fixture\LoadMagentoChannel;

class CreditMemoTest extends RestJsonApiTestCase
{
    protected function setUp(): void
    {
        $this->markTestSkipped('Magento integration is disabled in CRM-9202');
        parent::setUp();
        $this->loadFixtures([LoadMagentoChannel::class]);
    }

    public function testGetList()
    {
        $response = $this->cget(['entity' => 'magentocreditmemos']);
        $this->assertResponseContains('get_credit_memos.yml', $response);
    }

    public function testGet()
    {
        $response = $this->get(
            ['entity' => 'magentocreditmemos', 'id' => '<toString(@creditMemo->id)>']
        );
        $this->assertResponseContains('get_credit_memo.yml', $response);
    }

    public function testUpdate()
    {
        $creditMemoId = $this->getReference('creditMemo')->getId();

        $this->patch(
            ['entity' => 'magentocreditmemos', 'id' => (string)$creditMemoId],
            [
                'data' => [
                    'type'       => 'magentocreditmemos',
                    'id'         => (string)$creditMemoId,
                    'attributes' => [
                        'transactionId' => '100000XT'
                    ]
                ]
            ]
        );

        /** @var CreditMemo $creditMemo */
        $creditMemo = $this->getEntityManager()->find(CreditMemo::class, $creditMemoId);
        $this->assertEquals('100000XT', $creditMemo->getTransactionId());
    }

    public function testCreate()
    {
        $response = $this->post(
            ['entity' => 'magentocreditmemos'],
            'create_credit_memo.yml'
        );

        $creditMemoId = (int)$this->getResourceId($response);
        $responseContent = $this->updateResponseContent('create_credit_memo.yml', $response);
        $this->assertResponseContains($responseContent, $response);

        /** @var CreditMemo $creditMemo */
        $creditMemo = $this->getEntityManager()->find(CreditMemo::class, $creditMemoId);
        $this->assertSame($this->getReference('organization')->getId(), $creditMemo->getOrganization()->getId());
        $this->assertSame($this->getReference('user')->getId(), $creditMemo->getOwner()->getId());
        $this->assertSame($this->getReference('guestOrder')->getId(), $creditMemo->getOrder()->getId());
        $this->assertSame($this->getReference('store')->getId(), $creditMemo->getStore()->getId());
    }
}
