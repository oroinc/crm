<?php

namespace OroCRM\Bundle\CallBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CallStatus
 *
 * @ORM\Table(name="orocrm_call_status")
 * @ORM\Entity
 */

class CallStatus
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=255)
     */
    protected $status;


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
     * Set status
     *
     * @param string $status
     * @return CallStatus
     */
    public function setStatus($status)
    {
        $this->status = $status;
    
        return $this;
    }

    /**
     * Get status
     *
     * @return string 
     */
    public function getStatus()
    {
        return $this->status;
    }

    public function __toString()
    {
        return $this->status;
    }
}
