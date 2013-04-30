<?php

namespace Oro\Bundle\ContactBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\Exclude;
use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;
use Oro\Bundle\FlexibleEntityBundle\Entity\Mapping\AbstractEntityFlexible;

/**
 * @ORM\Entity(repositoryClass="Oro\Bundle\FlexibleEntityBundle\Entity\Repository\FlexibleEntityRepository")
 * @ORM\Table(name="oro_contact")
 * @ORM\HasLifecycleCallbacks()
 */
class Contact extends AbstractEntityFlexible
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Soap\ComplexType("int", nillable=true)
     * @Type("integer")
     */
    protected $id;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\UserBundle\Entity\User", inversedBy="contacts")
     * @Type("integer")
     */
    protected $createdBy;

    /**
     * @var \Oro\Bundle\FlexibleEntityBundle\Model\AbstractFlexibleValue[]
     *
     * @ORM\OneToMany(targetEntity="Oro\Bundle\AccountBundle\Entity\Value\AccountValue", mappedBy="entity", cascade={"persist", "remove"}, orphanRemoval=true)
     * @Exclude
     */
    protected $values;

    /**
     * Returns the contact unique id.
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get contact created date/time
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->created;
    }

    /**
     * Get contact last update date/time
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updated;
    }

    public function __toString()
    {
        return (string)$this->getName();
    }

    /**
     * Pre persist event listener
     *
     * @ORM\PrePersist
     */
    public function beforeSave()
    {
        $this->created = new \DateTime();
        $this->updated = new \DateTime();
    }
}
