<?php

namespace Oro\Bundle\CaseBundle\Tests\Unit\Form\Type;

use Oro\Bundle\CaseBundle\Form\Type\CaseEntityType;

class CaseEntityTypeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CaseEntityType
     */
    protected $formType;

    protected function setUp(): void
    {
        $this->formType = new CaseEntityType();
    }

    /**
     * @dataProvider formTypeProvider
     */
    public function testBuildForm(array $widgets)
    {
        $builder = $this->getMockBuilder('Symfony\Component\Form\FormBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $builder->expects($this->exactly(sizeof($widgets)))
            ->method('add')
            ->will($this->returnSelf());

        foreach ($widgets as $key => $widget) {
            $builder->expects($this->at($key))
                ->method('add')
                ->with($this->equalTo($widget))
                ->will($this->returnSelf());
        }

        $this->formType->buildForm($builder, []);
    }

    public function formTypeProvider()
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
        $resolver = $this->createMock('Symfony\Component\OptionsResolver\OptionsResolver');

        $resolver
            ->expects($this->once())
            ->method('setDefaults');

        $this->formType->configureOptions($resolver);
    }
}
