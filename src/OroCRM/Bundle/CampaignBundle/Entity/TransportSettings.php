<?php

namespace OroCRM\Bundle\CampaignBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Class Transport Settings
 *
 * @ORM\Table(name="orocrm_cmpgn_transport_stngs")
 * @ORM\Entity
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string", length=50)
 */
abstract class TransportSettings
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var ParameterBag
     */
    protected $settings;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return ParameterBag
     */
    abstract public function getSettingsBag();
}
