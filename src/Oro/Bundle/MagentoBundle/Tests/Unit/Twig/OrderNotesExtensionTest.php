<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Model;

use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\MagentoBundle\Entity\MagentoTransport;
use Oro\Bundle\MagentoBundle\Entity\Order;
use Oro\Bundle\MagentoBundle\Twig\OrderNotesExtension;
use Oro\Component\Testing\Unit\EntityTrait;

class OrderNotesExtensionTest extends \PHPUnit\Framework\TestCase
{
    use EntityTrait;

    /** @var  \PHPUnit\Framework\MockObject\MockObject|MagentoTransport */
    private $transport;

    /** @var  \PHPUnit\Framework\MockObject\MockObject|Channel */
    private $channel;

    /** @var  OrderNotesExtension */
    private $placeholder;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->transport = $this->createMock(MagentoTransport::class);
        $this->channel = $this->createMock(Channel::class);
        $this->channel->expects($this->any())
            ->method('getTransport')
            ->willReturn($this->transport);

        $this->placeholder = new OrderNotesExtension();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        unset(
            $this->transport,
            $this->channel,
            $this->placeholder
        );
    }

    /**
     * @dataProvider placeholderDataProvider
     *
     * @param $entityClass
     * @param $isSupportedOrderNoteExtensionVersion
     * @param $isDisplayOrderNotes
     * @param $expectedResult
     */
    public function testIsOrderNotesApplicable(
        $entityClass,
        $isSupportedOrderNoteExtensionVersion,
        $isDisplayOrderNotes,
        $expectedResult
    ) {
        $entity = $this->getEntity($entityClass, ['channel' => $this->channel]);
        $this->transport->expects($this->once())
            ->method('isSupportedOrderNoteExtensionVersion')
            ->willReturn($isSupportedOrderNoteExtensionVersion);

        if (!$entity instanceof Order) {
            $this->transport->expects($this->any())
                ->method('getIsDisplayOrderNotes')
                ->willReturn($isDisplayOrderNotes);
        }

        $result = $this->placeholder->isOrderNotesApplicable($entity);

        $this->assertEquals($result, $expectedResult);
    }

    /**
     * @return array
     */
    public function placeholderDataProvider()
    {
        return [
            'On customer page with supported extension version and enabled order notes' => [
                'entityClass' => Customer::class,
                'isSupportedOrderNoteExtensionVersion' => true,
                'isDisplayOrderNotes' => true,
                'expectedResult' => true
            ],
            'On customer page with enabled order notes, but not supported extension version' => [
                'entityClass' => Customer::class,
                'isSupportedOrderNoteExtensionVersion' => false,
                'isDisplayOrderNotes' => true,
                'expectedResult' => false
            ],
            'On customer page with supported extension version and disabled order notes' => [
                'entityClass' => Customer::class,
                'isSupportedOrderNoteExtensionVersion' => true,
                'isDisplayOrderNotes' => false,
                'expectedResult' => false
            ],
            'On customer page with not supported extension version and disabled order notes' => [
                'entityClass' => Customer::class,
                'isSupportedOrderNoteExtensionVersion' => false,
                'isDisplayOrderNotes' => false,
                'expectedResult' => false
            ],
            'On order page with not supported extension version and disabled order notes' => [
                'entityClass' => Customer::class,
                'isSupportedOrderNoteExtensionVersion' => false,
                'isDisplayOrderNotes' => false,
                'expectedResult' => false
            ],
            'On order page page with supported extension version and enabled order notes' => [
                'entityClass' => Order::class,
                'isSupportedOrderNoteExtensionVersion' => true,
                'isDisplayOrderNotes' => true,
                'expectedResult' => true
            ],
            'On order page page with supported extension version, but disabled order notes' => [
                'entityClass' => Order::class,
                'isSupportedOrderNoteExtensionVersion' => true,
                'isDisplayOrderNotes' => true,
                'expectedResult' => true
            ]
        ];
    }
}
