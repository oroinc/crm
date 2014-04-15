<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Converter;

use Doctrine\Common\Collections\ArrayCollection;

use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;

use OroCRM\Bundle\MagentoBundle\Converter\WebsiteCollectionToString;

class WebsiteCollectionToStringTest extends \PHPUnit_Framework_TestCase
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
        $websiteMock1 = $this->getMock('OroCRM\Bundle\MagentoBundle\Entity\Website');
        $websiteMock1->expects($this->any())->method('getName')
            ->will($this->returnValue('website 1'));

        $websiteMock2 = $this->getMock('OroCRM\Bundle\MagentoBundle\Entity\Website');
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
