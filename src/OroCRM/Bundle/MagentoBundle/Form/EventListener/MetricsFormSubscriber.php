<?php

namespace OroCRM\Bundle\MagentoBundle\Form\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Translation\TranslatorInterface;

use Oro\Bundle\DashboardBundle\Model\WidgetConfigs;

class MetricsFormSubscriber implements EventSubscriberInterface
{
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
        $widgetname = $event->getForm()->getConfig()->getOption('widget_name');
        $attributes = $this->widgetConfigs->getWidgetAttributesForTwig($widgetname);
        $dataItems = $attributes['widgetDataItems'];
        $originalData = $this->getIndexedData($event->getData());

        $data = [];
        $order = 1;
        foreach ($dataItems as $id => $item) {
            $oldItem = isset($originalData[$id]) ? $originalData[$id] : null;

            $data[$id] = [
                'id'    => $id,
                'label' => $this->translator->trans($item['label']),
                'show'  => $oldItem ? $oldItem['show'] : ($originalData ? false : true),
                'order' => $oldItem ? $oldItem['order'] : $order,
            ];

            $order++;
        }

        usort($data, function ($a, $b) {
            return $a['order'] - $b['order'];
        });

        $event->setData(['items' => array_values($data)]);
    }

    /**
     * @param array $data
     *
     * @return array
     */
    protected function getIndexedData(array $data = null)
    {
        $result = [];

        if (!$data) {
            return $result;
        }

        foreach ($data['items'] as $item) {
            $result[$item['id']] = $item;
        }

        return $result;
    }
}
