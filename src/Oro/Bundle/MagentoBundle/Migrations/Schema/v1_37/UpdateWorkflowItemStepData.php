<?php

namespace Oro\Bundle\MagentoBundle\Migrations\Schema\v1_37;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedMigrationQuery;
use Psr\Log\LoggerInterface;

class UpdateWorkflowItemStepData extends ParametrizedMigrationQuery
{
    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return 'Update steps for b2c_flow_order_follow_up and b2c_flow_abandoned_shopping_cart workflows.';
    }

    /**
     * {@inheritdoc}
     */
    public function execute(LoggerInterface $logger)
    {
        $this->updateB2CFlowOrderData($logger);
        $this->updateB2CFlowAbandonedShoppingCart($logger);
    }

    /**
     * @param LoggerInterface $logger
     */
    protected function updateB2CFlowOrderData(LoggerInterface $logger)
    {
        // Delete unused transition logs.
        $params = [
            'workflow_name' => 'b2c_flow_order_follow_up',
            'transitions'   => ['no_reply', 'log_call', 'send_email']
        ];
        $types = [
            'workflow_name' => Types::STRING,
            'transitions'   => Connection::PARAM_STR_ARRAY
        ];
        $sql = 'DELETE FROM oro_workflow_transition_log' .
               ' WHERE workflow_item_id IN (' .
                   'SELECT i.id FROM oro_workflow_item i' .
                   ' WHERE i.workflow_name = :workflow_name' .
               ')' .
               ' AND transition IN (:transitions)';

        $this->logQuery($logger, $sql, $params, $types);
        $this->connection->executeUpdate($sql, $params, $types);

        $notContactedId = $this->getB2CFlowOrderNotContactedId($logger);

        // Update step_from_id for transition logs.
        $params = [
            'not_contacted_id' => $notContactedId,
            'workflow_name'    => 'b2c_flow_order_follow_up',
            'transition'       => 'record_feedback'
        ];
        $types  = [
            'not_contacted_id' => Types::INTEGER,
            'workflow_name'    => Types::STRING,
            'transition'       => Types::STRING
        ];
        $sql = 'UPDATE oro_workflow_transition_log' .
               ' SET step_from_id = :not_contacted_id' .
               ' WHERE workflow_item_id IN (' .
                   'SELECT i.id FROM oro_workflow_item i' .
                   ' WHERE i.workflow_name = :workflow_name' .
               ') AND transition = :transition';
        $this->logQuery($logger, $sql, $params, $types);
        $this->connection->executeUpdate($sql, $params, $types);

        $params = [
            'not_contacted_id' => $notContactedId,
            'workflow_name'    => 'b2c_flow_order_follow_up',
            'names'            => ['emailed', 'called']
        ];
        $types  = [
            'not_contacted_id' => Types::INTEGER,
            'workflow_name'    => Types::STRING,
            'names'            => Connection::PARAM_STR_ARRAY
        ];

        // Update current_step_id for workflow items.
        $sql = 'UPDATE oro_workflow_item' .
               ' SET current_step_id = :not_contacted_id' .
               ' WHERE workflow_name = :workflow_name' .
               ' AND current_step_id IN (' .
                   'SELECT s.id FROM oro_workflow_step s' .
                   ' WHERE s.workflow_name = :workflow_name' .
                   ' AND s.name IN (:names)' .
               ' )';
        $this->logQuery($logger, $sql, $params, $types);
        $this->connection->executeUpdate($sql, $params, $types);

        // Update workflow_step_id for oro_magento_order.
        $sql = 'UPDATE orocrm_magento_order' .
               ' SET workflow_step_id = :not_contacted_id' .
               ' WHERE workflow_step_id IN (' .
                   'SELECT s.id FROM oro_workflow_step s' .
                   ' WHERE s.workflow_name = :workflow_name' .
                   ' AND s.name IN (:names)' .
               ' )';
        $this->logQuery($logger, $sql, $params, $types);
        $this->connection->executeUpdate($sql, $params, $types);
    }

    /**
     * @param LoggerInterface $logger
     *
     * @return int
     */
    protected function getB2CFlowOrderNotContactedId(LoggerInterface $logger)
    {
        $params = [
            'workflow_name' => 'b2c_flow_order_follow_up',
            'name'          => 'not_contacted'
        ];
        $types  = [
            'workflow_name' => Types::STRING,
            'name'          => Types::STRING
        ];
        $sql = 'SELECT s.id FROM oro_workflow_step s' .
               ' WHERE s.workflow_name = :workflow_name' .
               ' AND s.name = :name';
        $this->logQuery($logger, $sql, $params, $types);

        return $this->connection->fetchColumn($sql, $params, 0, $types);
    }

    /**
     * @param LoggerInterface $logger
     */
    protected function updateB2CFlowAbandonedShoppingCart(LoggerInterface $logger)
    {
        // Delete unused transition logs.
        $params = [
            'workflow_name' => 'b2c_flow_abandoned_shopping_cart',
            'transitions'   => [
                'send_email',
                'log_call',
                'send_email_from_converted',
                'log_call_from_converted',
                'contacted'
            ]
        ];
        $types  = [
            'workflow_name' => Types::STRING,
            'transitions'   => Connection::PARAM_STR_ARRAY
        ];
        $sql = 'DELETE FROM oro_workflow_transition_log' .
               ' WHERE workflow_item_id IN (' .
                   'SELECT i.id FROM oro_workflow_item i' .
                   ' WHERE i.workflow_name = :workflow_name' .
               ')' .
               ' AND transition IN (:transitions)';

        $this->logQuery($logger, $sql, $params, $types);
        $this->connection->executeUpdate($sql, $params, $types);

        $openId = $this->getB2CFlowAbandonedCartOpenId($logger);
        $params = [
            'open_id'       => $openId,
            'workflow_name' => 'b2c_flow_abandoned_shopping_cart',
            'name'          => 'contacted'
        ];
        $types  = [
            'open_id'       => Types::INTEGER,
            'workflow_name' => Types::STRING,
            'name'          => Types::STRING
        ];

        // Update step_from_id for transition logs.
        $sql = 'UPDATE oro_workflow_transition_log' .
               ' SET step_from_id = :open_id' .
               ' WHERE step_from_id IN (' .
                   'SELECT s.id FROM oro_workflow_step s' .
                   ' WHERE s.workflow_name = :workflow_name' .
                   ' AND s.name = :name' .
               ' )';
        $this->logQuery($logger, $sql, $params, $types);
        $this->connection->executeUpdate($sql, $params, $types);

        // Update current_step_id for workflow items.
        $sql = 'UPDATE oro_workflow_item' .
               ' SET current_step_id = :open_id' .
               ' WHERE workflow_name = :workflow_name' .
               ' AND current_step_id IN (' .
                   'SELECT s.id FROM oro_workflow_step s' .
                   ' WHERE s.workflow_name = :workflow_name' .
                   ' AND s.name = :name' .
               ' )';
        $this->logQuery($logger, $sql, $params, $types);
        $this->connection->executeUpdate($sql, $params, $types);

        // Update workflow_step_id for oro_magento_cart.
        $sql = 'UPDATE orocrm_magento_cart ' .
               ' SET workflow_step_id = :open_id' .
               ' WHERE workflow_step_id IN (' .
                   'SELECT s.id FROM oro_workflow_step s' .
                   ' WHERE s.workflow_name = :workflow_name' .
                   ' AND s.name = :name' .
               ' )';
        $this->logQuery($logger, $sql, $params, $types);
        $this->connection->executeUpdate($sql, $params, $types);
    }

    /**
     * @param LoggerInterface $logger
     *
     * @return int
     */
    protected function getB2CFlowAbandonedCartOpenId(LoggerInterface $logger)
    {
        $params = [
            'workflow_name' => 'b2c_flow_abandoned_shopping_cart',
            'name'          => 'open'
        ];
        $types  = [
            'workflow_name' => Types::STRING,
            'name'          => Types::STRING
        ];
        $sql = 'SELECT s.id FROM oro_workflow_step s' .
               ' WHERE s.workflow_name = :workflow_name' .
               ' AND s.name = :name';
        $this->logQuery($logger, $sql, $params, $types);

        return $this->connection->fetchColumn($sql, $params, 0, $types);
    }
}
