<?php

namespace Oro\Bundle\SalesBundle\Migrations\Schema\v1_23;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;
use Oro\Bundle\MigrationBundle\Migration\ArrayLogger;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedMigrationQuery;
use Psr\Log\LoggerInterface;

class UpdateWorkflowItemStepData extends ParametrizedMigrationQuery
{
    /** @var array */
    protected $steps;

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
     * @param bool            $dryRun
     */
    protected function doExecute(LoggerInterface $logger, $dryRun = false)
    {
        $this->updateWorkflowName($logger, $dryRun);
        $this->updateWorkflowSteps($logger, $dryRun);
        $this->updateWorkflowTransitionLogs($logger, $dryRun);
        $this->updateOpportunitySteps($logger, $dryRun);
    }

    /**
     * @param LoggerInterface $logger
     * @param bool            $dryRun
     */
    protected function updateWorkflowName(LoggerInterface $logger, $dryRun)
    {
        $workflowFields = implode(',', $this->getWorkflowFieldsByWorkflow($logger, 'b2b_flow_sales', ['name']));

        $params = [
            'old_workflow_name' => 'b2b_flow_sales',
            'new_workflow_name' => 'opportunity_flow'
        ];

        $types = [
            'old_workflow_name' => Types::STRING,
            'new_workflow_name' => Types::STRING,
        ];

        $queries = [
            // Copy workflow definition for new opportunity flow
            'INSERT INTO oro_workflow_definition (name,' . $workflowFields . ')' .
            ' SELECT ' .
                ':new_workflow_name as name,' . $workflowFields .
            ' FROM oro_workflow_definition WHERE name = :old_workflow_name',

            'UPDATE oro_workflow_step SET workflow_name = :new_workflow_name WHERE workflow_name = :old_workflow_name',
            'UPDATE oro_workflow_item SET workflow_name = :new_workflow_name WHERE workflow_name = :old_workflow_name',
        ];

        foreach ($queries as $sql) {
            $this->logQuery($logger, $sql, $params, $types);
            if (!$dryRun) {
                $this->connection->executeStatement($sql, $params, $types);
            }
        }

        $params = ['old_workflow_name' => 'b2b_flow_sales'];
        $types  = ['old_workflow_name' => Types::STRING];

        // Delete old workflow definition
        $sql    = 'DELETE FROM oro_workflow_definition WHERE name = :old_workflow_name ';
        $this->logQuery($logger, $sql, $params, $types);
        if (!$dryRun) {
            $this->connection->executeStatement($sql, $params, $types);
        }
    }

    /**
     * @param LoggerInterface $logger
     * @param bool            $dryRun
     */
    protected function updateWorkflowSteps(LoggerInterface $logger, $dryRun)
    {
        $params = [
            [
                'old_name'      => 'qualify',
                'new_name'      => 'open',
                'new_label'     => 'Open',
                'final'         => false,
                'workflow_name' => 'opportunity_flow',
            ],
            [
                'old_name'      => 'develop',
                'new_name'      => 'won',
                'new_label'     => 'Won',
                'final'         => true,
                'workflow_name' => 'opportunity_flow',
            ],
            [
                'old_name'      => 'close',
                'new_name'      => 'lost',
                'new_label'     => 'Lost',
                'final'         => true,
                'workflow_name' => 'opportunity_flow',
            ],
        ];

        $types = [
            'old_name'      => Types::STRING,
            'new_name'      => Types::STRING,
            'new_label'     => Types::STRING,
            'final'         => Types::BOOLEAN,
            'workflow_name' => Types::STRING,
        ];

        $sql = 'UPDATE oro_workflow_step SET name = :new_name, label = :new_label, is_final = :final' .
               ' WHERE workflow_name = :workflow_name AND name = :old_name';
        foreach ($params as $param) {
            $this->logQuery($logger, $sql, $param, $types);
            $this->connection->executeStatement($sql, $param, $types);
        }
    }

    /**
     * @param LoggerInterface $logger
     * @param bool            $dryRun
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function updateWorkflowTransitionLogs(LoggerInterface $logger, $dryRun)
    {
        // Delete old workflow transition logs
        $params = [
            'step_from_id' => $this->getStepIdByName($logger, 'open'),
            'step_to_id'   => $this->getStepIdByName($logger, 'won'),
        ];
        $types = [
            'step_from_id' => Types::INTEGER,
            'step_to_id'   => Types::INTEGER,
        ];
        $sql = 'DELETE FROM oro_workflow_transition_log' .
               ' WHERE step_from_id = :step_from_id AND step_to_id = :step_to_id';
        $this->logQuery($logger, $sql, $params, $types);
        if (!$dryRun) {
            $this->connection->executeStatement($sql, $params, $types);
        }

        // Update current step for workflow items from won to open
        $params = [
            'new_current_step_id' => $this->getStepIdByName($logger, 'open'),
            'old_current_step_id' => $this->getStepIdByName($logger, 'won'),
        ];
        $types = [
            'new_current_step_id' => Types::INTEGER,
            'old_current_step_id' => Types::INTEGER,
        ];
        $sql = 'UPDATE oro_workflow_item SET current_step_id = :new_current_step_id' .
               ' WHERE current_step_id = :old_current_step_id';
        $this->logQuery($logger, $sql, $params, $types);
        if (!$dryRun) {
            $this->connection->executeStatement($sql, $params, $types);
        }

        // Update old requalify transition to reopen
        $params = [
            'new_transition'  => 'reopen',
            'old_transitions' => ['requalify_lost', 'requalify_won'],
            'step_to_id'      => $this->getStepIdByName($logger, 'open'),
        ];
        $types = [
            'new_transition'  => Types::STRING,
            'old_transitions' => Connection::PARAM_STR_ARRAY,
            'step_to_id'      => Types::INTEGER,
        ];
        $sql = 'UPDATE oro_workflow_transition_log SET transition = :new_transition' .
               ' WHERE transition IN (:old_transitions) AND step_to_id = :step_to_id';
        $this->logQuery($logger, $sql, $params, $types);
        if (!$dryRun) {
            $this->connection->executeStatement($sql, $params, $types);
        }

        // Define and specify won step
        $params = [
            'new_transition' => 'close_won',
            'old_transition' => 'close_as_won',
            'new_step_to_id' => $this->getStepIdByName($logger, 'won'),
            'old_step_to_id' => $this->getStepIdByName($logger, 'lost'),
            'step_from_id'   => $this->getStepIdByName($logger, 'open')
        ];
        $types = [
            'new_transition' => Types::STRING,
            'old_transition' => Types::STRING,
            'new_step_to_id' => Types::INTEGER,
            'old_step_to_id' => Types::INTEGER,
            'step_from_id'   => Types::INTEGER,
        ];
        $sql = 'UPDATE oro_workflow_transition_log' .
               ' SET transition = :new_transition, step_to_id = :new_step_to_id, step_from_id = :step_from_id' .
               ' WHERE step_to_id = :old_step_to_id AND transition = :old_transition';
        $this->logQuery($logger, $sql, $params, $types);
        if (!$dryRun) {
            $this->connection->executeStatement($sql, $params, $types);
        }

        // Update old lost transition
        $params = [
            'new_transition' => 'close_lost',
            'old_transition' => 'close_as_lost',
            'step_to_id'     => $this->getStepIdByName($logger, 'lost'),
            'step_from_id'   => $this->getStepIdByName($logger, 'open')
        ];
        $types = [
            'new_transition' => Types::STRING,
            'old_transition' => Types::STRING,
            'step_to_id'     => Types::INTEGER,
            'step_from_id'   => Types::INTEGER,
        ];
        $sql = 'UPDATE oro_workflow_transition_log SET transition = :new_transition, step_from_id = :step_from_id' .
               ' WHERE step_to_id = :step_to_id AND transition = :old_transition';
        $this->logQuery($logger, $sql, $params, $types);
        if (!$dryRun) {
            $this->connection->executeStatement($sql, $params, $types);
        }

        // Update current steps in won workflows items
        $params = [
            'transition'      => 'close_won',
            'current_step_id' => $this->getStepIdByName($logger, 'won'),
            'workflow_name'   => 'opportunity_flow'
        ];
        $types = [
            'transition'      => Types::STRING,
            'current_step_id' => Types::INTEGER,
            'workflow_name'   => Types::STRING
        ];
        $sql = <<<SQL
                UPDATE oro_workflow_item
                SET current_step_id = :current_step_id
                WHERE id IN (
                    SELECT
                        tl.workflow_item_id
                    FROM
                        oro_workflow_transition_log tl
                    WHERE tl.id =
                          (
                            SELECT MAX(id)
                            FROM oro_workflow_transition_log
                            WHERE tl.workflow_item_id = workflow_item_id
                           ) AND tl.transition = :transition
                ) AND workflow_name = :workflow_name
SQL;
        $this->logQuery($logger, $sql, $params, $types);
        if (!$dryRun) {
            $this->connection->executeStatement($sql, $params, $types);
        }
    }

    /**
     * @param LoggerInterface $logger
     * @param bool            $dryRun
     */
    protected function updateOpportunitySteps(LoggerInterface $logger, $dryRun)
    {
        $params = [
            [
                'new_workflow_step_id' => $this->getStepIdByName($logger, 'open'),
                'old_workflow_step_id' => $this->getStepIdByName($logger, 'won'),
                'status_id'            => 'in_progress',
            ],
            [
                'new_workflow_step_id' => $this->getStepIdByName($logger, 'won'),
                'old_workflow_step_id' => $this->getStepIdByName($logger, 'lost'),
                'status_id'            => 'won',
            ]
        ];

        $types = [
            'new_workflow_step_id' => Types::INTEGER,
            'old_workflow_step_id' => Types::INTEGER,
            'status_id'            => Types::STRING,
        ];

        $sql = 'UPDATE orocrm_sales_opportunity SET workflow_step_id = :new_workflow_step_id' .
               ' WHERE workflow_step_id = :old_workflow_step_id AND status_id = :status_id';

        foreach ($params as $param) {
            $this->logQuery($logger, $sql, $param, $types);
            if (!$dryRun) {
                $this->connection->executeStatement($sql, $param, $types);
            }
        }
    }

    /**
     * @param LoggerInterface $logger
     * @param string          $name
     *
     * @return int
     */
    protected function getStepIdByName(LoggerInterface $logger, $name)
    {
        if (empty($this->steps)) {
            $this->steps = $this->getWorkflowSteps($logger);
        }

        $steps = array_filter(
            $this->steps,
            function ($val) use ($name) {
                return $val['name'] === $name;
            }
        );

        return reset($steps)['id'];
    }

    /**
     * @param LoggerInterface $logger
     *
     * @return array of ['id' => 'workflow step id', 'name' => 'workflow step name' ]
     */
    protected function getWorkflowSteps(LoggerInterface $logger)
    {
        $params = [
            'workflow_name' => 'opportunity_flow',
        ];
        $types  = [
            'workflow_name' => Types::STRING,
        ];
        $sql    = 'SELECT s.id, s.name FROM oro_workflow_step s WHERE s.workflow_name = :workflow_name';
        $this->logQuery($logger, $sql, $params, $types);

        return $this->connection->fetchAll($sql, $params, $types);
    }

    /**
     * @param LoggerInterface $logger
     * @param string $name
     * @param array $exclude
     * @return array
     */
    protected function getWorkflowFieldsByWorkflow(LoggerInterface $logger, $name, array $exclude)
    {
        $params = ['workflow_name' => $name];
        $types  = ['workflow_name' => Types::STRING];

        $sql = 'SELECT * FROM oro_workflow_definition WHERE name = :workflow_name LIMIT 1';
        $this->logQuery($logger, $sql, $params, $types);

        $fields = $this->connection->executeQuery($sql, $params, $types)->fetch();

        foreach ($exclude as $field) {
            unset($fields[$field]);
        }

        return array_keys($fields);
    }
}
