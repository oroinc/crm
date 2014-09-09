<?php

namespace OroCRM\Bundle\CampaignBundle\Form\EventListener;

use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class EntityNameListener implements EventSubscriberInterface
{
    /**
     * @var RegistryInterface
     */
    protected $registry;

    /**
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SUBMIT => ['preSubmit', 10],
        ];
    }

    /**
     * @param FormEvent $event
     */
    public function preSubmit(FormEvent $event)
    {
        $data = $event->getData();

        if (empty($data['marketingList'])) {
            return;
        }

        $marketingList = $this
            ->registry
            ->getRepository('OroCRMMarketingListBundle:MarketingList')
            ->find((int)$data['marketingList']);

        if (is_null($marketingList)) {
            return;
        }

        $data['entityName'] = $marketingList->getEntity();

        $event->setData($data);
    }
}
