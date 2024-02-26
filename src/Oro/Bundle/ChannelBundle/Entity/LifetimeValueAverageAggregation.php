<?php

namespace Oro\Bundle\ChannelBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\ChannelBundle\Entity\Repository\LifetimeValueAverageAggregationRepository;
use Oro\Bundle\ChannelBundle\Model\ChannelAwareInterface;

/**
* Entity that represents Lifetime Value Average Aggregation
*
*/
#[ORM\Entity(repositoryClass: LifetimeValueAverageAggregationRepository::class)]
#[ORM\Table(name: 'orocrm_channel_ltime_avg_aggr')]
#[ORM\HasLifecycleCallbacks]
class LifetimeValueAverageAggregation implements ChannelAwareInterface
{
    #[ORM\Column(name: 'id', type: Types::INTEGER, options: ['unsigned' => true])]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected ?int $id = null;

    /**
     * @var double
     */
    #[ORM\Column(name: 'amount', type: 'money', nullable: false)]
    protected $amount;

    #[ORM\ManyToOne(targetEntity: Channel::class)]
    #[ORM\JoinColumn(name: 'data_channel_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected ?Channel $dataChannel = null;

    /**
     * @var \DateTimeInterface $aggregationDate
     *
     * NOTE: Always in LOCAL TZ
     */
    #[ORM\Column(name: 'aggregation_date', type: Types::DATETIME_MUTABLE, nullable: false)]
    protected ?\DateTimeInterface $aggregationDate = null;

    /**
     * @var int
     *
     * NOTE: denormalized value in LOCAL TZ
     */
    #[ORM\Column(name: 'month', type: Types::SMALLINT, nullable: false, options: ['unsigned' => true])]
    protected ?int $month = null;

    /**
     * @var int
     *
     * NOTE: denormalized value in LOCAL TZ
     */
    #[ORM\Column(name: 'quarter', type: Types::SMALLINT, nullable: false, options: ['unsigned' => true])]
    protected ?int $quarter = null;

    /**
     * @var int
     *
     * NOTE: denormalized value in LOCAL TZ
     */
    #[ORM\Column(name: 'year', type: Types::SMALLINT, nullable: false, options: ['unsigned' => true])]
    protected ?int $year = null;

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

    #[ORM\PrePersist]
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
