<?php

namespace OroCRM\Bundle\ContactUsBundle\Tests\Unit\Entity;

use OroCRM\Bundle\ContactUsBundle\Entity\ContactRequest;

class ContactRequestTest extends \PHPUnit_Framework_TestCase
{
    /** @var ContactRequest */
    protected $entity;

    protected function setUp()
    {
        $this->entity = new ContactRequest();
    }

    protected function tearDown()
    {
        unset($this->entity);
    }

    /**
     * @dataProvider getSetDataProvider
     */
    public function testGetSet($property, $value, $expected)
    {
        if ($value !== null) {
            call_user_func_array([$this->entity, 'set' . ucfirst($property)], [$value]);
        }

        $this->assertEquals($expected, call_user_func_array([$this->entity, 'get' . ucfirst($property)], []));
    }

    public function getSetDataProvider()
    {
        $organization = $this->getMock('Oro\Bundle\OrganizationBundle\Entity\Organization');

        return array(
            'owner' => array('owner', $organization, $organization),
        );
    }
}
