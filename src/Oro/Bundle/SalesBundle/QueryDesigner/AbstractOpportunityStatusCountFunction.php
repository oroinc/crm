<?php

namespace Oro\Bundle\SalesBundle\QueryDesigner;

use Oro\Bundle\QueryDesignerBundle\QueryDesigner\AbstractQueryConverter;
use Oro\Bundle\QueryDesignerBundle\QueryDesigner\FunctionInterface;

abstract class AbstractOpportunityStatusCountFunction implements FunctionInterface
{
    /**
     * @return string
     */
    abstract protected function getStatus();

    /**
     * {@inheritdoc}
     */
    public function getExpression($tableAlias, $fieldName, $columnName, $columnAlias, AbstractQueryConverter $qc)
    {
        // split by dot $columnName
        // there we will have tableAlias.columnName for dictionary virtual column
        list($statusTableAlias) = explode('.', $columnName);

        return sprintf(
            "SUM(CASE WHEN %s.id = '%s' THEN 1 ELSE 0 END)",
            $statusTableAlias,
            $this->getStatus()
        );
    }
}
