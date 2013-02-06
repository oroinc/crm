<?php
namespace Oro\Bundle\DataFlowBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Source
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 * @ORM\Table(name="oro_dataflow_source")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks()
 */
class Source
{

    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string $code
     *
     * @ORM\Column(name="code", type="string", length=255)
     */
    protected $code;

    /**
     * @var string $parameters
     *
     * @ORM\Column(name="parameters", type="text", nullable=true)
     */
    protected $parameters;

    /**
     * @var string $configuration
     *
     * @ORM\Column(name="configuration", type="text", nullable=true)
     */
    protected $configuration;

    /**
     * Configuration
     *
     * @param \ArrayAccess $configuration
     */
    public function __construct($configuration)
    {
        $this->parameters = array();
        $this->configuration = $configuration;
    }

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
     * Set id
     *
     * @param integer $id
     *
     * @return Source
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set code
     *
     * @param string $code
     *
     * @return Source
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get configuration
     *
     * @return string
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * Set configuration
     *
     * @param string $configuration
     *
     * @return Source
     */
    public function setConfiguration($configuration)
    {
        $this->configuration = $configuration;

        return $this;
    }

    /**
     * Get parameters
     *
     * @return string
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Set parameters
     *
     * @param string $parameters
     *
     * @return Source
     */
    public function setParameters($parameters)
    {
        $this->parameters = $parameters;

        return $this;
    }

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function beforeSave()
    {
        // process configuration
        $this->parameters = $this->configuration->process(array('configuration' => $this->parameters));
        // prepare for saving
        $this->parameters = json_encode($this->parameters);
        $this->configuration = get_class($this->configuration);
    }

    /**
     * @ORM\PostLoad
     * @ORM\PostPersist
     * @ORM\PostUpdate
     */
    public function afterLoad()
    {
        $this->parameters = json_decode($this->parameters, true);
        $className = $this->configuration;
        $this->configuration = new $className();
    }

}
