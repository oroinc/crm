<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Form\EventListener;

use Oro\Bundle\MagentoBundle\Entity\MagentoSoapTransport;
use Oro\Bundle\MagentoBundle\Form\EventListener\SettingsFormSubscriber;
use Oro\Bundle\MagentoBundle\Form\Type\WebsiteSelectType;
use Oro\Bundle\MagentoBundle\Tests\Unit\Stub\TransportSettingFormTypeStub;
use Oro\Bundle\SecurityBundle\Encoder\SymmetricCrypterInterface;
use Oro\Component\Testing\Unit\PreloadedExtension;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Test\FormIntegrationTestCase;

class SettingsFormSubscriberTest extends FormIntegrationTestCase
{
    /** @var  SettingsFormSubscriber */
    protected $subscriber;

    /** @var \PHPUnit\Framework\MockObject\MockObject | FormEvent */
    protected $event;

    /** @var FormInterface */
    protected $form;

    /** @var \PHPUnit\Framework\MockObject\MockObject | SymmetricCrypterInterface */
    protected $crypter;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->crypter = $this->createMock(SymmetricCrypterInterface::class);

        $this->subscriber = new SettingsFormSubscriber($this->crypter);

        $this->event = $this
            ->getMockBuilder('Symfony\Component\Form\FormEvent')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @dataProvider preSetDataProvider
     *
     * @param array $websites
     * @param array $expected
     */
    public function testPreSetWebsites(array $websites, array $expected)
    {
        $this->initStubForm();

        $data = $this
            ->getMockBuilder(MagentoSoapTransport::class)
            ->disableOriginalConstructor()
            ->getMock();

        $data
            ->expects($this->once())
            ->method('getWebsites')
            ->willReturn($websites);

        $this->event
            ->expects($this->atLeastOnce())
            ->method('getData')
            ->willReturn($data);

        $this->subscriber->preSet($this->event);

        $choices = $this->form->get('websiteId')->getConfig()->getOption('choices');

        $this->assertEquals($expected, $choices);
    }

    /**
     * @return array
     */
    public function preSetDataProvider()
    {
        return [
              'with websites' => [
                  'websites' => [
                      [
                          'id' => 1, 'label' => 'Website 1'
                      ]
                  ],
                  'expected' => [
                      'Website 1' => 1
                  ]
              ]
        ];
    }

    /**
     * @dataProvider preSubmitWebsiteDataProvider
     *
     * @param array $data
     * @param array $expected
     */
    public function testPreSubmitWebsites(array $data, array $expected)
    {
        $this->initStubForm();

        $this->event
            ->expects($this->atLeastOnce())
            ->method('getData')
            ->willReturn($data);

        $this->subscriber->preSubmit($this->event);

        $choices = $this->form->get('websiteId')->getConfig()->getOption('choices');

        $this->assertEquals($expected, $choices);
    }

    /**
     * @return array
     */
    public function preSubmitWebsiteDataProvider()
    {
        return [
            'with websites' => [
                'data' => [
                    'websites' => [
                        [
                            'id' => 1,
                            'label' => 'Website 1',
                        ],
                    ]
                ],
                'expected' => [
                    'Website 1' => 1,
                ],
            ],
            'with encoded websites' => [
                'data' => [
                    'websites' => '{"websites":{"id":1,"label":"Website 1"}}',
                ],
                'expected' => [
                    'Website 1' => 1,
                ]
            ]
        ];
    }

    /**
     * @dataProvider preSubmitApiKeyDataProvider
     *
     * @param array $data
     * @param array $expected
     * @param null|string $apiKeyFormData
     */
    public function testPreSubmitApiKey(array $data, array $expected, $apiKeyFormData)
    {
        $apiKeyform = $this->createMock(FormInterface::class);
        $apiKeyform
            ->method('getData')
            ->willReturn($apiKeyFormData);

        $form = $this->createMock(FormInterface::class);
        $form
            ->method('get')
            ->with('apiKey')
            ->willReturn($apiKeyform);

        $this->event->method('getForm')->willReturn($form);

        $this->event
            ->expects($this->atLeastOnce())
            ->method('getData')
            ->willReturn($data);

        $this->event
            ->expects($this->atLeastOnce())
            ->method('setData')
            ->with($expected);

        if (!empty($data['apiKey'])) {
            $this->crypter
                ->expects($this->once())
                ->method('encryptData')
                ->with($data['apiKey'])
                ->willReturn(sprintf('%s_ENCRYPTED', $data['apiKey']));
        }

        $this->subscriber->preSubmit($this->event);
    }

    /**
     * @return array
     */
    public function preSubmitApiKeyDataProvider()
    {
        return [
            'New api_key submitted' => [
                'data' => [
                    'apiKey' => 'API_KEY'
                ],
                'expected' => [
                    'apiKey' => 'API_KEY_ENCRYPTED'
                ],
                'apiKeyFormData' => 'OLD_API_KEY',
            ],
            'New api_key not submitted, but exist in form data' => [
                'data' => [
                    'apiKey' => null
                ],
                'expected' => [
                    'apiKey' => 'OLD_API_KEY'
                ],
                'apiKeyFormData' => 'OLD_API_KEY'
            ],
            'No api_key in submitted data and in form data' => [
                'data' => [],
                'expected' => [],
                'apiKeyFormData' => null
            ],
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
                    WebsiteSelectType::NAME => new WebsiteSelectType(),
                    TransportSettingFormTypeStub::NAME => new TransportSettingFormTypeStub()
                ],
                []
            )
        ];
    }

    protected function initStubForm()
    {
        $this->form = $this->factory->create(TransportSettingFormTypeStub::class);
        $this->event->method('getForm')->willReturn($this->form);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        unset(
            $this->subscriber,
            $this->event,
            $this->form,
            $this->crypter
        );
    }
}
