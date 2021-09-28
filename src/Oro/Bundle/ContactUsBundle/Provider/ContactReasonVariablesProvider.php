<?php

namespace Oro\Bundle\ContactUsBundle\Provider;

use Oro\Bundle\ContactUsBundle\Entity\ContactRequest;
use Oro\Bundle\EntityBundle\Twig\Sandbox\EntityVariablesProviderInterface;

/**
 * Sets the default processor to a special field.
 */
class ContactReasonVariablesProvider implements EntityVariablesProviderInterface
{
    public function getVariableDefinitions(): array
    {
        return [];
    }

    public function getVariableGetters(): array
    {
        return [
            ContactRequest::class => [
                'preferredContactMethod' => [
                    'default_formatter' => [
                        'oro_translation.translator_formatter',
                    ],
                ],
                'contactReason' => [
                    'default_formatter' => [
                        'oro_locale.localized_fallback_value_formatter',
                        ['associationName' => 'titles']
                    ]
                ]
            ]
        ];
    }

    public function getVariableProcessors(string $entityClass): array
    {
        return [];
    }
}
