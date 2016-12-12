<?php

namespace Oro\Bundle\SalesBundle\Form\Type;

use Oro\Bundle\SoapBundle\Form\EventListener\PatchSubscriber;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class LeadApiType extends LeadType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder->add(
            'customer',
            'oro_sales_b2bcustomer_with_channel_create_or_select',
            [
                'required'               => true,
                'label'                  => 'oro.sales.opportunity.customer.label',
                'new_item_property_name' => 'name',
            ]
        );
        $builder->addEventSubscriber(new PatchSubscriber());
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Oro\Bundle\SalesBundle\Entity\Lead',
                'intention'  => 'group',
                'csrf_protection' => false,
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'oro_sales_lead_api';
    }
}
