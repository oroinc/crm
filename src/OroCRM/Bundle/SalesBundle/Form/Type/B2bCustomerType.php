<?php

namespace OroCRM\Bundle\SalesBundle\Form\Type;

use Doctrine\Common\Collections\Collection;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Routing\Router;

use Oro\Bundle\LocaleBundle\Formatter\NameFormatter;

use OroCRM\Bundle\SalesBundle\Entity\B2bCustomer;
use OroCRM\Bundle\SalesBundle\Entity\Lead;
use OroCRM\Bundle\SalesBundle\Entity\Opportunity;

class B2bCustomerType extends AbstractType
{
    /** @var Router */
    protected $router;

    /** @var NameFormatter */
    protected $nameFormatter;

    /**
     * @param Router        $router
     * @param NameFormatter $nameFormatter
     */
    public function __construct(Router $router, NameFormatter $nameFormatter)
    {
        $this->router        = $router;
        $this->nameFormatter = $nameFormatter;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'orocrm_sales_b2bcustomer';
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'name',
            'text',
            [
                'required' => true,
                'label'    => 'orocrm.sales.b2bcustomer.name.label'
            ]
        );
        $builder->add(
            'account',
            'orocrm_account_select',
            [
                'required' => true,
                'label'    => 'orocrm.sales.b2bcustomer.account.label'
            ]
        );
        $builder->add(
            'contact',
            'orocrm_contact_select',
            [
                'label'    => 'orocrm.sales.b2bcustomer.contact.label',
                'required' => false,
            ]
        );
        $builder->add(
            'tags',
            'oro_tag_select',
            [
                'label' => 'oro.tag.entity_plural_label'
            ]
        );
        $builder->add(
            'dataChannel',
            'orocrm_channel_select_type',
            [
                'required' => true,
                'label'    => 'orocrm.sales.b2bcustomer.data_channel.label',
                'entities' => [
                    'OroCRM\\Bundle\\SalesBundle\\Entity\\B2bCustomer'
                ],
            ]
        );
        $builder->add(
            'leads',
            'oro_multiple_entity_channel_aware',
            [
                'add_acl_resource'      => 'orocrm_sales_lead_view',
                'class'                 => 'OroCRMSalesBundle:Lead',
                'default_element'       => 'default_lead',
                'required'              => false,
                'selector_window_title' => 'orocrm.sales.b2bcustomer.leads.select',
            ]
        );
        $builder->add(
            'opportunities',
            'oro_multiple_entity_channel_aware',
            [
                'add_acl_resource'      => 'orocrm_sales_opportunity_view',
                'class'                 => 'OroCRMSalesBundle:Opportunity',
                'default_element'       => 'default_opportunity',
                'required'              => false,
                'selector_window_title' => 'orocrm.sales.b2bcustomer.opportunities.select',
            ]
        );
        $builder->add(
            'shippingAddress',
            'oro_address',
            [
                'cascade_validation' => true,
                'required'           => false
            ]
        );
        $builder->add(
            'billingAddress',
            'oro_address',
            [
                'cascade_validation' => true,
                'required'           => false
            ]
        );
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => 'OroCRM\Bundle\SalesBundle\Entity\B2bCustomer',
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        /** @var B2bCustomer $b2bCustomer */
        $b2bCustomer = $form->getData();
        $parameters  = $b2bCustomer->getId() ? ['id' => $b2bCustomer->getId()] : [];

        $view->children['leads']->vars['selection_route']            = 'orocrm_sales_widget_leads_assign';
        $view->children['leads']->vars['selection_route_parameters'] = $parameters;
        $view->children['leads']->vars['initial_elements']           = $this->getInitialElements(
            $b2bCustomer->getLeads()
        );

        $view->children['opportunities']->vars['selection_route'] = 'orocrm_sales_widget_opportunities_assign';
        $view->children['opportunities']->vars['selection_route_parameters'] = $parameters;
        $view->children['opportunities']->vars['initial_elements']           = $this->getInitialOpportunities(
            $b2bCustomer->getOpportunities()
        );
    }

    /**
     * @param Collection $leads
     *
     * @return array
     */
    protected function getInitialElements(Collection $leads)
    {
        $result = [];
        /** @var Lead $lead */
        foreach ($leads as $lead) {
            $phoneNumber = $lead->getPhoneNumber();
            $email       = $lead->getEmail();
            $result[]    = [
                'id'        => $lead->getId(),
                'label'     => $lead->getName(),
                'link'      => $this->router->generate('orocrm_sales_lead_info', ['id' => $lead->getId()]),
                'extraData' => [
                    ['label' => 'Phone', 'value' => $phoneNumber ? $phoneNumber : null],
                    ['label' => 'Email', 'value' => $email ? $email : null],
                ],
            ];
        }

        return $result;
    }

    /**
     * @param Collection $opportunities
     *
     * @return array
     */
    protected function getInitialOpportunities(Collection $opportunities)
    {
        $result = [];
        /** @var Opportunity $opportunity */
        foreach ($opportunities as $opportunity) {
            $email    = $opportunity->getEmail();
            $result[] = [
                'id'        => $opportunity->getId(),
                'label'     => $opportunity->getName(),
                'link'      => $this->router->generate('orocrm_sales_lead_info', ['id' => $opportunity->getId()]),
                'extraData' => [
                    ['label' => 'Email', 'value' => $email ? $email : null]

                ],
            ];
        }

        return $result;
    }
}
