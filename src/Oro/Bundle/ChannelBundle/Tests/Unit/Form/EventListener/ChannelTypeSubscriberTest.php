<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\Form\EventListener;

use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\ChannelBundle\Form\EventListener\ChannelTypeSubscriber;
use Oro\Bundle\ChannelBundle\Form\Type\ChannelType;
use Oro\Bundle\ChannelBundle\Provider\SettingsProvider;
use Oro\Bundle\EntityBundle\Provider\EntityProvider;
use Oro\Component\Testing\Unit\PreloadedExtension;
use Symfony\Component\Form\FormConfigInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\Test\FormIntegrationTestCase;
use Symfony\Component\Form\Test\FormInterface;

class ChannelTypeSubscriberTest extends FormIntegrationTestCase
{
    private const TEST_CHANNEL_TYPE = 'test_type';
    private const TEST_CUSTOMER_IDENTITY = 'Oro\Bundle\AcmeBundle\Entity\Test1';

    /** @var SettingsProvider|\PHPUnit\Framework\MockObject\MockObject */
    private $settingsProvider;

    /** @var ChannelTypeSubscriber */
    private $subscriber;

    protected function setUp(): void
    {
        $this->settingsProvider = $this->createMock(SettingsProvider::class);

        $this->settingsProvider->expects($this->any())
            ->method('getEntitiesByChannelType')
            ->willReturn(
                [
                    'Oro\Bundle\AcmeBundle\Entity\Test1',
                    'Oro\Bundle\AcmeBundle\Entity\Test2'
                ]
            );

        $this->settingsProvider->expects($this->any())
            ->method('getCustomerIdentityFromConfig')
            ->with(self::TEST_CHANNEL_TYPE)
            ->willReturn(self::TEST_CUSTOMER_IDENTITY);

        $this->settingsProvider->expects($this->any())
            ->method('getNonSystemChannelTypeChoiceList')
            ->willReturn([]);

        $this->subscriber = new ChannelTypeSubscriber($this->settingsProvider);
        parent::setUp();
    }

    /**
     * @dataProvider formDataProviderForPreSet
     */
    public function testPreSet(?Channel $formData, string $channelType)
    {
        $events = $this->subscriber->getSubscribedEvents();
        $this->assertArrayHasKey(FormEvents::PRE_SET_DATA, $events);
        $this->assertEquals($events[FormEvents::PRE_SET_DATA], 'preSet');

        $form = $this->createMock(FormInterface::class);
        $fieldMock = $this->createMock(FormInterface::class);
        $configMock = $this->createMock(FormConfigInterface::class);

        if ($formData) {
            $form->expects($this->any())
                ->method('get')
                ->willReturn($fieldMock);

            $fieldMock->expects($this->any())
                ->method('getConfig')
                ->willReturn($configMock);

            $formData->expects($this->exactly(4))
                ->method('getChannelType')
                ->willReturn($channelType);

            $this->settingsProvider->expects($this->once())
                ->method('getIntegrationType')
                ->willReturn($channelType);
        }

        $event = new FormEvent($form, $formData);
        $event->setData($formData);

        $this->subscriber->preSet($event);
    }

    public function formDataProviderForPreSet(): array
    {
        $channelUpdate = $this->createMock(Channel::class);
        $channelUpdate->expects($this->any())
            ->method('getId')
            ->willReturn(1);
        $channel = $this->createMock(Channel::class);

        return [
            'without data' => [
                null,
                self::TEST_CHANNEL_TYPE
            ],
            'with data'    => [
                $channel,
                self::TEST_CHANNEL_TYPE
            ]
        ];
    }

    public function testPreSubmit()
    {
        $data = [
            'customerIdentity' => 'Oro\Bundle\AcmeBundle\Entity\Test1',
            'entities'         => [
                'Oro\Bundle\AcmeBundle\Entity\Test1',
                'Oro\Bundle\AcmeBundle\Entity\Test2'
            ],
        ];

        $form  = $this->createMock(FormInterface::class);
        $event = new FormEvent($form, $data);
        $this->subscriber->preSubmit($event);
    }

    public function testGetSubscribedEvents()
    {
        $events = ChannelTypeSubscriber::getSubscribedEvents();
        $this->assertArrayHasKey(FormEvents::PRE_SET_DATA, $events);
        $this->assertArrayHasKey(FormEvents::PRE_SUBMIT, $events);
    }

    public function testPostSubmit()
    {
        $data = new Channel();
        $data->setChannelType(self::TEST_CHANNEL_TYPE);

        $form = $this->createMock(FormInterface::class);

        $event = new FormEvent($form, $data);
        $this->subscriber->postSubmit($event);

        $this->assertEquals(
            self::TEST_CUSTOMER_IDENTITY,
            $data->getCustomerIdentity()
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function getExtensions(): array
    {
        $channelType = new ChannelType($this->settingsProvider, $this->subscriber);
        $provider = $this->createMock(EntityProvider::class);
        $provider->expects($this->any())
            ->method('getEntities')
            ->willReturn(
                [
                    [
                        'name'         => 'name',
                        'label'        => 'label',
                        'plural_label' => 'plural_label',
                        'icon'         => 'icon'
                    ]
                ]
            );

        return [
            new PreloadedExtension(
                [
                    ChannelType::class => $channelType
                ],
                []
            )
        ];
    }
}
