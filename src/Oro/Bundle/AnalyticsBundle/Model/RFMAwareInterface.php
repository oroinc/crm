<?php

namespace Oro\Bundle\AnalyticsBundle\Model;

/**
 * Marks entities as RFM-aware, indicating they track and expose Recency, Frequency, and Monetary metrics
 * for customer analysis.
 */
interface RFMAwareInterface extends AnalyticsAwareInterface
{
    const RFM_STATE_KEY = 'rfm_enabled';
    const RFM_REQUIRE_DROP_KEY = 'rfm_require_drop';

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
