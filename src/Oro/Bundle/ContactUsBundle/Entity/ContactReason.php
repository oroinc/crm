<?php

namespace Oro\Bundle\ContactUsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;

use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;

/**
 * @ORM\Entity(repositoryClass="Oro\Bundle\ContactUsBundle\Entity\Repository\ContactReasonRepository")
 * @ORM\Table(name="orocrm_contactus_contact_rsn")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 *
 * @Config(
 *      defaultValues={
 *          "grouping"={
 *              "groups"={"dictionary"}
 *          },
 *          "grid"={
 *              "default"="orcrm-contact-reasons-grid"
 *          }
 *      }
 * )
 */
class ContactReason
{
    use SoftDeleteableEntity;

    /**
     * @var integer
     *
     * @ORM\Id
     * @ORM\Column(type="integer", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="label", type="string")
     */
    protected $label;

    public function __construct($label = null)
    {
        $this->label = $label;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getLabel();
    }
}
