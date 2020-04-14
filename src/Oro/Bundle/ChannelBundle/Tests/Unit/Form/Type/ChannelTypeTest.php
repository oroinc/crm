<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\Form\Type;

use Oro\Bundle\ChannelBundle\Form\EventListener\ChannelTypeSubscriber;
use Oro\Bundle\ChannelBundle\Form\Type\ChannelEntityType;
use Oro\Bundle\ChannelBundle\Form\Type\ChannelType;
use Oro\Bundle\ChannelBundle\Provider\SettingsProvider;
use Oro\Bundle\FormBundle\Form\Type\Select2ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormView;

class ChannelTypeTest extends \PHPUnit\Framework\TestCase
{
    /** @var FormBuilder|\PHPUnit\Framework\MockObject\MockObject */
    protected $builder;

    /** @var SettingsProvider|\PHPUnit\Framework\MockObject\MockObject */
    protected $settingsProvider;

    /** @var ChannelType */
    protected $type;

    /** @var ChannelTypeSubscriber */
    protected $channelTypeSubscriber;

    protected function setUp(): void
    {
        $this->builder          = $this->getMockBuilder('Symfony\Component\Form\FormBuilder')
            ->disableOriginalConstructor()->getMock();
        $this->settingsProvider = $this->getMockBuilder('Oro\Bundle\ChannelBundle\Provider\SettingsProvider')
            ->disableOriginalConstructor()->getMock();

        $this->channelTypeSubscriber = $this
            ->getMockBuilder('Oro\Bundle\ChannelBundle\Form\EventListener\ChannelTypeSubscriber')
            ->disableOriginalConstructor()->getMock();

        $this->settingsProvider->expects($this->any())->method('getChannelTypeChoiceList')
            ->will($this->returnValue([]));
        $this->settingsProvider->expects($this->any())
            ->method('getChannelTypeChoiceList')
            ->willReturn([]);

        $this->type = new ChannelType($this->settingsProvider, $this->channelTypeSubscriber);
    }

    protected function tearDown(): void
    {
        unset($this->type, $this->settingsProvider, $this->builder);
    }

    public function testBuildForm()
    {
        $fields = [];

        $this->builder->expects($this->exactly(4))->method('add')
            ->will(
                $this->returnCallback(
                    function ($filedName, $fieldType) use (&$fields) {
                        $fields[$filedName] = $fieldType;
                    }
                )
            );

        $this->type->buildForm($this->builder, []);

        $this->assertSame(
            [
                'name'             => TextType::class,
                'entities'         => ChannelEntityType::class,
                'channelType'      => Select2ChoiceType::class,
                'status'           => HiddenType::class
            ],
            $fields
        );
    }

    public function testConfigureOptions()
    {
        $resolver = $this->createMock('Symfony\Component\OptionsResolver\OptionsResolver');
        $resolver->expects($this->once())
            ->method('setDefaults')
            ->with($this->isType('array'));
        $this->type->configureOptions($resolver);
    }

    public function testFinishViewShouldNotFailsIfNoOwnerField()
    {
        $this->type->finishView(new FormView(), $this->createMock('Symfony\Component\Form\Test\FormInterface'), []);
    }

    /**
     * @dataProvider choicesDataProvider
     *
     * @param array $choices
     * @param bool  $shouldAdd
     */
    public function testFinishViewShouldAddHideClassRelyOnChoices(array $choices, $shouldAdd)
    {
        $mainView                    = new FormView();
        $ownerView                   = new FormView($mainView);
        $mainView->children['owner'] = $ownerView;

        $ownerView->vars['choices'] = $choices;

        $this->type->finishView($mainView, $this->createMock('Symfony\Component\Form\Test\FormInterface'), []);

        if ($shouldAdd) {
            $this->assertArrayHasKey('attr', $ownerView->vars);
            $this->assertArrayHasKey('class', $ownerView->vars['attr']);
            static::assertStringContainsString('hide', $ownerView->vars['attr']['class']);
        } else {
            $class = isset($ownerView->vars['attr'], $ownerView->vars['attr']['class'])
                ? $ownerView->vars['attr']['class'] : '';
            $this->assertStringNotContainsString('hide', $class);
        }
    }

    public function testFinishViewShouldAddHideClassAndNotOverrideOld()
    {
        $mainView                    = new FormView();
        $ownerView                   = new FormView($mainView);
        $mainView->children['owner'] = $ownerView;
        $ownerView->vars             = ['choices' => [], 'attr' => ['class' => 'testClass']];

        $this->type->finishView($mainView, $this->createMock('Symfony\Component\Form\Test\FormInterface'), []);

        static::assertStringContainsString('hide', $ownerView->vars['attr']['class']);
        static::assertStringContainsString('testClass', $ownerView->vars['attr']['class']);
    }

    /**
     * @return array
     */
    public function choicesDataProvider()
    {
        return [
            'should hide, single choice'            => [
                '$choices'   => ['test'],
                '$shouldAdd' => true
            ],
            'multiple choices, should keep visible' => [
                '$choices'   => ['test', 'test2'],
                '$shouldAdd' => false
            ]
        ];
    }
}
