<?php

namespace Oro\Bundle\ContactBundle\Tests\Unit\Datagrid\Extension\Customers;

use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\ParameterBag;
use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;

use Oro\Bundle\EntityBundle\Tests\Unit\ORM\Fixtures\TestEntity;
use Oro\Bundle\EntityConfigBundle\Config\Config;
use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityConfigBundle\Config\Id\EntityConfigId;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;

use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;

use Oro\Bundle\SalesBundle\Datagrid\Extension\Customers\OpportunitiesExtension;
use Oro\Bundle\SalesBundle\Entity\Opportunity;
use Oro\Bundle\SalesBundle\EntityConfig\CustomerScope;

class OpportunitiesExtensionTest extends \PHPUnit_Framework_TestCase
{
    /** @var OpportunitiesExtension */
    protected $extension;

    /** @var ConfigProvider|\PHPUnit_Framework_MockObject_MockObject */
    protected $opportunityProvider;

    /** @var ConfigManager|\PHPUnit_Framework_MockObject_MockObject */
    protected $configManger;

    public function setUp()
    {
        $configManager             = $this
            ->getMockBuilder('Oro\Bundle\EntityConfigBundle\Config\ConfigManager')
            ->disableOriginalConstructor()
            ->getMock();
        $this->opportunityProvider = $this
            ->getMockBuilder('Oro\Bundle\EntityConfigBundle\Config\ConfigProvider')
            ->disableOriginalConstructor()
            ->setMethods(['hasConfig', 'getConfig'])
            ->getMock();
        $configManager
            ->expects($this->any())
            ->method('getProvider')
            ->with('opportunity')
            ->willReturn($this->opportunityProvider);

        $this->extension = new OpportunitiesExtension($configManager);
    }

    /**
     * @dataProvider testIsApplicableDataProvider
     *
     * @param array     $config
     * @param array     $parameters
     * @param bool      $result
     * @param bool|null $enabledConfig
     */
    public function testIsApplicable(array $config, array $parameters, $result, $enabledConfig = null)
    {
        $this->extension->setParameters(new ParameterBag($parameters));
        $this->prepareEntityConfigs($parameters, $enabledConfig);
        $this->assertEquals(
            $result,
            $this->extension->isApplicable(DatagridConfiguration::create($config))
        );
    }

    public function testIsApplicableDataProvider()
    {
        $class = TestEntity::class;

        return [
            'not orm source type'           => [
                ['source' => ['type' => 'not_orm']], [], false
            ],
            'no customer_class param'       => [
                ['source' => ['type' => 'orm']], ['customer_id' => 1], false
            ],
            'no customer_id param'          => [
                ['source' => ['type' => 'orm']], ['customer_class' => 'test'], false
            ],
            'empty customer_class param'    => [
                ['source' => ['type' => 'orm']], ['customer_id' => 1, 'customer_class' => ''], false
            ],
            'empty customer_id param'       => [
                ['source' => ['type' => 'orm']], ['customer_class' => $class, 'customer_id' => null], false
            ],
            'not supported customer class'  => [
                ['source' => ['type' => 'orm']], ['customer_class' => $class, 'customer_id' => 1], false, false
            ],
            'all parameters and config set' => [
                ['source' => ['type' => 'orm']], ['customer_class' => $class, 'customer_id' => 1], true, true
            ]
        ];
    }

    public function testVisitDatasource()
    {
        $customerClass   = TestEntity::class;
        $customerField   = ExtendHelper::buildAssociationName(
            $customerClass,
            CustomerScope::ASSOCIATION_KIND
        );
        $customerId      = 1;
        $customerIdParam = sprintf(':customerIdParam_%s', $customerField);
        $qb              = $this->prepareQueryBuilder(Opportunity::class, $customerField, $customerId, $customerIdParam, 'o');
        $datasource      = $this->getDatasource($qb);
        $config          = DatagridConfiguration::create([]);

        $this->extension->setParameters(
            new ParameterBag(['customer_class' => $customerClass, 'customer_id' => $customerId])
        );
        $this->extension->visitDatasource($config, $datasource);
    }

    /**
     * @expectedException \Oro\Bundle\DataGridBundle\Exception\DatasourceException
     * @expectedExceptionMessage Couldn't find Opportunities alias in QueryBuilder.
     */
    public function testVisitDatasourceNotFoundOpportunityFrom()
    {
        $qb            = $this->prepareQueryBuilder(TestEntity::class);
        $datasource    = $this->getDatasource($qb);
        $config        = DatagridConfiguration::create([]);
        $this->extension->setParameters(
            new ParameterBag(['customer_class' => TestEntity::class])
        );
        $this->extension->visitDatasource($config, $datasource);
    }

    /**
     * @param bool|null $enabledConfig
     * @param array     $parameters
     */
    protected function prepareEntityConfigs(array $parameters, $enabledConfig = null)
    {
        if (null !== $enabledConfig) {
            $this->opportunityProvider
                ->expects($this->once())
                ->method('hasConfig')
                ->with($parameters['customer_class'])
                ->willReturn($enabledConfig);
            if ($enabledConfig) {
                $configId = new EntityConfigId('opportunity', $parameters['customer_class']);
                $config   = new Config($configId, ['enabled' => true]);
                $this->opportunityProvider
                    ->expects($this->once())
                    ->method('getConfig')
                    ->with($parameters['customer_class'])
                    ->willReturn($config);
            }
        }
    }

    /**
     * @param string      $opportunityClass
     * @param string      $customerField
     * @param int|null    $customerId
     * @param string|null $customerIdParam
     * @param string|null $alias
     *
     * @return QueryBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    public function prepareQueryBuilder(
        $opportunityClass,
        $customerField = null,
        $customerId = null,
        $customerIdParam = null,
        $alias = null
    ) {
        $qb = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $qb->expects($this->never())
            ->method('getDQLPart');
        $from = $this->getMockBuilder('Doctrine\ORM\Query\Expr\From')
            ->disableOriginalConstructor()
            ->getMock();
        $from->expects($this->once())
            ->method('getFrom')
            ->will($this->returnValue($opportunityClass));
        $qb = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $qb->expects($this->once())
            ->method('getDQLPart')
            ->with('from')
            ->will($this->returnValue([$from]));
        if (null !== $alias) {
            $from->expects($this->once())
                ->method('getAlias')
                ->will($this->returnValue($alias));
            $qb->expects($this->once())
                ->method('andWhere')
                ->with(sprintf('o.%s = %s', $customerField, $customerIdParam))
                ->will($this->returnSelf());
            $qb->expects($this->once())
                ->method('setParameter')
                ->with($customerIdParam, $customerId);
        }

        return $qb;
    }

    /**
     * @param QueryBuilder|\PHPUnit_Framework_MockObject_MockObject $qb
     *
     * @return OrmDatasource|\PHPUnit_Framework_MockObject_MockObject
     */
    public function getDatasource($qb)
    {
        $datasource = $this->getMockBuilder('Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource')
            ->disableOriginalConstructor()
            ->getMock();
        $datasource->expects($this->once())
            ->method('getQueryBuilder')
            ->willReturn($qb);

        return $datasource;
    }
}
