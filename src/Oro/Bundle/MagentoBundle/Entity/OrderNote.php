<?php

namespace Oro\Bundle\MagentoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\MagentoBundle\Model\ExtendOrderNote;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\UserBundle\Entity\User;

/**
 * @ORM\Table(
 *     "orocrm_magento_order_notes",
 *     indexes={
 *          @ORM\Index(name="IDX_D130A0378D9F6D38", columns={"order_id"}),
 *          @ORM\Index(name="IDX_D130A03756A273CC", columns={"origin_id"}),
 *          @ORM\Index(name="IDX_D130A03772F5A1AA", columns={"channel_id"}),
 *          @ORM\Index(name="IDX_D130A0379EB185F9", columns={"user_owner_id"}),
 *          @ORM\Index(name="IDX_D130A03732C8A3DE", columns={"organization_id"})
 *     },
 *     uniqueConstraints={
 *          @ORM\UniqueConstraint(name="unq_order_note_origin_id_channel_id", columns={"origin_id", "channel_id"})
 *     }
 * )
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks()
 *
 * @Config(
 *     defaultValues={
 *          "entity"={
 *              "icon"="fa-list-alt"
 *          },
 *          "ownership"={
 *              "owner_type"="USER",
 *              "owner_field_name"="owner",
 *              "owner_column_name"="user_owner_id",
 *              "organization_field_name"="organization",
 *              "organization_column_name"="organization_id"
 *          },
 *          "security"={
 *              "type"="ACL",
 *              "group_name"="",
 *              "category"="sales_data"
 *          }
 *     }
 * )
 */
class OrderNote extends ExtendOrderNote implements
    OriginAwareInterface,
    CreatedAtAwareInterface,
    UpdatedAtAwareInterface,
    IntegrationAwareInterface
{
    use OriginTrait;
    use CreatedAtTrait;
    use UpdatedAtTrait;
    use IntegrationEntityTrait;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var Order
     *
     * @ORM\ManyToOne(targetEntity="Order", inversedBy="orderNotes", cascade={"persist"})
     * @ORM\JoinColumn(name="order_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $order;

    /**
     * @var string
     *
     * @ORM\Column(name="message", type="text")
     */
    protected $message;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="user_owner_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $owner;

    /**
     * @var Organization
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\OrganizationBundle\Entity\Organization")
     * @ORM\JoinColumn(name="organization_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $organization;

    /**
     * @ORM\PrePersist()
     */
    public function setUpdatedAtValue()
    {
        if (null === $this->updatedAt) {
            $this->updatedAt = $this->createdAt;
        }
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param Order $order
     * @return OrderNote
     */
    public function setOrder(Order $order)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * @return Order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param string $message
     *
     * @return OrderNote
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @return User
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @param User $user
     *
     * @return OrderNote
     */
    public function setOwner(User $user)
    {
        $this->owner = $user;

        return $this;
    }

    /**
     * Set organization
     *
     * @param Organization $organization
     *
     * @return OrderNote
     */
    public function setOrganization(Organization $organization = null)
    {
        $this->organization = $organization;

        return $this;
    }

    /**
     * Get organization
     *
     * @return Organization
     */
    public function getOrganization()
    {
        return $this->organization;
    }
}
