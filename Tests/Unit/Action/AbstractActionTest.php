<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Action;

use Oro\Bundle\GridBundle\Action\ActionUrlGeneratorInterface;

class AbstractActionTest extends AbstractActionTestCase
{
    /**
     * Test parameters
     */
    const TEST_NAME          = 'test_name';
    const TEST_ACL_RESOURCE  = 'test_acl_resource';
    const TEST_ROUTE_NAME    = 'test_route_name';
    const TEST_ROUTE_PATTERN = '/test/route/{parameter}';

    /**
     * @var array
     */
    protected $testOptions = array('key' => 'value');

    /**
     * Prepare abstract action model
     *
     * @param array $arguments
     */
    protected function initializeAbstractActionMock($arguments = array())
    {
        $arguments = $this->getAbstractActionArguments($arguments);
        $this->model = $this->getMockForAbstractClass('Oro\Bundle\GridBundle\Action\AbstractAction', $arguments);
    }

    public function testSetName()
    {
        $this->initializeAbstractActionMock();

        $this->model->setName(self::TEST_NAME);
        $this->assertAttributeEquals(self::TEST_NAME, 'name', $this->model);
    }

    public function testGetName()
    {
        $this->initializeAbstractActionMock();

        $this->model->setName(self::TEST_NAME);
        $this->assertEquals(self::TEST_NAME, $this->model->getName());
    }

    public function testSetAclResource()
    {
        $this->initializeAbstractActionMock();

        $this->model->setAclResource(self::TEST_ACL_RESOURCE);
        $this->assertAttributeEquals(self::TEST_ACL_RESOURCE, 'aclResource', $this->model);
    }

    public function testGetAclResource()
    {
        $this->initializeAbstractActionMock();

        $this->model->setAclResource(self::TEST_ACL_RESOURCE);
        $this->assertEquals(self::TEST_ACL_RESOURCE, $this->model->getAclResource());
    }

    public function testSetOptions()
    {
        $this->initializeAbstractActionMock();

        $this->model->setOptions($this->testOptions);
        $this->assertAttributeEquals($this->testOptions, 'options', $this->model);
    }

    /**
     * Prepares mocks for route, route collection and router
     *
     * @return ActionUrlGeneratorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function prepareUrlGeneratorMock()
    {
        $urlGenerator = $this->getMockForAbstractClass(
            'Oro\Bundle\GridBundle\Action\ActionUrlGeneratorInterface',
            array(),
            '',
            false,
            true,
            true,
            array('generate')
        );
        $urlGenerator->expects($this->any())
            ->method('generate')
            ->will($this->returnCallback(array($this, 'generateUrl')));

        return $urlGenerator;
    }

    /**
     * @param string $routeName
     * @param array $parameters
     * @param array $placeholders
     * @return string
     */
    public function generateUrl($routeName, array $parameters = array(), array $placeholders = array())
    {
        $this->assertEquals(self::TEST_ROUTE_NAME, $routeName);
        return str_replace(array_keys($parameters), array_values($parameters), self::TEST_ROUTE_PATTERN);
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage There is no option "route" for action "test_name".
     */
    public function testGetOptionsNoRouteOption()
    {
        $this->initializeAbstractActionMock();

        $this->model->setName(self::TEST_NAME);
        $this->model->setOptions(array());
        $this->model->getOptions();
    }

    /**
     * Data provider for testGetOptions
     *
     * @return array
     */
    public function getOptionsDataProvider()
    {
        return array(
            'no_parameters_no_placeholders' => array(
                '$sourceOptions' => array(
                    'route' => self::TEST_ROUTE_NAME,
                ),
                '$expectedOptions' => array(
                    'url'          => self::TEST_ROUTE_PATTERN,
                    'placeholders' => array()
                )
            ),
            'with_parameters_with_placeholders' => array(
                '$sourceOptions' => array(
                    'route'        => self::TEST_ROUTE_NAME,
                    'parameters'   => array(
                        '{parameter}' => 'parameter_key'
                    ),
                    'placeholders' => array(
                        'place'  => 'place_key',
                        'holder' => 'holder_key',
                    )
                ),
                '$expectedOptions' => array(
                    'url'          => str_replace('{parameter}', 'parameter_key', self::TEST_ROUTE_PATTERN),
                    'placeholders' => array(
                        '{place}'  => 'place_key',
                        '{holder}' => 'holder_key',
                    )
                )
            ),
        );
    }

    /**
     * @param array $sourceOptions
     * @param array $expectedOptions
     * @dataProvider getOptionsDataProvider
     */
    public function testGetOptions(array $sourceOptions, array $expectedOptions)
    {
        $this->initializeAbstractActionMock(array('urlGenerator' => $this->prepareUrlGeneratorMock()));

        $this->model->setOptions($sourceOptions);
        $this->assertEquals($expectedOptions, $this->model->getOptions());
    }

    /**
     * Data provider for testIsGranted
     *
     * @return array
     */
    public function isGrantedDataProvider()
    {
        return array(
            'resource_granted' => array(
                '$isGranted' => true,
                '$expected'  => true,
            ),
            'resource_not_granted' => array(
                '$isGranted' => false,
                '$expected'  => false,
            ),
            'no_resource' => array(
                '$isGranted' => null,
                '$expected'  => true,
            ),
        );
    }

    /**
     * @param boolean $isGranted
     * @param boolean $expected
     * @dataProvider isGrantedDataProvider
     */
    public function testIsGranted($isGranted, $expected)
    {
        $aclManagerMock = $this->getMockForAbstractClass(
            'Oro\Bundle\UserBundle\Acl\ManagerInterface',
            array(),
            '',
            false,
            true,
            true,
            array('isResourceGranted')
        );
        if ($isGranted !== null) {
            $aclManagerMock->expects($this->once())
                ->method('isResourceGranted')
                ->with(self::TEST_ACL_RESOURCE)
                ->will($this->returnValue($isGranted));
        }

        $this->initializeAbstractActionMock(array('aclManager' => $aclManagerMock));

        if ($isGranted !== null) {
            $this->model->setAclResource(self::TEST_ACL_RESOURCE);
        }

        $this->assertEquals($expected, $this->model->isGranted());
    }
}
