<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Form\EventListener;

use Oro\Bundle\MagentoBundle\Form\EventListener\IsDisplayOrderNotesSubscriber;
use Oro\Bundle\MagentoBundle\Form\Type\AbstractTransportSettingFormType;
use Oro\Bundle\MagentoBundle\Form\Type\IsDisplayOrderNotesFormType;
use Oro\Bundle\MagentoBundle\Tests\Unit\Stub\MagentoTransportStub;
use Oro\Bundle\MagentoBundle\Tests\Unit\Stub\TransportSettingFormTypeWithIsDisplayOrderNotesStub;
use Oro\Component\Testing\Unit\EntityTrait;
use Oro\Component\Testing\Unit\PreloadedExtension;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Test\FormIntegrationTestCase;

class IsDisplayOrderCommentsSubscriberTest extends FormIntegrationTestCase
{
    use EntityTrait;

    /** @var \PHPUnit\Framework\MockObject\MockObject | FormEvent */
    protected $event;

    /** @var FormInterface */
    protected $form;

    /** @var IsDisplayOrderNotesSubscriber  */
    protected $subscriber;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->subscriber = new IsDisplayOrderNotesSubscriber();

        $this->event = $this->createMock(FormEvent::class);

        $this->form = $this->factory->create(
            TransportSettingFormTypeWithIsDisplayOrderNotesStub::class
        );

        $this->event->method('getForm')->willReturn($this->form);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        unset(
            $this->subscriber,
            $this->event,
            $this->form
        );
    }

    /**
     * @dataProvider testProcessIsDisplayOrderNotesFieldOnPreSetProvider
     *
     * @param null | MagentoTransportStub $eventData
     * @param boolean $expectedDisabledOptionValue
     */
    public function testProcessIsDisplayOrderNotesFieldOnPreSet($eventData, $expectedDisabledOptionValue)
    {
        $this->event
            ->expects($this->atLeastOnce())
            ->method('getData')
            ->willReturn($eventData);

        $this->subscriber->processIsDisplayOrderNotesFieldOnPreSet($this->event);

        $disabledOptionValue = $this->form
            ->get(AbstractTransportSettingFormType::IS_DISPLAY_ORDER_NOTES_FIELD_NAME)
            ->getConfig()
            ->getOption('disabled');

        $this->assertEquals($expectedDisabledOptionValue, $disabledOptionValue);
    }

    /**
     * @return array
     */
    public function testProcessIsDisplayOrderNotesFieldOnPreSetProvider()
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
            'When option extensionInstalled is true, but not supported version by order note functionality' => [
                'eventData' => $this->getEntity(
                    MagentoTransportStub::class,
                    [
                        'id' => 1,
                        'isExtensionInstalled' => true,
                        'extensionVersion' => '1.0.0'
                    ]
                ),
                'expectedDisabledOptionValue' => true
            ],
            'When option extensionInstalled is true and supported version by order note functionality' => [
                'eventData' => $this->getEntity(
                    MagentoTransportStub::class,
                    [
                        'id' => 1,
                        'isExtensionInstalled' => true,
                        'extensionVersion' => '1.2.20'
                    ]
                ),
                'expectedDisabledOptionValue' => false
            ],
        ];
    }

    public function testProcessIsDisplayOrderNotesOnPreSubmit()
    {
        $this->subscriber->processIsDisplayOrderNotesOnPreSubmit($this->event);

        $disabledOptionValue = $this->form
            ->get(AbstractTransportSettingFormType::IS_DISPLAY_ORDER_NOTES_FIELD_NAME)
            ->getConfig()
            ->getOption('disabled');

        $this->assertFalse($disabledOptionValue);
    }

    /**
     * {@inheritdoc}
     */
    protected function getExtensions()
    {
        return [
            new PreloadedExtension(
                [
                    IsDisplayOrderNotesFormType::NAME => new IsDisplayOrderNotesFormType()
                ],
                []
            )
        ];
    }
}
