<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Form\EventListener;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Test\FormIntegrationTestCase;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\FormEvent;

use Oro\Bundle\SecurityBundle\Encoder\Mcrypt;

use Oro\Bundle\MagentoBundle\Form\EventListener\SoapSettingsFormSubscriber;
use Oro\Bundle\MagentoBundle\Tests\Unit\Stub\SoapTransportSettingFormTypeStub;
use Oro\Bundle\MagentoBundle\Form\Type\WebsiteSelectType;
use Oro\Bundle\MagentoBundle\Entity\MagentoSoapTransport;

class SoapSettingsFormSubscriberTest extends FormIntegrationTestCase
{
    /** @var  SoapSettingsFormSubscriber */
    protected $subscriber;

    /** @var  FormEvent */
    protected $event;

    /** @var FormInterface */
    protected $form;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->form = $this->factory->create('oro_magento_soap_transport_setting_form_type');

        $mcrypt = $this
            ->getMockBuilder('Oro\Bundle\SecurityBundle\Encoder\Mcrypt')
            ->disableOriginalConstructor()
            ->getMock();

        $this->subscriber = new SoapSettingsFormSubscriber($mcrypt);

        $this->event = $this
            ->getMockBuilder('Symfony\Component\Form\FormEvent')
            ->disableOriginalConstructor()
            ->getMock();

        $this->event
            ->expects($this->once())
            ->method('getForm')
            ->willReturn($this->form);
    }

    /**
     * @dataProvider preSetDataProvider
     *
     * @param array $websites
     * @param array $expected
     */
    public function testPreSetWebsites($websites, $expected)
    {
        $data = $this
            ->getMockBuilder('Oro\Bundle\MagentoBundle\Entity\MagentoSoapTransport')
            ->disableOriginalConstructor()
            ->getMock();

        $data
            ->expects($this->once())
            ->method('getWebsites')
            ->willReturn($websites);

        $this->event
            ->expects($this->once())
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
     * @dataProvider preSubmitDataProvider
     *
     * @param array $data
     * @param array $expected
     */
    public function testPreSubmitWebsites($data, $expected)
    {
        $this->event
            ->expects($this->once())
            ->method('getData')
            ->willReturn($data);

        $this->subscriber->preSubmit($this->event);

        $choices = $this->form->get('websiteId')->getConfig()->getOption('choices');

        $this->assertEquals($expected, $choices);
    }

    /**
     * @return array
     */
    public function preSubmitDataProvider()
    {
        return [
            'with websites' => [
                'data' => [
                    'websites' => [
                        [
                            'id' => 1,
                            'label' => 'Website 1',
                        ],
                    ],
                    'apiKey' => '',
                ],
                'expected' => [
                    'Website 1' => 1,
                ],
            ],
            'with encoded websites' => [
                'data' => [
                    'websites' => '{"websites":{"id":1,"label":"Website 1"}}',
                    'apiKey' => '',
                ],
                'expected' => [
                    'Website 1' => 1,
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
                    WebsiteSelectType::NAME => new WebsiteSelectType(),
                    SoapTransportSettingFormTypeStub::NAME => new SoapTransportSettingFormTypeStub()
                ],
                []
            )
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        unset($this->subscriber);
        unset($this->form);
    }
}
