<?php
namespace Oro\Bundle\FlexibleEntityBundle\Form\EventListener;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttributeType;

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
     * Constructor
     * @param FormFactoryInterface $factory
     */
    public function __construct(FormFactoryInterface $factory)
    {
        $this->factory = $factory;
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
        $form = $event->getForm();

        // skip form creation with no data
        if (null === $value) {
            return;
        }

        $attribute          = $value->getAttribute();
        $attributeTypeClass = $attribute->getAttributeType();
        $attributeType      = new $attributeTypeClass();

        $formName    = $attribute->getBackendType();
        $formType    = $attributeType->getFormType();
        $formOptions = $attributeType->prepareFormOptions($attribute);
        $data        = is_null($value->getData()) ? $attribute->getDefaultValue() : $value->getData();

        $form->add($this->factory->createNamed($formName, $formType, $data, $formOptions));
    }
}
