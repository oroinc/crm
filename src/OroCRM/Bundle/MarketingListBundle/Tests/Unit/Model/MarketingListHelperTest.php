<?php

namespace OroCRM\Bundle\MarketingListBundle\Tests\Unit\Model;

use OroCRM\Bundle\MarketingListBundle\Datagrid\ConfigurationProvider;
use OroCRM\Bundle\MarketingListBundle\Model\MarketingListHelper;

class MarketingListHelperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $managerRegistry;

    /**
     * @var MarketingListHelper
     */
    protected $helper;

    protected function setUp()
    {
        $this->managerRegistry = $this->getMockBuilder('Doctrine\Common\Persistence\ManagerRegistry')
            ->getMock();

        $this->helper = new MarketingListHelper($this->managerRegistry);
    }

    /**
     * @dataProvider gridNameDataProvider
     *
     * @param string $grid
     * @param int $id
     */
    public function testGetMarketingListIdByGridName($grid, $id)
    {
        $this->assertEquals($id, $this->helper->getMarketingListIdByGridName($grid));
    }

    public function gridNameDataProvider()
    {
        return [
            ['some_grid_1', null],
            [ConfigurationProvider::GRID_PREFIX, null],
            ['pre_' . ConfigurationProvider::GRID_PREFIX, null],
            [ConfigurationProvider::GRID_PREFIX . 1, 1],
            [ConfigurationProvider::GRID_PREFIX . '1_suffix', 1],
        ];
    }

    public function testGetMarketingList()
    {
        $id = 100;
        $entity = new \stdClass();

        $repository = $this->getMockBuilder('\Doctrine\Common\Persistence\ObjectRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $repository->expects($this->once())
            ->method('find')
            ->with($id)
            ->will($this->returnValue($entity));

        $this->managerRegistry->expects($this->once())
            ->method('getRepository')
            ->with(MarketingListHelper::MARKETING_LIST)
            ->will($this->returnValue($repository));

        $this->assertEquals($entity, $this->helper->getMarketingList($id));
    }
}
