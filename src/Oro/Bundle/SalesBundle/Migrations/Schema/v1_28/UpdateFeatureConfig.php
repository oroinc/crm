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
    protected $enabledClassToFeatureMap = [
        'Oro\Bundle\SalesBundle\Entity\Lead'        => 'oro_sales.lead_feature_enabled',
        'Oro\Bundle\SalesBundle\Entity\Opportunity' => 'oro_sales.opportunity_feature_enabled',
    ];

    /** @var array */
    protected $disabledClassToFeatureMap = [
        'Oro\Bundle\SalesBundle\Entity\SalesFunnel' => 'oro_sales.salesfunnel_feature_enabled',
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
        $classToFeature = array_merge($this->enabledClassToFeatureMap, $this->disabledClassToFeatureMap);
        $disabledClasses = $this->getNewValues($logger);
        foreach ($disabledClasses as $newValue => $classNames) {
            foreach ($classNames as $className) {
                $this->setFeature($logger, $classToFeature[$className], $newValue, $dryRun);
            }
        }
    }

    /**
     * @param LoggerInterface $logger
     * @param string $featureToggle
     * @param bool $dryRun
     */
    protected function setFeature(LoggerInterface $logger, $featureToggle, $value, $dryRun = false)
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
            'text_value'   => $value,
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
     * @return array
     */
    protected function getNewValues(LoggerInterface $logger)
    {
        $query = <<<SQL
SELECT DISTINCT(e.name)
FROM orocrm_channel c
JOIN orocrm_channel_entity_name e ON e.channel_id = c.id
WHERE c.status = :status AND e.name IN(:entityClasses);
SQL;
        $params = [
            'entityClasses' => array_keys(array_merge(
                $this->enabledClassToFeatureMap,
                $this->disabledClassToFeatureMap
            )),
            'status'        => true,
        ];
        $types = [
            'entityClasses' => Connection::PARAM_STR_ARRAY,
            'status'        => \PDO::PARAM_BOOL,
        ];

        $this->logQuery($logger, $query, $params, $types);
        $entities = $this->connection->executeQuery($query, $params, $types)
            ->fetchAll(\PDO::FETCH_NUM);

        $activeEntities = array_map('current', $entities);

        return [
            '0' => array_diff(
                array_keys($this->enabledClassToFeatureMap),
                $activeEntities
            ),
            '1' => array_intersect(
                array_keys($this->disabledClassToFeatureMap),
                $activeEntities
            )
        ];
    }
}
