<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\Provider;

use Oro\Bundle\SalesBundle\Entity\LeadMailboxProcessSettings;
use Oro\Bundle\SalesBundle\Form\Type\LeadMailboxProcessSettingsType;
use Oro\Bundle\SalesBundle\Provider\LeadMailboxProcessProvider;
use PHPUnit\Framework\TestCase;

class LeadMailboxProcessProviderTest extends TestCase
{
    private LeadMailboxProcessProvider $provider;

    #[\Override]
    protected function setUp(): void
    {
        $this->provider = new LeadMailboxProcessProvider();
    }

    public function testIsEnabled(): void
    {
        self::assertTrue($this->provider->isEnabled());
    }

    public function testGetProcessDefinitionName(): void
    {
        self::assertEquals(
            LeadMailboxProcessProvider::PROCESS_DEFINITION_NAME,
            $this->provider->getProcessDefinitionName()
        );
    }

    public function testGetSettingsEntityFQCN(): void
    {
        self::assertEquals(LeadMailboxProcessSettings::class, $this->provider->getSettingsEntityFQCN());
    }

    public function testGetSettingsFormType(): void
    {
        self::assertEquals(LeadMailboxProcessSettingsType::class, $this->provider->getSettingsFormType());
    }

    public function testGetLabel(): void
    {
        self::assertEquals('oro.sales.mailbox.process.lead.label', $this->provider->getLabel());
    }
}
