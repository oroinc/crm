<?php

namespace Oro\Bundle\MagentoBundle\Provider\Customer;

use Oro\Bundle\IntegrationBundle\Manager\TypesRegistry;
use Oro\Bundle\IntegrationBundle\Provider\IconAwareIntegrationInterface;
use Oro\Bundle\MagentoBundle\Entity\IntegrationAwareInterface;
use Oro\Bundle\SalesBundle\Provider\Customer\CustomerIconProviderInterface;
use Oro\Bundle\UIBundle\Model\Image;

class CustomerIconProvider implements CustomerIconProviderInterface
{
    /** @var TypesRegistry */
    protected $typesRegistry;

    /**
     * @param TypesRegistry $typesRegistry
     */
    public function __construct(TypesRegistry $typesRegistry)
    {
        $this->typesRegistry = $typesRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function getIcon($entity)
    {
        if (!$entity instanceof IntegrationAwareInterface || !$entity->getChannel()) {
            return null;
        }

        $channelType = $entity->getChannel()->getType();
        $channelTypes = $this->typesRegistry->getRegisteredChannelTypes();

        $entityChannel = $channelTypes->get($channelType);
        if (!$entityChannel instanceof IconAwareIntegrationInterface) {
            return null;
        }

        return new Image(Image::TYPE_FILE_PATH, ['path' => $entityChannel->getIcon()]);
    }
}
