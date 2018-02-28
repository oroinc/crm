<?php

namespace Oro\Bundle\MagentoBundle\Provider\Customer;

use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Oro\Bundle\IntegrationBundle\Manager\TypesRegistry;
use Oro\Bundle\IntegrationBundle\Provider\IconAwareIntegrationInterface;
use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\SalesBundle\Provider\Customer\CustomerIconProviderInterface;
use Oro\Bundle\UIBundle\Model\Image;

class CustomerIconProvider implements CustomerIconProviderInterface
{
    /** @var TypesRegistry */
    protected $integrationTypeRegistry;

    /** @var CacheManager */
    protected $cacheManager;

    /**
     * @param TypesRegistry $integrationTypeRegistry
     * @param CacheManager  $cacheManager
     */
    public function __construct(
        TypesRegistry $integrationTypeRegistry,
        CacheManager $cacheManager
    ) {
        $this->integrationTypeRegistry = $integrationTypeRegistry;
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

        $channelTypeCode = $entity->getChannel()->getType();
        /**
         * @var IconAwareIntegrationInterface $channelType
         */
        $channelType = $this->integrationTypeRegistry->getIntegrationByType($channelTypeCode);

        if ($channelType instanceof IconAwareIntegrationInterface) {
            return new Image(
                Image::TYPE_FILE_PATH,
                ['path' => $this->cacheManager->getBrowserPath($channelType->getIcon(), 'avatar_xsmall')]
            );
        }

        return null;
    }
}
