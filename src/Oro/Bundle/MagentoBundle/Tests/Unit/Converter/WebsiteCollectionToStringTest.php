<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Converter;

use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;
use Oro\Bundle\MagentoBundle\Converter\WebsiteCollectionToString;

class WebsiteCollectionToStringTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider converterCallbackDataProvider
     *
     * @param ResultRecord $data
     * @param string       $expectedResult
     */
    public function testConverterCallback(ResultRecord $data, $expectedResult)
    {
        $callback = WebsiteCollectionToString::getConverterCallback();

        $result = $callback($data);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @return array
     */
    public function converterCallbackDataProvider()
    {
        $websiteMock1 = $this->createMock('Oro\Bundle\MagentoBundle\Entity\Website');
        $websiteMock1->expects($this->any())->method('getName')
            ->will($this->returnValue('website 1'));

        $websiteMock2 = $this->createMock('Oro\Bundle\MagentoBundle\Entity\Website');
        $websiteMock2->expects($this->any())->method('getName')
            ->will($this->returnValue('website 2'));

        return [
            'empty data'                => [new ResultRecord(['websites' => null]), ''],
            'websites collection given' => [
                new ResultRecord(
                    [
                        'websites' => new ArrayCollection(
                            [
                            $websiteMock1,
                            $websiteMock2
                            ]
                        )
                    ]
                ),
                'website 1, website 2'
            ],
        ];
    }
}
