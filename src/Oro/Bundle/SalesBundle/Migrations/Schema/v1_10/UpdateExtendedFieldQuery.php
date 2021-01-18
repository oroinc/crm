<?php

namespace Oro\Bundle\SalesBundle\Migrations\Schema\v1_10;

use Oro\Bundle\MigrationBundle\Migration\ArrayLogger;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedMigrationQuery;
use Psr\Log\LoggerInterface;

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
        $className = 'Oro\Bundle\AccountBundle\Entity\Account';
        $classConfig = $this->loadEntityConfigData($logger, $className);
        if ($classConfig) {
            $data = $this->connection->convertToPHPValue($classConfig['data'], 'array');
            $fields = [
                'extend_website',
                'extend_employees',
                'extend_ownership',
                'extend_ticker_symbol',
                'extend_rating'
            ];

            foreach ($fields as $field) {
                unset($data['extend']['index'][$field]);
                unset($data['extend']['schema']['property'][$field]);
                unset($data['extend']['schema']['doctrine']['Extend\Entity\ExtendAccount']['fields'][$field]);
            }

            $query = 'UPDATE oro_entity_config SET data = :data WHERE id = :id';
            $params = ['data' => $data, 'id' => $classConfig['id']];
            $types = ['data' => 'array', 'id' => 'integer'];
            $this->logQuery($logger, $query, $params, $types);
            if (!$dryRun) {
                $this->connection->executeStatement($query, $params, $types);
            }
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
        $sql = 'SELECT ec.id, ec.data FROM oro_entity_config ec WHERE ec.class_name = :class';
        $params = ['class' => $className];
        $types  = ['class' => 'string'];
        $this->logQuery($logger, $sql, $params, $types);

        $rows = $this->connection->fetchAll($sql, $params, $types);

        return reset($rows);
    }
}
