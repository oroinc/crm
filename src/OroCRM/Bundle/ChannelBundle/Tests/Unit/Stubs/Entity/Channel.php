<?php

namespace OroCRM\Bundle\ChannelBundle\Tests\Unit\Stubs\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class Channel
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var Integration
     *
     * @ORM\OneToOne(targetEntity="Integration", cascade={"all"}, orphanRemoval=true)
     * @ORM\JoinColumn(name="data_source_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $dataSource;

    /**
     * @var boolean
     *
     * @ORM\Column(name="status", type="boolean", nullable=false)
     */
    protected $status;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param Integration $dataSource
     */
    public function setDataSource(Integration $dataSource)
    {
        $this->dataSource = $dataSource;
    }

    /**
     * @return Integration
     */
    public function getDataSource()
    {
        return $this->dataSource;
    }
}
