<?php

namespace Oro\Bundle\AnalyticsBundle\Model;

trait RFMAwareTrait
{
    /**
     * @var int
     *
     * @ORM\Column(name="rfm_recency", type="integer", nullable=true)
     */
    protected $recency;

    /**
     * @var int
     *
     * @ORM\Column(name="rfm_frequency", type="integer", nullable=true)
     */
    protected $frequency;

    /**
     * @var int
     *
     * @ORM\Column(name="rfm_monetary", type="integer", nullable=true)
     */
    protected $monetary;

    /**
     * @return int
     */
    public function getRecency()
    {
        return $this->recency;
    }

    /**
     * @param int $recency
     *
     * @return RFMAwareInterface
     */
    public function setRecency($recency)
    {
        $this->recency = $recency;

        return $this;
    }

    /**
     * @return int
     */
    public function getFrequency()
    {
        return $this->frequency;
    }

    /**
     * @param int $frequency
     *
     * @return RFMAwareInterface
     */
    public function setFrequency($frequency)
    {
        $this->frequency = $frequency;

        return $this;
    }

    /**
     * @return int
     */
    public function getMonetary()
    {
        return $this->monetary;
    }

    /**
     * @param int $monetary
     *
     * @return RFMAwareInterface
     */
    public function setMonetary($monetary)
    {
        $this->monetary = $monetary;

        return $this;
    }
}
