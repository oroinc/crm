<?php
namespace Oro\Bundle\DataFlowBundle\Configuration;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;

/**
 * Configuration
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
abstract class AbstractConfiguration implements ConfigurationInterface
{

    /**
     * Root node name
     * @var string
     */
    const ROOT_NODE = 'parameters';

    /**
     * @var \ArrayAccess $parameters
     */
    protected $parameters;

    /**
     * Constructor
     * @param \ArrayAccess $parameters
     */
    public function __construct($parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * Process configuration
     *
     * @return AbstractConfiguration
     */
    public function process()
    {
        $processor = new Processor();
        $this->parameters = $processor->processConfiguration($this, $this->parameters);

        return $this;
    }

}
