<?php

namespace Oro\Bundle\MagentoBundle\Provider\Customer;

use Oro\Bundle\MagentoBundle\Provider\ChannelType;
use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\SalesBundle\Provider\Customer\CustomerIconProviderInterface;
use Oro\Bundle\UIBundle\Model\Image;

class CustomerIconProvider implements CustomerIconProviderInterface
{
    /** @var ChannelType */
    protected $channelType;

    /**
     * @param ChannelType $channelType
     */
    public function __construct(ChannelType $channelType)
    {
        $this->channelType = $channelType;
    }

    /**
     * {@inheritdoc}
     */
    public function getIcon($entity)
    {
        if (!$entity instanceof Customer) {
            return null;
        }
        return new Image(Image::TYPE_FILE_PATH, ['path' => '/'.$this->channelType->getIcon()]);
    }
}
