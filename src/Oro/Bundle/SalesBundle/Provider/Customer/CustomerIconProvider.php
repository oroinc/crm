<?php

namespace Oro\Bundle\SalesBundle\Provider\Customer;

use Doctrine\Common\Util\ClassUtils;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider as EntityConfigProvider;
use Oro\Bundle\UIBundle\Model\Image;

class CustomerIconProvider implements CustomerIconProviderInterface
{
    /** @var EntityConfigProvider */
    protected $configProvider;

    public function __construct(EntityConfigProvider $configProvider)
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
