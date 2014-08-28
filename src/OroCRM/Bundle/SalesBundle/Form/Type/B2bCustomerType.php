<?php

namespace OroCRM\Bundle\SalesBundle\Form\Type;

use Doctrine\Common\Collections\Collection;

use Oro\Bundle\LocaleBundle\Formatter\NameFormatter;

use OroCRM\Bundle\SalesBundle\Entity\B2bCustomer;
use OroCRM\Bundle\SalesBundle\Entity\Lead;

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

    public function __construct(Router $router, NameFormatter $nameFormatter)
    {
        $this->router = $router;
        $this->nameFormatter = $nameFormatter;
    }

    public function getName()
    {
        return 'orocrm_sales_B2bCustomer';
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', array('required' => true, 'label' => 'orocrm.sales.b2bcustomer.name.label'))
            ->add(
                'account',
                'orocrm_account_select',
                array(
                    'required' => false,
                    'label' => 'orocrm.sales.b2bcustomer.account.label'
                )
            )
            ->add(
                'contact',
                'orocrm_contact_select',
                [
                    'label'     => 'orocrm.sales.b2bcustomer.contact.label',
                    'required'  => false,
                ]
            )->add(
                'tags',
                'oro_tag_select',
                array(
                    'label' => 'oro.tag.entity_plural_label'
                )
            )->add(
                'channel',
                'choice'
            )->add(
                'leads',
                'oro_multiple_entity',
                array(
                    'add_acl_resource'      => 'orocrm_sales_lead_view',
                    'class'                 => 'OroCRMSalesBundle:Lead',
                    'default_element'       => 'default_contact', //TODO: for remove
                    'required'              => false,
                    'selector_window_title' => 'orocrm.sales.menu.lead_list.description',
                )
            )->add(
                'opportunities',
                'oro_multiple_entity',
                array(
                    'add_acl_resource'      => 'orocrm_sales_opportunity_view',
                    'class'                 => 'OroCRMSalesBundle:Opportunity',
                    'default_element'       => 'default_contact', //TODO: for remove
                    'required'              => false,
                    'selector_window_title' => 'orocrm.sales.menu.opportunities_list.description',
                )
            );

        $builder
            ->add(
                'shippingAddress',
                'oro_address',
                array(
                    'cascade_validation' => true,
                    'required' => false
                )
            )
            ->add(
                'billingAddress',
                'oro_address',
                array(
                    'cascade_validation' => true,
                    'required' => false
                )
            );
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class'         => 'OroCRM\Bundle\SalesBundle\Entity\B2bCustomer',
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
            $view->children['leads']->vars['grid_url']
                              = $this->router->generate(
                                  'orocrm_sales_widget_leads_info',
                                  array('id' => $b2bcustomer->getId())
                              );
            $view->children['leads']->vars['initial_elements']
                              = $this->getInitialElements($b2bcustomer->getLeads());

            $view->children['opportunities']->vars['grid_url']
                    = $this->router->generate(
                        'orocrm_sales_widget_opportunities_info',
                        array('id' => $b2bcustomer->getId())
                    );
            $view->children['opportunities']->vars['initial_elements']
                = $this->getInitialOpportunities($b2bcustomer->getOpportunities());
    }

    /**
     * @param Collection $leads
     * @return array
     */
    protected function getInitialElements(Collection $leads)
    {
        $result = array();
        /** @var Lead $lead */
        foreach ($leads as $lead) {
                $phoneNumber = $lead->getPhoneNumber();
                $email = $lead->getEmail();
                $result[] = array(
                    'id' => $lead->getId(),
                    'label' => $this->nameFormatter->format($lead),
                    'link' => $this->router->generate('orocrm_sales_lead_info', array('id' => $lead->getId())),
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
     * @return array
     */
    protected function getInitialOpportunities(Collection $opportunities)
    {
        $result = array();
        /** @var Lead $lead */
        foreach ($opportunities as $oppotunity) {
            $result[] = array(
                'id' => $oppotunity->getId(),
                'label' => $this->nameFormatter->format($oppotunity),
                'link' => $this->router->generate('orocrm_sales_lead_info', array('id' => $oppotunity->getId())),
                'extraData' => array(
                ),
            );
        }
        return $result;
    }

}
