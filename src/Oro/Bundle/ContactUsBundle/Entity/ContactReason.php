<?php

namespace Oro\Bundle\ContactUsBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityInterface;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityTrait;
use Oro\Bundle\LocaleBundle\Entity\FallbackTrait;
use Oro\Bundle\LocaleBundle\Entity\LocalizedFallbackValue;

/**
 * Entity that represents contact reason
 *
 * @ORM\Entity(repositoryClass="Oro\Bundle\ContactUsBundle\Entity\Repository\ContactReasonRepository")
 * @ORM\Table(name="orocrm_contactus_contact_rsn")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 *
 * @Config(
 *      routeName="oro_contactus_reason_index",
 *      defaultValues={
 *          "grouping"={
 *              "groups"={"dictionary"}
 *          },
 *          "grid"={
 *              "default"="orcrm-contact-reasons-grid"
 *          },
 *          "form"={
 *              "form_type"="Oro\Bundle\ContactUsBundle\Form\Type\ContactReasonSelectType",
 *              "grid_name"="orcrm-contact-reasons-grid"
 *          },
 *          "security"={
 *              "type"="ACL",
 *              "permissions"="All",
 *              "group_name"="",
 *              "category"="account_management"
 *          },
 *      }
 * )
 * @method LocalizedFallbackValue getDefaultTitle()
 * @method setDefaultTitle(string $value)
 */
class ContactReason implements ExtendEntityInterface
{
    use SoftDeleteableEntity;
    use FallbackTrait;
    use ExtendEntityTrait;

    /**
     * @var integer
     *
     * @ORM\Id
     * @ORM\Column(type="integer", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var Collection|LocalizedFallbackValue[]
     * @ORM\ManyToMany(
     *      targetEntity="Oro\Bundle\LocaleBundle\Entity\LocalizedFallbackValue",
     *      cascade={"ALL"},
     *      orphanRemoval=true
     * )
     * @ORM\JoinTable(
     *      name="orocrm_contactus_contact_rsn_t",
     *      joinColumns={
     *          @ORM\JoinColumn(name="contact_reason_id", referencedColumnName="id", onDelete="CASCADE")
     *      },
     *      inverseJoinColumns={
     *          @ORM\JoinColumn(name="localized_value_id", referencedColumnName="id", onDelete="CASCADE", unique=true)
     *      }
     * )
     */
    protected $titles;

    /**
     * @param null|string $defaultTitle
     */
    public function __construct($defaultTitle = null)
    {
        $this->titles = new ArrayCollection();
        $this->setDefaultTitle((string)$defaultTitle);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param Collection|LocalizedFallbackValue[] $titles
     */
    public function setTitles($titles)
    {
        $this->titles = $titles;
        $defaultTitle = $this->getDefaultFallbackValue($this->titles);
        $this->setDefaultTitle((string)$defaultTitle);
    }

    /**
     * @return Collection|LocalizedFallbackValue[]
     */
    public function getTitles()
    {
        return $this->titles;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->getDefaultFallbackValue($this->titles);
    }
}
