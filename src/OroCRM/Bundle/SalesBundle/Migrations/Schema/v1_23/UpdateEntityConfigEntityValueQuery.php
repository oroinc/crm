<?php

namespace OroCRM\Bundle\SalesBundle\Migrations\Schema\v1_23;

use Psr\Log\LoggerInterface;

use Doctrine\DBAL\Types\Type;

use Oro\Bundle\EntityConfigBundle\Migration\UpdateEntityConfigEntityValueQuery as BaseQuery;

class UpdateEntityConfigEntityValueQuery extends BaseQuery
{
    /**
     * @var string
     */
    protected $oldValue;

    /**
     * {@inheritdoc}
     * @param string $oldValue
     */
    public function __construct($entityName, $scope, $code, $value, $oldValue)
    {
        parent::__construct($entityName, $scope, $code, $value);

        $this->oldValue = $oldValue;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(LoggerInterface $logger)
    {
        if ($this->isEqualOldValue($logger)) {
            parent::execute($logger);
        }
    }

    /**
     * Checks if need to update workflow configuration
     *
     * @param LoggerInterface $logger
     *
     * @return bool
     */
    public function isEqualOldValue(LoggerInterface $logger)
    {
        $sql        = 'SELECT data FROM oro_entity_config WHERE class_name = ? LIMIT 1';
        $parameters = [$this->entityName];
        $data       = $this->connection->fetchColumn($sql, $parameters);
        $this->logQuery($logger, $sql, $parameters);

        $data = $data ? $this->connection->convertToPHPValue($data, Type::TARRAY) : [];
        if (isset($data[$this->scope][$this->code]) && $data[$this->scope][$this->code] === $this->oldValue) {
            return true;
        }

        return false;
    }
}
