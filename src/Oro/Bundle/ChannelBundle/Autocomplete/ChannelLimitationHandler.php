<?php

namespace Oro\Bundle\ChannelBundle\Autocomplete;

use Oro\Bundle\FormBundle\Autocomplete\SearchHandler;
use Oro\Bundle\SearchBundle\Engine\Indexer;
use Oro\Bundle\SearchBundle\Query\Criteria\Criteria;
use Oro\Bundle\SearchBundle\Query\Query;

/**
 * Adds restriction to search query to get documents only from passed channel
 */
class ChannelLimitationHandler extends SearchHandler
{
    /** @var string */
    protected $channelPropertyName;

    /** @var string */
    protected $channelSearchPropertyName;

    /**
     * @param string $entityName
     * @param array  $properties
     * @param string $channelRelationName
     * @param string $channelSearchPropertyName
     */
    public function __construct(
        $entityName,
        array $properties,
        $channelRelationName = 'dataChannel',
        $channelSearchPropertyName = 'dataChannelId'
    ) {
        parent::__construct($entityName, $properties);
        $this->channelRelationName       = $channelRelationName;
        $this->channelSearchPropertyName = $channelSearchPropertyName;
    }

    /**
     * {@inheritdoc}
     */
    protected function searchIds($search, $firstResult, $maxResults)
    {
        $parts        = explode(';', $search);
        $searchString = $parts[0];
        $channelId    = !empty($parts[1]) ? $parts[1] : false;

        $queryObj = $this->indexer->select()
            ->from($this->entitySearchAlias);
        $queryObj->getCriteria()
            ->setMaxResults((int)$maxResults)
            ->setFirstResult((int)$firstResult);

        if (false !== $channelId) {
            $field = Criteria::implodeFieldTypeName(Query::TYPE_INTEGER, $this->channelSearchPropertyName);
            $queryObj->getCriteria()->andWhere(Criteria::expr()->eq($field, $channelId));
        }

        if ($searchString) {
            $field = Criteria::implodeFieldTypeName(Query::TYPE_TEXT, Indexer::TEXT_ALL_DATA_FIELD);
            $queryObj->getCriteria()->andWhere(Criteria::expr()->contains($field, $searchString));
        }

        $ids      = [];
        $result   = $this->indexer->query($queryObj);
        $elements = $result->getElements();

        foreach ($elements as $element) {
            $ids[] = $element->getRecordId();
        }

        return $ids;
    }

    /**
     * {@inheritdoc}
     */
    protected function findById($query)
    {
        $parts     = explode(';', $query);
        $id        = $parts[0];
        $channelId = !empty($parts[1]) ? $parts[1] : false;

        $criteria = [$this->idFieldName => $id];
        if (false !== $channelId) {
            $criteria[$this->channelRelationName] = $channelId;
        }

        return $this->entityRepository->findBy($criteria, null, 1);
    }
}
