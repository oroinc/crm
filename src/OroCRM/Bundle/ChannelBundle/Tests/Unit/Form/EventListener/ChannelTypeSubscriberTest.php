<?php

namespace OroCRM\Bundle\ChannelBundle\Tests\Unit\Form\EventListener;

use Genemu\Bundle\FormBundle\Form\JQuery\Type\Select2Type;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\FormIntegrationTestCase;

use OroCRM\Bundle\ChannelBundle\Form\Type\ChannelEntityType;
use OroCRM\Bundle\ChannelBundle\Form\Type\ChannelType;
use OroCRM\Bundle\ChannelBundle\Entity\Channel;
use OroCRM\Bundle\ChannelBundle\Provider\SettingsProvider;
use OroCRM\Bundle\ChannelBundle\Form\EventListener\ChannelTypeSubscriber;

class ChannelTypeSubscriberTest extends FormIntegrationTestCase
{
    const TEST_CHANNEL_TYPE = 'test_type';
    const TEST_CUSTOMER_IDENTITY = 'OroCRM\Bundle\AcmeBundle\Entity\Test1';

    /** @var SettingsProvider|\PHPUnit_Framework_MockObject_MockObject */
    protected $settingsProvider;

    /** @var ChannelTypeSubscriber */
    protected $subscriber;

    public function setUp()
    {
        $this->settingsProvider = $this->getMockBuilder('OroCRM\Bundle\ChannelBundle\Provider\SettingsProvider')
            ->disableOriginalConstructor()->getMock();

        $this->settingsProvider->expects($this->any())
            ->method('getEntitiesByChannelType')
            ->will(
                $this->returnValue(
                    [
                        'OroCRM\Bundle\AcmeBundle\Entity\Test1',
                        'OroCRM\Bundle\AcmeBundle\Entity\Test2'
                    ]
                )
            );

        $this->settingsProvider->expects($this->any())
            ->method('getCustomerIdentityFromConfig')
            ->with(self::TEST_CHANNEL_TYPE)
            ->will(
                $this->returnValue(self::TEST_CUSTOMER_IDENTITY)
            );

        $this->subscriber = new ChannelTypeSubscriber($this->settingsProvider);
        parent::setUp();
    }

    public function tearDown()
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

        $form       = $this->getMock('Symfony\Component\Form\Test\FormInterface');
        $fieldMock  = $this->getMock('Symfony\Component\Form\Test\FormInterface');
        $configMock = $this->getMock('Symfony\Component\Form\FormConfigInterface');

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
        $channelUpdate = $this->getMock('OroCRM\Bundle\ChannelBundle\Entity\Channel');
        $channelUpdate->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(1));
        $channel = $this->getMock('OroCRM\Bundle\ChannelBundle\Entity\Channel');

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
            'customerIdentity' => 'OroCRM\Bundle\AcmeBundle\Entity\Test1',
            'entities'         => [
                'OroCRM\Bundle\AcmeBundle\Entity\Test1',
                'OroCRM\Bundle\AcmeBundle\Entity\Test2'
            ],
        ];

        $form  = $this->getMock('Symfony\Component\Form\Test\FormInterface');
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

        $form = $this->getMock('Symfony\Component\Form\Test\FormInterface');

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
                    $channelType->getName()                  => $channelType,
                    'orocrm_channel_entities'                => new ChannelEntityType(),
                    'orocrm_channel.form.type.entity_choice' => new ChannelEntityType($provider),
                    'orocrm_channel_entity_choice_form'      => new ChannelEntityType($provider),
                    'genemu_jqueryselect2_choice'            => new Select2Type('choice')
                ],
                []
            )
        ];
    }
}
