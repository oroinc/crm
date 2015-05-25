<?php

namespace OroCRM\Bundle\MagentoBundle\Form\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Translation\TranslatorInterface;

use Oro\Bundle\DashboardBundle\Model\WidgetConfigs;

class MetricsFormSubscriber implements EventSubscriberInterface
{
    const WIDGET_NAME = 'big_numbers_widget';

    /** @var WidgetConfigs $manager */
    protected $widgetConfigs;

    /** @var TranslatorInterface */
    protected $translator;

    /**
     * @param WidgetConfigs $widgetConfigs
     * @param TranslatorInterface $translator
     */
    public function __construct(WidgetConfigs $widgetConfigs, TranslatorInterface $translator)
    {
        $this->widgetConfigs = $widgetConfigs;
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SET_DATA => 'preSet',
        ];
    }

    /**
     * @param FormEvent $event
     */
    public function preSet(FormEvent $event)
    {
        $attributes = $this->widgetConfigs->getWidgetAttributesForTwig(static::WIDGET_NAME);
        $dataItems = $attributes['widgetDataItems'];

        $data = [];
        foreach ($dataItems as $id => $item) {
            $data[] = [
                'id'    => $id,
                'label' => $this->translator->trans($item['label']),
            ];
        }

        $event->setData($data);
    }
}
