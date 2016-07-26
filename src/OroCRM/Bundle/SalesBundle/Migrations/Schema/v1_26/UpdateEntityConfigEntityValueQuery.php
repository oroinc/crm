<?php

namespace OroCRM\Bundle\SalesBundle\Migrations\Schema\v1_26;

use Psr\Log\LoggerInterface;

use Doctrine\DBAL\Types\Type;

use Oro\Bundle\EntityConfigBundle\Migration\UpdateEntityConfigEntityValueQuery as BaseQuery;

class UpdateEntityConfigEntityValueQuery extends BaseQuery
{
    /**
     * @var string
     */
    protected $oldCode;

    /**
     * {@inheritdoc}
     * @param string $oldCode
     */
    public function __construct($entityName, $scope, $code, $value, $oldCode)
    {
        parent::__construct($entityName, $scope, $code, $value);

        $this->oldCode = $oldCode;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(LoggerInterface $logger)
    {
        $this->updateEntityConfig($logger);
    }

    /**
     * @param LoggerInterface $logger
     */
    protected function updateEntityConfig(LoggerInterface $logger)
    {
        $sql = 'SELECT data FROM oro_entity_config WHERE class_name = ? LIMIT 1';
        $parameters = [$this->entityName];
        $data = $this->connection->fetchColumn($sql, $parameters);
        $this->logQuery($logger, $sql, $parameters);

        $data = $data ? $this->connection->convertToPHPValue($data, Type::TARRAY) : [];
        $data[$this->scope][$this->code] = (array)$data[$this->scope][$this->oldCode];
        unset($data[$this->scope][$this->oldCode]);
        $data = $this->connection->convertToDatabaseValue($data, Type::TARRAY);

        $sql = 'UPDATE oro_entity_config SET data = ? WHERE class_name = ?';
        $parameters = [$data, $this->entityName];
        $statement = $this->connection->prepare($sql);
        $statement->execute($parameters);
        $this->logQuery($logger, $sql, $parameters);
    }
}
