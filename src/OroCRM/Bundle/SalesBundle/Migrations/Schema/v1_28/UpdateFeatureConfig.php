<?php

namespace Oro\Bundle\SalesBundle\Migrations\Schema\v1_28;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;

use Psr\Log\LoggerInterface;

use Oro\Bundle\MigrationBundle\Migration\ArrayLogger;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedMigrationQuery;

class UpdateFeatureConfig extends ParametrizedMigrationQuery
{
    /** @var array */
    protected $classToFeatureMap = [
        'Oro\Bundle\SalesBundle\Entity\Lead'        => 'oro_crm_sales.lead_feature_enabled',
        'Oro\Bundle\SalesBundle\Entity\Opportunity' => 'oro_crm_sales.opportunity_feature_enabled',
    ];

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
     * @param bool $dryRun
     */
    protected function doExecute(LoggerInterface $logger, $dryRun = false)
    {
        $disabledClasses = $this->getDisabledClasses($logger);
        foreach ($disabledClasses as $className) {
            $this->disableFeature($logger, $this->classToFeatureMap[$className], $dryRun);
        }
    }

    /**
     * @param LoggerInterface $logger
     * @param string $featureToggle
     * @param bool $dryRun
     */
    protected function disableFeature(LoggerInterface $logger, $featureToggle, $dryRun = false)
    {
        list($section, $name) = explode('.', $featureToggle);

        $query = <<<SQL
INSERT INTO oro_config_value
    (config_id, name, section, text_value, object_value, array_value, type, created_at, updated_at)
SELECT
    c.id,
    :name,
    :section,
    :text_value,
    :object_value,
    :array_value,
    :type,
    :created_at,
    :created_at
FROM oro_config c WHERE entity = :app
SQL;
        $params = [
            'name'         => $name,
            'section'      => $section,
            'text_value'   => '0',
            'object_value' => null,
            'array_value'  => null,
            'type'         => 'scalar',
            'created_at'   => new \DateTime(),
            'app'          => 'app',
        ];
        $types = [
            'name'         => Type::STRING,
            'section'      => Type::STRING,
            'text_value'   => Type::STRING,
            'object_value' => Type::OBJECT,
            'array_value'  => Type::TARRAY,
            'type'         => Type::STRING,
            'created_at'   => Type::DATETIME,
            'app'          => Type::STRING,
        ];

        $this->logQuery($logger, $query, $params, $types);
        if (!$dryRun) {
            $this->connection->executeQuery($query, $params, $types);
        }
    }

    /**
     * @param LoggerInterface $logger
     *
     * @return string[]
     */
    protected function getDisabledClasses(LoggerInterface $logger)
    {
        $query = <<<SQL
SELECT DISTINCT(e.name)
FROM oro_channel c
JOIN oro_channel_entity_name e ON e.channel_id = c.id
WHERE c.status = :status AND e.name IN(:entityClasses);
SQL;
        $params = [
            'entityClasses' => array_keys($this->classToFeatureMap),
            'status'        => true,
        ];
        $types = [
            'entityClasses' => Connection::PARAM_STR_ARRAY,
            'status'        => \PDO::PARAM_BOOL,
        ];

        $this->logQuery($logger, $query, $params, $types);
        $entities = $this->connection->executeQuery($query, $params, $types)
            ->fetchAll(\PDO::FETCH_NUM);

        return array_diff(
            array_keys($this->classToFeatureMap),
            array_map('current', $entities)
        );
    }
}
