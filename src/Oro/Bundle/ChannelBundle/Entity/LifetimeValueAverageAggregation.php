<?php

namespace Oro\Bundle\ChannelBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Oro\Bundle\ChannelBundle\Model\ChannelAwareInterface;

/**
 * @ORM\Entity(
 *     repositoryClass="Oro\Bundle\ChannelBundle\Entity\Repository\LifetimeValueAverageAggregationRepository"
 * )
 * @ORM\Table(name="orocrm_channel_ltime_avg_aggr")
 * @ORM\HasLifecycleCallbacks
 */
class LifetimeValueAverageAggregation implements ChannelAwareInterface
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var double
     *
     * @ORM\Column(name="amount", type="money", nullable=false)
     */
    protected $amount;

    /**
     * @var Channel
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\ChannelBundle\Entity\Channel")
     * @ORM\JoinColumn(name="data_channel_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $dataChannel;

    /**
     * @var \DateTime $aggregationDate
     *
     * NOTE: Always in LOCAL TZ
     * @ORM\Column(type="datetime", name="aggregation_date", nullable=false)
     */
    protected $aggregationDate;

    /**
     * @var int
     *
     * NOTE: denormalized value in LOCAL TZ
     * @ORM\Column(name="month", type="smallint", options={"unsigned"=true}, nullable=false)
     */
    protected $month;

    /**
     * @var int
     *
     * NOTE: denormalized value in LOCAL TZ
     * @ORM\Column(name="quarter", type="smallint", options={"unsigned"=true}, nullable=false)
     */
    protected $quarter;

    /**
     * @var int
     *
     * NOTE: denormalized value in LOCAL TZ
     * @ORM\Column(name="year", type="smallint", options={"unsigned"=true}, nullable=false)
     */
    protected $year;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param float $amount
     */
    public function setAmount($amount)
    {
        $this->amount = (float)$amount;
    }

    /**
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param Channel $dataChannel
     *
     * @TODO remove null after BAP-5248
     */
    public function setDataChannel(Channel $dataChannel = null)
    {
        $this->dataChannel = $dataChannel;
    }

    /**
     * @return Channel
     */
    public function getDataChannel()
    {
        return $this->dataChannel;
    }

    /**
     * @param int $month
     */
    public function setMonth($month)
    {
        $this->month = (int)$month;
    }

    /**
     * @return int
     */
    public function getMonth()
    {
        return $this->month;
    }

    /**
     * @param int $quarter
     */
    public function setQuarter($quarter)
    {
        $this->quarter = (int)$quarter;
    }

    /**
     * @return int
     */
    public function getQuarter()
    {
        return $this->quarter;
    }

    /**
     * @param int $year
     */
    public function setYear($year)
    {
        $this->year = (int)$year;
    }

    /**
     * @return int
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * @param \DateTime $aggregationDate
     */
    public function setAggregationDate(\DateTime $aggregationDate)
    {
        $this->aggregationDate = \DateTime::createFromFormat(
            \DateTime::ISO8601,
            $aggregationDate->format('Y-m-01\T00:00:00+0000')
        );
    }

    /**
     * @return \DateTime
     */
    public function getAggregationDate()
    {
        return $this->aggregationDate;
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        if (!$this->getAggregationDate()) {
            $date = new \DateTime('now', new \DateTimeZone('UTC'));
            $this->setAggregationDate($date);
        }

        $date = $this->getAggregationDate();
        $this->setMonth($date->format('m'));
        $this->setYear($date->format('Y'));
        $this->setQuarter(ceil($date->format('m') / 3));
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->getId();
    }
}
