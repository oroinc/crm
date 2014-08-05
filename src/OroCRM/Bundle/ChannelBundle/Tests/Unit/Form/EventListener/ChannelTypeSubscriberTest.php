<?php

namespace OroCRM\Bundle\ChannelBundle\Tests\Unit\Form\EventListener;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

use Oro\Bundle\FormBundle\Form\Type\ChoiceListItem;

use OroCRM\Bundle\ChannelBundle\Entity\Channel;
use OroCRM\Bundle\ChannelBundle\Form\EventListener\ChannelTypeSubscriber;

class ChannelTypeSubscriberTest extends \PHPUnit_Framework_TestCase
{
    /** @var ChannelTypeSubscriber */
    protected $subscriber;

    public function setUp()
    {
        $this->subscriber = new ChannelTypeSubscriber();
    }

    /**
     * @dataProvider formDataProvider
     *
     * @param Channel|null $formData
     * @param array        $entityChoices
     */
    public function testPreSet($formData, $entityChoices)
    {
        $events = $this->subscriber->getSubscribedEvents();
        $this->assertArrayHasKey(FormEvents::PRE_SET_DATA, $events);
        $this->assertEquals($events[FormEvents::PRE_SET_DATA], 'preSet');

        $form        = $this->getMock('Symfony\Component\Form\Test\FormInterface');
        $fieldMock   = $this->getMock('Symfony\Component\Form\Test\FormInterface');
        $data        = $this->getMock('OroCRM\Bundle\ChannelBundle\Entity\Channel');
        $formBuilder = $this->getMockBuilder('Symfony\Component\Form\FormBuilder')
            ->disableOriginalConstructor()->getMock();

        $resolvedFormType = $this->getMockBuilder('Symfony\Component\Form\ResolvedFormType')
            ->disableOriginalConstructor()->getMock();

        if (empty($formData)) {
            $data->expects($this->once())
                ->method('getEntities')
                ->will($this->returnValue([]));
        } else {
            $data->expects($this->once())
                ->method('getEntities')
                ->will($this->returnValue(['OroCRM\Bundle\AcmeBundle\Entity\Test1']));
        }

        $formBuilder->expects($this->once())
            ->method('getOption')
            ->with($this->equalTo('choices'))
            ->will($this->returnValue($entityChoices));

        $formBuilder->expects($this->any())
            ->method('getOptions')
            ->will($this->returnValue([]));

        $form->expects($this->any())
            ->method('get')
            ->will($this->returnValue($fieldMock));

        $fieldMock->expects($this->any())
            ->method('getConfig')
            ->will($this->returnValue($formBuilder));

        $formBuilder->expects($this->any())
            ->method('getType')
            ->will($this->returnValue($resolvedFormType));

        $resolvedFormType->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('test Entity1'));

        $event = new FormEvent($form, $formData);
        $event->setData($data);

        $this->subscriber->preSet($event);
    }

    /**
     * @return array
     */
    public function formDataProvider()
    {
        $channel = $this->getMock('OroCRM\Bundle\ChannelBundle\Entity\Channel');
        $channel->expects($this->any())->method('getId')
            ->will($this->returnValue(123));

        $ChoiceListItem1 = new ChoiceListItem(
            'test Entity1',
            [
                'data-label'        => 'test Entity1',
                'data-plural_label' => 'test Entity1',
                'data-icon'         => 'icon-envelope',
            ]
        );

        $ChoiceListItem2 = new ChoiceListItem(
            'test Entity2',
            [
                'data-label'        => 'test Entity2',
                'data-plural_label' => 'test Entity2',
                'data-icon'         => 'icon-envelope',
            ]
        );

        $entityChoices = [
            'OroCRM\Bundle\AcmeBundle\Entity\Test1' => $ChoiceListItem1,
            'OroCRM\Bundle\AcmeBundle\Entity\Test2' => $ChoiceListItem2,
        ];

        return [
            'without data' => [
                null,
                $entityChoices
            ],
            'with data'    => [
                $channel,
                $entityChoices
            ]
        ];
    }
}
