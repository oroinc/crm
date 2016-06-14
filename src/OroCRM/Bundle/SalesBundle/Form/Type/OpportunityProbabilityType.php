<?php

namespace OroCRM\Bundle\SalesBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Range;

use Oro\Bundle\EntityBundle\ORM\Registry;
use Oro\Bundle\EntityExtendBundle\Form\Util\EnumTypeHelper;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;

use OroCRM\Bundle\SalesBundle\Entity\Opportunity;

class OpportunityProbabilityType extends AbstractType
{
    const NAME = 'orocrm_sales_opportunity_probability';

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

        $this->enumStatuses = $registry->getRepository($enumValueClassName)->findBy([], ['priority' => 'DESC']);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setNormalizers(
            [
                'validation_groups' => function (Options $options, $value) {
                    return $options['disabled'] ? false : $value;
                },
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Generate a probability field for each status
        foreach ($this->enumStatuses as $status) {
            $builder
                ->add(
                    $status->getId(),
                    'percent',
                    [
                        'required' => false,
                        'label' => $status->getName(),
                        'constraints' => new Range(['min' => 0, 'max' => 100,]),
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
