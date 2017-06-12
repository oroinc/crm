<?php

namespace OroCRM\Bundle\CallBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Translatable\Entity\MappedSuperclass\AbstractTranslation;

/**
 * @ORM\Table(name="orocrm_call_direction_trans", indexes={
 *      @ORM\Index(
 *          name="orocrm_call_direction_trans_idx", columns={"locale", "object_class", "field", "foreign_key"}
 *      )
 * })
 * @ORM\Entity(repositoryClass="Gedmo\Translatable\Entity\Repository\TranslationRepository")
 */
class CallDirectionTranslation extends AbstractTranslation
{
    /**
     * @var string $foreignKey
     *
     * @ORM\Column(name="foreign_key", type="string", length=32)
     */
    protected $foreignKey;

    /**
     * @var string $content
     *
     * @ORM\Column(type="string", length=255)
     */
    protected $content;
}
