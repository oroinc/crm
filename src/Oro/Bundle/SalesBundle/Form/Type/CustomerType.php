<?php

namespace Oro\Bundle\SalesBundle\Form\Type;

use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\EntityBundle\ORM\EntityAliasResolver;
use Oro\Bundle\SalesBundle\Provider\Customer\CustomerIconProviderInterface;
use Oro\Bundle\SalesBundle\Provider\CustomerConfigProvider;
use Oro\Component\PhpUtils\ArrayUtil;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;

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

    /** @var TranslatorInterface */
    protected $translator;

    /**
     * @param DataTransformerInterface $transformer
     * @param CustomerConfigProvider   $customerConfigProvider
     * @param EntityAliasResolver      $entityAliasResolver
     * @param CustomerIconProviderInterface $customerIconProvider
     * @param TranslatorInterface $translator
     */
    public function __construct(
        DataTransformerInterface $transformer,
        CustomerConfigProvider $customerConfigProvider,
        EntityAliasResolver $entityAliasResolver,
        CustomerIconProviderInterface $customerIconProvider,
        TranslatorInterface $translator
    ) {
        $this->transformer            = $transformer;
        $this->customerConfigProvider = $customerConfigProvider;
        $this->entityAliasResolver    = $entityAliasResolver;
        $this->customerIconProvider = $customerIconProvider;
        $this->translator = $translator;
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
                'configs' => function (Options $options, $value) {
                    return [
                        'component'               => 'sales-customer',
                        'renderedPropertyName'    => 'text',
                        'newAccountIcon'          => $this->customerIconProvider->getIcon(new Account()),
                        'accountLabel'            => $this->translator->trans(
                            $this->customerConfigProvider->getLabel(Account::class)
                        ),
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
