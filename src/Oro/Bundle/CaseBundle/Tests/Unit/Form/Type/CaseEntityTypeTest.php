<?php

namespace Oro\Bundle\CaseBundle\Tests\Unit\Form\Type;

use Oro\Bundle\CaseBundle\Form\Type\CaseEntityType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CaseEntityTypeTest extends \PHPUnit\Framework\TestCase
{
    /** @var CaseEntityType */
    private $formType;

    protected function setUp(): void
    {
        $this->formType = new CaseEntityType();
    }

    /**
     * @dataProvider formTypeProvider
     */
    public function testBuildForm(array $widgets)
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

    public function testConfigureOptions()
    {
        $resolver = $this->createMock(OptionsResolver::class);
        $resolver->expects($this->once())
            ->method('setDefaults');

        $this->formType->configureOptions($resolver);
    }
}
