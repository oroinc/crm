<?php

namespace Oro\Bundle\MagentoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class Region
 *
 * @package Oro\Bundle\OroMagentoBundle\Entity
 * @ORM\Entity(repositoryClass="Oro\Bundle\MagentoBundle\Entity\Repository\RegionRepository")
 * @ORM\Table(
 *  name="orocrm_magento_region",
 *  indexes={
 *      @ORM\Index(name="idx_region", columns={"region_id"})
 *  },
 *  uniqueConstraints={@ORM\UniqueConstraint(name="unq_code", columns={"combined_code"})}
 * )
 */
class Region
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
     * @ORM\Column(name="combined_code", type="string", length=60, nullable=false)
     */
    protected $combinedCode;

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=32, nullable=false)
     */
    protected $code;

    /**
     * @var string
     *
     * @ORM\Column(name="country_code", type="string", length=255, nullable=false)
     */
    protected $countryCode;

    /**
     * @var integer
     *
     * @ORM\Column(name="region_id", type="integer")
     */
    protected $regionId;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    protected $name;

    /**
     * @param string $code
     *
     * @return Region
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
     * @param string $combinedCode
     *
     * @return Region
     */
    public function setCombinedCode($combinedCode)
    {
        $this->combinedCode = $combinedCode;

        return $this;
    }

    /**
     * @return string
     */
    public function getCombinedCode()
    {
        return $this->combinedCode;
    }

    /**
     * @param string $countryCode
     *
     * @return Region
     */
    public function setCountryCode($countryCode)
    {
        $this->countryCode = $countryCode;

        return $this;
    }

    /**
     * @return string
     */
    public function getCountryCode()
    {
        return $this->countryCode;
    }

    /**
     * @param int $id
     *
     * @return Region
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
     * @param int $regionId
     *
     * @return Region
     */
    public function setRegionId($regionId)
    {
        $this->regionId = $regionId;

        return $this;
    }

    /**
     * @return int
     */
    public function getRegionId()
    {
        return $this->regionId;
    }

    /**
     * @param string $name
     *
     * @return Region
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return int
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->getName();
    }
}
