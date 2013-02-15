<?php
namespace Oro\Bundle\DataFlowBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Entity connector is an instance of a configured connector
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 * @ORM\Table(name="oro_dataflow_connector")
 * @ORM\Entity()
 */
class Connector
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
     * Connector service id
     *
     * @var string
     *
     * @ORM\Column(name="service_id", type="string", length=255)
     */
    protected $serviceId;

    /**
     * @var Configuration $configuration
     *
     * @ORM\ManyToOne(targetEntity="Configuration")
     * @ORM\JoinColumn(name="configuration_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $configuration;

    /**
     * @var Value
     *
     * @ORM\OneToMany(targetEntity="Job", mappedBy="connector", cascade={"persist", "remove"})
     */
    protected $jobs;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->jobs = new ArrayCollection();
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
     * Set connector service id
     *
     * @param string $serviceId
     *
     * @return Job
     */
    public function setServiceId($serviceId)
    {
        $this->serviceId = $serviceId;

        return $this;
    }

    /**
     * Get connector service id
     *
     * @return string
     */
    public function getServiceId()
    {
        return $this->serviceId;
    }

    /**
     * Set connector configuration
     *
     * @param Configuration $configuration
     *
     * @return Job
     */
    public function setConfiguration(Configuration $configuration)
    {
        $this->configuration = $configuration;

        return $this;
    }

    /**
     * Get connector configuration
     *
     * @return Configuration
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * Add job
     *
     * @param Job $job
     *
     * @return Configuration
     */
    public function addJob(Job $job)
    {
        $this->jobs[] = $job;
        $job->setConnector($this);

        return $this;
    }

    /**
     * Remove job
     *
     * @param Job $value
     */
    public function removeJob(Job $job)
    {
        $this->jobs->removeElement($job);
    }

    /**
     * Get jobs
     *
     * @return \ArrayAccess
     */
    public function getJobs()
    {
        return $this->jobs;
    }
}
