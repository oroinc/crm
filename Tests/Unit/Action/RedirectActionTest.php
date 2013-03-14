<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Action;

use Oro\Bundle\GridBundle\Action\ActionInterface;
use Oro\Bundle\GridBundle\Action\RedirectAction;

class RedirectActionTest extends AbstractActionTestCase
{
    /**
     * Prepare redirect action model
     *
     * @param array $arguments
     */
    protected function initializeAbstractActionMock($arguments = array())
    {
        $arguments = $this->getAbstractActionArguments($arguments);
        $this->model = new RedirectAction($arguments['router'], $arguments['aclManager']);
    }

    public function testGetType()
    {
        $this->initializeAbstractActionMock();

        $this->assertEquals(ActionInterface::TYPE_REDIRECT, $this->model->getType());
    }
}
