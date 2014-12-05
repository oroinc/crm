<?php

namespace OroCRM\Bundle\AnalyticsBundle\Model;

interface RFMAwareInterface extends AnalyticsAwareInterface
{
    /**
     * @return int
     */
    public function getRecency();

    /**
     * @param int $recency
     *
     * @return RFMAwareInterface
     */
    public function setRecency($recency);

    /**
     * @return int
     */
    public function getFrequency();

    /**
     * @param int $frequency
     *
     * @return RFMAwareInterface
     */
    public function setFrequency($frequency);

    /**
     * @return int
     */
    public function getMonetary();

    /**
     * @param int $monetary
     *
     * @return RFMAwareInterface
     */
    public function setMonetary($monetary);
}
