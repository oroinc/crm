<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\Stubs\Model;

use Oro\Bundle\TestFrameworkBundle\Test\Doctrine\ORM\Mocks\DatabasePlatformMock;

class ConnectionMock extends \Doctrine\DBAL\Connection
{
    private $platformMock;

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
}
