<?php

namespace OroCRM\Bundle\MagentoBundle\Utils;

use OroCRM\Bundle\MagentoBundle\Entity\Order;

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
        if (is_string($message)) {
            return preg_replace('#(<apiKey.*?>)(.*)(</apiKey>)#i', '$1***$3', $message);
        }

        return $message;
    }
}
