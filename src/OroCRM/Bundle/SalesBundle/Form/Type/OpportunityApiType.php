<?php

namespace OroCRM\Bundle\SalesBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Oro\Bundle\SoapBundle\Form\EventListener\PatchSubscriber;

class OpportunityApiType extends OpportunityType
{
    const NAME = 'orocrm_sales_opportunity_api';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder->addEventSubscriber(new PatchSubscriber());
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'OroCRM\Bundle\SalesBundle\Entity\Opportunity',
                'intention'  => 'opportunity',
                'csrf_protection' => false,
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }
}
