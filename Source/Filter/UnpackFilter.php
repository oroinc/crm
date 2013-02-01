<?php
namespace Oro\Bundle\DataFlowBundle\Source\Filter;

use Oro\Bundle\DataFlowBundle\Source\Filter\FilterInterface;

use Oro\Bundle\DataFlowBundle\Source\SourceInterface;

/**
 * Filter to unpack a content
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
class UnpackFilter implements FilterInterface
{

    /**
     * Pathfile after unpacking content
     * @var string
     */
    protected $path;

    /**
     * {@inheritdoc}
     */
    protected $name = 'unpack';

    /**
     * Unpacker filter constructor
     * @param string $path
     */
    public function __construct($path)
    {
        $this->path = $path;
    }

    /**
     * {@inheritdoc}
     */
    public function filter(SourceInterface $source)
    {
        file_put_contents($this->path, readgzfile($source->getPath()));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'unpack';
    }

}
