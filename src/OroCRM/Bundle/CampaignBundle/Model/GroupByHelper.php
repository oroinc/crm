<?php

namespace OroCRM\Bundle\CampaignBundle\Model;

class GroupByHelper
{
    /**
     * Get fields that must appear in GROUP BY.
     *
     * @param string|array $groupBy
     * @param array $selects
     * @return array
     */
    public function getGroupByFields($groupBy, $selects)
    {
        $groupBy = $this->getPreparedGroupBy($groupBy);

        foreach ($selects as $select) {
            $select = trim((string)$select);
            // Do not add fields with aggregate functions
            if ($this->hasAggregate($select)) {
                continue;
            }

            if ($field = $this->getFieldForGroupBy($select)) {
                $groupBy[] = $field;
            }
        }

        return array_unique($groupBy);
    }

    /**
     * Get GROUP BY statements as array of trimmed parts.
     *
     * @param string|array $groupBy
     * @return array
     */
    protected function getPreparedGroupBy($groupBy)
    {
        if (!is_array($groupBy)) {
            $groupBy = explode(',', $groupBy);
        }

        $result = [];
        foreach ($groupBy as $groupByPart) {
            $groupByPart = trim((string)$groupByPart);
            if ($groupByPart) {
                $result[] = $groupByPart;
            }
        }

        return $result;
    }

    /**
     * @param string $select
     * @return bool
     */
    protected function hasAggregate($select)
    {
        preg_match('/(MIN|MAX|AVG|COUNT|SUM)\(/i', $select, $matches);

        return (bool)$matches;
    }

    /**
     * Search for field alias if applicable or field name to use in group by
     *
     * @param string $select
     * @return string|null
     */
    protected function getFieldForGroupBy($select)
    {
        preg_match('/([^\s]+)\s+as\s+(\w+)$/i', $select, $parts);
        if (!empty($parts[2])) {
            // Add alias
            return $parts[2];
        } elseif (!$parts && strpos($select, ' ') === false) {
            // Add field itself when there is no alias
            return $select;
        }

        return null;
    }
}
