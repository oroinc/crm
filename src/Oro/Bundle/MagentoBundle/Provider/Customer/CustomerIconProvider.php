<?php

namespace Oro\Bundle\MagentoBundle\Provider\Customer;

use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\MagentoBundle\Provider\ChannelType;
use Oro\Bundle\SalesBundle\Provider\Customer\CustomerIconProviderInterface;
use Oro\Bundle\UIBundle\Model\Image;

class CustomerIconProvider implements CustomerIconProviderInterface
{
    /** @var ChannelType */
    protected $channelType;

    /** @var CacheManager */
    protected $cacheManager;

    /**
     * @param ChannelType $channelType
     */
    public function __construct(ChannelType $channelType, CacheManager $cacheManager)
    {
        $this->channelType = $channelType;
        $this->cacheManager = $cacheManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getIcon($entity)
    {
        if (!$entity instanceof Customer) {
            return null;
        }

        return new Image(
            Image::TYPE_FILE_PATH,
            ['path' => $this->cacheManager->getBrowserPath($this->channelType->getIcon(), 'avatar_xsmall')]
        );
    }
}
