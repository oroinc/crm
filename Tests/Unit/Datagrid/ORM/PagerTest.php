<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Datagrid\ORM;

use Oro\Bundle\GridBundle\Datagrid\ORM\Pager;

class PagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Pager
     */
    protected $model;

    /**
     * @var array
     */
    protected $complexFields = array(
        'key1' => 'value1',
        'key2' => 'value2',
    );

    protected function setUp()
    {
        $this->model = new Pager();
    }

    protected function tearDown()
    {
        unset($this->model);
    }

    public function testSetComplexFields()
    {
        $this->assertAttributeEmpty('complexFields', $this->model);
        $this->model->setComplexFields($this->complexFields);
        $this->assertAttributeEquals($this->complexFields, 'complexFields', $this->model);
    }
}
