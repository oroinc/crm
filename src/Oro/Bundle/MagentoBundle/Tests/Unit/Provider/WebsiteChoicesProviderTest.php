<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Provider;

use Oro\Bundle\MagentoBundle\Provider\Transport\MagentoTransportInterface;
use Oro\Bundle\MagentoBundle\Provider\WebsiteChoicesProvider;
use Symfony\Contracts\Translation\TranslatorInterface;

class WebsiteChoicesProviderTest extends \PHPUnit\Framework\TestCase
{
    /** @var  WebsiteChoicesProvider */
    protected $websiteChoicesProvider;

    /** @var  MagentoTransportInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $transport;

    /** @var TranslatorInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $translator;

    protected function setUp(): void
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

    protected function tearDown(): void
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
                        'website_id' => 0
                    ],
                    [
                        'name' => 'Website 1',
                        'code' => 'english',
                        'website_id' => 1
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
                        'website_id' => 1
                    ],
                    [
                        'name' => 'Website 2',
                        'code' => 'english',
                        'website_id' => 2
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
