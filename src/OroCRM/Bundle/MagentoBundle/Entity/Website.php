<?php

namespace OroCRM\Bundle\MagentoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class Website
 *
 * @package OroCRM\Bundle\OroCRMMagentoBundle\Entity
 * @ORM\Entity
 * @ORM\Table(
 *  name="orocrm_magento_website",
 *  uniqueConstraints={@ORM\UniqueConstraint(name="unq_website_code", columns={"website_code"})}
 * )
 */
class Website
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
     * @ORM\Column(name="website_code", type="string", length=32, nullable=false)
     */
    protected $code;

    /**
     * @var string
     *
     * @ORM\Column(name="website_name", type="string", length=255, nullable=false)
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
