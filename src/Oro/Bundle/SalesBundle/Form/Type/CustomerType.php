<?php

namespace Oro\Bundle\SalesBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;

use Oro\Component\PhpUtils\ArrayUtil;

use Oro\Bundle\DataGridBundle\Datagrid\ManagerInterface;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Oro\Bundle\EntityBundle\ORM\EntityAliasResolver;
use Oro\Bundle\SalesBundle\Provider\Customer\CustomerIconProviderInterface;
use Oro\Bundle\SalesBundle\Provider\Customer\ConfigProvider;
use Oro\Bundle\AccountBundle\Entity\Account;

class CustomerType extends AbstractType
{
    /** @var DataTransformerInterface */
    protected $transformer;

    /** @var ConfigProvider */
    protected $customerConfigProvider;

    /** @var EntityAliasResolver */
    protected $entityAliasResolver;

    /** @var CustomerIconProviderInterface */
    protected $customerIconProvider;

    /** @var TranslatorInterface */
    protected $translator;

    /** @var SecurityFacade */
    protected $securityFacade;

    /** @var ManagerInterface */
    protected $gridManager;

    /**
     * @param DataTransformerInterface      $transformer
     * @param ConfigProvider                $customerConfigProvider
     * @param EntityAliasResolver           $entityAliasResolver
     * @param CustomerIconProviderInterface $customerIconProvider
     * @param TranslatorInterface           $translator
     * @param SecurityFacade                $securityFacade
     * @param ManagerInterface              $gridManager
     */
    public function __construct(
        DataTransformerInterface $transformer,
        ConfigProvider $customerConfigProvider,
        EntityAliasResolver $entityAliasResolver,
        CustomerIconProviderInterface $customerIconProvider,
        TranslatorInterface $translator,
        SecurityFacade $securityFacade,
        ManagerInterface $gridManager
    ) {
        $this->transformer            = $transformer;
        $this->customerConfigProvider = $customerConfigProvider;
        $this->entityAliasResolver    = $entityAliasResolver;
        $this->customerIconProvider   = $customerIconProvider;
        $this->translator             = $translator;
        $this->securityFacade         = $securityFacade;
        $this->gridManager            = $gridManager;
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $customersData       = $this->customerConfigProvider->getCustomersData();
        $hasGridData         = false;
        $createCustomersData = [];
        foreach ($customersData as $customer) {
            if ($customer['gridName'] &&
                $this->securityFacade->isGranted($this->getGridAclResource($customer['gridName']))
            ) {
                $hasGridData = true;
                unset($customer['gridName']);
            }
            if ($this->securityFacade->isGranted($customer['routeCreate'])) {
                $createCustomersData[] = $customer;
            }
        }

        $view->vars['parentClass']         = $options['parent_class'];
        $view->vars['hasGridData']         = $hasGridData;
        $view->vars['createCustomersData'] = $createCustomersData;


        $view->vars['configs']['allowCreateNew'] = ArrayUtil::some(
            function (array $customer) {
                return $customer['className'] === Account::class;
            },
            $view->vars['createCustomersData']
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
                        'placeholder'             => 'oro.sales.form.choose_account',
                        'separator'               => ';',
                        'minimumInputLength'      => 1,
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

    /**
     * @param string $gridName
     *
     * @return bool
     */
    protected function getGridAclResource($gridName)
    {
        $gridConfig = $this->gridManager->getConfigurationForGrid($gridName);

        return $gridConfig ? $gridConfig->getAclResource() : null;
    }
}
