<?php

namespace OroCRM\Bundle\ChannelBundle\Tests\Unit\Form\EventListener;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

use OroCRM\Bundle\ChannelBundle\Entity\Channel;
use OroCRM\Bundle\ChannelBundle\Provider\SettingsProvider;
use OroCRM\Bundle\ChannelBundle\Form\EventListener\ChannelTypeSubscriber;

class ChannelTypeSubscriberTest extends \PHPUnit_Framework_TestCase
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
    }

    public function tearDown()
    {
        unset($this->subscriber, $this->settingsProvider);
    }

    /**
     * @dataProvider formDataProviderForPreSet
     *
     * @param Channel|null $formData
     */
    public function testPreSet($formData)
    {
        $events = $this->subscriber->getSubscribedEvents();
        $this->assertArrayHasKey(FormEvents::PRE_SET_DATA, $events);
        $this->assertEquals($events[FormEvents::PRE_SET_DATA], 'preSet');

        $form = $this->getMock('Symfony\Component\Form\Test\FormInterface');
        $data = $this->getMock('OroCRM\Bundle\ChannelBundle\Entity\Channel');

        $event = new FormEvent($form, $formData);
        $event->setData($data);

        $this->subscriber->preSet($event);
    }

    /**
     * @return array
     */
    public function formDataProviderForPreSet()
    {
        $channel = $this->getMock('OroCRM\Bundle\ChannelBundle\Entity\Channel');

        return [
            'without data' => [
                null,
            ],
            'with data'    => [
                $channel,
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
}
