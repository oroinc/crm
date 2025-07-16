<?php

namespace Oro\Bundle\CaseBundle\Tests\Unit\Form\Type;

use Oro\Bundle\CaseBundle\Form\Type\CaseEntityType;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CaseEntityTypeTest extends TestCase
{
    private CaseEntityType $formType;

    #[\Override]
    protected function setUp(): void
    {
        $this->formType = new CaseEntityType();
    }

    /**
     * @dataProvider formTypeProvider
     */
    public function testBuildForm(array $widgets): void
    {
        $builder = $this->createMock(FormBuilder::class);
        $builder->expects($this->exactly(count($widgets)))
            ->method('add')
            ->withConsecutive(...array_map(
                function ($widget) {
                    return [$widget];
                },
                $widgets
            ))
            ->willReturnSelf();

        $this->formType->buildForm($builder, []);
    }

    public function formTypeProvider(): array
    {
        return [
            'all' => [
                'widgets' => [
                    'subject',
                    'description',
                    'resolution',
                    'source',
                    'status',
                    'priority',
                    'relatedContact',
                    'relatedAccount',
                    'assignedTo',
                ]
            ]
        ];
    }

    public function testConfigureOptions(): void
    {
        $resolver = $this->createMock(OptionsResolver::class);
        $resolver->expects($this->once())
            ->method('setDefaults');

        $this->formType->configureOptions($resolver);
    }
}
