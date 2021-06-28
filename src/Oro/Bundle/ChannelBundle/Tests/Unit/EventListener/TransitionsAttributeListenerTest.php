<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\EventListener;

use Oro\Bundle\ActionBundle\Model\Attribute;
use Oro\Bundle\ChannelBundle\EventListener\TransitionsAttributeListener;
use Oro\Bundle\ChannelBundle\Form\Type\ChannelEntityType;
use Oro\Bundle\ChannelBundle\Form\Type\CreateOrSelectInlineChannelAwareType;
use Oro\Bundle\ChannelBundle\Tests\Unit\Form\Type\TestForm;
use Oro\Bundle\SalesBundle\Form\Type\OpportunitySelectType;
use Oro\Bundle\WorkflowBundle\Event\TransitionsAttributeEvent;
use Oro\Component\ConfigExpression\ContextAccessor;
use Oro\Component\Testing\Unit\PreloadedExtension;
use Symfony\Component\Form\Forms;

class TransitionsAttributeListenerTest extends \PHPUnit\Framework\TestCase
{
    /** @var TransitionsAttributeListener */
    private $listener;

    protected function setUp(): void
    {
        $formArray = [
            'oro_channel_entities' => new ChannelEntityType(),
            'oro_sales_opportunity_select' => new OpportunitySelectType(),
            'oro_entity_create_or_select_inline_channel_aware' => new CreateOrSelectInlineChannelAwareType(),
            'oro_entity_create_or_select_inline' => new TestForm('oro_entity_create_or_select_inline'),
            'oro_jqueryselect2_hidden' => new TestForm('oro_jqueryselect2_hidden')
        ];

        $factory = Forms::createFormFactoryBuilder()
            ->addExtensions(
                [
                    new PreloadedExtension($formArray, [])
                ]
            )
            ->getFormFactory();

        $this->listener = new TransitionsAttributeListener(
            $factory,
            $this->createMock(ContextAccessor::class)
        );
    }

    public function testWrongFormType()
    {
        $attribute = new Attribute();
        $attributeOptions = ['form_type' => 'wrong_form'];
        $options = ['option1', 'option2'];

        $event = new TransitionsAttributeEvent($attribute, $attributeOptions, $options);
        $this->listener->beforeAddAttribute($event);

        $this->assertEquals($attributeOptions, $event->getAttributeOptions());
    }

    public function testNotAbstractChannelAwareType()
    {
        $attribute = new Attribute();
        $attributeOptions = ['form_type' => ChannelEntityType::class];
        $options = ['option1', 'option2'];

        $event = new TransitionsAttributeEvent($attribute, $attributeOptions, $options);
        $this->listener->beforeAddAttribute($event);

        $this->assertEquals($attributeOptions, $event->getAttributeOptions());
    }
}
