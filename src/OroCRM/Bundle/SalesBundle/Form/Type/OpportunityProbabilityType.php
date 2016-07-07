<?php

namespace OroCRM\Bundle\SalesBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Range;

use Oro\Bundle\EntityBundle\ORM\Registry;
use Oro\Bundle\EntityExtendBundle\Form\Util\EnumTypeHelper;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;

use OroCRM\Bundle\SalesBundle\Entity\Opportunity;

class OpportunityProbabilityType extends AbstractType
{
    const NAME = 'orocrm_sales_opportunity_probability';

    /**
     * @var array List of statuses which have non-editable probability
     */
    public static $immutableStatuses = ['won', 'lost'];

    /** @var EnumTypeHelper */
    protected $typeHelper;

    /** @var array */
    private $enumStatuses;

    /**
     * @param EnumTypeHelper $typeHelper
     * @param Registry $registry
     */
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
            $disabled = in_array($status->getId(), self::$immutableStatuses);

            $builder
                ->add(
                    $status->getId(),
                    'oro_percent',
                    [
                        'required' => false,
                        'disabled' => $disabled,
                        'label' => $status->getName(),
                        'attr' => ['readonly' => $disabled],
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
        return self::NAME;
    }
}
