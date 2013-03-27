<?php
namespace Oro\Bundle\SegmentationTreeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Translatable\Entity\MappedSuperclass\AbstractTranslation;

/**
 * Item segment translation entity
 *
 * @ORM\Table(name="oro_segmentationtree_tests_itemsegment_translations", indexes={
 *      @ORM\Index(
 *          name="oro_segmentationtree_tests_itemsegment_translation_idx",
 *          columns={"locale", "object_class", "field", "foreign_key"}
 *     )
 * })
 * @ORM\Entity(repositoryClass="Gedmo\Translatable\Entity\Repository\TranslationRepository")
 */
class ItemSegmentTranslation extends AbstractTranslation
{
    /**
     * All required columns are mapped through inherited superclass
     */
}
