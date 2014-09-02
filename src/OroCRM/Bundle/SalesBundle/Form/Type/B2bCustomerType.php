<?php

namespace OroCRM\Bundle\SalesBundle\Form\Type;

use Doctrine\Common\Collections\Collection;

use Oro\Bundle\LocaleBundle\Formatter\NameFormatter;

use OroCRM\Bundle\SalesBundle\Entity\B2bCustomer;
use OroCRM\Bundle\SalesBundle\Entity\Lead;
use OroCRM\Bundle\SalesBundle\Entity\Opportunity;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Routing\Router;

class B2bCustomerType extends AbstractType
{
    /**
     * @var Router
     */
    protected $router;

    /**
     * @var NameFormatter
     */
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
                'label'    => 'orocrm.sales.b2bcustomer.data_channel.label'
            ]
        );
        $builder->add(
            'leads',
            'oro_multiple_entity',
            [
                'add_acl_resource'      => 'orocrm_sales_lead_view',
                'class'                 => 'OroCRMSalesBundle:Lead',
                'default_element'       => 'default_contact',
                'required'              => false,
                'selector_window_title' => 'orocrm.sales.b2bcustomer.leads.select',
            ]
        );
        $builder->add(
            'opportunities',
            'oro_multiple_entity',
            [
                'add_acl_resource'      => 'orocrm_sales_opportunity_view',
                'class'                 => 'OroCRMSalesBundle:Opportunity',
                'default_element'       => 'default_contact',
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
            array(
                'data_class' => 'OroCRM\Bundle\SalesBundle\Entity\B2bCustomer',
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        /** @var B2bCustomer $b2bcustomer */
        $b2bcustomer = $form->getData();
        $view->children['leads']->vars['grid_url'] = $this->router->generate(
            'orocrm_sales_widget_leads_assign',
            array('id' => $b2bcustomer->getId())
        );
        $view->children['leads']->vars['initial_elements'] = $this->getInitialElements($b2bcustomer->getLeads());

        $view->children['opportunities']->vars['grid_url'] = $this->router->generate(
            'orocrm_sales_widget_opportunities_assign',
            array('id' => $b2bcustomer->getId())
        );
        $view->children['opportunities']->vars['initial_elements'] = $this->getInitialOpportunities(
            $b2bcustomer->getOpportunities()
        );
    }

    /**
     * @param Collection $leads
     *
     * @return array
     */
    protected function getInitialElements(Collection $leads)
    {
        $result = array();
        /** @var Lead $lead */
        foreach ($leads as $lead) {
            $phoneNumber = $lead->getPhoneNumber();
            $email       = $lead->getEmail();
            $result[]    = array(
                'id'        => $lead->getId(),
                'label'     => $lead->getName(),
                'link'      => $this->router->generate('orocrm_sales_lead_info', array('id' => $lead->getId())),
                'extraData' => array(
                    array('label' => 'Phone', 'value' => $phoneNumber ? $phoneNumber : null),
                    array('label' => 'Email', 'value' => $email ? $email : null),
                ),
            );
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
        $result = array();
        /** @var Opportunity $opportunity */
        foreach ($opportunities as $opportunity) {
            $email    = $opportunity->getEmail();
            $result[] = array(
                'id'        => $opportunity->getId(),
                'label'     => $opportunity->getName(),
                'link'      => $this->router->generate('orocrm_sales_lead_info', array('id' => $opportunity->getId())),
                'extraData' => array(
                    array('label' => 'Email', 'value' => $email ? $email : null)

                ),
            );
        }
        return $result;
    }
}
