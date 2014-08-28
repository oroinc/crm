<?php

namespace OroCRM\Bundle\MarketingListBundle\Tests\Unit\Datagrid;

use Symfony\Bridge\Doctrine\RegistryInterface;

use Oro\Bundle\DataGridBundle\Datagrid\ParameterBag;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use OroCRM\Bundle\MarketingListBundle\Datagrid\MarketingListRemovedItemsListener;
use OroCRM\Bundle\MarketingListBundle\Model\DataGridConfigurationHelper;

class MarketingListRemovedItemsListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MarketingListRemovedItemsListener
     */
    protected $listener;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|RegistryInterface
     */
    protected $registry;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|DoctrineHelper
     */
    protected $doctrineHelper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|DataGridConfigurationHelper
     */
    protected $dataGridHelper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $om;

    protected function setUp()
    {
        $this->om = $this
            ->getMockBuilder('Doctrine\Common\Persistence\ObjectManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->registry = $this->getMock('Symfony\Bridge\Doctrine\RegistryInterface');

        $this->registry
            ->expects($this->any())
            ->method('getManagerForClass')
            ->will($this->returnValue($this->om));

        $this->doctrineHelper = $this->getMockBuilder('Oro\Bundle\EntityBundle\ORM\DoctrineHelper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->dataGridHelper = $this
            ->getMockBuilder('OroCRM\Bundle\MarketingListBundle\Model\DataGridConfigurationHelper')
            ->disableOriginalConstructor()
            ->getMock();


        $this->listener = new MarketingListRemovedItemsListener(
            $this->registry,
            $this->doctrineHelper,
            $this->dataGridHelper
        );
    }

    /**
     * @param ParameterBag $parameters
     * @param string       $entity
     * @param array        $from
     * @param integer      $expectedExtendConfiguration
     * @param integer      $expectedAddConfiguration
     *
     * @dataProvider preBuildDataProvider
     */
    public function testOnPreBuild(
        ParameterBag $parameters,
        $entity,
        array $from,
        $expectedExtendConfiguration,
        $expectedAddConfiguration
    ) {
        $event = $this
            ->getMockBuilder('Oro\Bundle\DataGridBundle\Event\PreBuild')
            ->disableOriginalConstructor()
            ->getMock();

        $this->om
            ->expects($this->any())
            ->method('find')
            ->will($this->returnValue($entity));

        $event
            ->expects($this->once())
            ->method('getParameters')
            ->will($this->returnValue($parameters));

        $config = $this
            ->getMockBuilder('Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration')
            ->disableOriginalConstructor()
            ->getMock();

        $event
            ->expects($this->any())
            ->method('getConfig')
            ->will($this->returnValue($config));

        $config
            ->expects($this->any())
            ->method('offsetGetByPath')
            ->will($this->returnValue($from));

        if ($expectedAddConfiguration) {
            $config
                ->expects($this->exactly($expectedAddConfiguration))
                ->method('offsetAddToArrayByPath')
                ->with($this->isType('string'), $this->isType('array'));
        }

        if ($expectedExtendConfiguration) {
            $this->dataGridHelper
                ->expects($this->exactly($expectedExtendConfiguration))
                ->method('extendConfiguration')
                ->with(
                    $this->equalTo($config),
                    $this->equalTo(MarketingListRemovedItemsListener::REMOVED_ITEMS_MIXIN_NAME)
                );
        }

        $this->listener->onPreBuild($event);
    }

    /**
     * @return array
     */
    public function preBuildDataProvider()
    {
        return [
            [
                'parameters'                  => new ParameterBag(),
                'entity'                      => null,
                'from'                        => [],
                'expectedExtendConfiguration' => 0,
                'expectedAddConfiguration'    => 0,
            ],
            [
                'parameters'                  => new ParameterBag(),
                'entity'                      => $this->getMarketingListMock(),
                'from'                        => [],
                'expectedExtendConfiguration' => 0,
                'expectedAddConfiguration'    => 0,
            ],
            [
                'parameters'                  => new ParameterBag(['marketing_list_id' => 2]),
                'entity'                      => null,
                'from'                        => [],
                'expectedExtendConfiguration' => 0,
                'expectedAddConfiguration'    => 0,
            ],
            [
                'parameters'                  => new ParameterBag(['marketing_list_id' => 2]),
                'entity'                      => $this->getMarketingListMock(),
                'from'                        => [],
                'expectedExtendConfiguration' => 1,
                'expectedAddConfiguration'    => 0,
            ],
            [
                'parameters'                  => new ParameterBag(['marketing_list_id' => 2]),
                'entity'                      => $this->getMarketingListMock('\stdClass'),
                'from'                        => [],
                'expectedExtendConfiguration' => 1,
                'expectedAddConfiguration'    => 0,
            ],
            [
                'parameters'                  => new ParameterBag(['marketing_list_id' => 2]),
                'entity'                      => $this->getMarketingListMock('\stdClass'),
                'from'                        => [['table' => '\stdClass', 'alias' => 'alias']],
                'expectedExtendConfiguration' => 1,
                'expectedAddConfiguration'    => 2,
            ],
            [
                'parameters'                  => new ParameterBag(['marketing_list_id' => 2]),
                'entity'                      => $this->getMarketingListMock('\stdClass'),
                'from'                        => [
                    ['table' => '\stdClass2', 'alias' => 'alias2'],
                    ['table' => '\stdClass', 'alias' => 'alias'],
                ],
                'expectedExtendConfiguration' => 1,
                'expectedAddConfiguration'    => 2,
            ],
        ];
    }

    /**
     * @param string $entity
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMarketingListMock($entity = null)
    {
        $marketingList = $this->getMock('OroCRM\Bundle\MarketingListBundle\Entity\MarketingList');

        if ($entity) {
            $marketingList
                ->expects($this->any())
                ->method('getEntity')
                ->will($this->returnValue($entity));
        }

        return $marketingList;
    }
}
