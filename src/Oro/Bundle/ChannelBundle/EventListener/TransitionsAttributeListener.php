<?php

namespace Oro\Bundle\ChannelBundle\EventListener;

use Oro\Bundle\ChannelBundle\Form\Type\AbstractChannelAwareType;
use Oro\Bundle\WorkflowBundle\Event\TransitionsAttributeEvent;
use Oro\Component\ConfigExpression\ContextAccessor;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\PropertyAccess\PropertyPath;

class TransitionsAttributeListener
{
    /** @var FormFactoryInterface */
    protected $formFactory;

    /** @var ContextAccessor */
    protected $contextAccessor;

    public function __construct(FormFactoryInterface $formFactory, ContextAccessor $contextAccessor)
    {
        $this->formFactory = $formFactory;
        $this->contextAccessor = $contextAccessor;
    }

    public function beforeAddAttribute(TransitionsAttributeEvent $event)
    {
        $attributeOptions = $event->getAttributeOptions();
        $options = $event->getOptions();

        try {
            $form = $this->formFactory->create($attributeOptions['form_type']);
        } catch (\Exception $e) {
            return;
        }

        $parent = $form->getConfig()->getType()->getParent();
        if ($parent && $parent->getInnerType() instanceof AbstractChannelAwareType) {
            $attributeOptions['options']['channel_id'] = new PropertyPath('data.dataChannel.id');
            $contextAccessor = $this->contextAccessor;
            array_walk_recursive(
                $attributeOptions,
                function (&$leaf) use ($options, $contextAccessor) {
                    $leaf = $contextAccessor->getValue($options['workflow_item'], $leaf);
                }
            );
            $event->setAttributeOptions($attributeOptions);
        }
    }
}
