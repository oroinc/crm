<?php

namespace Oro\Bundle\SalesBundle\Migrations\Data\ORM;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

use Oro\Bundle\ConfigBundle\Config\GlobalScopeManager;
use Oro\Bundle\MigrationBundle\Fixture\VersionedFixtureInterface;

class UpdateFeaturesConfigs extends AbstractFixture implements ContainerAwareInterface, VersionedFixtureInterface
{
    use ContainerAwareTrait;

    /**
     * List of classes for which feature will be enabled if at least one active channel contains them,
     * otherwise disable them.
     *
     * @var array
     */
    protected $featuresClasses = [
        'Oro\Bundle\SalesBundle\Entity\Lead'        => 'oro_sales.lead_feature_enabled',
        'Oro\Bundle\SalesBundle\Entity\Opportunity' => 'oro_sales.opportunity_feature_enabled',
        'Oro\Bundle\SalesBundle\Entity\SalesFunnel' => 'oro_sales.salesfunnel_feature_enabled',
        'Oro\Bundle\SalesBundle\Entity\B2bCustomer' => 'oro_sales.b2bcustomer_feature_enabled',
    ];

    /**
     * {@inheritdoc}
     */
    public function getVersion()
    {
        return '1.1';
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        if ($this->container->hasParameter('installed') && $this->container->getParameter('installed')) {
            $configManager = $this->getConfigManager();

            $classes = $this->getEnabledFeatureClasses();
            foreach ($this->featuresClasses as $className => $featureName) {
                $value = in_array($className, $classes, true);
                $configManager->set($this->featuresClasses[$className], $value);
            }
            $configManager->flush();

            $this->clearChannelsConfigurations();
        }
    }

    /**
     * @return array
     */
    protected function getEnabledFeatureClasses()
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
            'status'  => Type::BOOLEAN,
        ];

        $entities = $this->getConnection()->executeQuery($query, $params, $types)
            ->fetchAll(\PDO::FETCH_NUM);

        $classes = array_map('current', $entities);

        return $classes;
    }

    protected function clearChannelsConfigurations()
    {
        $this->getConnection()->executeUpdate(
            'DELETE FROM orocrm_channel_entity_name WHERE name IN (:classes)',
            [
                'classes' => [
                    'Oro\Bundle\SalesBundle\Entity\Lead',
                    'Oro\Bundle\SalesBundle\Entity\Opportunity',
                    'Oro\Bundle\SalesBundle\Entity\B2bCustomer',
                ],
            ],
            [
                'classes' => Connection::PARAM_STR_ARRAY,
            ]
        );
    }

    /**
     * @return GlobalScopeManager
     */
    protected function getConfigManager()
    {
        return $this->container->get('oro_config.global');
    }

    /**
     * @return Connection
     */
    protected function getConnection()
    {
        return $this->container->get('doctrine.dbal.default_connection');
    }
}
