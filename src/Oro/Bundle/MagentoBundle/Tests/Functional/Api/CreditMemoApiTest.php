<?php

namespace Oro\Bundle\MagentoBundle\Tests\Functional\Api;

use Symfony\Component\HttpFoundation\Response;

use Oro\Bundle\ApiBundle\Tests\Functional\RestJsonApiTestCase;
use Oro\Bundle\MagentoBundle\Entity\CreditMemo;
use Oro\Bundle\MagentoBundle\Tests\Functional\Fixture\LoadMagentoChannel;

class CreditMemoApiTest extends RestJsonApiTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
        $this->loadFixtures([LoadMagentoChannel::class]);
    }

    public function testGetCreditMemos()
    {
        $entityType = $this->getEntityType(CreditMemo::class);
        $response = $this->cget(['entity' => $entityType]);
        $this->assertResponseContains(__DIR__.'/responses/get_credit_memos.yml', $response);
    }

    public function testGetCreditMemo()
    {
        $response = $this->get([
            'entity' => $this->getEntityType(CreditMemo::class),
            'id' => '<toString(@creditMemo->id)>',
        ]);
        $this->assertResponseContains(__DIR__.'/responses/get_credit_memo.yml', $response);
    }

    public function testUpdateCreditMemo()
    {
        /** @var CreditMemo $creditMemo */
        $creditMemo = $this->getReference('creditMemo');

        $entityType = $this->getEntityType(CreditMemo::class);
        $data = [
            'data' => [
                'type' => $entityType,
                'id' => (string) $creditMemo->getId(),
                'attributes' =>
                [
                    'transactionId' => '100000XT',
                ],
            ]
        ];
        $response = $this->request(
            'PATCH',
            $this->getUrl(
                'oro_rest_api_patch',
                ['entity' => $entityType, 'id' => $creditMemo->getId()]
            ),
            $data
        );

        $this->assertResponseStatusCodeEquals($response, Response::HTTP_OK);
        $creditMemo = $this->getCreditMemoRepository()->find($creditMemo->getId());
        $this->assertEquals('100000XT', $creditMemo->getTransactionId());
    }

    public function testCreateCreditMemo()
    {
        $entityType = $this->getEntityType(CreditMemo::class);

        $response = $this->post(
            ['entity' => $entityType],
            __DIR__.'/requests/create_credit_memo.yml'
        );

        /** @var CreditMemo $creditMemo */
        $creditMemo = $this->getCreditMemoRepository()->findOneByOriginId('2');
        $this->assertResponseContains(__DIR__.'/responses/create_credit_memo.yml', $response, $creditMemo);
        $this->assertSame($this->getReference('organization')->getId(), $creditMemo->getOrganization()->getId());
        $this->assertSame($this->getReference('user')->getId(), $creditMemo->getOwner()->getId());
        $this->assertSame($this->getReference('guestOrder')->getId(), $creditMemo->getOrder()->getId());
        $this->assertSame($this->getReference('store')->getId(), $creditMemo->getStore()->getId());
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getCreditMemoRepository()
    {
        return $this->doctrineHelper->getEntityRepository(CreditMemo::class);
    }
}
