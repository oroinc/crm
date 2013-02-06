<?php
namespace Oro\Bundle\DataFlowBundle\Connector;

use Symfony\Component\Config\Definition\ConfigurationInterface;
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
     * Process configuration
     *
     * @param \ArrayAccess $parameters
     *
     * @return \ArrayAccess $configuration
     */
    public function process($parameters)
    {
        $processor = new Processor();
        $configuration = $processor->processConfiguration($this, $parameters);

        return $configuration;
    }

}
