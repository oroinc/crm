<?php

namespace Oro\Bundle\SalesBundle\Autocomplete;

use Oro\Bundle\FormBundle\Autocomplete\SearchHandler;
use Oro\Bundle\OrganizationBundle\Provider\BusinessUnitAclProvider;

/**
 * Autocomplete search handler for business units in forecast widgets.
 *
 * Filters business units by ACL permissions for opportunities using {@see BusinessUnitAclProvider},
 * returning only units the user can view.
 */
class ForecastWidgetBusinessUnitSearchHandler extends SearchHandler
{
    /** @var BusinessUnitAclProvider */
    protected $businessUnitAclProvider;

    /** @var string */
    protected $opportunityClassName;

    /**
     * @param string $entityName
     * @param array $properties
     * @param BusinessUnitAclProvider $businessUnitAclProvider
     * @param string $opportunityClassName
     */
    public function __construct(
        $entityName,
        $properties,
        BusinessUnitAclProvider $businessUnitAclProvider,
        $opportunityClassName
    ) {
        parent::__construct($entityName, $properties);
        $this->businessUnitAclProvider = $businessUnitAclProvider;
        $this->opportunityClassName = $opportunityClassName;
    }

    /**
     * @param string $search
     * @param int    $firstResult
     * @param int    $maxResults
     * @return array
     */
    #[\Override]
    protected function searchIds($search, $firstResult, $maxResults)
    {
        $ids = [];
        $allowedBusinessUnitIds = $this
            ->businessUnitAclProvider
            ->getBusinessUnitIds($this->opportunityClassName, 'VIEW');

        if (!is_array($allowedBusinessUnitIds) || count($allowedBusinessUnitIds) === 0) {
            return $ids;
        }

        $this->indexer->setIsAllowedApplyAcl(false);
        $result   = $this->indexer->simpleSearch($search, $firstResult, $maxResults, $this->entitySearchAlias);
        $elements = $result->getElements();

        foreach ($elements as $element) {
            $recordId = (int)$element->getRecordId();
            if (in_array($recordId, $allowedBusinessUnitIds, true)) {
                $ids[] = $element->getRecordId();
            }
        }

        return $ids;
    }
}
