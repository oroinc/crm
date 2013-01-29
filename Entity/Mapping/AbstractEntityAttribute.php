<?php
namespace Oro\Bundle\FlexibleEntityBundle\Entity\Mapping;

use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;
use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttributeOption;
use Doctrine\ORM\Mapping as ORM;

/**
 * Base Doctrine ORM entity attribute
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT  MIT
 *
 */
abstract class AbstractEntityAttribute extends AbstractAttribute
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string $code
     *
     * @ORM\Column(name="code", type="string", length=255)
     */
    protected $code;

    /**
     * @var string $entityType
     *
     * @ORM\Column(name="entity_type", type="string", length=255)
     */
    protected $entityType;

    /**
     * @var string $backendType
     *
     * @ORM\Column(name="backend_type", type="string", length=255)
     */
    protected $backendType;

    /**
     * @var string $backendStorage
     *
     * @ORM\Column(name="backend_storage", type="string", length=255)
     */
    protected $backendStorage;

    /**
     * @var string $frontendType
     *
     * @ORM\Column(name="frontend_type", type="string", length=255)
     */
    protected $frontendType;

    /**
     * @var datetime $created
     *
     * @ORM\Column(type="datetime")
     */
    protected $created;

    /**
     * @var datetime $updated
     *
     * @ORM\Column(type="datetime")
     */
    protected $updated;

    /**
     * @ORM\Column(name="is_required", type="boolean")
     */
    protected $required;

    /**
     * @ORM\Column(name="is_unique", type="boolean")
     */
    protected $unique;

    /**
     * @ORM\Column(name="default_value", type="string", length=255, nullable=true)
     */
    protected $defaultValue;

    /**
     * @ORM\Column(name="is_searchable", type="boolean")
     */
    protected $searchable;

    /**
     * @ORM\Column(name="is_translatable", type="boolean")
     */
    protected $translatable;

    /**
     * @ORM\Column(name="is_scopable", type="boolean")
     */
    protected $scopable;

    /**
     * @var ArrayCollection $options
     *
     * @ORM\OneToMany(targetEntity="AbstractEntityAttributeOption", mappedBy="attribute", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    protected $options;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->options      = new \Doctrine\Common\Collections\ArrayCollection();
        $this->required     = false;
        $this->unique       = false;
        $this->defaultValue = null;
        $this->searchable   = false;
        $this->translatable = false;
        $this->scopable     = false;
    }

    /**
     * Add option
     *
     * @param AbstractAttributeOption $option
     *
     * @return AbstractAttribute
     */
    public function addOption(AbstractAttributeOption $option)
    {
        $this->options[] = $option;
        $option->setAttribute($this);

        return $this;
    }

}
