<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Unit\Form\Type;

use Doctrine\Common\Persistence\ObjectRepository;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Oro\Bundle\EntityBundle\ORM\Registry;
use Oro\Bundle\EntityExtendBundle\Form\Util\EnumTypeHelper;
use Oro\Bundle\EntityExtendBundle\Tests\Unit\Fixtures\TestEnumValue;

use OroCRM\Bundle\SalesBundle\Form\Type\OpportunityProbabilityType;
use Symfony\Component\Validator\Constraints\Range;

class OpportunityProbabilityTypeTest extends \PHPUnit_Framework_TestCase
{
    public function testConfigureOptions()
    {
        $resolver = new OptionsResolver();
        $type = $this->getFormType([]);

        $type->configureOptions($resolver);
        $options = $resolver->resolve();

        $this->assertArrayHasKey('validation_groups', $options);
        $this->assertArrayHasKey('disabled', $options);
    }

    public function testGetName()
    {
        $type = $this->getFormType([]);

        $this->assertEquals('orocrm_sales_opportunity_probability', $type->getName());
    }

    /**
     * @dataProvider enumOptionsDataProvider
     *
     * @param array $enumOptions
     */
    public function testBuildForm(array $enumOptions)
    {
        /** @var $builder FormBuilderInterface|\PHPUnit_Framework_MockObject_MockObject */
        $builder = $this->getMock('Symfony\Component\Form\FormBuilderInterface');

        $builder->expects($this->exactly(count($enumOptions)))
            ->method('add');

        $type = $this->getFormType($enumOptions);

        $constraint = new Range(['min' => 0, 'max' => 100]);
        $counter = 0;
        foreach ($enumOptions as $status) {
            $disabled = in_array($status->getId(), $type::$immutableStatuses);
            $builder->expects($this->at($counter))
                ->method('add')
                ->with(
                    $status->getId(),
                    'oro_percent',
                    [
                        'required' => false,
                        'disabled' => $disabled,
                        'label' => $status->getName(),
                        'attr' => ['readonly' => $disabled],
                        'constraints' => $constraint,
                    ]
                )
                ->willReturnSelf();
            $counter++;
        }

        $type->buildForm($builder, []);
    }

    /**
     * @return array
     */
    public function enumOptionsDataProvider()
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

    /**
     * @param array $enumOptions
     *
     * @return OpportunityProbabilityType
     */
    protected function getFormType(array $enumOptions)
    {
        /** @var $enumTypeHelper EnumTypeHelper|\PHPUnit_Framework_MockObject_MockObject */
        $enumTypeHelper = $this->getMockBuilder(EnumTypeHelper::class)->disableOriginalConstructor()->getMock();
        /** @var $objectRepository ObjectRepository|\PHPUnit_Framework_MockObject_MockObject */
        $objectRepository = $this->getMockBuilder(ObjectRepository::class)->disableOriginalConstructor()->getMock();
        /** @var $registry Registry|\PHPUnit_Framework_MockObject_MockObject */
        $registry = $this->getMockBuilder(Registry::class)->disableOriginalConstructor()->getMock();

        $enumTypeHelper->expects($this->once())
            ->method('getEnumCode')
            ->will($this->returnValue('opportunity_status'));

        $objectRepository->expects($this->once())
            ->method('findBy')
            ->willReturn($enumOptions);

        $registry->expects($this->any())
            ->method('getRepository')
            ->willReturn($objectRepository);

        return new OpportunityProbabilityType($enumTypeHelper, $registry);
    }
}
