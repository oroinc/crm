<?php
namespace Oro\Bundle\FlexibleEntityBundle\Form\EventListener;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Oro\Bundle\FlexibleEntityBundle\AttributeType\AbstractAttributeType;
use Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;

/**
 * Aims to generate value type based on entity value and attribute type
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
class AddValueFieldSubscriber implements EventSubscriberInterface
{
    /**
     * @var FormFactoryInterface
     */
    protected $factory;

    /**
     * @var FlexibleManager
     */
    protected $flexibleManager;

    /**
     * Constructor
     * @param FormFactoryInterface $factory         the form factory
     * @param FlexibleManager      $flexibleManager the flexible manager
     */
    public function __construct(FormFactoryInterface $factory, FlexibleManager $flexibleManager)
    {
        $this->factory         = $factory;
        $this->flexibleManager = $flexibleManager;
    }

    /**
     * Get subscribed events
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_SET_DATA => 'preSetData',
        );
    }

    /**
     * Add form field type
     * @param FormEvent $event
     */
    public function preSetData(FormEvent $event)
    {
        $value = $event->getData();
        $form  = $event->getForm();

        // skip form creation with no data
        if (null === $value) {
            return;
        }

        $attribute     = $value->getAttribute();
        $attributeType = $this->flexibleManager->getAttributeTypeFactory()->get($attribute->getAttributeType());
        $valueForm     = $attributeType->buildValueFormType($this->factory, $value);

        $form->add($valueForm);
    }
}
