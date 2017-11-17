<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Model;

use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\MagentoBundle\Entity\MagentoTransport;
use Oro\Bundle\MagentoBundle\Entity\Order;
use Oro\Bundle\MagentoBundle\Twig\OrderNotesExtension;
use Oro\Component\Testing\Unit\EntityTrait;

class OrderNotesExtensionTest extends \PHPUnit_Framework_TestCase
{
    use EntityTrait;

    /** @var  \PHPUnit_Framework_MockObject_MockObject|MagentoTransport */
    private $transport;

    /** @var  \PHPUnit_Framework_MockObject_MockObject|Channel */
    private $channel;

    /** @var  OrderNotesExtension */
    private $placeholder;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
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
    protected function tearDown()
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
     * @param $isSupportedExtensionVersion
     * @param $isDisplayOrderComments
     * @param $expectedResult
     */
    public function testIsOrderNotesApplicable(
        $entityClass,
        $isSupportedExtensionVersion,
        $isDisplayOrderComments,
        $expectedResult
    ) {
        $entity = $this->getEntity($entityClass, ['channel' => $this->channel]);
        $this->transport->expects($this->once())
            ->method('isSupportedExtensionVersion')
            ->willReturn($isSupportedExtensionVersion);

        if (!$entity instanceof Order) {
            $this->transport->expects($this->any())
                ->method('getIsDisplayOrderComments')
                ->willReturn($isDisplayOrderComments);
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
                'isSupportedExtensionVersion' => true,
                'isDisplayOrderComments' => true,
                'expectedResult' => true
            ],
            'On customer page with enabled order notes, but not supported extension version' => [
                'entityClass' => Customer::class,
                'isSupportedExtensionVersion' => false,
                'isDisplayOrderComments' => true,
                'expectedResult' => false
            ],
            'On customer page with supported extension version and disabled order notes' => [
                'entityClass' => Customer::class,
                'isSupportedExtensionVersion' => true,
                'isDisplayOrderComments' => false,
                'expectedResult' => false
            ],
            'On customer page with not supported extension version and disabled order notes' => [
                'entityClass' => Customer::class,
                'isSupportedExtensionVersion' => false,
                'isDisplayOrderComments' => false,
                'expectedResult' => false
            ],
            'On order page with not supported extension version and disabled order notes' => [
                'entityClass' => Customer::class,
                'isSupportedExtensionVersion' => false,
                'isDisplayOrderComments' => false,
                'expectedResult' => false
            ],
            'On order page page with supported extension version and enabled order notes' => [
                'entityClass' => Order::class,
                'isSupportedExtensionVersion' => true,
                'isDisplayOrderComments' => true,
                'expectedResult' => true
            ],
            'On order page page with supported extension version, but disabled order notes' => [
                'entityClass' => Order::class,
                'isSupportedExtensionVersion' => true,
                'isDisplayOrderComments' => true,
                'expectedResult' => true
            ]
        ];
    }
}
