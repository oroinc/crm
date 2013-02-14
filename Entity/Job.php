<?php
namespace Oro\Bundle\DataFlowBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Entity job is an instance of a configured job for a configured connector
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 * @ORM\Table(name="oro_dataflow_job")
 * @ORM\Entity()
 */
class Job
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
     * @ORM\Column(name="connector_service_id", type="string", length=255)
     */
    protected $connectorService;

    /**
     * @var Configuration $connectorConfiguration
     *
     * @ORM\ManyToOne(targetEntity="Configuration")
     * @ORM\JoinColumn(name="connector_configuration_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $connectorConfiguration;

    /**
     * Job service id
     *
     * @var string
     *
     * @ORM\Column(name="job_service_id", type="string", length=255)
     */
    protected $jobService;

    /**
     * @var Configuration $connectorConfiguration
     *
     * @ORM\ManyToOne(targetEntity="Configuration")
     * @ORM\JoinColumn(name="job_configuration_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $jobConfigurationId;

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
     * @param string $connectorService
     *
     * @return Job
     */
    public function setConnectorService($connectorService)
    {
        $this->connectorService = $connectorService;

        return $this;
    }

    /**
     * Get connector service id
     *
     * @return string
     */
    public function getConnectorService()
    {
        return $this->connectorService;
    }

    /**
     * Set connector configuration
     *
     * @param Configuration $connectorConfiguration
     *
     * @return Job
     */
    public function setConnectorConfiguration(Configuration $connectorConfiguration)
    {
        $this->connectorConfiguration = $connectorConfiguration;

        return $this;
    }

    /**
     * Get connector configuration
     *
     * @return Configuration
     */
    public function getConnectorConfiguration()
    {
        return $this->connectorConfiguration;
    }

    /**
     * Set job service id
     *
     * @param string $connectorService
     *
     * @return Job
     */
    public function setJobService($jobService)
    {
        $this->jobService = $jobService;

        return $this;
    }

    /**
     * Get job service id
     *
     * @return string
     */
    public function getJobService()
    {
        return $this->jobService;
    }

    /**
     * Set job configuration
     *
     * @param Configuration $connectorConfiguration
     *
     * @return Job
     */
    public function setJobConfiguration(Configuration $jobConfiguration)
    {
        $this->jobConfiguration = $jobConfiguration;

        return $this;
    }

    /**
     * Get job configuration
     *
     * @return Configuration
     */
    public function getJobConfiguration()
    {
        return $this->jobConfiguration;
    }

}
