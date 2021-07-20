<?php

namespace Oro\Bundle\SalesBundle\Form\Type;

use Oro\Bundle\EntityBundle\ORM\Registry;
use Oro\Bundle\EntityExtendBundle\Form\Util\EnumTypeHelper;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\FormBundle\Form\Type\OroPercentType;
use Oro\Bundle\SalesBundle\Entity\Opportunity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Range;

class OpportunityProbabilityType extends AbstractType
{
    const NAME = 'oro_sales_opportunity_probability';

    /**
     * @var array List of statuses which have non-editable probability
     */
    public static $immutableProbabilityStatuses = ['won', 'lost'];

    /** @var EnumTypeHelper */
    protected $typeHelper;

    /** @var array */
    private $enumStatuses;

    public function __construct(EnumTypeHelper $typeHelper, Registry $registry)
    {
        $enumCode = $typeHelper->getEnumCode(Opportunity::class, 'status');
        $enumValueClassName = ExtendHelper::buildEnumValueClassName($enumCode);

        $this->enumStatuses = $registry->getRepository($enumValueClassName)->findBy([], ['priority' => 'ASC']);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'validation_groups' => null,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $constraint = new Range(['min' => 0, 'max' => 100]);
        // Generate a probability field for each status
        foreach ($this->enumStatuses as $status) {
            $disabled = in_array($status->getId(), self::$immutableProbabilityStatuses);

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
}
