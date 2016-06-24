<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Unit\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;

use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;

use OroCRM\Bundle\SalesBundle\Form\Type\OpportunityStatusEnumValueType;
use Symfony\Component\Validator\Constraints\Range;

class OpportunityStatusEnumValueTypeTest extends \PHPUnit_Framework_TestCase
{
    public function testGetName()
    {
        $type = $this->getFormType();

        $this->assertEquals('orocrm_sales_opportunity_status_enum_value', $type->getName());
    }

    public function testBuildForm()
    {
        /** @var $builder FormBuilderInterface|\PHPUnit_Framework_MockObject_MockObject */
        $builder = $this->getMock('Symfony\Component\Form\FormBuilderInterface');

        $builder->expects($this->once())
            ->method('addEventListener')
            ->with(FormEvents::PRE_SET_DATA);

        $builder->expects($this->atLeastOnce())
            ->method('add')
            ->willReturnSelf();

        $type = $this->getFormType();
        $type->buildForm($builder, []);
    }

    /**
     * @dataProvider preSetDataProvider
     *
     * @param $enumOptionId
     * @param $shouldBeDisabled
     */
    public function testPreSetData($enumOptionId, $shouldBeDisabled)
    {
        $type = $this->getFormType();

        /** @var $form FormInterface|\PHPUnit_Framework_MockObject_MockObject */
        $form = $this->getMock('Symfony\Component\Form\FormInterface');
        $form->expects($this->once())
            ->method('add')
            ->with(
                'probability',
                'oro_percent',
                [
                    'disabled' => $shouldBeDisabled,
                    'attr' => ['readonly' => $shouldBeDisabled],
                    'constraints' => new Range(['min' => 0, 'max' => 100]),
                ]
            );
        $formEvent = new FormEvent($form, ['id' => $enumOptionId]);

        $type->preSetData($formEvent);
    }

    public function preSetDataProvider()
    {
        return [
            'default' => ['test', false],
            'win should be disabled' => ['won', true],
            'lost should be disabled' => ['lost', true],
        ];
    }

    protected function getFormType()
    {
        /** @var $configProvider ConfigProvider|\PHPUnit_Framework_MockObject_MockObject */
        $configProvider = $this->getMockBuilder(ConfigProvider::class)->disableOriginalConstructor()->getMock();

        return new OpportunityStatusEnumValueType($configProvider);
    }
}
