<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\Form\Type;

use Oro\Bundle\FormBundle\Form\Type\OroPercentType;
use Oro\Bundle\SalesBundle\Form\Type\OpportunityStatusEnumValueType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Constraints\Range;

class OpportunityStatusEnumValueTypeTest extends \PHPUnit\Framework\TestCase
{
    /** @var OpportunityStatusEnumValueType */
    private $type;

    protected function setUp(): void
    {
        $this->type = new OpportunityStatusEnumValueType();
    }

    public function testBuildForm()
    {
        $builder = $this->createMock(FormBuilderInterface::class);

        $this->type->buildForm($builder, ['allow_multiple_selection' => false]);
    }

    /**
     * @dataProvider preSetDataProvider
     */
    public function testPreSetData(string $enumOptionId, bool $shouldBeDisabled)
    {
        $form = $this->createMock(FormInterface::class);
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

        $this->type->preSetData($formEvent);
    }

    public function preSetDataProvider(): array
    {
        return [
            'default' => ['test', false],
            'win should be disabled' => ['won', true],
            'lost should be disabled' => ['lost', true],
        ];
    }
}
