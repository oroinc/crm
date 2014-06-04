<?php

namespace OroCRM\Bundle\SalesBundle\QueryDesigner;

use Oro\Bundle\QueryDesignerBundle\QueryDesigner\AbstractQueryConverter;
use Oro\Bundle\QueryDesignerBundle\QueryDesigner\FunctionInterface;

abstract class AbstractRevenueSumFunction implements FunctionInterface
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
        // Make sure status table joined
        $parentJoinId                = $qc->getJoinIdentifierByTableAlias($tableAlias);
        $joinId                      = $qc->buildJoinIdentifier('OroCRM\Bundle\SalesBundle\Entity\Opportunity::status', $parentJoinId);
        $opportunityStatusTableAlias = $qc->ensureTableJoined($joinId);

        return sprintf(
            "SUM(CASE WHEN (%s.name='%s') THEN %s ELSE 0 END)",
            $opportunityStatusTableAlias,
            $this->getStatus(),
            $columnName
        );
    }
}
