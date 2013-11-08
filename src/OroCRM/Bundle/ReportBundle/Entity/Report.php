<?php

namespace OroCRM\Bundle\ReportBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\OrganizationBundle\Entity\BusinessUnit;

/**
 * @ORM\Entity
 * @ORM\Table(name="orocrm_report")
 * @Config(
 *  defaultValues={
 *      "entity"={"label"="Report", "plural_label"="Reports"},
 *      "ownership"={
 *          "owner_type"="BUSINESS_UNIT",
 *          "owner_field_name"="owner",
 *          "owner_column_name"="business_unit_owner_id"
 *      },
 *      "security"={
 *          "type"="ACL",
 *          "group_name"=""
 *      }
 *  }
 * )
 */
class Report
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
     * @ORM\Column(name="name", type="string", length=255)
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    protected $description;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=100)
     */
    protected $type;

    /**
     * @var string $entity
     *
     * @ORM\Column(name="entity", type="string", length=255)
     */
    protected $entity;

    /**
     * @var BusinessUnit
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\OrganizationBundle\Entity\BusinessUnit")
     * @ORM\JoinColumn(name="business_unit_owner_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $owner;

    /**
     * @var string
     *
     * @ORM\Column(name="definition", type="text")
     */
    protected $definition;

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
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Report
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return Report
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get report type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set report type
     *
     * @param string $type
     * @return Report
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get the full name of an entity on which this report is based
     *
     * @return string
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * Set the full name of an entity on which this report is based
     *
     * @param string $entity
     *
     * @return Report
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;

        return $this;
    }

    /**
     * Get a business unit owning this report
     *
     * @return BusinessUnit
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * Set a business unit owning this report
     *
     * @param BusinessUnit $owner
     * @return BusinessUnit
     */
    public function setOwner($owner)
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * Get this report definition in YAML format
     *
     * @return string
     */
    public function getDefinition()
    {
        return $this->definition;
    }

    /**
     * Set this report definition in YAML format
     *
     * @param string $definition
     * @return Report
     */
    public function setDefinition($definition)
    {
        $this->definition = $definition;

        return $this;
    }
}
