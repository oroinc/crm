<?php

namespace OroCRM\Bundle\SalesBundle\QueryDesigner;

use Oro\Bundle\QueryDesignerBundle\QueryDesigner\AbstractQueryConverter;
use Oro\Bundle\QueryDesignerBundle\QueryDesigner\FunctionInterface;

class WonRevenueSumFunction implements FunctionInterface
{
    /**
     * {@inheritdoc}
     */
    public function getExpression($tableAlias, $fieldName, $columnName, $columnAlias, AbstractQueryConverter $qc)
    {
        // Make sure status table joined
        $opportunityStatusTableAlias = $qc->ensureChildTableJoined($tableAlias, 'status', 'left');

        return sprintf(
            "SUM(CASE WHEN (%s.name='won') THEN %s ELSE 0 END)",
            $opportunityStatusTableAlias,
            $columnName
        );
    }
}
