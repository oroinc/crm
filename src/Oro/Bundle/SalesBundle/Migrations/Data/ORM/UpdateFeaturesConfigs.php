<?php

namespace Oro\Bundle\SalesBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\ConfigBundle\Config\GlobalScopeManager;
use Oro\Bundle\DistributionBundle\Handler\ApplicationState;
use Oro\Bundle\SalesBundle\Entity\Lead;
use Oro\Bundle\SalesBundle\Entity\Opportunity;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Enable corresponding feature for listed classes if at least one active channel contains them, otherwise disable them.
 *
 *  | Class                                      | Feature                               |
 *  | Oro\Bundle\SalesBundle\Entity\Lead         | oro_sales.lead_feature_enabled        |
 *  | Oro\Bundle\SalesBundle\Entity\Opportunity  | oro_sales.opportunity_feature_enabled |
 */
class UpdateFeaturesConfigs extends AbstractFixture implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * List of classes for which feature will be enabled if at least one active channel contains them,
     * otherwise disable them.
     */
    protected array $featuresClasses = [
        Lead::class        => 'oro_sales.lead_feature_enabled',
        Opportunity::class => 'oro_sales.opportunity_feature_enabled',
    ];

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        if ($this->container->get(ApplicationState::class)->isInstalled()) {
            $configManager = $this->getConfigManager();

            $classes = $this->getEnabledFeatureClasses();
            foreach (array_keys($this->featuresClasses) as $className) {
                $value = \in_array($className, $classes, true);
                $configManager->set($this->featuresClasses[$className], $value);
            }
            $configManager->flush();

            $this->clearChannelsConfigurations();
        }
    }

    protected function getEnabledFeatureClasses(): array
    {
        $query = <<<SQL
SELECT DISTINCT(e.name)
FROM orocrm_channel c
JOIN orocrm_channel_entity_name e ON e.channel_id = c.id
WHERE c.status = :status AND e.name IN(:classes);
SQL;
        $params = [
            'classes' => array_keys($this->featuresClasses),
            'status'  => true,
        ];
        $types = [
            'classes' => Connection::PARAM_STR_ARRAY,
            'status'  => Types::BOOLEAN,
        ];

        $entities = $this->getConnection()
            ->executeQuery($query, $params, $types)
            ->fetchAll(\PDO::FETCH_NUM);

        return array_map('current', $entities);
    }

    protected function clearChannelsConfigurations(): void
    {
        $this->getConnection()->executeStatement(
            'DELETE FROM orocrm_channel_entity_name WHERE name IN (:classes)',
            [
                'classes' => [
                    'Oro\Bundle\SalesBundle\Entity\Lead',
                    'Oro\Bundle\SalesBundle\Entity\Opportunity',
                ],
            ],
            [
                'classes' => Connection::PARAM_STR_ARRAY,
            ]
        );
    }

    protected function getConfigManager(): GlobalScopeManager
    {
        return $this->container->get('oro_config.global');
    }

    protected function getConnection(): Connection
    {
        return $this->container->get('doctrine.dbal.default_connection');
    }
}
