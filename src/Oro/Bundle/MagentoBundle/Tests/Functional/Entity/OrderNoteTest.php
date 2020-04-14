<?php

namespace Oro\Bundle\MagentoBundle\Tests\Functional\Entity;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\EntityBundle\Helper\FieldHelper;
use Oro\Bundle\MagentoBundle\Entity\Order;
use Oro\Bundle\MagentoBundle\Entity\OrderNote;
use Oro\Bundle\MagentoBundle\Tests\Functional\Fixtures\LoadOrderNotesData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

/**
 * @dbIsolationPerTest
 */
class OrderNoteTest extends WebTestCase
{
    /** @var EntityManager */
    protected $manager;

    /** @var  FieldHelper */
    protected $fieldHelper;

    /** {@inheritdoc} */
    protected function setUp(): void
    {
        $this->initClient([]);

        $this->manager = $this->getContainer()
                              ->get('doctrine')
                              ->getManager();

        $this->fieldHelper = $this->getContainer()
                                  ->get('oro_entity.helper.field_helper');

        $this->loadFixtures([LoadOrderNotesData::class]);
    }

    /** {@inheritdoc} */
    protected function tearDown(): void
    {
        $this->manager = null;
        $this->fieldHelper = null;
        parent::tearDown();
    }

    public function testFieldConfig()
    {
        $fields = $this->fieldHelper->getFields(Order::class, true);

        $passed = false;
        foreach ($fields as $fieldConfig) {
            if ($fieldConfig['name'] === 'orderNotes') {
                $passed = true;
            }
        }

        $this->assertTrue($passed);
    }

    public function testRelation()
    {
        $order = $this->getReference(LoadOrderNotesData::DEFAULT_ORDER_REFERENCE_ALIAS);

        $this->assertCount(1, $order->getOrderNotes()->toArray());
    }

    public function testGetCreatedOrderNote()
    {
        $orderNote = $this->manager
                          ->getRepository('OroMagentoBundle:OrderNote')
                          ->findOneBy(['originId' => LoadOrderNotesData::DEFAULT_ORIGIN_ID]);

        $this->assertNotNull($orderNote);
        $this->assertEquals($orderNote->getOriginId(), LoadOrderNotesData::DEFAULT_ORIGIN_ID);
        $this->assertSame(
            $orderNote->getOrder(),
            $this->getReference(LoadOrderNotesData::DEFAULT_ORDER_REFERENCE_ALIAS)
        );
    }

    public function testCreateOrderNote()
    {
        $now = new \DateTime('now', new \DateTimeZone('UTC'));

        $orderNote = new OrderNote();
        $orderNote->setOriginId(123456789);
        $orderNote->setMessage('test message');
        $orderNote->setCreatedAt($now);
        $orderNote->setUpdatedAt($now);

        $this->manager->persist($orderNote);
        $this->manager->flush();

        $this->assertNotNull($orderNote->getId());
        $this->assertEquals(123456789, $orderNote->getOriginId());
    }
}
