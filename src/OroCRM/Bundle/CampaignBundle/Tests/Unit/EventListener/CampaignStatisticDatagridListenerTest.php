<?php

namespace OroCRM\Bundle\CampaignBundle\Tests\Unit\EventListener;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\ParameterBag;
use Oro\Bundle\DataGridBundle\Event\BuildAfter;
use Oro\Bundle\DataGridBundle\Event\PreBuild;
use OroCRM\Bundle\CampaignBundle\EventListener\CampaignStatisticDatagridListener;
use OroCRM\Bundle\MarketingListBundle\Datagrid\MarketingListItemsListener;
use OroCRM\Bundle\MarketingListBundle\Datagrid\ConfigurationProvider;

class CampaignStatisticDatagridListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CampaignStatisticDatagridListener
     */
    protected $listener;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $marketingListHelper;

    protected function setUp()
    {
        $this->marketingListHelper = $this
            ->getMockBuilder('OroCRM\Bundle\MarketingListBundle\Model\MarketingListHelper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->listener = new CampaignStatisticDatagridListener($this->marketingListHelper);
    }

    /**
     * @dataProvider applicableDataProvider
     * @param string $gridName
     * @param bool $hasCampaign
     * @param int|null $id
     * @param bool $expected
     */
    public function testIsApplicable($gridName, $hasCampaign, $id, $expected)
    {
        $parametersBag = $this->getMockBuilder('Oro\Bundle\DataGridBundle\Datagrid\ParameterBag')
            ->disableOriginalConstructor()
            ->getMock();
        $parametersBag->expects($this->once())
            ->method('has')
            ->with('emailCampaign')
            ->will($this->returnValue($hasCampaign));

        if ($hasCampaign) {
            $this->marketingListHelper->expects($this->once())
                ->method('getMarketingListIdByGridName')
                ->with($gridName)
                ->will($this->returnValue($id));
        }

        $this->assertEquals($expected, $this->listener->isApplicable($gridName, $parametersBag));
    }

    public function applicableDataProvider()
    {
        return [
            ['test_grid', false, null, false],
            ['test_grid', true, null, false],
            ['test_grid', true, 1, true],
        ];
    }

    /**
     * @param string $gridName
     * @param array  $parameters
     * @param array  $select
     * @param string $groupBy
     * @param string $expected
     *
     * @dataProvider preBuildDataProvider
     */
    public function testOnPreBuild($gridName, array $parameters, array $select, $groupBy, $expected)
    {
        $config = DatagridConfiguration::create(
            [
                'name'   => $gridName,
                'source' => [
                    'query' => [
                        'select'  => $select,
                        'groupBy' => $groupBy
                    ]
                ]
            ]
        );

        $event = new PreBuild($config, new ParameterBag($parameters));

        $this->marketingListHelper
            ->expects($this->any())
            ->method('getMarketingListIdByGridName')
            ->with($this->equalTo($gridName))
            ->will($this->returnValue(1));

        $this->listener->onPreBuild($event);

        $this->assertEquals($expected, $config->offsetGetByPath(CampaignStatisticDatagridListener::PATH_GROUPBY));
    }

    /**
     * @return array
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function preBuildDataProvider()
    {
        return [
            'no emailCampaign' => [
                'gridName'   => ConfigurationProvider::GRID_PREFIX,
                'parameters' => [],
                'selects'    => [],
                'groupBy'    => null,
                'expected'   => null,
            ],
            'no fields' => [
                'gridName'   => ConfigurationProvider::GRID_PREFIX,
                'parameters' => ['emailCampaign' => 1],
                'selects'    => [],
                'groupBy'    => null,
                'expected'   => null,
            ],
            'group by no fields' => [
                'gridName'   => ConfigurationProvider::GRID_PREFIX,
                'parameters' => ['emailCampaign' => 1],
                'selects'    => [],
                'groupBy'    => 'alias.existing',
                'expected'   => 'alias.existing',
            ],
            'field without alias' => [
                'gridName'   => ConfigurationProvider::GRID_PREFIX,
                'parameters' => ['emailCampaign' => 1],
                'selects'    => ['alias.field'],
                'groupBy'    => null,
                'expected'   => 'alias.field',
            ],
            'aliases and without' => [
                'gridName'   => ConfigurationProvider::GRID_PREFIX,
                'parameters' => ['emailCampaign' => 1],
                'selects'    => ['alias.field', 'alias.matchedFields  as  c1', 'alias.secondMatched aS someAlias3'],
                'groupBy'    => null,
                'expected'   => 'alias.field,c1,someAlias3',
            ],
            'mixed fields and group by' => [
                'gridName'   => ConfigurationProvider::GRID_PREFIX,
                'parameters' => ['emailCampaign' => 1],
                'selects'    => ['alias.field', 'alias.matchedFields as c1'],
                'groupBy'    => 'alias.existing',
                'expected'   => 'alias.existing,alias.field,c1',
            ],
            'wrong field definition' => [
                'gridName'   => ConfigurationProvider::GRID_PREFIX,
                'parameters' => ['emailCampaign' => 1],
                'selects'    => ['alias.matchedFields wrongas c1'],
                'groupBy'    => null,
                'expected'   => null,
            ],
            'with aggregate' => [
                'gridName'   => ConfigurationProvider::GRID_PREFIX,
                'parameters' => ['emailCampaign' => 1],
                'selects'    => ['MAX(t1.f0)', 'AvG(t10.F19) as agF1', 'alias.matchedFields AS c1'],
                'groupBy'    => null,
                'expected'   => 'c1',
            ],
        ];
    }
}
