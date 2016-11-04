<?php

namespace Oro\Bundle\SalesBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Oro\Bundle\SalesBundle\Manager\CustomerManager;
use Oro\Bundle\SalesBundle\Provider\CustomerConfigProvider;

class CustomerType extends AbstractType
{
    /** @var CustomerManager */
    protected $customerManager;

    /** @var DataTransformerInterface */
    protected $customerToStringTransformer;

    /** @var CustomerConfigProvider */
    protected $customerConfigProvider;

    /**
     * @param CustomerManager          $customerManager
     * @param DataTransformerInterface $customerToStringTransformer
     * @param CustomerConfigProvider   $customerConfigProvider
     */
    public function __construct(
        CustomerManager $customerManager,
        DataTransformerInterface $customerToStringTransformer,
        CustomerConfigProvider $customerConfigProvider
    ) {
        $this->customerManager  = $customerManager;
        $this->customerToStringTransformer = $customerToStringTransformer;
        $this->customerConfigProvider      = $customerConfigProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['parentClass'] = $options['parent_class'];
        $view->vars['customersData'] = $this->customerConfigProvider->getCustomersData($options['parent_class']);
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer($this->customerToStringTransformer);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'updateData']);
        // needs to be called before validation
        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'updateCustomer'], 10);
    }

    /**
     * @param FormEvent $event
     */
    public function updateCustomer(FormEvent $event)
    {
        $form = $event->getForm();
        if (!$form->getParent()) {
            return;
        }

        $parentData = $form->getParent()->getData();
        if (!$parentData) {
            return;
        }

        $customer = $event->getForm()->getData();
        $this->customerManager->setCustomer($parentData, $customer);
    }

    /**
     * @param FormEvent $event
     */
    public function updateData(FormEvent $event)
    {
        $form = $event->getForm();
        $parent = $form->getParent();
        if (!$parent) {
            return;
        }

        $parentData = $parent->getData();
        if (!$parentData) {
            return;
        }

        $event->setData($this->customerManager->getCustomer($parentData));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('parent_class');
        $resolver->addAllowedTypes('parent_class', 'string');

        $resolver->setDefaults([
            'mapped'  => false,
            'configs' => [
                'allowClear'         => true,
                'placeholder'        => 'oro.sales.form.choose_customer',
                'separator'          => ';',
                'minimumInputLength' => 0,
                'route_name'         => 'oro_sales_autocomplete_customers',
                'selection_template_twig' => 'OroSalesBundle:Autocomplete:customer/selection.html.twig',
                'result_template_twig'    => 'OroSalesBundle:Autocomplete:customer/result.html.twig',
                'route_parameters'   => [
                    'name' => 'name',
                ],
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'genemu_jqueryselect2_hidden';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_sales_customer';
    }
}
