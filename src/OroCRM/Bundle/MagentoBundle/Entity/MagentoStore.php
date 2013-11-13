<?php

namespace OroCRM\Bundle\MagentoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class MagentoStore
 *
 * @package OroCRM\Bundle\OroCRMMagentoBundle\Entity
 * @ORM\Entity
 * @ORM\Table(
 *  name="orocrm_magento_store",
 *  indexes={
 *      @ORM\Index(name="idx_website", columns={"website_id"})
 *  },
 *  uniqueConstraints={@ORM\UniqueConstraint(name="unq_code", columns={"store_code"})}
 * )
 */
class MagentoStore
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
     * @ORM\Column(name="store_code", type="string", length=32, nullable=false)
     */
    protected $code;

    /**
     * @var string
     *
     * @ORM\Column(name="store_name", type="string", length=255, nullable=false)
     */
    protected $name;

    /**
     * @var MagentoWebsite
     *
     * @ORM\ManyToOne(targetEntity="OroCRM\Bundle\MagentoBundle\Entity\MagentoWebsite")
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
     * @param MagentoWebsite $website
     *
     * @return $this
     */
    public function setWebsite(MagentoWebsite $website)
    {
        $this->website = $website;

        return $this;
    }

    /**
     * @return \OroCRM\Bundle\MagentoBundle\Entity\MagentoWebsite
     */
    public function getWebsite()
    {
        return $this->website;
    }
}
