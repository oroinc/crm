<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Provider\Iterator;

use Oro\Bundle\MagentoBundle\Entity\Store;
use Oro\Bundle\MagentoBundle\Provider\Iterator\Soap\WebsiteSoapIterator;

class WebsiteSoapIteratorTest extends BaseSoapIteratorTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->iterator = new WebsiteSoapIterator($this->transport);
    }

    /**
     * @dataProvider iterationProvider
     *
     * @param array $storesList
     * @param mixed $expectedResult
     */
    public function testIteration($storesList, $expectedResult)
    {
        $this->transport->expects($this->once())->method('getStores')
            ->will($this->returnValue($storesList));

        $this->assertResponseFormat($this->iterator);
        $this->assertEquals($expectedResult, iterator_to_array($this->iterator));
    }

    /**
     * @dataProvider extensionIterationProvider
     *
     * @param array $storesList
     * @param mixed $expectedResult
     */
    public function testIterationWithExtension($storesList, $expectedResult)
    {
        $this->transport->expects($this->once())->method('isSupportedExtensionVersion')
            ->willReturn(true);
        $this->transport->expects($this->once())->method('call')
            ->willReturn($storesList);

        $this->assertResponseFormat($this->iterator);
        $this->assertEquals($expectedResult, iterator_to_array($this->iterator));
    }

    /**
     * @return array
     */
    public function extensionIterationProvider()
    {
        return [
            'bad data retrieved, should be empty array' => [[], []],
            'data retrieved correctly, admin store should not be skipped, passed as is' => [
                [
                    [
                        'website_id' => 0,
                        'code' => 'admin',
                        'name' => 'Admin',
                    ],
                    [
                        'website_id' => 1,
                        'code' => 'custom',
                        'name' => 'Custom',
                    ]
                ],
                [
                    [
                        'website_id' => 0,
                        'code' => 'admin',
                        'name' => 'Admin',
                    ],
                    [
                        'website_id' => 1,
                        'code' => 'custom',
                        'name' => 'Custom',
                    ]
                ]
            ]
        ];
    }

    /**
     * @return array
     */
    public function iterationProvider()
    {
        return [
            'bad data retrieved, should be empty array' => [[], []],
            'data retrieved correctly, admin store should not be skipped' => [
                [
                    0 => [
                        'website_id' => 0,
                        'code' => 'admin',
                        'name' => 'Admin',
                        'store_id' => Store::ADMIN_STORE_ID
                    ],
                    2 => [
                        'store_id' => 2,
                        'code' => 'fr_b2c',
                        'website_id' => 1,
                        'name' => 'B2C French'
                    ]
                ],
                [
                    0 => [
                        'name' => 'Admin',
                        'code' => 'admin',
                        'website_id' => Store::ADMIN_STORE_ID
                    ],
                    1 => [
                        'name' => 'B2C French',
                        'code' => 'fr_b2c',
                        'website_id' => 1
                    ]
                ]
            ],
            'multiple stores for website' => [
                [
                    1 => [
                        'store_id' => 1,
                        'code' => 'fr_b2b',
                        'website_id' => 1,
                        'name' => 'B2B French'
                    ],
                    2 => [
                        'store_id' => 2,
                        'code' => 'fr_b2c',
                        'website_id' => 1,
                        'name' => 'B2C French'
                    ]
                ],
                [
                    1 => [
                        'name' => 'B2B French, B2C French',
                        'code' => 'fr_b2b / fr_b2c',
                        'website_id' => 1
                    ]
                ]
            ]
        ];
    }

    /**
     * Asserts if iterator return data in format which can be processed correctly later
     *
     * @param WebsiteSoapIterator $iterator
     *
     * @throws \PHPUnit\Framework\AssertionFailedError
     */
    protected function assertResponseFormat(WebsiteSoapIterator $iterator)
    {
        foreach ($iterator as $websiteData) {
            $extraFields = array_diff(array_keys($websiteData), [
                'website_id',
                'code',
                'name'
            ]);

            $this->assertEmpty(
                $extraFields,
                sprintf(
                    'Website data contains extra fields which are not mapped in converter: %s',
                    implode(', ', $extraFields)
                )
            );
        }
    }
}
