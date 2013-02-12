<?php
namespace Oro\Bundle\DataFlowBundle\Configuration;

use JMS\Serializer\Annotation\Exclude;

/**
 * Abstract Configuration
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
abstract class AbstractConfiguration implements ConfigurationInterface
{
    /**
     * @Exclude
     * @var integer
     */
    protected $id;

    /**
     * @Exclude
     * @var description
     */
    protected $description;

    /**
     * {@inheritDoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritDoc}
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * {@inheritDoc}
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

}
