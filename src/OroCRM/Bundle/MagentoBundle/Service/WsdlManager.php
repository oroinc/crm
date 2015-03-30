<?php

namespace OroCRM\Bundle\MagentoBundle\Service;

use Symfony\Component\Filesystem\Filesystem;

class WsdlManager
{
    /**
     * @var string
     */
    protected $cachePath;

    /**
     * @var Filesystem
     */
    protected $fs;

    /**
     * @param Filesystem $filesystem
     * @param $cachePath
     */
    public function __construct(Filesystem $filesystem, $cachePath)
    {
        $this->cachePath = $cachePath;
        $this->fs = $filesystem;

        $filesystem->mkdir($this->getWsdlCachePath());
    }

    /**
     * @param string $url
     * @return string
     */
    public function loadWsdl($url)
    {
        $wsdl = file_get_contents($url);
        $cacheFilePath = $this->getCachedWsdlPath($url);
        file_put_contents($cacheFilePath, $wsdl);

        return $cacheFilePath;
    }

    /**
     * @param string $url
     * @return bool
     */
    public function isCacheLoaded($url)
    {
        return $this->fs->exists($this->getCachedWsdlPath($url));
    }

    /**
     * @param string $url
     * @return string
     */
    public function getCachedWsdlPath($url)
    {
        return $this->getWsdlCachePath() . DIRECTORY_SEPARATOR . md5($url) . '.wsdl';
    }

    /**
     * @param string $url
     */
    public function clearCacheForUrl($url)
    {
        $this->fs->remove($this->getCachedWsdlPath($url));
    }

    /**
     * @param string $url
     * @return string
     */
    public function refreshCacheForUrl($url)
    {
        $this->clearCacheForUrl($url);

        return $this->loadWsdl($url);
    }

    /**
     * @return string
     */
    protected function getWsdlCachePath()
    {
        return $this->cachePath . DIRECTORY_SEPARATOR . 'wsdl';
    }
}
