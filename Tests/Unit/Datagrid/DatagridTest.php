<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Datagrid;

use Oro\Bundle\GridBundle\Datagrid\Datagrid;

class DatagridTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test grid name
     */
    const TEST_NAME = 'defaultName';

    /**
     * @var Datagrid
     */
    protected $datagrid;

    protected function tearDown()
    {
        unset($this->datagrid);
    }

    /**
     * Prepare all constructor argument mocks for datagrid and create
     *
     * @param array $arguments
     */
    protected function initializeDatagridMock($arguments = array())
    {
        $defaultArguments = array(
            'query'          => $this->getMock('Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface'),
            'columns'        => $this->getMock('Oro\Bundle\GridBundle\Field\FieldDescriptionCollection'),
            'pager'          => $this->getMock('Oro\Bundle\GridBundle\Datagrid\PagerInterface'),
            'formBuilder'    => $this->getMock('Symfony\Component\Form\FormBuilder', array(), array(), '', false),
            'routeGenerator' => $this->getMock('Oro\Bundle\GridBundle\Route\RouteGeneratorInterface'),
            'parameters'     => $this->getMock('Oro\Bundle\GridBundle\Datagrid\ParametersInterface'),
            'name'           => null
        );

        $arguments = array_merge($defaultArguments, $arguments);

        $this->datagrid = new Datagrid(
            $arguments['query'],
            $arguments['columns'],
            $arguments['pager'],
            $arguments['formBuilder'],
            $arguments['routeGenerator'],
            $arguments['parameters'],
            $arguments['name']
        );
    }

    public function testGetName()
    {
        $this->initializeDatagridMock(array('name' => self::TEST_NAME));
        $this->assertEquals(self::TEST_NAME, $this->datagrid->getName());
    }
}
