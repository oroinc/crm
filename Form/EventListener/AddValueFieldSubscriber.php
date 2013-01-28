<?php
namespace Oro\Bundle\FlexibleEntityBundle\Form\EventListener;

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
        $data = $event->getData();
        $form = $event->getForm();

        // During form creation setData() is called with null as an argument
        // by the FormBuilder constructor. You're only concerned with when
        // setData is called with an actual Entity object in it (whether new
        // or fetched with Doctrine). This if statement lets you skip right
        // over the null condition.
        if (null === $data) {
            return;
        }

        // configure and add relevant form field type
        $options = array(
            'required' => $data->getAttribute()->getRequired(),
            'property_path' => true,
            'label' => $data->getAttribute()->getCode()
        );

        switch ($data->getAttribute()->getBackendType()) {
            case AbstractAttributeType::FRONTEND_TYPE_DATE:
                $type = 'date';

                $options['widget'] = 'single_text';
                $options['input'] = 'datetime';
                $options['attr'] = array(
                    'class' => 'datepicker input-small',
                    'placeholder' => 'YYYY-MM-DD',
                );

                break;
/*
                case AbstractAttributeType::FRONTEND_TYPE_TEXTFIELD:
                    $type = 'integer';

                    $options['attr'] = array(
                        'class' => 'input-small',
                    );

                    break;*/
/*
                case AbstractAttributeType::FRONTEND_TYPE_LIST:
                    $type = 'choice';

                    $options['choices'] = array();

                    foreach ($data->getOptions() as $option) {
                        $options['choices'][$option->getId()] = $option->getValue();
                    }

                    break;*/

            case AbstractAttributeType::FRONTEND_TYPE_TEXTFIELD:
            default:
                $type = 'text';

                break;
        }

        $form->add($this->factory->createNamed('data', $type, $data->getData(), $options));
    }
}