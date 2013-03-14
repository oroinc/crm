<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Action;

use Oro\Bundle\GridBundle\Action\AbstractAction;

class AbstractActionTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AbstractAction
     */
    protected $model;

    protected function tearDown()
    {
        unset($this->model);
    }

    /**
     * Prepare all constructor argument mocks
     *
     * @param array $arguments
     * @return array
     */
    protected function getAbstractActionArguments($arguments = array())
    {
        $defaultArguments = array(
            'router'     => $this->getMock('Symfony\Component\Routing\RouterInterface'),
            'aclManager' => $this->getMock('Oro\Bundle\UserBundle\Acl\ManagerInterface'),
        );

        return array_merge($defaultArguments, $arguments);
    }
}
