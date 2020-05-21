<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\Form\EventListener;

use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\ChannelBundle\Form\EventListener\ChannelTypeSubscriber;
use Oro\Bundle\ChannelBundle\Form\Type\ChannelType;
use Oro\Bundle\ChannelBundle\Provider\SettingsProvider;
use Oro\Component\Testing\Unit\PreloadedExtension;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\Test\FormIntegrationTestCase;

class ChannelTypeSubscriberTest extends FormIntegrationTestCase
{
    const TEST_CHANNEL_TYPE = 'test_type';
    const TEST_CUSTOMER_IDENTITY = 'Oro\Bundle\AcmeBundle\Entity\Test1';

    /** @var SettingsProvider|\PHPUnit\Framework\MockObject\MockObject */
    protected $settingsProvider;

    /** @var ChannelTypeSubscriber */
    protected $subscriber;

    protected function setUp(): void
    {
        $this->settingsProvider = $this->getMockBuilder('Oro\Bundle\ChannelBundle\Provider\SettingsProvider')
            ->disableOriginalConstructor()->getMock();

        $this->settingsProvider->expects($this->any())
            ->method('getEntitiesByChannelType')
            ->will(
                $this->returnValue(
                    [
                        'Oro\Bundle\AcmeBundle\Entity\Test1',
                        'Oro\Bundle\AcmeBundle\Entity\Test2'
                    ]
                )
            );

        $this->settingsProvider->expects($this->any())
            ->method('getCustomerIdentityFromConfig')
            ->with(self::TEST_CHANNEL_TYPE)
            ->will(
                $this->returnValue(self::TEST_CUSTOMER_IDENTITY)
            );

        $this->settingsProvider->expects($this->any())
            ->method('getNonSystemChannelTypeChoiceList')
            ->willReturn([]);

        $this->subscriber = new ChannelTypeSubscriber($this->settingsProvider);
        parent::setUp();
    }

    protected function tearDown(): void
    {
        unset($this->subscriber, $this->settingsProvider);
    }

    /**
     * @dataProvider formDataProviderForPreSet
     *
     * @param Channel|null $formData
     * @param string       $channelType
     */
    public function testPreSet($formData, $channelType)
    {
        $events = $this->subscriber->getSubscribedEvents();
        $this->assertArrayHasKey(FormEvents::PRE_SET_DATA, $events);
        $this->assertEquals($events[FormEvents::PRE_SET_DATA], 'preSet');

        $form       = $this->createMock('Symfony\Component\Form\Test\FormInterface');
        $fieldMock  = $this->createMock('Symfony\Component\Form\Test\FormInterface');
        $configMock = $this->createMock('Symfony\Component\Form\FormConfigInterface');

        if ($formData) {
            $form->expects($this->any())
                ->method('get')
                ->will($this->returnValue($fieldMock));

            $fieldMock->expects($this->any())
                ->method('getConfig')
                ->will($this->returnValue($configMock));

            $formData->expects($this->exactly(4))
                ->method('getChannelType')
                ->will($this->returnValue($channelType));

            $this->settingsProvider
                ->expects($this->once())
                ->method('getIntegrationType')
                ->will($this->returnValue($channelType));
        }

        $event = new FormEvent($form, $formData);
        $event->setData($formData);

        $this->subscriber->preSet($event);
    }

    /**
     * @return array
     */
    public function formDataProviderForPreSet()
    {
        $channelUpdate = $this->createMock('Oro\Bundle\ChannelBundle\Entity\Channel');
        $channelUpdate->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(1));
        $channel = $this->createMock('Oro\Bundle\ChannelBundle\Entity\Channel');

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

        $form  = $this->createMock('Symfony\Component\Form\Test\FormInterface');
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

        $form = $this->createMock('Symfony\Component\Form\Test\FormInterface');

        $event = new FormEvent($form, $data);
        $this->subscriber->postSubmit($event);

        $this->assertEquals(
            self::TEST_CUSTOMER_IDENTITY,
            $data->getCustomerIdentity()
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getExtensions()
    {
        $channelType = new ChannelType($this->settingsProvider, $this->subscriber);
        $provider    = $this->getMockBuilder('Oro\Bundle\EntityBundle\Provider\EntityProvider')
            ->disableOriginalConstructor()->getMock();
        $provider->expects($this->any())
            ->method('getEntities')
            ->will(
                $this->returnValue(
                    [
                        [
                            'name'         => 'name',
                            'label'        => 'label',
                            'plural_label' => 'plural_label',
                            'icon'         => 'icon'
                        ]
                    ]
                )
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
