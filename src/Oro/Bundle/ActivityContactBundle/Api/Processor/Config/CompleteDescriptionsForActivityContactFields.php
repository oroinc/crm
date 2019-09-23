<?php

namespace Oro\Bundle\ActivityContactBundle\Api\Processor\Config;

use Oro\Bundle\ApiBundle\Processor\Config\ConfigContext;
use Oro\Bundle\ApiBundle\Util\FieldDescriptionUtil;
use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;

/**
 * Adds "read-only" hint to descriptions of "contacting activity" (ac_*) fields.
 */
class CompleteDescriptionsForActivityContactFields implements ProcessorInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContextInterface $context)
    {
        /** @var ConfigContext $context */

        $definition = $context->getResult();
        if (!$definition->isExcludeAll()) {
            // expected completed config
            return;
        }

        $targetAction = $context->getTargetAction();
        $fieldNames = [
            'lastContactedDate',
            'lastContactedDateIn',
            'lastContactedDateOut',
            'timesContacted',
            'timesContactedIn',
            'timesContactedOut'
        ];
        foreach ($fieldNames as $fieldName) {
            FieldDescriptionUtil::updateReadOnlyFieldDescription($definition, $fieldName, $targetAction);
        }
    }
}
