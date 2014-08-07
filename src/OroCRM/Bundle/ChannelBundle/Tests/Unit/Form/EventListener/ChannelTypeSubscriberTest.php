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
     * @return array
     */
    public function formDataProviderForPreSet()
    {
        $channel = $this->getMock('OroCRM\Bundle\ChannelBundle\Entity\Channel');
        $channel->expects($this->any())->method('getId')
            ->will($this->returnValue(123));

        $choiceListItem1 = new ChoiceListItem(
            'test Entity1',
            [
                'data-label'        => 'test Entity1',
                'data-plural_label' => 'test Entity1',
                'data-icon'         => 'icon-envelope',
            ]
        );

        $choiceListItem2 = new ChoiceListItem(
            'test Entity2',
            [
                'data-label'        => 'test Entity2',
                'data-plural_label' => 'test Entity2',
                'data-icon'         => 'icon-envelope',
            ]
        );

        $entityChoices = [
            'OroCRM\Bundle\AcmeBundle\Entity\Test1' => $choiceListItem1,
            'OroCRM\Bundle\AcmeBundle\Entity\Test2' => $choiceListItem2,
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

    public function testPreSubmit()
    {
        $data = [
            'customerIdentity' => 'OroCRM\Bundle\AcmeBundle\Entity\Test1',
            'entities'         => [
                'OroCRM\Bundle\AcmeBundle\Entity\Test1',
                'OroCRM\Bundle\AcmeBundle\Entity\Test2'
            ],
        ];

        $events = $this->subscriber->getSubscribedEvents();
        $this->assertArrayHasKey(FormEvents::PRE_SUBMIT, $events);
        $this->assertEquals($events[FormEvents::PRE_SUBMIT], 'preSubmit');

        $form             = $this->getMock('Symfony\Component\Form\Test\FormInterface');
        $fieldMock        = $this->getMock('Symfony\Component\Form\Test\FormInterface');
        $formBuilder      = $this->getMockBuilder('Symfony\Component\Form\FormBuilder')
            ->disableOriginalConstructor()->getMock();
        $resolvedFormType = $this->getMockBuilder('Symfony\Component\Form\ResolvedFormType')
            ->disableOriginalConstructor()->getMock();

        $form->expects($this->any())
            ->method('get')
            ->will($this->returnValue($fieldMock));

        $fieldMock->expects($this->any())
            ->method('getConfig')
            ->will($this->returnValue($formBuilder));

        $formBuilder->expects($this->any())
            ->method('getOptions')
            ->will($this->returnValue([]));
        $formBuilder->expects($this->any())
            ->method('getType')
            ->will($this->returnValue($resolvedFormType));

        $resolvedFormType->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('test Entity1'));

        $event = new FormEvent($form, $data);
        $this->subscriber->preSubmit($event);
    }
}
