<?php

namespace OroCRM\Bundle\ChannelBundle\Autocomplete;

use Oro\Bundle\SearchBundle\Engine\Indexer;
use Oro\Bundle\FormBundle\Autocomplete\SearchHandler;

class ChannelLimitationHandler extends SearchHandler
{
    /** @var string */
    protected $channelPropertyName;

    /**
     * @param string $entityName
     * @param array  $properties
     * @param string $channelPropertyName
     */
    public function __construct($entityName, array $properties, $channelPropertyName = 'dataChannelId')
    {
        parent::__construct($entityName, $properties);
        $this->channelPropertyName = $channelPropertyName;
    }

    /**
     * @param string $search
     * @param int    $firstResult
     * @param int    $maxResults
     *
     * @return array
     */
    protected function searchIds($search, $firstResult, $maxResults)
    {
        $parts        = explode(';', $search);
        $searchString = $parts[0];
        $channelId    = isset($parts[1]) ? $parts[1] : false;

        $queryObj = $this->indexer->select()
            ->from($this->entitySearchAlias)
            ->setMaxResults($maxResults)
            ->setFirstResult($firstResult);

        if ($channelId) {
            $queryObj->andWhere($this->channelPropertyName, '=', $channelId, 'integer');
        }

        if ($searchString) {
            $queryObj->andWhere(Indexer::TEXT_ALL_DATA_FIELD, '~', $searchString);
        }

        $ids      = [];
        $result   = $this->indexer->query($queryObj);
        $elements = $result->getElements();

        foreach ($elements as $element) {
            $ids[] = $element->getRecordId();
        }

        return $ids;
    }
}
