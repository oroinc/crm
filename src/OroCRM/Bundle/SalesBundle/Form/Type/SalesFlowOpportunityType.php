<?php

namespace OroCRM\Bundle\SalesBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

use OroCRM\Bundle\SalesBundle\Entity\SalesFlowOpportunity;

class SalesFlowOpportunityType extends AbstractType
{
    const NAME = 'orocrm_sales_sales_flow_opportunity';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) {
                /** @var SalesFlowOpportunity $opportunity */
                $opportunity = $event->getData();
                $form = $event->getForm();
                if (!$opportunity || null === $opportunity->getId()) {
                    $form->add(
                        'opportunity',
                        'orocrm_sales_opportunity_select',
                        array(
                            'required' => true,
                            'error_bubbling' => false,
                            'label' => 'orocrm.sales.salesflowopportunity.opportunity.label'
                        )
                    );
                }
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'OroCRM\Bundle\SalesBundle\Entity\SalesFlowOpportunity',
                'error_mapping' => array('.' => 'opportunity')
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
