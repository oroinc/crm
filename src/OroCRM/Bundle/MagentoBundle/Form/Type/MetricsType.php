<?php

namespace OroCRM\Bundle\MagentoBundle\Form\Type;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class MetricsType extends AbstractType
{
    const NAME = 'orocrm_magento_metrics';

    /** @var EventSubscriberInterface */
    private $itemsSubscriber;

    /**
     * @param EventSubscriberInterface $itemsSubscriber
     */
    public function __construct(EventSubscriberInterface $itemsSubscriber)
    {
        $this->itemsSubscriber = $itemsSubscriber;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventSubscriber($this->itemsSubscriber);

        $builder->add('items', 'collection', [
            'type' => 'orocrm_magento_metric',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setRequired([
            'widget_name',
        ]);

        $resolver->setAllowedTypes([
            'widget_name' => 'string',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return static::NAME;
    }
}
