<?php

namespace OroCRM\Bundle\SalesBundle\QueryDesigner;

use Oro\Bundle\QueryDesignerBundle\QueryDesigner\FunctionInterface;
use Oro\Bundle\QueryDesignerBundle\QueryDesigner\AbstractQueryConverter;

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
            "SUM(CASE WHEN (%s.id='won') THEN %s ELSE 0 END)",
            $opportunityStatusTableAlias,
            $columnName
        );
    }
}
