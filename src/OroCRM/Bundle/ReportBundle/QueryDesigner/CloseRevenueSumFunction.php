<?php

namespace OroCRM\Bundle\ReportBundle\QueryDesigner;

use Oro\Bundle\QueryDesignerBundle\QueryDesigner\AbstractQueryConverter;
use Oro\Bundle\QueryDesignerBundle\QueryDesigner\FunctionInterface;

class CloseRevenueSumFunction implements FunctionInterface
{
    /**
     * {@inheritdoc}
     */
    public function getExpression(
        $tableAlias,
        $fieldName,
        $columnName,
        $columnAlias,
        AbstractQueryConverter $queryConverter
    ) {
        // Make sure WorkflowItem table joined
        $workflowItemTableAlias = $queryConverter->ensureSiblingTableJoined($tableAlias, 'workflowItem');

        return sprintf(
            'SUM(CASE WHEN (%s.currentStepName=\'close\') THEN %s ELSE 0 END)',
            $workflowItemTableAlias,
            $columnName
        );
    }
}
