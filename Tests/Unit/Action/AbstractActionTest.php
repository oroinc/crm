<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Action;

class AbstractActionTest extends AbstractActionTestCase
{
    /**
     * Test parameters
     */
    const TEST_NAME         = 'test_name';
    const TEST_ACL_RESOURCE = 'test_acl_resource';

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
}
