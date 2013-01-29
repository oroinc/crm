<?php
namespace Oro\Bundle\FlexibleEntityBundle\Form\EventListener;

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
     * @var FlexibleEntityManager
     */
    protected $manager;

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
     * @return multitype:string
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
        // configure and add relevant form field type
        $attribute = $value->getAttribute();
        $fieldName = 'data'; // for classic backend type
        $options = array(
            'required'      => $attribute->getRequired(),
            'property_path' => true,
            'label'         => $attribute->getCode()
        );
        // configuration depends on field type
        switch ($attribute->getFrontendType()) {
            case AbstractAttributeType::FRONTEND_TYPE_DATE:
                $type = 'date';
                $options['widget'] = 'single_text';
                $options['input'] = 'datetime';
                $options['attr'] = array(
                    'class' => 'datepicker input-small',
                    'placeholder' => 'YYYY-MM-DD',
                );
                $default = $value->getData();
                // set default value from attribute
                $default = is_null($default) ? $attribute->getDefaultValue() : $default;

                break;

            case AbstractAttributeType::FRONTEND_TYPE_DATETIME:
                $type = 'date';
                $options['widget'] = 'single_text';
                $options['input'] = 'datetime';
                $options['attr'] = array(
                    'class' => 'datepicker input-small',
                    'placeholder' => 'YYYY-MM-DD',
                );
                $default = $value->getData();
                // set default value from attribute
                $default = is_null($default) ? $attribute->getDefaultValue() : $default;

                break;


            case AbstractAttributeType::FRONTEND_TYPE_TEXTAREA:
                $type = 'textarea';
                $default = $value->getData();
                // set default value from attribute
                $default = is_null($default) ? $attribute->getDefaultValue() : $default;

                break;


            case AbstractAttributeType::FRONTEND_TYPE_PRICE:
                $type = 'money';
                $options['currency']= $value->getCurrency();
                $default = $value->getData();
                // set default value from attribute
                $default = is_null($default) ? $attribute->getDefaultValue() : $default;

                break;

            case AbstractAttributeType::FRONTEND_TYPE_LIST:

                $fieldName = 'option';
                $type = 'entity';
                // radio buttons (one option)
                $options['expanded'] = true;
                $options['multiple'] = false;
                // TODO : get from flexible manager config ?
                $options['class'] = 'OroFlexibleEntityBundle:AttributeOption';
                $options['query_builder'] = function(EntityRepository $er) use ($attribute) {
                    return $er->createQueryBuilder('opt')->where('opt.attribute = '.$attribute->getId());
                };
                $default = $value->getOption();

                break;

            case AbstractAttributeType::FRONTEND_TYPE_MULTILIST:

                $fieldName = 'options';
                $type = 'entity';
                // checkbox buttons (many options)
                $options['expanded'] = true;
                $options['multiple'] = true;
                // TODO : get from flexible manager config ?
                $options['class'] = 'OroFlexibleEntityBundle:AttributeOption';
                $options['query_builder'] = function(EntityRepository $er) use ($attribute) {
                    return $er->createQueryBuilder('opt')->where('opt.attribute = '.$attribute->getId());
                };
                $default = $value->getOptions();

                break;

            case AbstractAttributeType::FRONTEND_TYPE_TEXTFIELD:
            default:
                $type = 'text';
                $default = $value->getData();
                // set default value from attribute
                $default = is_null($default) ? $attribute->getDefaultValue() : $default;

                break;
        }

        $form->add($this->factory->createNamed($fieldName, $type, $default, $options));
    }
}