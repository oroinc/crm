<?php

namespace OroCRM\Bundle\SalesBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotNull;

use Oro\Bundle\EntityExtendBundle\Entity\AbstractEnumValue;
use Oro\Bundle\EntityExtendBundle\Form\Util\EnumTypeHelper;
use Oro\Bundle\EntityExtendBundle\Provider\EnumValueProvider;

use OroCRM\Bundle\AccountBundle\Entity\Account;
use OroCRM\Bundle\SalesBundle\Entity\Opportunity;
use OroCRM\Bundle\SalesBundle\Provider\ProbabilityProvider;

class OpportunityType extends AbstractType
{
    const NAME = 'orocrm_sales_opportunity';
    const PROBABILITIES_CONFIG_KEY = 'oro_crm_sales.default_opportunity_probabilities';

    /** @var ProbabilityProvider */
    protected $probabilityProvider;

    /** @var EnumValueProvider */
    protected $enumValueProvider;

    /** @var EnumTypeHelper */
    protected $typeHelper;

    /**
     * @param ProbabilityProvider $probabilityProvider
     * @param EnumValueProvider $enumValueProvider
     * @param EnumTypeHelper $typeHelper
     */
    public function __construct(
        ProbabilityProvider $probabilityProvider,
        EnumValueProvider $enumValueProvider,
        EnumTypeHelper $typeHelper
    ) {
        $this->probabilityProvider = $probabilityProvider;
        $this->enumValueProvider = $enumValueProvider;
        $this->typeHelper = $typeHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
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
            )
            ->add(
                'contact',
                'orocrm_contact_select',
                [
                    'required'               => false,
                    'label'                  => 'orocrm.sales.opportunity.contact.label',
                    'new_item_property_name' => 'firstName',
                    'configs'                => [
                        'allowCreateNew'          => true,
                        'renderedPropertyName'    => 'fullName',
                        'placeholder'             => 'orocrm.contact.form.choose_contact',
                        'result_template_twig'    => 'OroFormBundle:Autocomplete:fullName/result.html.twig',
                        'selection_template_twig' => 'OroFormBundle:Autocomplete:fullName/selection.html.twig'
                    ],
                ]
            )
            ->add(
                'customer',
                'orocrm_sales_b2bcustomer_with_channel_select',
                [
                    'required'               => true,
                    'label'                  => 'orocrm.sales.opportunity.customer.label',
                    'new_item_property_name' => 'name',
                    'configs'                => ['allowCreateNew' => true],
                ]
            )
            ->add('name', 'text', ['required' => true, 'label' => 'orocrm.sales.opportunity.name.label'])
            ->add(
                'dataChannel',
                'orocrm_channel_select_type',
                [
                    'required' => false,
                    'label'    => 'orocrm.sales.opportunity.data_channel.label',
                    'entities' => ['OroCRM\\Bundle\\SalesBundle\\Entity\\Opportunity'],
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
                'status',
                'orocrm_sales_opportunity_status_select',
                [
                    'required'  => true,
                    'label'     => 'orocrm.sales.opportunity.status.label',
                    'enum_code' => Opportunity::INTERNAL_STATUS_CODE,
                    'constraints' => [
                        new NotNull()
                    ]
                ]
            );
        
        $this->addListeners($builder);
    }

    /**
     * Set default probability based on default enum status value
     *
     * @param FormEvent $event
     */
    public function onPreSetData(FormEvent $event)
    {
        $opportunity = $event->getData();
        if (null === $opportunity) {
            return;
        }

        if (null !== $opportunity->getProbability()) {
            return;
        }

        $status = $opportunity->getStatus();

        if (!$status) {
            $status = $this->getDefaultStatus();
        }

        if (!$status) {
            return;
        }

        $opportunity->setProbability($this->probabilityProvider->get($status));
        $event->setData($opportunity);
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

    /**
     * @param FormBuilderInterface $builder
     */
    protected function addListeners(FormBuilderInterface $builder)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'onPreSetData']);

        $builder->addEventListener(
            FormEvents::SUBMIT,
            function (FormEvent $event) {
                $opportunity = $event->getData();

                if ($opportunity instanceof Opportunity) {
                    $b2bCustomer = $opportunity->getCustomer();
                    if ($b2bCustomer && !$b2bCustomer->getDataChannel()) {
                        // new customer needs a channel
                        $b2bCustomer->setDataChannel($opportunity->getDataChannel());
                    }

                    if ($b2bCustomer && !$b2bCustomer->getAccount()) {
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
     * Return default enum value for Opportunity Status
     *
     * @return AbstractEnumValue|null Return null if there is no default status
     */
    private function getDefaultStatus()
    {
        $enumCode = $this->typeHelper->getEnumCode(Opportunity::class, 'status');
        $defaultStatuses = $this->enumValueProvider->getDefaultEnumValuesByCode($enumCode);

        return array_shift($defaultStatuses);
    }
}
