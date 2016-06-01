<?php

namespace OroCRM\Bundle\ChannelBundle\EventListener;

use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\PropertyAccess\PropertyPath;

use Oro\Bundle\WorkflowBundle\Event\TransitionsAttributeEvent;
use Oro\Component\Action\Model\ContextAccessor;

use OroCRM\Bundle\ChannelBundle\Form\Type\AbstractChannelAwareType;

class TransitionsAttributeListener
{
    /** @var FormFactoryInterface */
    protected $formFactory;

    /** @var ContextAccessor */
    protected $contextAccessor;

    /**
     * @param FormFactoryInterface $formFactory
     * @param ContextAccessor $contextAccessor
     */
    public function __construct(FormFactoryInterface $formFactory, ContextAccessor $contextAccessor)
    {
        $this->formFactory = $formFactory;
        $this->contextAccessor = $contextAccessor;
    }

    /**
     * @param TransitionsAttributeEvent $event
     */
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
