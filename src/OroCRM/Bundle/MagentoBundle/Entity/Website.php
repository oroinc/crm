<?php

namespace OroCRM\Bundle\MagentoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Oro\Bundle\DataAuditBundle\Metadata\Annotation as Oro;
use Oro\Bundle\IntegrationBundle\Model\IntegrationEntityTrait;

/**
 * Class Website
 *
 * @package OroCRM\Bundle\OroCRMMagentoBundle\Entity
 * @Oro\Loggable
 * @ORM\Entity
 * @ORM\Table(
 *  name="orocrm_magento_website",
 *  uniqueConstraints={@ORM\UniqueConstraint(name="unq_site_idx", columns={"website_code", "origin_id", "channel_id"})}
 * )
 */
class Website
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
     * @ORM\Column(name="website_code", type="string", length=32, nullable=false)
     * @Oro\Versioned
     */
    protected $code;

    /**
     * @var string
     *
     * @ORM\Column(name="website_name", type="string", length=255, nullable=false)
     * @Oro\Versioned
     */
    protected $name;

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
}
