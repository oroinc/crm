<?php

namespace OroCRM\Bundle\MarketingListBundle\Tests\Unit\Datagrid;

use OroCRM\Bundle\MarketingListBundle\Datagrid\GridMarketingListTypeProvider;
use OroCRM\Bundle\MarketingListBundle\Entity\MarketingListType;

class GridMarketingListTypeProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $registry;

    /**
     * @var GridMarketingListTypeProvider
     */
    protected $provider;

    protected function setUp()
    {
        $this->registry = $this->getMock('Symfony\Bridge\Doctrine\RegistryInterface');

        $this->provider = new GridMarketingListTypeProvider($this->registry);
    }

    /**
     * @param array $data
     * @param array $expected
     *
     * @dataProvider typeChoicesDataProvider
     */
    public function testGetListTypeChoices(array $data, array $expected)
    {
        $repository = $this
            ->getMockBuilder('\Doctrine\Common\Persistence\ObjectRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $repository
            ->expects($this->any())
            ->method('findBy')
            ->will($this->returnValue($data));

        $om = $this
            ->getMockBuilder('Doctrine\Common\Persistence\ObjectManager')
            ->disableOriginalConstructor()
            ->getMock();

        $om
            ->expects($this->any())
            ->method('getRepository')
            ->with($this->equalTo(GridMarketingListTypeProvider::MARKETING_LIST_TYPE))
            ->will($this->returnValue($repository));

        $this->registry
            ->expects($this->any())
            ->method('getManagerForClass')
            ->with($this->equalTo(GridMarketingListTypeProvider::MARKETING_LIST_TYPE))
            ->will($this->returnValue($om));


        $this->assertEquals(
            $expected,
            $this->provider->getListTypeChoices()
        );
    }

    /**
     * @return array
     */
    public function typeChoicesDataProvider()
    {
        return [
            [[], []],
            [
                [
                    $this->getMarketingListType(MarketingListType::TYPE_DYNAMIC, 'label1'),
                    $this->getMarketingListType(MarketingListType::TYPE_MANUAL, 'label2'),
                ],
                [
                    MarketingListType::TYPE_DYNAMIC => 'label1',
                    MarketingListType::TYPE_MANUAL  => 'label2',
                ]
            ]
        ];
    }

    /**
     * @param string $type
     * @param string $label
     *
     * @return MarketingListType
     */
    protected function getMarketingListType($type, $label)
    {
        $listType = new MarketingListType($type);

        return $listType->setLabel($label);
    }
}
