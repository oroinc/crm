<?php

namespace Oro\Bundle\SalesBundle\Form\Type;

use Doctrine\Common\Util\ClassUtils;
use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\DataGridBundle\Datagrid\ManagerInterface;
use Oro\Bundle\DataGridBundle\Provider\MultiGridProvider;
use Oro\Bundle\EntityBundle\ORM\EntityAliasResolver;
use Oro\Bundle\EntityBundle\Provider\EntityNameResolver;
use Oro\Bundle\FormBundle\Form\Type\Select2HiddenType;
use Oro\Bundle\SalesBundle\Autocomplete\CustomerSearchHandler;
use Oro\Bundle\SalesBundle\Entity\Customer;
use Oro\Bundle\SalesBundle\Provider\Customer\ConfigProvider;
use Oro\Bundle\SalesBundle\Provider\Customer\CustomerIconProviderInterface;
use Oro\Component\PhpUtils\ArrayUtil;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Form type for Customer entity.
 */
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

    /** @var AuthorizationCheckerInterface */
    protected $authorizationChecker;

    /** @var ManagerInterface */
    protected $gridManager;

    /** @var EntityNameResolver */
    protected $entityNameResolver;

    /** @var MultiGridProvider */
    protected $multiGridProvider;

    public function __construct(
        DataTransformerInterface $transformer,
        ConfigProvider $customerConfigProvider,
        EntityAliasResolver $entityAliasResolver,
        CustomerIconProviderInterface $customerIconProvider,
        TranslatorInterface $translator,
        AuthorizationCheckerInterface $authorizationChecker,
        ManagerInterface $gridManager,
        EntityNameResolver $entityNameResolver,
        MultiGridProvider $multiGridProvider
    ) {
        $this->transformer = $transformer;
        $this->customerConfigProvider = $customerConfigProvider;
        $this->entityAliasResolver = $entityAliasResolver;
        $this->customerIconProvider = $customerIconProvider;
        $this->translator = $translator;
        $this->authorizationChecker = $authorizationChecker;
        $this->gridManager = $gridManager;
        $this->entityNameResolver = $entityNameResolver;
        $this->multiGridProvider = $multiGridProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars = array_merge(
            $view->vars,
            $this->getCreateCustomersData()
        );

        $view->vars['parentClass']         = $options['parent_class'];
        $view->vars['configs']['allowCreateNew'] = ArrayUtil::some(
            function (array $customer) {
                return $customer['className'] === Account::class;
            },
            $view->vars['createCustomersData']
        );

        if ($form->getData()) {
            $view->vars['attr']['data-selected-data'] = $this->getSelectedData($form);
        }
    }

    protected function getSelectedData(FormInterface $form)
    {
        /** @var Customer $target */
        $customer = $form->getData();

        $target = $customer->getTarget();
        $className = ClassUtils::getClass($target);
        $identifier = $target->getId();

        $item = [
            'id'   => json_encode(
                [
                    'entityClass' => $className,
                    'entityId'    => $identifier,
                ]
            )
            ,
            'text' => $this->entityNameResolver->getName($target),
            'icon' => $this->customerIconProvider->getIcon($target),
        ];

        return json_encode([$item]);
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
                    $accountLabel = (string) $this->customerConfigProvider->getLabel(Account::class);

                    return [
                        'component'               => 'sales-customer',
                        'renderedPropertyName'    => 'text',
                        'newAccountIcon'          => $this->customerIconProvider->getIcon(new Account()),
                        'accountLabel'            => $this->translator->trans($accountLabel),
                        'allowClear'              => true,
                        'placeholder'             => 'oro.sales.form.choose_account',
                        'separator'               => ';',
                        'minimumInputLength'      => 1,
                        'per_page'                => CustomerSearchHandler::AMOUNT_SEARCH_RESULT,
                        'route_name'              => 'oro_sales_customers_form_autocomplete_search',
                        'dropdownCssClass'        => 'sales-account-autocomplete',
                        'selection_template_twig' => '@OroSales/Autocomplete/customer/selection.html.twig',
                        'result_template_twig'    => '@OroSales/Autocomplete/customer/result.html.twig',
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
        return Select2HiddenType::class;
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

    /**
     * @return array
     * [
     *     hasGridData => bool,
     *     createCustomersData => [
     *         [
     *             className   => customer class,
     *             label       => entity label,
     *             icon        => entity icon,
     *             routeCreate => route to create entity
     *         ],
     *         ...
     *     ],
     * ]
     */
    protected function getCreateCustomersData()
    {
        $customerClasses = $this->customerConfigProvider->getCustomerClasses();

        $result = [
            'createCustomersData' => [],
            'hasGridData' => (bool) $this->multiGridProvider->getEntitiesData($customerClasses),
        ];

        foreach ($customerClasses as $class) {
            if (!$this->authorizationChecker->isGranted('CREATE', sprintf('entity:%s', $class))) {
                continue;
            }

            $routeCreate = $this->customerConfigProvider->getRouteCreate($class);
            $result['createCustomersData'][] = [
                'className'       => $class,
                'label'           => $this->customerConfigProvider->getLabel($class),
                'icon'            => $this->customerConfigProvider->getIcon($class),
                'routeCreate'     => $routeCreate,
            ];
        }

        return $result;
    }
}
