<?php

namespace Oro\Bundle\MagentoBundle\Utils;

use Oro\Bundle\IntegrationBundle\Utils\SecureErrorMessageHelper;
use Oro\Bundle\MagentoBundle\Entity\Order;

class ValidationUtils
{
    /**
     * Guess validation message prefix based on entity type
     *
     * @param object $entity
     *
     * @return string
     */
    public static function guessValidationMessagePrefix($entity)
    {
        $prefix = 'Validation error: ';
        if ($entity instanceof Order) {
            $prefix .= sprintf('Magento order #%s', $entity->getIncrementId());
        } elseif (method_exists($entity, 'getOriginId')) {
            $prefix .= sprintf('Magento entity ID %d', $entity->getOriginId());
        }

        return $prefix;
    }

    /**
     * Sanitise error message for secure info
     *
     * @param string
     *
     * @return string
     */
    public static function sanitizeSecureInfo($message)
    {
        return SecureErrorMessageHelper::sanitizeSecureInfo($message);
    }
}
