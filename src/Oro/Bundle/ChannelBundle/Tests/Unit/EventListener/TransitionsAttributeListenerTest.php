<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\EventListener;

use Symfony\Component\Form\Forms;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\PropertyAccess\PropertyPath;

use Oro\Bundle\ActionBundle\Model\Attribute;
use Oro\Bundle\WorkflowBundle\Event\TransitionsAttributeEvent;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\ChannelBundle\EventListener\TransitionsAttributeListener;
use Oro\Bundle\ChannelBundle\Form\Type\ChannelEntityType;
use Oro\Bundle\SalesBundle\Form\Type\OpportunitySelectType;
use Oro\Bundle\ChannelBundle\Form\Type\CreateOrSelectInlineChannelAwareType;
use Oro\Bundle\ChannelBundle\Tests\Unit\Form\Type\TestForm;

class TransitionsAttributeListenerTest extends \PHPUnit_Framework_TestCase
{
    const EXPECTED_CHANNEL_ID = 7;

    /** @var TransitionsAttributeListener */
    protected $listener;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $contextAccessor;

    protected function setUp()
    {
        $formArray = [
            'oro_channel_entities' => new ChannelEntityType(),
            'oro_sales_opportunity_select' => new OpportunitySelectType(),
            'oro_entity_create_or_select_inline_channel_aware' => new CreateOrSelectInlineChannelAwareType(),
            'oro_entity_create_or_select_inline' => new TestForm('oro_entity_create_or_select_inline'),
            'oro_jqueryselect2_hidden' => new TestForm('oro_jqueryselect2_hidden')
        ];
        $this->contextAccessor = $this
            ->getMockBuilder('Oro\Component\ConfigExpression\ContextAccessor')
            ->setMethods(['getValue'])
            ->getMock();

        $factory = Forms::createFormFactoryBuilder()
            ->addExtensions(
                [
                    new PreloadedExtension($formArray, [])
                ]
            )
            ->getFormFactory();

        $this->listener = new TransitionsAttributeListener($factory, $this->contextAccessor);
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
        $attributeOptions = ['form_type' => 'oro_channel_entities'];
        $options = ['option1', 'option2'];

        $event = new TransitionsAttributeEvent($attribute, $attributeOptions, $options);
        $this->listener->beforeAddAttribute($event);

        $this->assertEquals($attributeOptions, $event->getAttributeOptions());
    }
}
