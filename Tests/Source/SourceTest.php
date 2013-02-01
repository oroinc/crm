<?php
namespace Oro\Bundle\DataFlowBundle\Tests\Source;

use Oro\Bundle\DataFlowBundle\Source\Filter\UnpackFilter;

use Oro\Bundle\DataFlowBundle\Source\HttpSource;

/**
 * Test file for source
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
class SourceTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Unit to present how to use DataFlowBundle
     */
    public function testFilters()
    {
        // Create a source
        $source = new HttpSource(
            'http://data.icecat.biz/export/freexml.int/refs/SuppliersList.xml.gz',
            '/tmp/SuppliersList.xml.gz',
            'NicolasDupont',
            '1cec4t**)'
        );

        // Add a filter (unpacking content)
        $filter = new UnpackFilter('/tmp/SuppliersList.xml');
        $source->addPostFilter($filter);

        // Get source
        $file = $source->getSource();
    }

}