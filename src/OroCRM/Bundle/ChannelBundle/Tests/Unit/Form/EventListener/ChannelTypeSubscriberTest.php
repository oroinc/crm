<?php

namespace OroCRM\Bundle\ChannelBundle\Tests\Unit\Form\EventListener;

use Genemu\Bundle\FormBundle\Form\JQuery\Type\Select2Type;

use OroCRM\Bundle\ChannelBundle\Form\Type\ChannelEntityType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\FormIntegrationTestCase;

use OroCRM\Bundle\ChannelBundle\Form\Type\ChannelEntityChoiceType;
use OroCRM\Bundle\ChannelBundle\Form\Type\ChannelType;
use OroCRM\Bundle\ChannelBundle\Entity\Channel;
use OroCRM\Bundle\ChannelBundle\Provider\SettingsProvider;
use OroCRM\Bundle\ChannelBundle\Form\EventListener\ChannelTypeSubscriber;

class ChannelTypeSubscriberTest extends FormIntegrationTestCase
{
    /** @var SettingsProvider|\PHPUnit_Framework_MockObject_MockObject */
    protected $settingsProvider;

    /** @var ChannelTypeSubscriber */
    protected $subscriber;

    public function setUp()
    {
        $this->settingsProvider = $this->getMockBuilder('OroCRM\Bundle\ChannelBundle\Provider\SettingsProvider')
            ->disableOriginalConstructor()->getMock();

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
     * @param boolean $expected
     */
    public function testPreSet($formData, $expected)
    {
        $form = $this->factory->create('orocrm_channel_form');
        $form->setData($formData);
        $customerIdentity = $form->get('customerIdentity');
        $channelType = $form->get('channelType');
        $this->assertEquals($expected, $customerIdentity->isDisabled());
        $this->assertEquals($expected, $channelType->isDisabled());
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
                'formData' => null,
                'expected' => false
            ],
            'with data update' => [
                'formData' => $channelUpdate,
                'expected' => true
            ],
            'with data new channel' => [
                'formData' => $channel,
                'expected' => false
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

    /**
     * {@inheritdoc}
     */
    protected function getExtensions()
    {
        $channelType = new ChannelType($this->settingsProvider, $this->subscriber);
        $provider = $this->getMockBuilder('Oro\Bundle\EntityBundle\Provider\EntityProvider')
            ->disableOriginalConstructor()->getMock();
        $provider->expects($this->any())
            ->method('getEntities')
            ->will(
                $this->returnValue(
                    [
                        [
                            'name'          => 'name',
                            'label'         => 'label',
                            'plural_label'  => 'plural_label',
                            'icon'          => 'icon'
                        ]
                    ]
                )
            );
        return [
            new PreloadedExtension(
                [
                    $channelType->getName() => $channelType,
                    'orocrm_channel_entities' => new ChannelEntityType(),
                    'orocrm_channel.form.type.entity_choice' => new ChannelEntityType($provider),
                    'orocrm_channel_entity_choice_form' => new ChannelEntityType($provider),
                    'genemu_jqueryselect2_choice' => new Select2Type('choice')
                ],
                []
            )
        ];
    }
}
