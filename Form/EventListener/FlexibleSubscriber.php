<?php
namespace Oro\Bundle\FlexibleEntityBundle\Form\EventListener;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleEntityManager;
use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttributeType;

/**
 * Aims to generate flexible form type field based on attributes
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
class FlexibleSubscriber implements EventSubscriberInterface
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
    public function __construct(FormFactoryInterface $factory/*, FlexibleEntityManager $manager*/)
    {
        $this->factory = $factory;
        // TODO: we have to inject flexible entity type and dedicated flexible manager too
        // $this->manager = $manager;
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

        // add entity attributes one by one
        foreach ($this->manager->getEntityRepository()->getCodeToAttributes(array()) as $attr) {
            $options = array(
                'required' => $attr->getRequired(),
                'property_path' => false,
            );

            switch ($attr->getBackendType()) {
                case AbstractAttributeType::BACKEND_TYPE_DATE:
                    $type = 'date';

                    $options['widget'] = 'single_text';
                    $options['input'] = 'datetime';
                    $options['attr'] = array(
                        'class' => 'datepicker input-small',
                        'placeholder' => 'YYYY-MM-DD',
                    );

                    break;

                case AbstractAttributeType::BACKEND_TYPE_INTEGER:
                    $type = 'integer';

                    $options['attr'] = array(
                        'class' => 'input-small',
                    );

                    break;

                case AbstractAttributeType::BACKEND_TYPE_OPTION:
                    $type = 'choice';

                    $options['choices'] = array();

                    foreach ($this->manager->getAttributeOptionValueRepository()->findAll() as $option) {
                        $options['choices'][$option->getId()] = $option->getValue();
                    }

                    break;

                case AbstractAttributeType::BACKEND_TYPE_VARCHAR:
                default:
                    $type = 'text';

                    break;
            }

            $value = $data->getId()
                ? $data->getValueData($attr->getCode())
                : null;

            $form->add($this->factory->createNamed($attr->getCode(), $type, $value, $options));
        }
    }
}