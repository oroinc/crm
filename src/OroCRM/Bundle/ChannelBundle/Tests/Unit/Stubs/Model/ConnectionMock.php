<?php

namespace OroCRM\Bundle\ChannelBundle\Tests\Unit\Stubs\Model;

use Oro\Bundle\TestFrameworkBundle\Test\Doctrine\ORM\Mocks\DatabasePlatformMock;

class ConnectionMock extends \Doctrine\DBAL\Connection
{
    private $fetchOneResult;
    private $platformMock;
    private $lastInsertId = 0;
    private $inserts = array();
    private $executeUpdates = array();

    public function __construct(array $params, $driver, $config = null, $eventManager = null)
    {
        $this->platformMock = new DatabasePlatformMock();

        parent::__construct($params, $driver, $config, $eventManager);

        // Override possible assignment of platform to database platform mock
        $this->_platform = $this->platformMock;
    }

    /**
     * @override
     */
    public function getDatabasePlatform()
    {
        return $this->platformMock;
    }

    /**
     * @override
     */
    public function insert($tableName, array $data, array $types = array())
    {
        $this->inserts[$tableName][] = $data;
    }

    /**
     * @override
     */
    /*public function executeUpdate($query, array $params = array(), array $types = array())
    {
        $this->executeUpdates[] = array('query' => $query, 'params' => $params, 'types' => $types);
    }*/

    /**
     * @override
     */
    public function lastInsertId($seqName = null)
    {
        return $this->lastInsertId;
    }

    /**
     * @override
     */
    public function fetchColumn($statement, array $params = array(), $colnum = 0)
    {
        return $this->fetchOneResult;
    }

    /**
     * @override
     */
    public function quote($input, $type = null)
    {
        if (is_string($input)) {
            return "'" . $input . "'";
        }
        return $input;
    }

    /* Mock API */

    public function setFetchOneResult($fetchOneResult)
    {
        $this->fetchOneResult = $fetchOneResult;
    }

    public function setDatabasePlatform($platform)
    {
        $this->platformMock = $platform;
    }

    public function setLastInsertId($id)
    {
        $this->lastInsertId = $id;
    }

    public function getInserts()
    {
        return $this->inserts;
    }

    public function getExecuteUpdates()
    {
        return $this->executeUpdates;
    }

    public function reset()
    {
        $this->inserts = array();
        $this->lastInsertId = 0;
    }
}
