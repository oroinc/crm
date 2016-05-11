<?php

namespace OroCRM\Bundle\ChannelBundle\Tests\Unit\Form\Extension;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormView;

use OroCRM\Bundle\ChannelBundle\Entity\Channel;
use OroCRM\Bundle\ChannelBundle\Form\Type\ChannelSelectType;
use OroCRM\Bundle\ChannelBundle\Form\Extension\SingleChannelModeExtension;
use OroCRM\Bundle\ChannelBundle\Provider\ChannelsByEntitiesProvider;

class SingleChannelModeExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SingleChannelModeExtension
     */
    protected $extension;

    /**
     * @var ChannelsByEntitiesProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $channelsProvider;

    protected function setUp()
    {
        $this->channelsProvider = $this
            ->getMockBuilder('OroCRM\Bundle\ChannelBundle\Provider\ChannelsByEntitiesProvider')
            ->disableOriginalConstructor()
            ->getMock();

        $this->extension = new SingleChannelModeExtension($this->channelsProvider);
    }


    public function testGetExtendedType()
    {
        $this->assertEquals(
            $this->extension->getExtendedType(),
            ChannelSelectType::NAME
        );
    }

    /**
     * @dataProvider testBuildFormDataProvider
     */
    public function testBuildForm(array $entities, array $channels, callable $callback = null)
    {
        $builder = $this->getMock('Symfony\Component\Form\Test\FormBuilderInterface');
        $this->channelsProvider
            ->expects($this->once())
            ->method('getChannelsByEntities')
            ->with($entities)
            ->willReturn($channels);
        if (count($channels) === 1) {
            $builder->expects($this->once())
                ->method('addEventListener')
                ->with(FormEvents::PRE_SET_DATA, $callback);
        }

        $this->extension->buildForm($builder, ['entities' => $entities, 'single_channel_mode' => true]);

    }

    /**
     * @dataProvider testBuildViewDataProvider
     */
    public function testBuildView(array $entities, array $channels, $readOnly = false, $hide = false)
    {
        $view = new FormView();

        $form = $this->getMock('Symfony\Component\Form\FormInterface');
        $options = ['entities' => $entities, 'single_channel_mode' => true];

        $view->vars['read_only'] = false;

        $this->channelsProvider
            ->expects($this->once())
            ->method('getChannelsByEntities')
            ->with($entities)
            ->willReturn($channels);
        $this->extension->buildView($view, $form, $options);

        $this->assertEquals($readOnly, $view->vars['read_only']);
        if ($hide) {
            $this->assertEquals('hide', $view->vars['attr']['class']);
        }
    }

    public function testSetDefaultOptions()
    {
        $resolver = $this->getMock('Symfony\Component\OptionsResolver\OptionsResolverInterface');
        $resolver->expects($this->once())
            ->method('setDefaults')
            ->with(['single_channel_mode' => true]);

        $this->extension->setDefaultOptions($resolver);
    }

    public function testBuildFormDataProvider()
    {
        $channel = new Channel();

        return [
            'one channel' => [
                ['Entity1'],
                [$channel],
                function (FormEvent $event) use ($channel) {
                    $event->setData($channel);
                }
            ],
            'more channels' => [
                ['Entity1'],
                [$channel, new Channel()]
            ]
        ];
    }

    public function testBuildViewDataProvider()
    {
        return [
            'one channel' => [
                ['Entity1'],
                [new Channel()],
                true,
                true
            ],
            'more channels' => [
                ['Entity1'],
                [new Channel(), new Channel()]
            ]
        ];
    }
}
