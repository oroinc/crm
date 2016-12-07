<?php

namespace Oro\Bundle\SalesBundle\Provider\Customer;

use Doctrine\Common\Util\ClassUtils;

use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;
use Oro\Bundle\UIBundle\Model\Image;

class CustomerIconProvider implements CustomerIconProviderInterface
{
    /** @var ConfigProvider */
    protected $configProvider;

    /**
     * @param ConfigProvider $configProvider
     */
    public function __construct(ConfigProvider $configProvider)
    {
        $this->configProvider = $configProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function getIcon($entity)
    {
        $entityClass = ClassUtils::getClass($entity);
        if (!$this->configProvider->hasConfig($entityClass)) {
            return null;
        }

        $icon = $this->configProvider->getConfig($entityClass)->get('icon');
        if (!$icon) {
            return null;
        }

        return new Image(Image::TYPE_ICON, ['class' => $icon]);
    }
}
