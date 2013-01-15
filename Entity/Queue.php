<?php

namespace Oro\Bundle\SearchBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Oro\Bundle\SearchBundle\Entity\Queue
 *
 * @ORM\Table(name="queue")
 * @ORM\Entity()
 */
class Queue
{
    const EVENT_SAVE = 'save';
    const EVENT_DELETE = 'delete';

    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string $entity
     *
     * @ORM\Column(name="entity", type="string", length=255)
     */
    private $entity;

    /**
     * @var integer $record_id
     *
     * @ORM\Column(name="record_id", type="integer")
     */
    private $recordId;

    /**
     * @var string $event
     *
     * @ORM\Column(name="event", type="string")
     */
    private $event;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set entity
     *
     * @param string $entity
     * @return Queue
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;
    
        return $this;
    }

    /**
     * Get entity
     *
     * @return string 
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * Set event
     *
     * @param string $event
     * @return Queue
     */
    public function setEvent($event)
    {
        $this->event = $event;
    
        return $this;
    }

    /**
     * Get event
     *
     * @return string 
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * Set recordId
     *
     * @param integer $recordId
     * @return Queue
     */
    public function setRecordId($recordId)
    {
        $this->recordId = $recordId;
    
        return $this;
    }

    /**
     * Get recordId
     *
     * @return integer 
     */
    public function getRecordId()
    {
        return $this->recordId;
    }
}