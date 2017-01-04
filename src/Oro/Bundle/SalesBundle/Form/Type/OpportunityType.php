<?php

namespace Oro\Bundle\SalesBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\NotBlank;

use Oro\Bundle\CurrencyBundle\Form\Type\MultiCurrencyType;
use Oro\Bundle\EntityExtendBundle\Entity\AbstractEnumValue;
use Oro\Bundle\EntityExtendBundle\Form\Util\EnumTypeHelper;
use Oro\Bundle\EntityExtendBundle\Provider\EnumValueProvider;
use Oro\Bundle\SalesBundle\Entity\Opportunity;
use Oro\Bundle\SalesBundle\Builder\OpportunityRelationsBuilder;
use Oro\Bundle\SalesBundle\Provider\ProbabilityProvider;

class OpportunityType extends AbstractType
{
    const NAME = 'oro_sales_opportunity';

    /** @var ProbabilityProvider */
    protected $probabilityProvider;

    /** @var EnumValueProvider */
    protected $enumValueProvider;

    /** @var EnumTypeHelper */
    protected $typeHelper;

    /** @var OpportunityRelationsBuilder */
    protected $relationsBuilder;

    /**
     * @param ProbabilityProvider         $probabilityProvider
     * @param EnumValueProvider           $enumValueProvider
     * @param EnumTypeHelper              $typeHelper
     * @param OpportunityRelationsBuilder $relationsBuilder
     */
    public function __construct(
        ProbabilityProvider $probabilityProvider,
        EnumValueProvider $enumValueProvider,
        EnumTypeHelper $typeHelper,
        OpportunityRelationsBuilder $relationsBuilder
    ) {
        $this->probabilityProvider = $probabilityProvider;
        $this->enumValueProvider   = $enumValueProvider;
        $this->typeHelper          = $typeHelper;
        $this->relationsBuilder    = $relationsBuilder;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'closeReason',
                'translatable_entity',
                [
                    'label'       => 'oro.sales.opportunity.close_reason.label',
                    'class'       => 'OroSalesBundle:OpportunityCloseReason',
                    'property'    => 'label',
                    'required'    => false,
                    'disabled'    => false,
                    'empty_value' => 'oro.sales.form.choose_close_rsn'
                ]
            )
            ->add(
                'contact',
                'oro_contact_select',
                [
                    'required'               => false,
                    'label'                  => 'oro.sales.opportunity.contact.label',
                    'new_item_property_name' => 'firstName',
                    'configs'                => [
                        'allowCreateNew'          => true,
                        'renderedPropertyName'    => 'fullName',
                        'placeholder'             => 'oro.contact.form.choose_contact',
                        'result_template_twig'    => 'OroFormBundle:Autocomplete:fullName/result.html.twig',
                        'selection_template_twig' => 'OroFormBundle:Autocomplete:fullName/selection.html.twig'
                    ]
                ]
            )
            ->add(
                'customerAssociation',
                'oro_sales_customer',
                [
                    'required' => true,
                    'label'    => 'oro.sales.opportunity.customer.label',
                    'parent_class' => $options['data_class'],
                    'constraints' => [new NotBlank()]
                ]
            )
            ->add('name', 'text', ['required' => true, 'label' => 'oro.sales.opportunity.name.label'])
            ->add(
                'closeDate',
                'oro_date',
                ['required' => false, 'label' => 'oro.sales.opportunity.close_date.label']
            )
            ->add(
                'probability',
                'oro_percent',
                ['required' => false, 'label' => 'oro.sales.opportunity.probability.label']
            )
            ->add(
                'budgetAmount',
                MultiCurrencyType::NAME,
                [
                    'required' => false,
                    'label' => 'oro.sales.opportunity.budget_amount.label',
                    'currency_empty_value' => false,
                    'full_currency_list' => false,
                    'attr' => ['class' => 'currency-price']
                ]
            )
            ->add(
                'closeRevenue',
                MultiCurrencyType::NAME,
                [
                    'required' => false,
                    'label' => 'oro.sales.opportunity.close_revenue.label',
                    'currency_empty_value' => false,
                    'full_currency_list' => false,
                ]
            )
            ->add(
                'customerNeed',
                'oro_resizeable_rich_text',
                ['required' => false, 'label' => 'oro.sales.opportunity.customer_need.label']
            )
            ->add(
                'proposedSolution',
                'oro_resizeable_rich_text',
                ['required' => false, 'label' => 'oro.sales.opportunity.proposed_solution.label']
            )
            ->add(
                'notes',
                'oro_resizeable_rich_text',
                ['required' => false, 'label' => 'oro.sales.opportunity.notes.label']
            )
            ->add(
                'status',
                'oro_sales_opportunity_status_select',
                [
                    'required'    => true,
                    'label'       => 'oro.sales.opportunity.status.label',
                    'enum_code'   => Opportunity::INTERNAL_STATUS_CODE,
                    'constraints' => [new NotNull()]
                ]
            );

        $this->addListeners($builder);
    }

    /**
     * Set new opportunities default probability based on default enum status value
     *
     * @param FormEvent $event
     */
    public function onFormPreSetData(FormEvent $event)
    {
        $opportunity = $event->getData();
        if (null === $opportunity) {
            return;
        }

        if ($opportunity->getId()) {
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
                'data_class' => Opportunity::class,
                'intention'  => 'opportunity'
            ]
        );
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
        return self::NAME;
    }

    /**
     * @param FormBuilderInterface $builder
     */
    protected function addListeners(FormBuilderInterface $builder)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'onFormPreSetData']);

        $builder->addEventListener(
            FormEvents::SUBMIT,
            function (FormEvent $event) {
                $this->relationsBuilder->buildAll($event->getData());
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
        $enumCode        = $this->typeHelper->getEnumCode(Opportunity::class, 'status');
        $defaultStatuses = $this->enumValueProvider->getDefaultEnumValuesByCode($enumCode);

        return reset($defaultStatuses);
    }
}
