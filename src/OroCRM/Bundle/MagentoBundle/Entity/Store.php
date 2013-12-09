<?php

namespace OroCRM\Bundle\MagentoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Oro\Bundle\DataAuditBundle\Metadata\Annotation as Oro;
use Oro\Bundle\IntegrationBundle\Model\IntegrationEntityTrait;

/**
 * Class Store
 *
 * @package OroCRM\Bundle\OroCRMMagentoBundle\Entity
 * @Oro\Loggable
 * @ORM\Entity
 * @ORM\Table(
 *  name="orocrm_magento_store",
 *  indexes={
 *      @ORM\Index(name="idx_website", columns={"website_id"})
 *  },
 *  uniqueConstraints={
 *      @ORM\UniqueConstraint(name="unq_code_channel_id", columns={"store_code", "channel_id"})
 *  }
 * )
 */
class Store
{
    use IntegrationEntityTrait, OriginTrait;

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
     * @ORM\Column(name="store_code", type="string", length=32, nullable=false)
     * @Oro\Versioned
     */
    protected $code;

    /**
     * @var string
     *
     * @ORM\Column(name="store_name", type="string", length=255, nullable=false)
     * @Oro\Versioned
     */
    protected $name;

    /**
     * @var Website
     *
     * @ORM\ManyToOne(targetEntity="OroCRM\Bundle\MagentoBundle\Entity\Website", cascade="PERSIST")
     * @ORM\JoinColumn(name="website_id", referencedColumnName="id", onDelete="cascade", nullable=false)
     */
    protected $website;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @param string $code
     *
     * @return $this
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param Website $website
     *
     * @return $this
     */
    public function setWebsite(Website $website)
    {
        $this->website = $website;

        return $this;
    }

    /**
     * @return Website
     */
    public function getWebsite()
    {
        return $this->website;
    }
}
