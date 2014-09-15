<?php

namespace OroCRM\Bundle\ChannelBundle\Tests\Unit\Form\Type;

use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormBuilder;

use OroCRM\Bundle\ChannelBundle\Form\Type\ChannelType;
use OroCRM\Bundle\ChannelBundle\Provider\SettingsProvider;
use OroCRM\Bundle\ChannelBundle\Form\EventListener\ChannelTypeSubscriber;

class ChannelTypeTest extends \PHPUnit_Framework_TestCase
{
    /** @var FormBuilder|\PHPUnit_Framework_MockObject_MockObject */
    protected $builder;

    /** @var SettingsProvider|\PHPUnit_Framework_MockObject_MockObject */
    protected $settingsProvider;

    /** @var ChannelType */
    protected $type;

    /** @var ChannelTypeSubscriber */
    protected $channelTypeSubscriber;

    public function setUp()
    {
        $this->builder          = $this->getMockBuilder('Symfony\Component\Form\FormBuilder')
            ->disableOriginalConstructor()->getMock();
        $this->settingsProvider = $this->getMockBuilder('OroCRM\Bundle\ChannelBundle\Provider\SettingsProvider')
            ->disableOriginalConstructor()->getMock();

        $this->channelTypeSubscriber = $this
            ->getMockBuilder('OroCRM\Bundle\ChannelBundle\Form\EventListener\ChannelTypeSubscriber')
            ->disableOriginalConstructor()->getMock();

        $this->settingsProvider->expects($this->any())->method('getSettings')
            ->will($this->returnValue([]));

        $this->type = new ChannelType($this->settingsProvider, $this->channelTypeSubscriber);
    }

    public function tearDown()
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
                'name'             => 'text',
                'entities'         => 'orocrm_channel_entities',
                'channelType'      => 'genemu_jqueryselect2_choice',
                'status'           => 'choice'
            ],
            $fields
        );
    }

    public function testGetName()
    {
        $this->assertEquals('orocrm_channel_form', $this->type->getName());
    }

    public function testGetParent()
    {
        $this->assertEquals('form', $this->type->getParent());
    }

    public function testSetDefaultOptions()
    {
        $resolver = $this->getMock('Symfony\Component\OptionsResolver\OptionsResolverInterface');
        $resolver->expects($this->once())
            ->method('setDefaults')
            ->with($this->isType('array'));
        $this->type->setDefaultOptions($resolver);
    }

    public function testFinishViewShouldNotFailsIfNoOwnerField()
    {
        $this->type->finishView(new FormView(), $this->getMock('Symfony\Component\Form\Test\FormInterface'), []);
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

        $this->type->finishView($mainView, $this->getMock('Symfony\Component\Form\Test\FormInterface'), []);

        if ($shouldAdd) {
            $this->assertArrayHasKey('attr', $ownerView->vars);
            $this->assertArrayHasKey('class', $ownerView->vars['attr']);
            $this->assertContains('hide', $ownerView->vars['attr']['class']);
        } else {
            $class = isset($ownerView->vars['attr'], $ownerView->vars['attr']['class'])
                ? $ownerView->vars['attr']['class'] : '';
            $this->assertNotContains('hide', $class);
        }
    }

    public function testFinishViewShouldAddHideClassAndNotOverrideOld()
    {
        $mainView                    = new FormView();
        $ownerView                   = new FormView($mainView);
        $mainView->children['owner'] = $ownerView;
        $ownerView->vars             = ['choices' => [], 'attr' => ['class' => 'testClass']];

        $this->type->finishView($mainView, $this->getMock('Symfony\Component\Form\Test\FormInterface'), []);

        $this->assertContains('hide', $ownerView->vars['attr']['class']);
        $this->assertContains('testClass', $ownerView->vars['attr']['class']);
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
