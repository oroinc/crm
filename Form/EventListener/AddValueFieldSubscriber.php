<?php
namespace Oro\Bundle\FlexibleEntityBundle\Form\EventListener;

use Oro\Bundle\FlexibleEntityBundle\Form\DataTransformer\FileToTextTransformer;

use Oro\Bundle\FlexibleEntityBundle\Entity\AttributeOption;

use Oro\Bundle\FlexibleEntityBundle\Entity\Attribute;

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
        // skip form creation with no value
        if (null === $value) {
            return;
        }

        // prepare basic configuration
        $attribute = $value->getAttribute();
        $options = array('property_path' => true,);

        // get attribute type
        $attTypeClass = $attribute->getAttributeType();
        $attType = new $attTypeClass();

        // merge with attribute type configuration
        $fieldName   = $attType->getFieldName();
        $formType    = $attType->getFormType();
        $formOptions = array_merge($options, $attType->prepareFormOptions($attribute));

        // prepare current value
        if ($fieldName == 'option') {
            $data = $value->getOption();
        } elseif ($fieldName == 'options') {
            $data = $value->getOptions();
        } elseif ($fieldName == 'media') {
            $data = $value->getMedia();
        } else {
            $data = $value->getData();
        }

        // get default value if null
        $data = is_null($data) ? $attribute->getDefaultValue() : $data;

        $form->add(
            $this->factory->createNamed($fieldName, $formType, $data, $formOptions)
        );
    }
}
