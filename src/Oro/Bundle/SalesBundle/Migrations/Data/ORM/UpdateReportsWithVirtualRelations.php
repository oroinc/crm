<?php

namespace Oro\Bundle\SalesBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\DistributionBundle\Handler\ApplicationState;
use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Updates old Query Designer definitions for opportunities and leads to new virtual fields to
 * make work existing (old) definitions
 */
class UpdateReportsWithVirtualRelations extends AbstractFixture implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        if ($this->container->get(ApplicationState::class)->isInstalled()) {
            try {
                /** @var Connection $connection */
                $connection = $this->container->get('doctrine.dbal.default_connection');
                $connection->beginTransaction();
                $connection->executeQuery("UPDATE oro_report 
                SET definition = REPLACE(definition, 'opportunities+', 'opportunities_virtual+');");

                $connection->executeQuery("UPDATE oro_report 
                SET definition = REPLACE(definition, 'leads+', 'leads_virtual+');");

                $connection->executeQuery("UPDATE oro_segment 
                SET definition = REPLACE(definition, 'opportunities+', 'opportunities_virtual+');");

                $connection->executeQuery("UPDATE oro_segment
                SET definition = REPLACE(definition, 'leads+', 'leads_virtual+');");

                //Removes field configs which is not present in Opportunity anymore
                $fields = ['budgetAmount', 'budget_amount_value', 'closeRevenue', 'close_revenue_value'];

                $classId = $connection->executeQuery(
                    'SELECT id FROM oro_entity_config WHERE class_name = :class',
                    ['class' => 'Oro\Bundle\SalesBundle\Entity\Opportunity'],
                    ['class' => Types::STRING]
                )->fetchColumn();

                $connection->executeQuery(
                    'DELETE FROM oro_entity_config_field WHERE field_name IN (:fields)
                    AND entity_id = :id',
                    ['id' => $classId, 'fields' => $fields],
                    ['id' => Types::INTEGER, 'fields' => Connection::PARAM_STR_ARRAY]
                );
                $connection->commit();
            } catch (\Exception $exception) {
                $connection->rollBack();

                throw $exception;
            }
            /** @var ConfigManager $cm */
            $cm = $this->container->get('oro_entity_config.config_manager');
            $cm->clearCache();
        }
    }
}
