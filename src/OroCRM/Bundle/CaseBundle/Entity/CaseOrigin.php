<?php

namespace OroCRM\Bundle\CaseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(
 *      name="orocrm_case_origin"
 * )
 */
class CaseOrigin
{
    const TYPE_EMAIL = 1;
    const TYPE_PHONE = 2;
    const TYPE_WEB = 3;
    const TYPE_OTHER = 4;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var CaseEntity
     *
     * @ORM\ManyToOne(targetEntity="CaseEntity", inversedBy="origins", cascade={"persist"})
     * @ORM\JoinColumn(name="case_entity_id", referencedColumnName="id")
     */
    protected $caseEntity;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="integer")
     */
    protected $type;

    /**
     * @var string
     *
     * @ORM\Column(name="value", type="string", length=100, nullable=true)
     */
    protected $value;

    /**
     * @param int $id
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $type
     *
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param CaseEntity $caseEntity
     *
     * @return $this
     */
    public function setCaseEntity($caseEntity)
    {
        $this->caseEntity = $caseEntity;

        return $this;
    }

    /**
     * @return CaseEntity
     */
    public function getCaseEntity()
    {
        return $this->caseEntity;
    }
}
