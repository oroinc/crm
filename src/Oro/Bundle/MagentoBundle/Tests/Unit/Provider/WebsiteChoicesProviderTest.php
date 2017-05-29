<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Provider;

use Symfony\Component\Translation\TranslatorInterface;

use Oro\Bundle\MagentoBundle\Provider\WebsiteChoicesProvider;
use Oro\Bundle\MagentoBundle\Provider\Transport\MagentoTransportInterface;

class WebsiteChoicesProviderTest extends \PHPUnit_Framework_TestCase
{
    /** @var  WebsiteChoicesProvider */
    protected $websiteChoicesProvider;

    /** @var  MagentoTransportInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $transport;

    /** @var TranslatorInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $translator;

    public function setUp()
    {
        $this->transport     = $this->createMock(MagentoTransportInterface::class);
        $this->translator    = $this->createMock(TranslatorInterface::class);

        $this->translator
            ->expects($this->any())
            ->method('trans')
            ->will($this->returnArgument(0));

        $this->websiteChoicesProvider = new WebsiteChoicesProvider(
            $this->translator
        );
    }

    public function tearDown()
    {
        parent::tearDown();

        unset(
            $this->transport,
            $this->translator,
            $this->websiteChoicesProvider
        );
    }

    /**
     * @dataProvider websiteProvider
     *
     * @param array $websites
     * @param array $expected
     */
    public function testFormatWebsiteChoices($websites, $expected)
    {
        $stubIterator = new \ArrayIterator($websites);

        $this->transport->expects($this->once())->method('getWebsites')->willReturn($stubIterator);

        $formatWebsites = $this->websiteChoicesProvider->formatWebsiteChoices($this->transport);

        $this->assertEquals($expected, $formatWebsites);
    }

    /**
     * @return array
     */
    public function websiteProvider()
    {
        return [
            'with_admin_website' => [
                'websites' => [
                    [
                        'name' => 'Admin',
                        'code' => 'admin',
                        'id' => 0
                    ],
                    [
                        'name' => 'Website 1',
                        'code' => 'english',
                        'id' => 1
                    ]
                ],
                'expected' =>  [
                    [
                        'id' => -1,
                        'label' => 'oro.magento.magentotransport.all_sites',
                    ],
                    [
                        'id' => 1,
                        'label' => 'Website ID: %websiteId%, Stores: %storesList%',
                    ],
                ]
            ],
            'without_admin_website' => [
                'websites' =>  [
                    [
                        'name' => 'Website 1',
                        'code' => 'german',
                        'id' => 1
                    ],
                    [
                        'name' => 'Website 2',
                        'code' => 'english',
                        'id' => 2
                    ]
                ],
                'expected' => [
                    [
                        'id' => -1,
                        'label' => 'oro.magento.magentotransport.all_sites',
                    ],
                    [
                        'id' => 1,
                        'label' => 'Website ID: %websiteId%, Stores: %storesList%',
                    ],
                    [
                        'id' => 2,
                        'label' => 'Website ID: %websiteId%, Stores: %storesList%',
                    ]
                ]
            ]
        ];
    }
}
