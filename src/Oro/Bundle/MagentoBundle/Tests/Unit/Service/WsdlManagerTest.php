<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Service;

use Guzzle\Http\ClientInterface;
use Oro\Bundle\MagentoBundle\Service\WsdlManager;
use Oro\Component\Testing\TempDirExtension;
use Symfony\Component\Filesystem\Filesystem;

class WsdlManagerTest extends \PHPUnit\Framework\TestCase
{
    use TempDirExtension;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|Filesystem
     */
    protected $fs;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|ClientInterface
     */
    protected $guzzleClient;

    /**
     * @var string
     */
    protected $cacheDir;

    /**
     * @var WsdlManager
     */
    protected $manager;

    protected function setUp(): void
    {
        $this->fs = $this->getMockBuilder('Symfony\Component\Filesystem\Filesystem')
            ->disableOriginalConstructor()
            ->getMock();
        $this->guzzleClient = $this->getMockBuilder('Guzzle\Http\ClientInterface')
            ->setMethods(['get'])
            ->getMockForAbstractClass();
        $this->cacheDir = $this->getTempDir('magento_wsdl', null);

        $this->manager = new WsdlManager($this->fs, $this->guzzleClient, $this->cacheDir);
    }

    protected function tearDown(): void
    {
        unset($this->manager, $this->fs, $this->guzzleClient);
    }

    public function testGetCachedWsdlPath()
    {
        $url = 'http://test.local';
        $expected = $this->manager->getWsdlCachePath() . DIRECTORY_SEPARATOR . md5($url) . '.wsdl';
        $this->assertEquals($expected, $this->manager->getCachedWsdlPath($url));
    }

    /**
     * @dataProvider isLoadedDataProvider
     * @param bool $expected
     */
    public function testIsCacheLoaded($expected)
    {
        $url = 'http://test.local';
        $path = $this->manager->getCachedWsdlPath($url);
        $this->fs->expects($this->once())
            ->method('exists')
            ->with($path)
            ->will($this->returnValue($expected));

        $this->assertEquals($expected, $this->manager->isCacheLoaded($url));
    }

    /**
     * @return array
     */
    public function isLoadedDataProvider()
    {
        return [
            [true],
            [false]
        ];
    }

    public function testClearCacheForUrl()
    {
        $url = 'http://test.local';
        $path = $this->manager->getCachedWsdlPath($url);

        $this->assertClearCache($path);
        $this->manager->clearCacheForUrl($url);
    }

    public function testLoadWsdl()
    {
        $url = 'http://test.local';
        $path = $this->manager->getCachedWsdlPath($url);
        $wsdl = 'WSDL';

        $this->assertLoadWsdl($wsdl, $url, $path);
        $this->assertEquals($path, $this->manager->loadWsdl($url));
    }

    public function testRefreshCacheForUrl()
    {
        $url = 'http://test.local';
        $path = $this->manager->getCachedWsdlPath($url);
        $wsdl = 'WSDL';

        $this->assertClearCache($path);
        $this->assertLoadWsdl($wsdl, $url, $path);

        $this->assertEquals($path, $this->manager->refreshCacheForUrl($url));
    }

    /**
     * @param string $wsdl
     * @param string $url
     * @param string $path
     */
    protected function assertLoadWsdl($wsdl, $url, $path)
    {
        $response = $this->getMockBuilder('Guzzle\Http\Message\Response')
            ->disableOriginalConstructor()
            ->getMock();
        $response->expects($this->once())
            ->method('getBody')
            ->with(true)
            ->will($this->returnValue($wsdl));

        $request = $this->getMockBuilder('Guzzle\Http\Message\RequestInterface')
            ->setMethods(['send'])
            ->getMockForAbstractClass();
        $request->expects($this->once())
            ->method('send')
            ->will($this->returnValue($response));

        $this->guzzleClient->expects($this->once())
            ->method('get')
            ->with($url)
            ->will($this->returnValue($request));

        $this->fs->expects($this->once())
            ->method('dumpFile')
            ->with($path, $wsdl);
    }

    /**
     * @param string $path
     */
    protected function assertClearCache($path)
    {
        $this->fs->expects($this->once())
            ->method('exists')
            ->with($path)
            ->willReturn(true);

        $this->fs->expects($this->once())
            ->method('remove')
            ->with($path);
    }

    public function testClearAllWsdlCaches()
    {
        $path = $this->manager->getWsdlCachePath();
        $this->assertClearCache($path);
        $this->manager->clearAllWsdlCaches();
    }
}
