<?php

namespace OroCRM\Bundle\SalesBundle\Migrations\Schema\v1_23;

use Psr\Log\LoggerInterface;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Connection;

use Oro\Bundle\MigrationBundle\Migration\ArrayLogger;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedMigrationQuery;

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
        $params = [
            'old_workflow_name' => 'b2b_flow_sales',
            'new_workflow_name' => 'opportunity_flow'
        ];

        $types = [
            'old_workflow_name' => Type::STRING,
            'new_workflow_name' => Type::STRING,
        ];

        $queries = [
            // Copy workflow definition for new opportunity flow
            'INSERT INTO oro_workflow_definition ' .
            ' SELECT ' .
                ':new_workflow_name as name,' .
                'start_step_id,' .
                'label,' .
                'related_entity,' .
                'entity_attribute_name,' .
                'steps_display_ordered,' .
                'system, configuration,' .
                'created_at, updated_at' .
            ' FROM oro_workflow_definition WHERE name = :old_workflow_name',

            'UPDATE oro_workflow_step SET workflow_name = :new_workflow_name WHERE workflow_name = :old_workflow_name',
            'UPDATE oro_workflow_item SET workflow_name = :new_workflow_name WHERE workflow_name = :old_workflow_name',
        ];

        foreach ($queries as $sql) {
            $this->logQuery($logger, $sql, $params, $types);
            if (!$dryRun) {
                $this->connection->executeUpdate($sql, $params, $types);
            }
        }

        $params = ['old_workflow_name' => 'b2b_flow_sales'];
        $types  = ['old_workflow_name' => Type::STRING];

        // Delete old workflow definition
        $sql    = 'DELETE FROM oro_workflow_definition WHERE name = :old_workflow_name ';
        $this->logQuery($logger, $sql, $params, $types);
        if (!$dryRun) {
            $this->connection->executeUpdate($sql, $params, $types);
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
                'workflow_name' => 'opportunity_flow',
            ],
            [
                'old_name'      => 'develop',
                'new_name'      => 'won',
                'workflow_name' => 'opportunity_flow',
            ],
            [
                'old_name'      => 'close',
                'new_name'      => 'lost',
                'workflow_name' => 'opportunity_flow',
            ],
        ];

        $types = [
            'old_name'      => Type::STRING,
            'new_name'      => Type::STRING,
            'workflow_name' => Type::STRING,
        ];

        $sql = 'UPDATE oro_workflow_step SET name = :new_name' .
               ' WHERE workflow_name = :workflow_name AND name = :old_name';
        foreach ($params as $param) {
            $this->logQuery($logger, $sql, $param, $types);
            $this->connection->executeUpdate($sql, $param, $types);
        }
    }

    /**
     * @param LoggerInterface $logger
     * @param bool            $dryRun
     */
    protected function updateWorkflowTransitionLogs(LoggerInterface $logger, $dryRun)
    {
        // Delete old workflow transition logs
        $params = [
            'step_from_id' => $this->getStepIdByName($logger, 'open'),
            'step_to_id'   => $this->getStepIdByName($logger, 'won'),
        ];
        $types = [
            'step_from_id' => Type::INTEGER,
            'step_to_id'   => Type::INTEGER,
        ];
        $sql = 'DELETE FROM oro_workflow_transition_log' .
               ' WHERE step_from_id = :step_from_id AND step_to_id = :step_to_id';
        $this->logQuery($logger, $sql, $params, $types);
        if (!$dryRun) {
            $this->connection->executeUpdate($sql, $params, $types);
        }

        // Update current step for workflow items from won to open
        $params = [
            'new_current_step_id' => $this->getStepIdByName($logger, 'open'),
            'old_current_step_id' => $this->getStepIdByName($logger, 'won'),
        ];
        $types = [
            'new_current_step_id' => Type::INTEGER,
            'old_current_step_id' => Type::INTEGER,
        ];
        $sql = 'UPDATE oro_workflow_item SET current_step_id = :new_current_step_id' .
               ' WHERE current_step_id = :old_current_step_id';
        $this->logQuery($logger, $sql, $params, $types);
        if (!$dryRun) {
            $this->connection->executeUpdate($sql, $params, $types);
        }

        // Update old requalify transition to reopen
        $params = [
            'new_transition'  => 'reopen',
            'old_transitions' => ['requalify_lost', 'requalify_won'],
            'step_to_id'      => $this->getStepIdByName($logger, 'open'),
        ];
        $types = [
            'new_transition'  => Type::STRING,
            'old_transitions' => Connection::PARAM_STR_ARRAY,
            'step_to_id'      => Type::INTEGER,
        ];
        $sql = 'UPDATE oro_workflow_transition_log SET transition = :new_transition' .
               ' WHERE transition IN (:old_transitions) AND step_to_id = :step_to_id';
        $this->logQuery($logger, $sql, $params, $types);
        if (!$dryRun) {
            $this->connection->executeUpdate($sql, $params, $types);
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
            'new_transition' => Type::STRING,
            'old_transition' => Type::STRING,
            'new_step_to_id' => Type::INTEGER,
            'old_step_to_id' => Type::INTEGER,
            'step_from_id'   => Type::INTEGER,
        ];
        $sql = 'UPDATE oro_workflow_transition_log' .
               ' SET transition = :new_transition, step_to_id = :new_step_to_id, step_from_id = :step_from_id' .
               ' WHERE step_to_id = :old_step_to_id AND transition = :old_transition';
        $this->logQuery($logger, $sql, $params, $types);
        if (!$dryRun) {
            $this->connection->executeUpdate($sql, $params, $types);
        }

        // Update old lost transition
        $params = [
            'new_transition' => 'close_lost',
            'old_transition' => 'close_as_lost',
            'step_to_id'     => $this->getStepIdByName($logger, 'lost'),
            'step_from_id'   => $this->getStepIdByName($logger, 'open')
        ];
        $types = [
            'new_transition' => Type::STRING,
            'old_transition' => Type::STRING,
            'step_to_id'     => Type::INTEGER,
            'step_from_id'   => Type::INTEGER,
        ];
        $sql = 'UPDATE oro_workflow_transition_log SET transition = :new_transition, step_from_id = :step_from_id' .
               ' WHERE step_to_id = :step_to_id AND transition = :old_transition';
        $this->logQuery($logger, $sql, $params, $types);
        if (!$dryRun) {
            $this->connection->executeUpdate($sql, $params, $types);
        }

        // Update current steps in won workflows items
        $params = [
            'transition'      => 'close_won',
            'current_step_id' => $this->getStepIdByName($logger, 'won'),
        ];
        $types = [
            'transition'      => Type::STRING,
            'current_step_id' => Type::INTEGER,
        ];
        $sql = 'UPDATE oro_workflow_item SET current_step_id = :current_step_id WHERE id IN(' .
                    ' SELECT workflow_item_id FROM oro_workflow_transition_log WHERE id IN(' .
                        ' SELECT MAX(id) FROM oro_workflow_transition_log ' .
                        ' GROUP BY workflow_item_id' .
                    ') AND transition = :transition' .
               ')';
        $this->logQuery($logger, $sql, $params, $types);
        if (!$dryRun) {
            $this->connection->executeUpdate($sql, $params, $types);
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
            'new_workflow_step_id' => Type::INTEGER,
            'old_workflow_step_id' => Type::INTEGER,
            'status_id'            => Type::STRING,
        ];

        $sql = 'UPDATE orocrm_sales_opportunity SET workflow_step_id = :new_workflow_step_id' .
               ' WHERE workflow_step_id = :old_workflow_step_id AND status_id = :status_id';

        foreach ($params as $param) {
            $this->logQuery($logger, $sql, $param, $types);
            if (!$dryRun) {
                $this->connection->executeUpdate($sql, $param, $types);
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
            'workflow_name' => Type::STRING,
        ];
        $sql    = 'SELECT s.id, s.name FROM oro_workflow_step s WHERE s.workflow_name = :workflow_name';
        $this->logQuery($logger, $sql, $params, $types);

        return $this->connection->fetchAll($sql, $params, $types);
    }
}
