<?php

namespace Oro\Bundle\SalesBundle\Form\Type;

use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\EntityBundle\ORM\EntityAliasResolver;
use Oro\Bundle\SalesBundle\Model\CustomerAssociationInterface;
use Oro\Bundle\SalesBundle\Provider\Customer\CustomerIconProviderInterface;
use Oro\Bundle\SalesBundle\Provider\CustomerConfigProvider;
use Oro\Component\PhpUtils\ArrayUtil;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CustomerType extends AbstractType
{
    /** @var DataTransformerInterface */
    protected $transformer;

    /** @var CustomerConfigProvider */
    protected $customerConfigProvider;

    /** @var EntityAliasResolver  */
    protected $entityAliasResolver;

    /** @var CustomerIconProviderInterface */
    protected $customerIconProvider;

    /**
     * @param DataTransformerInterface $transformer
     * @param CustomerConfigProvider   $customerConfigProvider
     * @param EntityAliasResolver      $entityAliasResolver
     * @param CustomerIconProviderInterface $customerIconProvider
     */
    public function __construct(
        DataTransformerInterface $transformer,
        CustomerConfigProvider $customerConfigProvider,
        EntityAliasResolver $entityAliasResolver,
        CustomerIconProviderInterface $customerIconProvider
    ) {
        $this->transformer            = $transformer;
        $this->customerConfigProvider = $customerConfigProvider;
        $this->entityAliasResolver    = $entityAliasResolver;
        $this->customerIconProvider = $customerIconProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $customersData = $this->customerConfigProvider->getData($options['parent_class']);
        $view->vars['parentClass'] = $options['parent_class'];
        $view->vars['customersData'] = $customersData;
        $view->vars['configs']['allowCreateNew'] = ArrayUtil::some(
            function (array $customer) {
                return $customer['className'] === Account::class;
            },
            $customersData
        );
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer($this->transformer);

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
        if ($parentData instanceof CustomerAssociationInterface) {
            $parentData->setCustomerTarget($customer);
        }
    }

    /**
     * @param FormEvent $event
     */
    public function updateData(FormEvent $event)
    {
        $form   = $event->getForm();
        $parent = $form->getParent();
        if (!$parent) {
            return;
        }

        $parentData = $parent->getData();
        if (!$parentData) {
            return;
        }

        if ($parentData instanceof CustomerAssociationInterface) {
            $event->setData($parentData->getCustomerTarget());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('parent_class');
        $resolver->addAllowedTypes('parent_class', 'string');

        $resolver->setDefaults(
            [
                'mapped'  => false,
                'configs' => function (Options $options, $value) {
                    return [
                        'component'               => 'sales-customer',
                        'renderedPropertyName'    => 'text',
                        'newAccountIcon'          => $this->customerIconProvider->getIcon(new Account()),
                        'accountLabel'            => $this->customerConfigProvider->getLabel(Account::class),
                        'allowClear'              => true,
                        'placeholder'             => 'oro.sales.form.choose_customer',
                        'separator'               => ';',
                        'minimumInputLength'      => 0,
                        'route_name'              => 'oro_sales_customers_form_autocomplete_search',
                        'selection_template_twig' => 'OroSalesBundle:Autocomplete:customer/selection.html.twig',
                        'result_template_twig'    => 'OroSalesBundle:Autocomplete:customer/result.html.twig',
                        'route_parameters'        => [
                            'name'            => 'name',
                            'ownerClassAlias' => $this->entityAliasResolver->getPluralAlias($options['parent_class']),
                        ],
                    ];
                },
            ]
        );
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
