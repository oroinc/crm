<?php

namespace OroCRM\Bundle\CallBundle\Tests\Unit\Entity;

use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\DataGridBundle\Event\BuildAfter;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use OroCRM\Bundle\CallBundle\EventListener\Datagrid\CallListener;
use Oro\Bundle\UserBundle\Entity\User;

class CallListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CallListener
     */
    protected $listener;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $entityManager;

    protected function setUp()
    {
        $this->entityManager = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->listener = new CallListener($this->entityManager);
    }

    protected function tearDown()
    {
        unset($this->entityManager);
        unset($this->listener);
    }

    /**
     * @param array $parameters
     * @param array $expectedUnsets
     * @dataProvider onBuildBeforeDataProvider
     */
    public function testOnBuildBefore(array $parameters, array $expectedUnsets = array())
    {
        $buildBeforeEvent = $this->createBuildBeforeEvent($expectedUnsets, $parameters);
        $this->listener->onBuildBefore($buildBeforeEvent);
    }

    /**
     * @return array
     */
    public function onBuildBeforeDataProvider()
    {
        return array(
            'no filters' => array(
                'parameters' => array(),
            ),
            'filter by contact' => array(
                'parameters' => array(
                    'contactId' => 1,
                ),
                'expectedUnsets' => array(
                    '[columns][contactName]',
                    '[filters][columns][contactName]',
                    '[sorters][columns][contactName]',
                ),
            ),
            'filter by account' => array(
                'parameters' => array(
                    'accountId' => 1,
                ),
                'expectedUnsets' => array(
                    '[columns][accountName]',
                    '[filters][columns][accountName]',
                    '[sorters][columns][accountName]',
                ),
            ),
        );
    }

    /**
     * @param array $parameters
     * @param array $entityManagerExpectations
     * @param array $queryBuilderExpectations
     * @dataProvider onBuildAfterDataProvider
     */
    public function testOnBuildAfter(
        array $parameters,
        array $entityManagerExpectations = array(),
        array $queryBuilderExpectations = array()
    ) {
        $queryBuilder = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $this->applyExpectations($this->entityManager, $entityManagerExpectations);
        $this->applyExpectations($queryBuilder, $queryBuilderExpectations);

        $buildAfterEvent = $this->createBuildAfterEvent($queryBuilder, $parameters);
        $this->listener->onBuildAfter($buildAfterEvent);
    }

    /**
     * @return array
     */
    public function onBuildAfterDataProvider()
    {
        $user = new User();

        return array(
            'no filters' => array(
                'parameters' => array(),
            ),
            'filter by user' => array(
                'parameters' => array(
                    'userId' => 12,
                ),
                'entityManagerExpectations' => array(
                    0 => array(
                        'method' => 'find',
                        'parameters' => array('OroUserBundle:User', 12),
                        'return' => $user,
                    )
                ),
                'queryBuilderExpectations' => array(
                    0 => array(
                        'method' => 'andWhere',
                        'parameters' => array('call.owner = :user'),
                    ),
                    1 => array(
                        'method' => 'setParameter',
                        'parameters' => array('user', $user),
                    )
                ),
            ),
        );
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject $mock
     * @param array $expectations
     */
    protected function applyExpectations(
        \PHPUnit_Framework_MockObject_MockObject $mock,
        array $expectations = array()
    ) {
        foreach ($expectations as $number => $expectation) {
            $mocker = $mock->expects($this->at($number))
                ->method($expectation['method']);

            if (!empty($expectation['parameters'])) {
                call_user_func_array(array($mocker, 'with'), $expectation['parameters']); // ->with(<parameters>)
            }

            if (!empty($expectation['return'])) {
                $mocker->will($this->returnValue($expectation['return']));
            } else {
                $mocker->will($this->returnSelf());
            }
        }
    }

    /**
     * @param array $expectedUnsets
     * @param array $parametersData
     * @return BuildBefore
     */
    protected function createBuildBeforeEvent(array $expectedUnsets, array $parameters)
    {
        $config = $this->getMockBuilder('Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration')
            ->setMethods(array('offsetUnsetByPath'))
            ->disableOriginalConstructor()
            ->getMock();

        if ($expectedUnsets) {
            foreach ($expectedUnsets as $iteration => $value) {
                $config->expects($this->at($iteration))->method('offsetUnsetByPath')->with($value);
            }
        } else {
            $config->expects($this->never())->method('offsetUnsetByPath');
        }

        $dataGrid = $this->getMock('Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface');

        $dataGrid->expects($this->any())
            ->method('getParameters')
            ->will($this->returnValue($this->createParameterBag($parameters)));

        return new BuildBefore($dataGrid, $config);
    }

    /**
     * @param QueryBuilder|\PHPUnit_Framework_MockObject_MockObject $queryBuilder
     * @param array $parameters
     * @return BuildAfter
     */
    protected function createBuildAfterEvent($queryBuilder, array $parameters)
    {
        $ormDataSource = $this->getMockBuilder('Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource')
            ->disableOriginalConstructor()
            ->setMethods(array('getQueryBuilder'))
            ->getMock();
        $ormDataSource->expects($this->any())
            ->method('getQueryBuilder')
            ->will($this->returnValue($queryBuilder));

        $dataGrid = $this->getMock('Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface');

        $dataGrid->expects($this->any())
            ->method('getDatasource')
            ->will($this->returnValue($ormDataSource));

        $dataGrid->expects($this->any())
            ->method('getParameters')
            ->will($this->returnValue($this->createParameterBag($parameters)));

        return new BuildAfter($dataGrid, $parameters);
    }

    /**
     * @param array $data
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function createParameterBag(array $data)
    {
        $parameters = $this->getMock('Oro\Bundle\DataGridBundle\Datagrid\ParameterBag');

        $parameters->expects($this->any())
            ->method('has')
            ->will(
                $this->returnCallback(
                    function ($key) use ($data) {
                        return isset($data[$key]);
                    }
                )
            );

        $parameters->expects($this->any())
            ->method('get')
            ->will(
                $this->returnCallback(
                    function ($key) use ($data) {
                        return $data[$key];
                    }
                )
            );

        return $parameters;
    }
}
