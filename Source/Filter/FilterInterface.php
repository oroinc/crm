<?php
namespace Oro\Bundle\DataFlowBundle\Source\Filter;

use Oro\Bundle\DataFlowBundle\Source\SourceInterface;

/**
 * Interface for source filters
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
interface FilterInterface
{

    /**
     * Filter method
     * @param SourceInterface $source
     */
    public function filter(SourceInterface $source);

    /**
     * Get name
     * @return string
     */
    public function getName();

}
