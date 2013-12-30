<?php

namespace OroCRM\Bundle\MagentoBundle\Entity;


trait OriginTrait
{
    /**
     * Mage entity origin id
     * @var integer
     *
     * @ORM\Column(name="origin_id", type="integer", options={"unsigned"=true}, nullable=true)
     */
    protected $originId;

    /**
     * @param int $originId
     *
     * @return $this
     */
    public function setOriginId($originId)
    {
        $this->originId = $originId;

        return $this;
    }

    /**
     * @return int
     */
    public function getOriginId()
    {
        return $this->originId;
    }
}
