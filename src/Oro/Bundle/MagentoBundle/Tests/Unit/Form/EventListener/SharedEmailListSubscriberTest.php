<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Form\EventListener;

use Oro\Bundle\MagentoBundle\Form\EventListener\SharedEmailListSubscriber;
use Oro\Bundle\MagentoBundle\Form\Type\AbstractTransportSettingFormType;
use Oro\Bundle\MagentoBundle\Form\Type\SharedGuestEmailListType;
use Oro\Bundle\MagentoBundle\Tests\Unit\Stub\MagentoTransportStub;
use Oro\Bundle\MagentoBundle\Tests\Unit\Stub\TransportSettingFormTypeWithSharedEmailListStub;
use Oro\Component\Testing\Unit\EntityTrait;
use Oro\Component\Testing\Unit\PreloadedExtension;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Test\FormIntegrationTestCase;

class SharedEmailListSubscriberTest extends FormIntegrationTestCase
{
    use EntityTrait;

    /** @var \PHPUnit\Framework\MockObject\MockObject | FormEvent */
    protected $event;

    /** @var FormInterface */
    protected $form;

    /** @var SharedEmailListSubscriber  */
    protected $subscriber;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->subscriber = new SharedEmailListSubscriber();

        $this->event = $this->createMock(FormEvent::class);

        $this->form = $this->factory->create(
            TransportSettingFormTypeWithSharedEmailListStub::class
        );

        $this->event->method('getForm')->willReturn($this->form);
    }

    /**
     * @dataProvider testProcessSharedGuestEmailListFieldOnPreSetProvider
     *
     * @param null | MagentoTransportStub $eventData
     * @param boolean $expectedDisabledOptionValue
     */
    public function testProcessSharedGuestEmailListFieldOnPreSet($eventData, $expectedDisabledOptionValue)
    {
        $this->event
            ->expects($this->atLeastOnce())
            ->method('getData')
            ->willReturn($eventData);

        $this->subscriber->processSharedGuestEmailListFieldOnPreSet($this->event);

        $disabledOptionValue = $this->form
            ->get(AbstractTransportSettingFormType::SHARED_GUEST_EMAIL_FIELD_NAME)
            ->getConfig()
            ->getOption('disabled');

        $this->assertEquals($expectedDisabledOptionValue, $disabledOptionValue);
    }

    /**
     * @return array
     */
    public function testProcessSharedGuestEmailListFieldOnPreSetProvider()
    {
        return [
            'Event data is null' => [
                'eventData' => null,
                'expectedDisabledOptionValue' => false
            ],
            'Without id but with option extensionInstalled' => [
                'eventData' => $this->getEntity(
                    MagentoTransportStub::class,
                    [
                        'isExtensionInstalled' => true
                    ]
                ),
                'expectedDisabledOptionValue' => true
            ],
            'Without id but without option extensionInstalled' => [
                'eventData' => $this->getEntity(MagentoTransportStub::class),
                'expectedDisabledOptionValue' => true
            ],
            'When option extensionInstalled is null' => [
                'eventData' => $this->getEntity(MagentoTransportStub::class, ['id' => 1]),
                'expectedDisabledOptionValue' => true
            ],
            'When option extensionInstalled is false' => [
                'eventData' => $this->getEntity(
                    MagentoTransportStub::class,
                    [
                        'id' => 1,
                        'isExtensionInstalled' => false
                    ]
                ),
                'expectedDisabledOptionValue' => true
            ],
            'When option extensionInstalled is true' => [
                'eventData' => $this->getEntity(
                    MagentoTransportStub::class,
                    [
                        'id' => 1,
                        'isExtensionInstalled' => true
                    ]
                ),
                'expectedDisabledOptionValue' => false
            ],
        ];
    }

    /**
     * @dataProvider testProcessSharedGuestEmailListFieldOnPreSubmitProvider
     *
     * @param array $eventData
     * @param array $expectedData
     */
    public function testProcessSharedGuestEmailListFieldOnPreSubmit(array $eventData, array $expectedData)
    {
        $this->event
            ->expects($this->atLeastOnce())
            ->method('getData')
            ->willReturn($eventData);

        $this->event
            ->expects($this->once())
            ->method('setData')
            ->with($expectedData);

        $this->subscriber->processSharedGuestEmailListFieldOnPreSubmit($this->event);

        $disabledOptionValue = $this->form
            ->get(AbstractTransportSettingFormType::SHARED_GUEST_EMAIL_FIELD_NAME)
            ->getConfig()
            ->getOption('disabled');

        $this->assertFalse($disabledOptionValue);
    }

    /**
     * @return array
     */
    public function testProcessSharedGuestEmailListFieldOnPreSubmitProvider()
    {
        return [
            'Extension is installed' => [
                'eventData' => [
                    'isExtensionInstalled' => true,
                    AbstractTransportSettingFormType::SHARED_GUEST_EMAIL_FIELD_NAME => 'shared_guest_email_list'
                ],
                'expectedData' => [
                    'isExtensionInstalled' => true,
                    AbstractTransportSettingFormType::SHARED_GUEST_EMAIL_FIELD_NAME => 'shared_guest_email_list'
                ],
            ],
            'Extension not installed' => [
                'eventData' => [
                    'isExtensionInstalled' => false,
                    AbstractTransportSettingFormType::SHARED_GUEST_EMAIL_FIELD_NAME => 'shared_guest_email_list'
                ],
                'expectedData' => [
                    'isExtensionInstalled' => false,
                    AbstractTransportSettingFormType::SHARED_GUEST_EMAIL_FIELD_NAME => ''
                ]
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getExtensions()
    {
        return [
            new PreloadedExtension(
                [
                    SharedGuestEmailListType::NAME => new SharedGuestEmailListType()
                ],
                []
            )
        ];
    }
}
