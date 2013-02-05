<?php
namespace Oro\Bundle\DataFlowBundle\Source\Filter;

use Oro\Bundle\DataFlowBundle\Exception\UnpackException;

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
     * @staticvar integer
     */
    const LENGTH_BYTES = 10000;

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
        if (!file_exists($source->getPath())) {
            throw new UnpackException('Archive file does not exist');
        }

        // open gzip files
        $gzip = gzopen($source->getPath(), 'rb');

        // write unpack content in a new file
        $handler = fopen($this->path, 'w');
        while ($content = gzread($gzip, self::LENGTH_BYTES)) {
            fputs($handler, $content);
        }

        // close files
        gzclose($gzip);
        fclose($handler);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'unpack';
    }

}
