<?php

namespace OroCRM\Bundle\SalesBundle\Form\Type;

use Oro\Bundle\FormBundle\Form\EventListener\NewEntityFormListener;
use OroCRM\Bundle\AccountBundle\Entity\Account;
use OroCRM\Bundle\SalesBundle\Form\EventListener\SetChannelListener;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\AbstractType;

use OroCRM\Bundle\SalesBundle\Entity\Opportunity;

class OpportunityType extends AbstractType
{
    const NAME = 'orocrm_sales_opportunity';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'closeReason',
            'translatable_entity',
            [
                'label'       => 'orocrm.sales.opportunity.close_reason.label',
                'class'       => 'OroCRMSalesBundle:OpportunityCloseReason',
                'property'    => 'label',
                'required'    => false,
                'disabled'    => false,
                'empty_value' => 'orocrm.sales.form.choose_close_rsn'
            ]
        );

        $builder
            ->add(
                'contact',
                'orocrm_contact_select',
                [
                    'required' => false,
                    'label' => 'orocrm.sales.opportunity.contact.label',
                    'new_item_property_name'  => 'firstName',
                    'configs'            => [
                        'propertyNameForNewItem'  => 'fullName',
                    ],
                ]
            )
            ->add(
                'customer',
                'orocrm_sales_b2bcustomer_select',
                [
                    'required' => true,
                    'label' => 'orocrm.sales.opportunity.customer.label',
                    'new_item_property_name'  => 'name',
                    'configs'            => [
                        'result_template_twig'   => 'OroFormBundle:Autocomplete:default/result.html.twig',
                        'propertyNameForNewItem' => 'name',
                    ],
                ]
            )
            ->add('name', 'text', ['required' => true, 'label' => 'orocrm.sales.opportunity.name.label'])
            ->add(
                'dataChannel',
                'orocrm_channel_select_type',
                [
                    'required' => true,
                    'label'    => 'orocrm.sales.opportunity.data_channel.label',
                    'entities' => [
                        'OroCRM\\Bundle\\SalesBundle\\Entity\\Opportunity'
                    ],
                ]
            )
            ->add(
                'closeDate',
                'oro_date',
                ['required' => false, 'label' => 'orocrm.sales.opportunity.close_date.label']
            )
            ->add(
                'probability',
                'oro_percent',
                ['required' => false, 'label' => 'orocrm.sales.opportunity.probability.label']
            )
            ->add(
                'budgetAmount',
                'oro_money',
                ['required' => false, 'label' => 'orocrm.sales.opportunity.budget_amount.label']
            )
            ->add(
                'closeRevenue',
                'oro_money',
                ['required' => false, 'label' => 'orocrm.sales.opportunity.close_revenue.label']
            )
            ->add(
                'customerNeed',
                'oro_resizeable_rich_text',
                ['required' => false, 'label' => 'orocrm.sales.opportunity.customer_need.label']
            )
            ->add(
                'proposedSolution',
                'oro_resizeable_rich_text',
                ['required' => false, 'label' => 'orocrm.sales.opportunity.proposed_solution.label']
            )
            ->add(
                'notes',
                'oro_resizeable_rich_text',
                ['required' => false, 'label' => 'orocrm.sales.opportunity.notes.label']
            )
            ->add(
                'state',
                'oro_enum_select',
                [
                    'required'  => false,
                    'label'     => 'orocrm.sales.opportunity.state.label',
                    'enum_code' => Opportunity::INTERNAL_STATE_CODE
                ]
            );

        $builder->addEventListener(
            FormEvents::SUBMIT,
            function(FormEvent $event) {
                $opportunity = $event->getData();

                if ($opportunity instanceof Opportunity) {
                    $b2bCustomer = $opportunity->getCustomer();
                    if (!$b2bCustomer->getDataChannel()) {
                        // new customer needs channel
                        $b2bCustomer->setDataChannel($opportunity->getDataChannel());
                    }

                    if (!$b2bCustomer->getAccount()) {
                        // new Account for new B2bCustomer
                        $account = new Account();
                        $account->setName($b2bCustomer->getName());
                        $b2bCustomer->setAccount($account);
                    }
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
            [
                'data_class' => 'OroCRM\Bundle\SalesBundle\Entity\Opportunity',
                'intention'  => 'opportunity'
            ]
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
