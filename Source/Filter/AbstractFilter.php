<?php
namespace Oro\Bundle\DataFlowBundle\Source\Filter;

use Oro\Bundle\DataFlowBundle\Source\SourceInterface;

/**
 * Abstract filter
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 * @abstract
 */
abstract class AbstractFilter implements FilterInterface
{

    /**
     * Name of the filter
     * @var string
     */
    protected $name;

    /**
     * {@inheritdoc}
     */
    public static function getName()
    {
        return $this->name;
    }

}
