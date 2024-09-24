<?php

namespace Oro\Bundle\SalesBundle\QueryDesigner;

use Oro\Bundle\QueryDesignerBundle\QueryDesigner\AbstractQueryConverter;
use Oro\Bundle\QueryDesignerBundle\QueryDesigner\FunctionInterface;

/**
 * Generates a string represents a function expression to aggregate report values.
 */
class WonRevenueSumFunction implements FunctionInterface
{
    #[\Override]
    public function getExpression(
        $tableAlias,
        $fieldName,
        $columnName,
        $columnAlias,
        AbstractQueryConverter $qc
    ): string {
        return sprintf(
            "SUM(CASE WHEN (CAST(JSON_EXTRACT(%s.serialized_data,'status') as string) = 'won') THEN (%s) ELSE 0 END)",
            $tableAlias,
            $columnName
        );
    }
}
