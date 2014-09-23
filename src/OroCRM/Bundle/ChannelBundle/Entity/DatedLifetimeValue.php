<?php

namespace OroCRM\Bundle\ChannelBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use OroCRM\Bundle\ChannelBundle\Model\ChannelAwareInterface;

/**
 * @ORM\Entity(repositoryClass="OroCRM\Bundle\ChannelBundle\Entity\Repository\DatedLifetimeValueRepository")
 * @ORM\Table(name="orocrm_channel_dated_lifetime")
 * @ORM\HasLifecycleCallbacks()
 */
class DatedLifetimeValue implements ChannelAwareInterface
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
     * @var Channel
     *
     * @ORM\ManyToOne(targetEntity="OroCRM\Bundle\ChannelBundle\Entity\Channel")
     * @ORM\JoinColumn(name="data_channel_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $dataChannel;

    /**
     * @var double
     *
     * @ORM\Column(name="amount", type="money")
     */
    protected $amount;

    /**
     * @var \DateTime $createdAt
     *
     * @ORM\Column(type="datetime", name="created_at")
     */
    protected $createdAt;

    /**
     * @var int
     *
     * @ORM\Column(name="month", type="smallint", options={"unsigned"=true})
     */
    protected $month;

    /**
     * @var int
     *
     * @ORM\Column(name="quarter", type="smallint", options={"unsigned"=true})
     */
    protected $quarter;

    /**
     * @var int
     *
     * @ORM\Column(name="year", type="smallint", options={"unsigned"=true})
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
        $this->amount = $amount;
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
     */
    public function setDataChannel(Channel $dataChannel)
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
     * @param \DateTime $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param mixed $month
     */
    public function setMonth($month)
    {
        $this->month = $month;
    }

    /**
     * @return mixed
     */
    public function getMonth()
    {
        return $this->month;
    }

    /**
     * @param mixed $quarter
     */
    public function setQuarter($quarter)
    {
        $this->quarter = $quarter;
    }

    /**
     * @return mixed
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
        $this->year = $year;
    }

    /**
     * @return int
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        $date = $this->getCreatedAt();

        if (empty($date)) {
            $date = new \DateTime('now', new \DateTimeZone('UTC'));
            $this->setCreatedAt($date);
        }

        $this->setMonth($date->format('m'));
        $this->setYear($date->format('Y'));
        $this->setQuarter(ceil($date->format('m') / 3));
    }
}
