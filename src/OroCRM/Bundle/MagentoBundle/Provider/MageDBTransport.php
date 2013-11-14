<?php

namespace OroCRM\Bundle\MagentoBundle\Provider;

use Doctrine\Bundle\DoctrineBundle\ConnectionFactory;
use Doctrine\DBAL\Connection;

use Oro\Bundle\IntegrationBundle\Provider\TransportInterface;

/**
 * Magento DB transport
 * used to fetch and pull data to/from Magento instance
 * with sessionId param using direct DB access
 *
 * @package Oro\Bundle\IntegrationBundle\Provider\Magento
 */
class MageDBTransport implements TransportInterface
{
    /** @var ConnectionFactory */
    protected $connFactory;

    /** @var Connection */
    protected $conn;

    /**
     * @param ConnectionFactory $connFactory
     */
    public function __construct(ConnectionFactory $connFactory)
    {
        $this->connFactory = $connFactory;
    }

    /**
     * @param array $settings
     * @return bool|mixed
     */
    public function init(array $settings)
    {
        $this->conn = $this->connFactory->createConnection($settings);

        return false;
    }

    /**
     * @param $action
     * @param $params
     * @return mixed
     */
    public function call($action, $params = [])
    {
        // TODO: based on action create query and return array data
        switch ($action) {
            case 'customerCustomerList':
                $qb = $this->conn->createQueryBuilder();
                // query configuration, etc
                break;
            case 'customerCustomerInfo':
                break;
            case 'customerAddressList':
                break;
            case 'customerGroupList':
                break;
            default:
                break;
        }
    }
}
