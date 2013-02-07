<?php
namespace Oro\Bundle\DataFlowBundle\Configuration;

use Symfony\Component\Config\Definition\ConfigurationInterface as SfConfigurationInterface;

/**
 * Configuration
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
interface ConfigurationInterface extends SfConfigurationInterface
{

    /**
     * Process configuration
     *
     * @return ConfigurationInterface
     */
    public function process();

}
