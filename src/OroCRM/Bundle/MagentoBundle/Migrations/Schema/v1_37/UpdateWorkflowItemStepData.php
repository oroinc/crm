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
        return 'Update steps for b2c_flow_order_follow_up workflow items from called or emailed to the not_contacted.';
    }

    /**
     * {@inheritdoc}
     */
    public function execute(LoggerInterface $logger)
    {
        $notContactedId = $this->getB2CFlowOrderNotContactedId($logger);

        $sql = 'UPDATE oro_workflow_item i' .
               ' SET i.current_step_id = :not_contacted_id' .
               ' WHERE i.workflow_name = :b2c_flow_order_follow_up' .
               ' AND i.current_step_id IN(' .
                    ' SELECT s.id FROM oro_workflow_step s' .
                    ' WHERE s.workflow_name = :b2c_flow_order_follow_up' .
                    ' AND (s.name = :emailed OR s.name = :called)' .
               ' )';

        $params = [
            'not_contacted_id'         => $notContactedId,
            'b2c_flow_order_follow_up' => 'b2c_flow_order_follow_up',
            'emailed'                  => 'emailed',
            'called'                   => 'called'
        ];

        $types = [
            'not_contacted_id'         => Type::INTEGER,
            'b2c_flow_order_follow_up' => Type::STRING,
            'emailed'                  => Type::STRING,
            'called'                   => Type::STRING,
        ];

        $this->logQuery($logger, $sql, $params, $types);
        return $this->connection->executeUpdate($sql, $params, $types);

    }

    /**
     * @param LoggerInterface $logger
     *
     * @return int
     */
    protected function getB2CFlowOrderNotContactedId(LoggerInterface $logger)
    {
        $sql    = 'SELECT s.id FROM oro_workflow_step s' .
                  ' WHERE s.workflow_name = :b2c_flow_order_follow_up' .
                  ' AND s.name = :not_contacted';
        $params = [
            'b2c_flow_order_follow_up' => 'b2c_flow_order_follow_up',
            'not_contacted'            => 'not_contacted',
        ];
        $types  = [
            'b2c_flow_order_follow_up' => Type::STRING,
            'not_contacted'            => Type::STRING,
        ];
        $this->logQuery($logger, $sql, $params, $types);

        return $this->connection->fetchColumn($sql, $params, 0, $types);
    }
}
