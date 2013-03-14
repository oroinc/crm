<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Action;

use Oro\Bundle\GridBundle\Action\ActionInterface;
use Oro\Bundle\GridBundle\Action\DeleteAction;

class DeleteActionTest extends AbstractActionTestCase
{
    /**
     * Prepare redirect action model
     *
     * @param array $arguments
     */
    protected function initializeAbstractActionMock($arguments = array())
    {
        $arguments = $this->getAbstractActionArguments($arguments);
        $this->model = new DeleteAction($arguments['router'], $arguments['aclManager']);
    }

    public function testGetType()
    {
        $this->initializeAbstractActionMock();

        $this->assertEquals(ActionInterface::TYPE_DELETE, $this->model->getType());
    }
}
