<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\ImportExport\Processor\OrderNotes;

use Oro\Bundle\MagentoBundle\Entity\Order;
use Oro\Bundle\MagentoBundle\Entity\OrderNote;
use Oro\Bundle\MagentoBundle\ImportExport\Processor\OrderNotes\Context;
use Oro\Bundle\MagentoBundle\ImportExport\Processor\OrderNotes\NoteFieldsProcessor;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\UIBundle\Tools\HtmlTagHelper;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Component\Testing\Unit\EntityTrait;

class NoteFieldsProcessorTest extends \PHPUnit\Framework\TestCase
{
    use EntityTrait;

    /**
     * @var NoteFieldsProcessor
     */
    private $processor;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject | HtmlTagHelper
     */
    private $htmlTagHelper;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->htmlTagHelper = $this->createMock(HtmlTagHelper::class);
        $this->htmlTagHelper
            ->method('sanitize')
            ->willReturnCallback(
                function ($message) {
                    return sprintf('%s (sanitized)', $message);
                }
            );

        $this->processor = new NoteFieldsProcessor($this->htmlTagHelper);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        unset($this->htmlTagHelper);
        unset($this->processor);
    }

    public function testProcess()
    {
        /**
         * @var $order Order
         */
        $owner = $this->getEntity(User::class, ['id' => 1]);
        $organization = $this->getEntity(Organization::class, ['id' => 2]);
        $order = $this->getEntity(
            Order::class,
            [
                'id' => 12,
                'owner' => $owner,
                'organization' => $organization
            ]
        );
        /**
         * @var $orderNote OrderNote
         */
        $orderNote = $this->getEntity(
            OrderNote::class,
            [
                'id' => 14,
                'message' => 'Not sanitized message',
            ]
        );
        $order->addOrderNote($orderNote);
        $context = Context::createContext($order, $orderNote);

        $this->processor->process($context);

        $this->assertSame(
            $owner,
            $orderNote->getOwner(),
            'Order Note must contain the same owner as Order !'
        );
        $this->assertSame(
            $organization,
            $orderNote->getOrganization(),
            'Order Note must contain the same organization as Order !'
        );
        $this->assertEquals(
            'Not sanitized message (sanitized)',
            $orderNote->getMessage(),
            'Message must be sanitized !'
        );
    }
}
