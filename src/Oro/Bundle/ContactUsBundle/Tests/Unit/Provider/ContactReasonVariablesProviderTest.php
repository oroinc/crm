<?php

namespace Oro\Bundle\ContactUsBundle\Tests\Unit\Provider;

use Oro\Bundle\ContactUsBundle\Entity\ContactRequest;
use Oro\Bundle\ContactUsBundle\Provider\ContactReasonVariablesProvider;
use Oro\Bundle\TestFrameworkBundle\Entity\TestActivity;
use PHPUnit\Framework\TestCase;

class ContactReasonVariablesProviderTest extends TestCase
{
    private ContactReasonVariablesProvider $contactReasonVariablesProvider;

    #[\Override]
    protected function setUp(): void
    {
        $this->contactReasonVariablesProvider = new ContactReasonVariablesProvider();
    }

    public function testGetVariableDefinitions(): void
    {
        $this->assertEmpty($this->contactReasonVariablesProvider->getVariableDefinitions());
    }

    public function testGetVariableGetters(): void
    {
        $this->assertEquals(
            [
                ContactRequest::class => [
                    'preferredContactMethod' => [
                        'default_formatter' => [
                            'oro_translation.translator_formatter'
                        ],
                    ],
                    'contactReason' => [
                        'default_formatter' => [
                            'oro_locale.localized_fallback_value_formatter',
                            ['associationName' => 'titles']
                        ]
                    ]
                ]
            ],
            $this->contactReasonVariablesProvider->getVariableGetters()
        );
    }

    public function testGetVariableProcessors(): void
    {
        $this->assertEmpty($this->contactReasonVariablesProvider->getVariableProcessors(TestActivity::class));
    }
}
