<?php

namespace OroCRM\Bundle\MagentoBundle\Migrations\Schema\v1_37;

use Psr\Log\LoggerInterface;

use Doctrine\DBAL\Types\Type;

use Oro\Bundle\MigrationBundle\Migration\ParametrizedMigrationQuery;

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
        $sql = 'DELETE FROM oro_workflow_transition_log' .
               ' WHERE workflow_item_id IN (' .
                   'SELECT i.id FROM oro_workflow_item i' .
                   ' WHERE i.workflow_name = \'b2c_flow_order_follow_up\'' .
               ')' .
               ' AND transition IN (\'no_reply\', \'log_call\', \'send_email\')';
        $this->logQuery($logger, $sql);
        $this->connection->executeUpdate($sql);

        $notContactedId = $this->getB2CFlowOrderNotContactedId($logger);
        $params = ['not_contacted_id' => $notContactedId];
        $types  = ['not_contacted_id' => Type::INTEGER];

        // Update step_from_id for transition logs.
        $sql = 'UPDATE oro_workflow_transition_log' .
               ' SET step_from_id = :not_contacted_id' .
               ' WHERE workflow_item_id IN (' .
                   'SELECT i.id FROM oro_workflow_item i' .
                   ' WHERE i.workflow_name = \'b2c_flow_order_follow_up\'' .
               ') AND transition = \'record_feedback\'';
        $this->logQuery($logger, $sql, $params, $types);
        $this->connection->executeUpdate($sql, $params, $types);

        // Update current_step_id for workflow items.
        $sql = 'UPDATE oro_workflow_item' .
               ' SET current_step_id = :not_contacted_id' .
               ' WHERE workflow_name = \'b2c_flow_order_follow_up\'' .
               ' AND current_step_id IN (' .
                   'SELECT s.id FROM oro_workflow_step s' .
                   ' WHERE s.workflow_name = \'b2c_flow_order_follow_up\'' .
                   ' AND s.name IN (\'emailed\', \'called\')' .
               ' )';
        $this->logQuery($logger, $sql, $params, $types);
        $this->connection->executeUpdate($sql, $params, $types);

        // Update workflow_step_id for orocrm_magento_order.
        $sql = 'UPDATE orocrm_magento_order' .
               ' SET workflow_step_id = :not_contacted_id' .
               ' WHERE workflow_step_id IN (' .
                   'SELECT s.id FROM oro_workflow_step s' .
                   ' WHERE s.workflow_name = \'b2c_flow_order_follow_up\'' .
                   ' AND s.name IN (\'emailed\', \'called\')' .
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
        $sql = 'SELECT s.id FROM oro_workflow_step s' .
               ' WHERE s.workflow_name = \'b2c_flow_order_follow_up\'' .
               ' AND s.name = \'not_contacted\'';
        $this->logQuery($logger, $sql);

        return $this->connection->fetchColumn($sql);
    }

    /**
     * @param LoggerInterface $logger
     */
    protected function updateB2CFlowAbandonedShoppingCart(LoggerInterface $logger)
    {
        // Delete unused transition logs.
        $sql = 'DELETE FROM oro_workflow_transition_log' .
               ' WHERE workflow_item_id IN (' .
                   'SELECT i.id FROM oro_workflow_item i' .
                   ' WHERE i.workflow_name = \'b2c_flow_abandoned_shopping_cart\'' .
               ')' .
               ' AND transition IN (' .
                   ' \'send_email\',' .
                   ' \'log_call\',' .
                   ' \'send_email_from_converted\',' .
                   ' \'log_call_from_converted\',' .
                   ' \'contacted\'' .
               ')';
        $this->logQuery($logger, $sql);
        $this->connection->executeUpdate($sql);

        $openId = $this->getB2CFlowAbandonedCartOpenId($logger);
        $params = ['open_id' => $openId];
        $types  = ['open_id' => Type::INTEGER];

        // Update step_from_id for transition logs.
        $sql = 'UPDATE oro_workflow_transition_log' .
               ' SET step_from_id = :open_id' .
               ' WHERE step_from_id IN (' .
                   'SELECT s.id FROM oro_workflow_step s' .
                   ' WHERE s.workflow_name = \'b2c_flow_abandoned_shopping_cart\'' .
                   ' AND s.name = \'contacted\'' .
               ' )';
        $this->logQuery($logger, $sql, $params, $types);
        $this->connection->executeUpdate($sql, $params, $types);

        // Update current_step_id for workflow items.
        $sql = 'UPDATE oro_workflow_item' .
               ' SET current_step_id = :open_id' .
               ' WHERE workflow_name = \'b2c_flow_abandoned_shopping_cart\'' .
               ' AND current_step_id IN (' .
                   'SELECT s.id FROM oro_workflow_step s' .
                   ' WHERE s.workflow_name = \'b2c_flow_abandoned_shopping_cart\'' .
                   ' AND s.name = \'contacted\'' .
               ' )';
        $this->logQuery($logger, $sql, $params, $types);
        $this->connection->executeUpdate($sql, $params, $types);

        // Update workflow_step_id for orocrm_magento_cart.
        $sql = 'UPDATE orocrm_magento_cart ' .
               ' SET workflow_step_id = :open_id' .
               ' WHERE workflow_step_id IN (' .
                   'SELECT s.id FROM oro_workflow_step s' .
                   ' WHERE s.workflow_name = \'b2c_flow_abandoned_shopping_cart\'' .
                   ' AND s.name IN (\'contacted\')' .
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
        $sql = 'SELECT s.id FROM oro_workflow_step s' .
               ' WHERE s.workflow_name = \'b2c_flow_abandoned_shopping_cart\'' .
               ' AND s.name = \'open\'';
        $this->logQuery($logger, $sql);

        return $this->connection->fetchColumn($sql);
    }
}
