<?php

namespace Oro\Bundle\CaseBundle\EventListener;

use Oro\Bundle\CaseBundle\Entity\CaseEntity;
use Oro\Bundle\SearchBundle\Engine\Indexer;
use Oro\Bundle\SearchBundle\Engine\ObjectMapper;
use Oro\Bundle\SearchBundle\Event\PrepareEntityMapEvent;
use Oro\Bundle\SearchBundle\Query\Query;

/**
 * Listener cuts amount of data for search index in some fields
 * TODO: Implement proper cutting configuration and functionality in scope of BAP-14415
 */
class SearchIndexDataListener
{
    /**
     * @var ObjectMapper
     */
    protected $mapper;

    /**
     * ['<fieldName>' => <maxLength>, ...]
     *
     * @var array
     */
    protected $shortenedFields = [];

    public function __construct(ObjectMapper $mapper)
    {
        $this->mapper = $mapper;
    }

    public function setShortenedFields(array $shortenedFields)
    {
        $this->shortenedFields = $shortenedFields;
    }

    public function onPrepareEntityMap(PrepareEntityMapEvent $event)
    {
        if ($event->getClassName() !== CaseEntity::class) {
            return;
        }

        $data = $event->getData();
        $textData = $data[Query::TYPE_TEXT];

        $rebuildAllText = false;
        foreach ($this->shortenedFields as $field => $maxLength) {
            if (array_key_exists($field, $textData) && mb_strlen($textData[$field]) > $maxLength) {
                $textData[$field] = mb_substr($textData[$field], 0, $maxLength);
                $rebuildAllText = true;
            }
        }

        if ($rebuildAllText) {
            $newAllText = '';
            foreach ($textData as $field => $value) {
                if ($field !== Indexer::TEXT_ALL_DATA_FIELD) {
                    $newAllText = $this->mapper->buildAllDataField($newAllText, $value);
                }
            }
            $textData[Indexer::TEXT_ALL_DATA_FIELD] = $newAllText;
        }

        $data[Query::TYPE_TEXT] = $textData;
        $event->setData($data);
    }
}
