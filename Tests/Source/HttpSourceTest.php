<?php
namespace Oro\Bundle\DataFlowBundle\Tests\Source;

use Ddeboer\DataImport\Source\Http;

use Oro\Bundle\DataFlowBundle\Source\HttpSource;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
class HttpSourceTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Url for remote file to download
     * @staticvar string
     */
    const REMOTE_FILE = 'https://i1.wp.com/a248.e.akamai.net/assets.github.com/images/gravatars/gravatar-org-420.png?ssl=1';

    /**
     *
     * @staticvar
     */
    const LOCALE_FILE = '/tmp/image.png';

    /**
     * Test related method
     */
    public function testDownloadFile()
    {
        $source = new HttpSource(self::REMOTE_FILE);
        $source->downloadFile(self::LOCALE_FILE);
        $this->assertTrue(file_exists(self::LOCALE_FILE));
    }
}
