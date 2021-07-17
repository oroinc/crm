<?php

namespace Oro\Bundle\ActivityContactBundle\Migrations\Schema\v1_0;

use Doctrine\DBAL\Types\Types;
use Oro\Bundle\ActivityContactBundle\EntityConfig\ActivityScope;
use Oro\Bundle\MigrationBundle\Migration\ArrayLogger;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedMigrationQuery;
use Psr\Log\LoggerInterface;

class UpdateConfigQuery extends ParametrizedMigrationQuery
{
    /**
     * {@inheritDoc}
     */
    public function getDescription()
    {
        $logger = new ArrayLogger();
        $this->doExecute($logger, true);

        return $logger->getMessages();
    }

    /**
     * {@inheritDoc}
     */
    public function execute(LoggerInterface $logger)
    {
        $this->doExecute($logger);
    }

    /**
     * @param LoggerInterface $logger
     * @param bool            $dryRun
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function doExecute(LoggerInterface $logger, $dryRun = false)
    {
        $className = 'Oro\Bundle\UserBundle\Entity\User';
        if ($className) {
            $sql = 'SELECT e.data FROM oro_entity_config as e WHERE e.class_name = ? LIMIT 1';
            $entityRow = $this->connection->fetchAssoc($sql, [$className]);
            if ($entityRow) {
                $this->updateEntityData($className, $entityRow['data'], $logger);
            }
        }
    }

    /**
     * @param string $className
     * @param string $data
     * @param LoggerInterface $logger
     */
    protected function updateEntityData($className, $data, $logger)
    {
        $data = $data ? $this->connection->convertToPHPValue($data, Types::ARRAY) : [];

        foreach (array_keys(ActivityScope::$fieldsConfiguration) as $fieldName) {
            if (isset($data['extend']['schema']['property'][$fieldName])) {
                unset($data['extend']['schema']['property'][$fieldName]);
            }
            $entityName = $data['extend']['schema']['entity'];
            if (isset($data['extend']['schema']['doctrine'][$entityName]['fields'][$fieldName])) {
                unset($data['extend']['schema']['doctrine'][$entityName]['fields'][$fieldName]);
            }
        }

        $data = $this->connection->convertToDatabaseValue($data, Types::ARRAY);

        $this->executeQuery(
            $logger,
            'UPDATE oro_entity_config SET data = ? WHERE class_name = ?',
            [$data, $className]
        );
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function executeQuery(LoggerInterface $logger, $sql, array $parameters = [])
    {
        $statement = $this->connection->prepare($sql);
        $statement->execute($parameters);
        $this->logQuery($logger, $sql, $parameters);
    }
}
