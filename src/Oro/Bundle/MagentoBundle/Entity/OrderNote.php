<?php

namespace Oro\Bundle\MagentoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\MagentoBundle\Model\ExtendOrderNote;

/**
 * @ORM\Table(
 *     "orocrm_magento_order_notes",
 *     indexes={
 *          @ORM\Index(name="IDX_D130A0378D9F6D38", columns={"order_id"}),
 *          @ORM\Index(name="IDX_D130A03756A273CC", columns={"origin_id"}),
 *          @ORM\Index(name="IDX_D130A03772F5A1AA", columns={"channel_id"})
 *     },
 *     uniqueConstraints={
 *          @ORM\UniqueConstraint(name="unq_order_note_origin_id_channel_id", columns={"origin_id", "channel_id"})
 *     }
 * )
 * @ORM\Entity
 *
 * @Config(
 *     routeView="oro_magento_credit_memo_view",
 *     defaultValues={
 *          "entity"={
 *              "icon"="fa-list-alt"
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
}
