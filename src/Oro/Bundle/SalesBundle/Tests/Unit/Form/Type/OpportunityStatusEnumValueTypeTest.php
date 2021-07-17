<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\Form\Type;

use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;
use Oro\Bundle\FormBundle\Form\Type\OroPercentType;
use Oro\Bundle\SalesBundle\Form\Type\OpportunityStatusEnumValueType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Constraints\Range;

class OpportunityStatusEnumValueTypeTest extends \PHPUnit\Framework\TestCase
{
    public function testBuildForm()
    {
        /** @var $builder FormBuilderInterface|\PHPUnit\Framework\MockObject\MockObject */
        $builder = $this->createMock('Symfony\Component\Form\FormBuilderInterface');

        $type = $this->getFormType();
        $type->buildForm($builder, ['allow_multiple_selection' => false]);
    }

    /**
     * @dataProvider preSetDataProvider
     */
    public function testPreSetData($enumOptionId, $shouldBeDisabled)
    {
        $type = $this->getFormType();

        /** @var $form FormInterface|\PHPUnit\Framework\MockObject\MockObject */
        $form = $this->createMock('Symfony\Component\Form\FormInterface');
        $attr = [];

        if ($shouldBeDisabled) {
            $attr['readonly'] = true;
        }

        $form->expects($this->once())
            ->method('add')
            ->with(
                'probability',
                OroPercentType::class,
                [
                    'disabled' => $shouldBeDisabled,
                    'attr' => $attr,
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
        /** @var $configProvider ConfigProvider|\PHPUnit\Framework\MockObject\MockObject */
        $configProvider = $this->getMockBuilder(ConfigProvider::class)->disableOriginalConstructor()->getMock();

        return new OpportunityStatusEnumValueType($configProvider);
    }
}
