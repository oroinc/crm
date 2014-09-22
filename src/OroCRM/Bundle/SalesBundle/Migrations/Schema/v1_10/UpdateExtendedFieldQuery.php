<?php

namespace Oro\Bundle\UserBundle\Migrations\Schema\v1_7;

use Psr\Log\LoggerInterface;

use Oro\Bundle\MigrationBundle\Migration\ArrayLogger;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedMigrationQuery;

class UpdateExtendedFieldQuery extends ParametrizedMigrationQuery
{
    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        $logger = new ArrayLogger();
        $this->doExecute($logger, true);

        return $logger->getMessages();
    }

    /**
     * {@inheritdoc}
     */
    public function execute(LoggerInterface $logger)
    {
        $this->doExecute($logger);
    }

    /**
     * @param LoggerInterface $logger
     * @param bool            $dryRun
     */
    public function doExecute(LoggerInterface $logger, $dryRun = false)
    {
        $className = 'OroCRM\Bundle\AccountBundle\Entity\Account';
        $classConfig = $this->loadEntityConfigData($logger, $className);
        $data = $this->connection->convertToPHPValue($classConfig['data'], 'array');

        unset($data['extend']['index']['extend_website']);
        unset($data['extend']['index']['extend_employees']);
        unset($data['extend']['index']['extend_ownership']);
        unset($data['extend']['index']['extend_ticker_symbol']);
        unset($data['extend']['index']['extend_rating']);
        unset($data['extend']['schema']['property']['extend_website']);
        unset($data['extend']['schema']['property']['extend_employees']);
        unset($data['extend']['schema']['property']['extend_ownership']);
        unset($data['extend']['schema']['property']['extend_ticker_symbol']);
        unset($data['extend']['schema']['property']['extend_rating']);
        unset($data['extend']['schema']['doctrine']['Extend\Entity\ExtendAccount']['fields']['extend_website']);
        unset($data['extend']['schema']['doctrine']['Extend\Entity\ExtendAccount']['fields']['extend_employees']);
        unset($data['extend']['schema']['doctrine']['Extend\Entity\ExtendAccount']['fields']['extend_ownership']);
        unset($data['extend']['schema']['doctrine']['Extend\Entity\ExtendAccount']['fields']['extend_ticker_symbol']);
        unset($data['extend']['schema']['doctrine']['Extend\Entity\ExtendAccount']['fields']['extend_rating']);

        $query  = 'UPDATE oro_entity_config SET data = :data WHERE id = :id';
        $params = ['data' => $data, 'id' => $classConfig['id']];
        $types  = ['data' => 'array', 'id' => 'integer'];
        $this->logQuery($logger, $query, $params, $types);
        if (!$dryRun) {
            $this->connection->executeUpdate($query, $params, $types);
        }
    }

    /**
     * @param LoggerInterface $logger
     * @param string          $className
     *
     * @return array
     */
    protected function loadEntityConfigData(LoggerInterface $logger, $className)
    {
        $sql = 'SELECT ec.id, ec.data'
            . ' FROM oro_entity_config ec'
            . ' WHERE ec.class_name = :class';
        $params = ['class' => $className];
        $types  = ['class' => 'string'];
        $this->logQuery($logger, $sql, $params, $types);

        $rows = $this->connection->fetchAll($sql, $params, $types);

        return $rows[0];
    }
}
