<?php
namespace Oro\Bundle\DataFlowBundle\Source;

/**
 * Source interface for each source
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
interface SourceInterface
{

    /**
     * algorithm to explain how to get source content
     */
    public function getSource();

}
