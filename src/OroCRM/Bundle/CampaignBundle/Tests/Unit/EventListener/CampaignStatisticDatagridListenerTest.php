<?php

namespace OroCRM\Bundle\CampaignBundle\Tests\Unit\EventListener;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\ParameterBag;
use Oro\Bundle\DataGridBundle\Event\PreBuild;
use Oro\Bundle\DataGridBundle\EventListener\MixinListener;
use OroCRM\Bundle\CampaignBundle\EventListener\CampaignStatisticDatagridListener;
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

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $registry;

    protected function setUp()
    {
        $this->marketingListHelper = $this
            ->getMockBuilder('OroCRM\Bundle\MarketingListBundle\Model\MarketingListHelper')
            ->disableOriginalConstructor()
            ->getMock();
        $this->registry = $this
            ->getMockBuilder('Doctrine\Common\Persistence\ManagerRegistry')
            ->getMock();

        $this->listener = new CampaignStatisticDatagridListener($this->marketingListHelper, $this->registry);
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

    /**
     * @return array
     */
    public function applicableDataProvider()
    {
        return [
            ['test_grid', false, null, false],
            ['test_grid', true, null, false],
            ['test_grid', true, 1, true],
        ];
    }

    /**
     *
     * @dataProvider preBuildDataProvider
     * @param bool $isSent
     * @param string $expectedMixin
     */
    public function testOnPreBuildSentCampaign($isSent, $expectedMixin)
    {
        $id = 1;
        $gridName = ConfigurationProvider::GRID_PREFIX;
        $parameters = new ParameterBag(['emailCampaign' => $id]);
        $config = DatagridConfiguration::create(
            [
                'name'   => $gridName,
                'source' => [
                    'query' => [
                        'where' => '1 = 0'
                    ]
                ]
            ]
        );

        $this->marketingListHelper
            ->expects($this->any())
            ->method('getMarketingListIdByGridName')
            ->with($this->equalTo($gridName))
            ->will($this->returnValue($id));

        $marketingList = $this->getMockBuilder('OroCRM\Bundle\CampaignBundle\Entity\EmailCampaign')
            ->disableOriginalConstructor()
            ->getMock();
        $marketingList->expects($this->once())
            ->method('isSent')
            ->will($this->returnValue($isSent));
        $this->assertEntityFind($id, $marketingList);

        $this->listener->onPreBuild(new PreBuild($config, $parameters));

        if ($isSent) {
            $this->assertEmpty($config->offsetGetByPath(CampaignStatisticDatagridListener::PATH_DATAGRID_WHERE));
        }

        $this->assertEquals($expectedMixin, $parameters->get(MixinListener::GRID_MIXIN));
    }

    /**
     * @return array
     */
    public function preBuildDataProvider()
    {
        return [
            'not sent' => [false, CampaignStatisticDatagridListener::MIXIN_UNSENT_NAME],
            'sent' => [true, CampaignStatisticDatagridListener::MIXIN_SENT_NAME],
        ];
    }

    /**
     * @param int $id
     * @param object $entity
     */
    protected function assertEntityFind($id, $entity)
    {
        $repository = $this->getMockBuilder('\Doctrine\Common\Persistence\ObjectRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $repository->expects($this->once())
            ->method('find')
            ->with($id)
            ->will($this->returnValue($entity));

        $this->registry->expects($this->once())
            ->method('getRepository')
            ->with('OroCRMCampaignBundle:EmailCampaign')
            ->will($this->returnValue($repository));
    }

    public function testOnPreBuildNotApplicable()
    {
        $gridName = ConfigurationProvider::GRID_PREFIX;
        $config = DatagridConfiguration::create([]);

        $event = new PreBuild($config, new ParameterBag([]));

        $this->marketingListHelper
            ->expects($this->any())
            ->method('getMarketingListIdByGridName')
            ->with($this->equalTo($gridName));
        $this->registry->expects($this->never())
            ->method('getRepository');

        $this->listener->onPreBuild($event);
    }
}
