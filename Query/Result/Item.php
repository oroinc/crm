<?php

namespace Oro\Bundle\SearchBundle\Query\Result;

use Doctrine\Common\Persistence\ObjectManager;
use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\Exclude;

class Item
{
    /**
     * @var string
     * @Type("string")
     */
    protected $entityName;

    /**
     * @var int
     * @Type("integer")
     */
    protected $recordId;

    /**
     * @var \Doctrine\Common\Persistence\ObjectManager
     * @Exclude
     */
    protected $em;

    public function __construct(ObjectManager $em, $entityName = null, $recordId = 0)
    {
        $this->em = $em;
        if ($entityName) {
            $this->setEntityName($entityName);
        }
        if ($recordId) {
            $this->setRecordId($recordId);
        }
    }

    /**
     * Set entity name
     *
     * @param string $entityName
     * @return \Oro\Bundle\SearchBundle\Query\Result\Item
     */
    public function setEntityName($entityName)
    {
        $this->entityName = $entityName;

        return $this;
    }

    /**
     * Set record id
     *
     * @param $recordId
     * @return \Oro\Bundle\SearchBundle\Query\Result\Item
     */
    public function setRecordId($recordId)
    {
        $this->recordId = $recordId;

        return $this;
    }

    /**
     * Get entity name
     *
     * @return string
     */
    public function getEntityName()
    {
        return $this->entityName;
    }

    /**
     * Get record id
     *
     * @return int
     */
    public function getRecordId()
    {
        return $this->recordId;
    }

    /**
     * Load related object
     * @return object
     */
    public function getEntity()
    {
        return $this->em->getRepository($this->getEntityName())->find($this->getRecordId());
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return array(
            'entity_name' => $this->entityName,
            'record_id' => $this->recordId,
            'record_string' => $this->getEntity()->__toString()
        );
    }
}
