<?php

namespace Oro\Bundle\MagentoBundle\Entity;

use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;

trait OriginTrait
{
    /**
     * Mage entity origin id
     * @var integer
     *
     * @ORM\Column(name="origin_id", type="integer", options={"unsigned"=true}, nullable=true)
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "identity"=true
     *          }
     *      }
     * )
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
