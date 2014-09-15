<?php

namespace OroCRM\Bundle\CampaignBundle\Form\Type;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

abstract class AbstractTransportSettingsType extends AbstractType
{
    /**
     * @var EventSubscriberInterface[]
     */
    protected $subscribers = [];

    /**
     * @param EventSubscriberInterface $subscriber
     */
    public function addSubscriber(EventSubscriberInterface $subscriber)
    {
        $this->subscribers[] = $subscriber;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        foreach ($this->subscribers as $subscriber) {
            $builder->addEventSubscriber($subscriber);
        }

        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event) {
                $data = $event->getData();
                unset($data['parentData']);
                $event->setData($data);
            }
        );
    }
}
