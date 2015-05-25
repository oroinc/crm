<?php

namespace OroCRM\Bundle\MagentoBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MetricsType extends AbstractType
{
    const NAME = 'orocrm_magento_metrics';

    /** @var EventSubscriberInterface */
    protected $metricsSubscriber;

    /**
     * @param EventSubscriberInterface $metricsSubscriber
     */
    public function __construct(EventSubscriberInterface $metricsSubscriber)
    {
        $this->metricsSubscriber = $metricsSubscriber;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventSubscriber($this->metricsSubscriber);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return static::NAME;
    }
}
