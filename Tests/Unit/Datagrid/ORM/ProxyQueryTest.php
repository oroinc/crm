<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Datagrid\ORM;

use Oro\Bundle\GridBundle\Datagrid\ORM\ProxyQuery;

class ProxyQueryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProxyQuery
     */
    protected $model;

    protected function tearDown()
    {
        unset($this->model);
    }

    public function testGetQueryBuilder()
    {
        $queryBuilderMock = $this->getMock('Doctrine\ORM\QueryBuilder', array(), array(), '', false);
        $this->model = new ProxyQuery($queryBuilderMock);
        $this->assertEquals($queryBuilderMock, $this->model->getQueryBuilder());
    }
}
