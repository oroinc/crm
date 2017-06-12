<?php

namespace OroCRM\Bundle\CallBundle\Entity;

use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Translatable\Translatable;

use Doctrine\ORM\Mapping as ORM;

use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;

/**
 * CallDirection
 *
 * @ORM\Table(name="orocrm_call_direction")
 * @ORM\Entity
 * @Gedmo\TranslationEntity(class="OroCRM\Bundle\CallBundle\Entity\CallDirectionTranslation")
 * @Config(
 *      defaultValues={
 *          "grouping"={
 *              "groups"={"dictionary"}
 *          },
 *          "dictionary"={
 *              "virtual_fields"={"label"},
 *              "search_fields"={"label"},
 *              "representation_field"="label",
 *          }
 *      }
 * )
 */
class CallDirection implements Translatable
{
    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=32)
     * @ORM\Id
     */
    protected $name;

    /**
     * @var string
     *
     * @Gedmo\Translatable
     * @ORM\Column(name="label", type="string", length=255, unique=true)
     */
    protected $label;

    /**
     * @Gedmo\Locale
     */
    protected $locale;

    /**
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * Get type name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set address type label
     *
     * @param string $label
     * @return CallDirection
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Get address type label
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Set locale
     *
     * @param string $locale
     * @return CallDirection
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * Returns locale code
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->label;
    }
}
