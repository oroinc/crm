<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\Form\Extension;

use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\ChannelBundle\Form\Extension\SingleChannelModeExtension;
use Oro\Bundle\ChannelBundle\Form\Type\ChannelSelectType;
use Oro\Bundle\ChannelBundle\Provider\ChannelsByEntitiesProvider;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\Test\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SingleChannelModeExtensionTest extends \PHPUnit\Framework\TestCase
{
    /** @var SingleChannelModeExtension */
    private $extension;

    /** @var ChannelsByEntitiesProvider|\PHPUnit\Framework\MockObject\MockObject */
    private $channelsProvider;

    protected function setUp(): void
    {
        $this->channelsProvider = $this->createMock(ChannelsByEntitiesProvider::class);

        $this->extension = new SingleChannelModeExtension($this->channelsProvider);
    }

    public function testGetExtendedTypes()
    {
        $this->assertEquals([ChannelSelectType::class], SingleChannelModeExtension::getExtendedTypes());
    }

    /**
     * @dataProvider testBuildFormDataProvider
     * @param array $entities
     * @param array $channels
     * @param callable $callback
     */
    public function testBuildForm(array $entities, array $channels, callable $callback = null)
    {
        $builder = $this->createMock(FormBuilderInterface::class);
        $this->channelsProvider->expects($this->once())
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
     * @param array $entities
     * @param array $channels
     * @param bool $readOnly
     * @param bool $hide
     */
    public function testBuildView(array $entities, array $channels, $readOnly = false, $hide = false)
    {
        $view = new FormView();

        $form = $this->createMock(FormInterface::class);
        $options = ['entities' => $entities, 'single_channel_mode' => true];

        $view->vars['attr']['readonly'] = false;

        $this->channelsProvider->expects($this->once())
            ->method('getChannelsByEntities')
            ->with($entities)
            ->willReturn($channels);
        $this->extension->buildView($view, $form, $options);

        $this->assertEquals($readOnly, $view->vars['attr']['readonly']);
        if ($hide) {
            $this->assertEquals('hide', $view->vars['attr']['class']);
        }
    }

    public function testConfigureOptions()
    {
        $resolver = $this->createMock(OptionsResolver::class);
        $resolver->expects($this->once())
            ->method('setDefaults')
            ->with(['single_channel_mode' => true]);

        $this->extension->configureOptions($resolver);
    }

    public function testBuildFormDataProvider(): array
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

    public function testBuildViewDataProvider(): array
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
