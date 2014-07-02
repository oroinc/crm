<?php

namespace OroCRM\Bundle\ChannelBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;

/**
 * @ORM\Entity()
 * @ORM\Table(name="orocrm_channel")
 * @Config(
 *  routeView="orocrm_channel_view",
 *  defaultValues={
 *      "entity"={"icon"="icon-sitemap"},
 *      "ownership"={
 *          "owner_type"="ORGANIZATION",
 *          "owner_field_name"="owner",
 *          "owner_column_name"="organization_owner_id"
 *      },
 *      "security"={
 *          "type"="ACL",
 *          "group_name"=""
 *      }
 *  }
 * )
 */
class Channel
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
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="OroCRM\Bundle\ChannelBundle\Entity\EntityName",cascade={"all"}, mappedBy="channel")
     */
    protected $entities;

    /**
     * @ORM\ManyToMany(targetEntity="Oro\Bundle\IntegrationBundle\Entity\Channel")
     * @ORM\JoinTable(
     *      name="orocrm_chl_to_integration_chl",
     *      joinColumns={@ORM\JoinColumn(name="channel_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="integrations_id", referencedColumnName="id", onDelete="CASCADE")}
     * )
     **/
    protected $integrations;

    /**
     * @var Organization
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\OrganizationBundle\Entity\Organization")
     * @ORM\JoinColumn(name="organization_owner_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $owner;

    public function __construct()
    {
        $this->entities     = new ArrayCollection();
        $this->integrations = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param array $entities
     */
    public function setEntities(array $entities)
    {
        $this->entities = $entities;
    }

    /**
     * @return array
     */
    public function getEntities()
    {
        return $this->entities;
    }

    /**
     * @return ArrayCollection
     */
    public function getIntegrations()
    {
        return $this->integrations;
    }

    /**
     * @param Integration $integration
     *
     * @return $this
     */
    public function addIntegrations(Integration $integration)
    {
        if (!$this->getIntegrations()->contains($integration)) {
            $this->getIntegrations()->add($integration);
        }

        return $this;
    }

    /**
     * Remove specified integration
     *
     * @param Integration $integration
     *
     * @return $this
     */
    public function removeIntegrations(Integration $integration)
    {
        if ($this->getIntegrations()->contains($integration)) {
            $this->getIntegrations()->removeElement($integration);
        }

        return $this;
    }

    /**
     * @param Organization $owner
     */
    public function setOwner(Organization $owner)
    {
        $this->owner = $owner;
    }

    /**
     * @return Organization
     */
    public function getOwner()
    {
        return $this->owner;
    }
}
