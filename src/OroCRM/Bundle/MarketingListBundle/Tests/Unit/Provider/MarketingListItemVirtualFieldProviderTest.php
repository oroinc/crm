<?php

namespace OroCRM\Bundle\MarketingListBundle\Tests\Unit\Provider;

use OroCRM\Bundle\MarketingListBundle\Provider\MarketingListItemVirtualFieldProvider;
use OroCRM\Bundle\MarketingListBundle\Provider\MarketingListVirtualRelationProvider;

class MarketingListItemVirtualFieldProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $relationProvider;

    /**
     * @var MarketingListItemVirtualFieldProvider
     */
    protected $fieldProvider;

    protected function setUp()
    {
        $this->relationProvider = $this
            ->getMockBuilder('OroCRM\Bundle\MarketingListBundle\Provider\MarketingListVirtualRelationProvider')
            ->disableOriginalConstructor()
            ->getMock();

        $this->fieldProvider = new MarketingListItemVirtualFieldProvider($this->relationProvider);
    }

    protected function tearDown()
    {
        unset($this->fieldProvider, $this->relationProvider);
    }

    /**
     * @dataProvider virtualFieldDataProvider
     *
     * @param bool $hasMarketingList
     * @param string $fieldName
     * @param bool $expected
     */
    public function testIsVirtualField($hasMarketingList, $fieldName, $expected)
    {
        $className = 'stdClass';

        $this->relationProvider->expects($this->once())
            ->method('hasMarketingList')
            ->with($className)
            ->will($this->returnValue($hasMarketingList));

        $this->assertEquals($expected, $this->fieldProvider->isVirtualField($className, $fieldName));
    }

    /**
     * @return array
     */
    public function virtualFieldDataProvider()
    {
        return [
            [false, 'test', false],
            [false, MarketingListItemVirtualFieldProvider::FIELD_CONTACTED_TIMES, false],
            [true, 'test', false],
            [true, MarketingListItemVirtualFieldProvider::FIELD_CONTACTED_TIMES, true],
        ];
    }

    /**
     * @dataProvider fieldsDataProvider
     *
     * @param bool $hasMarketingList
     * @param array $expected
     */
    public function testGetVirtualFields($hasMarketingList, array $expected)
    {
        $className = 'stdClass';

        $this->relationProvider->expects($this->once())
            ->method('hasMarketingList')
            ->with($className)
            ->will($this->returnValue($hasMarketingList));

        $this->assertEquals($expected, $this->fieldProvider->getVirtualFields($className));
    }

    /**
     * @return array
     */
    public function fieldsDataProvider()
    {
        return [
            [
                true,
                [
                    MarketingListItemVirtualFieldProvider::FIELD_CONTACTED_TIMES,
                    MarketingListItemVirtualFieldProvider::FIELD_LAST_CONTACTED_AT
                ]
            ],
            [false, []]
        ];
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage No virtual field found for stdClass::test
     */
    public function testGetVirtualFieldQueryException()
    {
        $this->fieldProvider->getVirtualFieldQuery('stdClass', 'test');
    }

    /**
     * @dataProvider queryDataProvider
     * @param string $fieldName
     * @param array $expected
     */
    public function testGetVirtualFieldQuery($fieldName, array $expected)
    {
        $className = 'stdClass';

        $definition = [
            'query' => [
                'join' => [
                    'left' => [
                        ['entity.field']
                    ]
                ]
            ]
        ];
        $this->relationProvider->expects($this->once())
            ->method('getRelationDefinition')
            ->with($className)
            ->will($this->returnValue($definition));

        $expected['join'] = $definition['query']['join'];
        $this->assertEquals($expected, $this->fieldProvider->getVirtualFieldQuery($className, $fieldName));
    }

    /**
     * @return array
     */
    public function queryDataProvider()
    {
        $mliAlias = MarketingListVirtualRelationProvider::MARKETING_LIST_ITEM_RELATION_NAME;

        return [
            [
                MarketingListItemVirtualFieldProvider::FIELD_CONTACTED_TIMES,
                [
                    'select' => [
                        'expr' => $mliAlias . '.contactedTimes',
                        'label' => 'orocrm.marketinglist.marketinglistitem.contacted_times.label',
                        'return_type' => 'integer'
                    ]
                ]
            ],
            [
                MarketingListItemVirtualFieldProvider::FIELD_LAST_CONTACTED_AT,
                [
                    'select' => [
                        'expr' => $mliAlias . '.lastContactedAt',
                        'label' => 'orocrm.marketinglist.marketinglistitem.last_contacted_at.label',
                        'return_type' => 'datetime'
                    ],
                ]
            ]
        ];
    }
}
