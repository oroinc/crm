<?php

namespace OroCRM\Bundle\ChannelBundle\Autocomplete;

use Oro\Bundle\FormBundle\Autocomplete\SearchHandler;

class ChannelLimitationHandler extends SearchHandler
{
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
            $queryObj->andWhere('dataChannelId', '=', $channelId, 'integer');
        }

        if ($searchString) {
            $queryObj->andWhere('name', '~', $searchString, 'string');
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
