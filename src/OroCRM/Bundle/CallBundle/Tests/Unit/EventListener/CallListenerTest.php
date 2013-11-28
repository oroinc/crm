<?php

namespace OroCRM\Bundle\CallBundle\Tests\Unit\Entity;

use Symfony\Component\HttpFoundation\Request;

use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\DataGridBundle\Event\BuildAfter;
use OroCRM\Bundle\CallBundle\EventListener\Datagrid\CallListener;

use Oro\Bundle\UserBundle\Entity\User;
use OroCRM\Bundle\ContactBundle\Entity\Contact;
use OroCRM\Bundle\AccountBundle\Entity\Account;

class CallListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CallListener
     */
    protected $listener;

    /**
     * @var RequestParameters
     */
    protected $requestParameters;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $entityManager;

    protected function setUp()
    {
        $this->requestParameters = new RequestParameters();
        $this->requestParameters->setRequest(new Request());

        $this->entityManager = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->listener = new CallListener($this->requestParameters, $this->entityManager);
    }

    protected function tearDown()
    {
        unset($this->requestParameters);
        unset($this->entityManager);
        unset($this->listener);
    }

    /**
     * @param array $request
     * @param array $entityManagerExpectations
     * @param array $queryBuilderExpectations
     * @dataProvider onBuildAfterDataProvider
     */
    public function testOnBuildAfter(
        array $request,
        array $entityManagerExpectations = array(),
        array $queryBuilderExpectations = array()
    ) {
        $queryBuilder = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        foreach ($request as $key => $value) {
            $this->requestParameters->set($key, $value);
        }

        $this->applyExpectations($this->entityManager, $entityManagerExpectations);
        $this->applyExpectations($queryBuilder, $queryBuilderExpectations);

        $buildAfterEvent = $this->createBuildAfterEvent($queryBuilder);
        $this->listener->onBuildAfter($buildAfterEvent);
    }

    /**
     * @return array
     */
    public function onBuildAfterDataProvider()
    {
        $user = new User();
        $contact = new Contact();
        $account = new Account();

        return array(
            'no filters' => array(
                'request' => array(),
            ),
            'filter by user' => array(
                'request' => array(
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
            'filter by contact' => array(
                'request' => array(
                    'contactId' => 13,
                ),
                'entityManagerExpectations' => array(
                    0 => array(
                        'method' => 'find',
                        'parameters' => array('OroCRMContactBundle:Contact', 13),
                        'return' => $contact,
                    )
                ),
                'queryBuilderExpectations' => array(
                    0 => array(
                        'method' => 'andWhere',
                        'parameters' => array('call.relatedContact = :contact'),
                    ),
                    1 => array(
                        'method' => 'setParameter',
                        'parameters' => array('contact', $contact),
                    )
                ),
            ),
            'filter by account' => array(
                'request' => array(
                    'accountId' => 14,
                ),
                'entityManagerExpectations' => array(
                    0 => array(
                        'method' => 'find',
                        'parameters' => array('OroCRMAccountBundle:Account', 14),
                        'return' => $account,
                    )
                ),
                'queryBuilderExpectations' => array(
                    0 => array(
                        'method' => 'andWhere',
                        'parameters'
                        => array('(call.relatedAccount = :account OR :account MEMBER OF contact.accounts)'),
                    ),
                    1 => array(
                        'method' => 'setParameter',
                        'parameters' => array('account', $account),
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
     * @param QueryBuilder|\PHPUnit_Framework_MockObject_MockObject $queryBuilder
     * @return BuildAfter
     */
    protected function createBuildAfterEvent($queryBuilder)
    {
        $ormDataSource = $this->getMockBuilder('Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource')
            ->disableOriginalConstructor()
            ->setMethods(array('getQueryBuilder'))
            ->getMock();
        $ormDataSource->expects($this->any())
            ->method('getQueryBuilder')
            ->will($this->returnValue($queryBuilder));

        $dataGrid = $this->getMockBuilder('Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface')
            ->getMockForAbstractClass();
        $dataGrid->expects($this->any())
            ->method('getDatasource')
            ->will($this->returnValue($ormDataSource));

        return new BuildAfter($dataGrid);
    }
}
