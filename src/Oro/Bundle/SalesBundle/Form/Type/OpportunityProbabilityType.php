<?php

namespace Oro\Bundle\SalesBundle\Form\Type;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\EntityExtendBundle\Form\Util\EnumTypeHelper;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\FormBundle\Form\Type\OroPercentType;
use Oro\Bundle\SalesBundle\Entity\Opportunity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Range;

/**
 * The form type for an opportunity probability.
 */
class OpportunityProbabilityType extends AbstractType
{
    const NAME = 'oro_sales_opportunity_probability';

    /** @var array List of statuses which have non-editable probability */
    public static $immutableProbabilityStatuses = ['won', 'lost'];

    private EnumTypeHelper $typeHelper;
    private ManagerRegistry $doctrine;

    public function __construct(EnumTypeHelper $typeHelper, ManagerRegistry $doctrine)
    {
        $this->typeHelper = $typeHelper;
        $this->doctrine = $doctrine;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'validation_groups' => null
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $constraint = new Range(['min' => 0, 'max' => 100]);
        // Generate a probability field for each status
        $enumStatuses = $this->getEnumStatuses();
        foreach ($enumStatuses as $status) {
            $disabled = \in_array($status->getId(), self::$immutableProbabilityStatuses, true);

            $attr = [];
            if ($disabled) {
                $attr['readonly'] = true;
            }

            $builder
                ->add(
                    $status->getId(),
                    OroPercentType::class,
                    [
                        'required' => false,
                        'disabled' => $disabled,
                        'label' => $status->getName(),
                        'attr' => $attr,
                        'constraints' => $constraint,
                    ]
                );
        }
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

    private function getEnumStatuses(): array
    {
        $enumValueClassName = ExtendHelper::buildEnumValueClassName(
            $this->typeHelper->getEnumCode(Opportunity::class, 'status')
        );

        return $this->doctrine->getRepository($enumValueClassName)
            ->findBy([], ['priority' => 'ASC']);
    }
}
