<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\Form\Type;

use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\EntityExtendBundle\Form\Util\EnumTypeHelper;
use Oro\Bundle\EntityExtendBundle\Tests\Unit\Fixtures\TestEnumValue;
use Oro\Bundle\FormBundle\Form\Type\OroPercentType;
use Oro\Bundle\SalesBundle\Form\Type\OpportunityProbabilityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Range;

class OpportunityProbabilityTypeTest extends \PHPUnit\Framework\TestCase
{
    public function testConfigureOptions()
    {
        $resolver = new OptionsResolver();
        $type = $this->getFormType([]);

        $type->configureOptions($resolver);
        $options = $resolver->resolve();

        $this->assertArrayHasKey('validation_groups', $options);
    }

    /**
     * @dataProvider enumOptionsDataProvider
     */
    public function testBuildForm(array $enumOptions)
    {
        $type = $this->getFormType($enumOptions);

        $fields = [];
        $constraint = new Range(['min' => 0, 'max' => 100]);
        foreach ($enumOptions as $status) {
            $disabled = in_array($status->getId(), $type::$immutableProbabilityStatuses, true);
            $attr = [];
            if ($disabled) {
                $attr['readonly'] = true;
            }
            $fields[] = [
                $status->getId(),
                OroPercentType::class,
                [
                    'required' => false,
                    'disabled' => $disabled,
                    'label' => $status->getName(),
                    'attr' => $attr,
                    'constraints' => $constraint
                ]
            ];
        }

        $builder = $this->createMock(FormBuilderInterface::class);
        $builder->expects($this->exactly(count($fields)))
            ->method('add')
            ->withConsecutive(...$fields)
            ->willReturnSelf();

        $type->buildForm($builder, []);
    }

    public function enumOptionsDataProvider(): array
    {
        return [
            'default' => [
                [
                    new TestEnumValue('in_progress', 'Open'),
                    new TestEnumValue('lost', 'Lost'),
                    new TestEnumValue('win', 'Win'),
                ],
            ],
            'empty' => [
                [],
            ],
        ];
    }

    private function getFormType(array $enumOptions): OpportunityProbabilityType
    {
        $enumTypeHelper = $this->createMock(EnumTypeHelper::class);
        $repository = $this->createMock(EntityRepository::class);
        $doctrine = $this->createMock(ManagerRegistry::class);

        $enumTypeHelper->expects($this->any())
            ->method('getEnumCode')
            ->willReturn('opportunity_status');

        $doctrine->expects($this->any())
            ->method('getRepository')
            ->willReturn($repository);
        $repository->expects($this->any())
            ->method('findBy')
            ->willReturn($enumOptions);

        return new OpportunityProbabilityType($enumTypeHelper, $doctrine);
    }
}
