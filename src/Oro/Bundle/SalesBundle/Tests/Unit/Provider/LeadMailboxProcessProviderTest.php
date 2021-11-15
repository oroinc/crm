<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\Provider;

use Oro\Bundle\SalesBundle\Entity\LeadMailboxProcessSettings;
use Oro\Bundle\SalesBundle\Form\Type\LeadMailboxProcessSettingsType;
use Oro\Bundle\SalesBundle\Provider\LeadMailboxProcessProvider;

class LeadMailboxProcessProviderTest extends \PHPUnit\Framework\TestCase
{
    /** @var LeadMailboxProcessProvider */
    private $provider;

    protected function setUp(): void
    {
        $this->provider = new LeadMailboxProcessProvider();
    }

    public function testIsEnabled()
    {
        self::assertTrue($this->provider->isEnabled());
    }

    public function testGetProcessDefinitionName()
    {
        self::assertEquals(
            LeadMailboxProcessProvider::PROCESS_DEFINITION_NAME,
            $this->provider->getProcessDefinitionName()
        );
    }

    public function testGetSettingsEntityFQCN()
    {
        self::assertEquals(LeadMailboxProcessSettings::class, $this->provider->getSettingsEntityFQCN());
    }

    public function testGetSettingsFormType()
    {
        self::assertEquals(LeadMailboxProcessSettingsType::class, $this->provider->getSettingsFormType());
    }

    public function testGetLabel()
    {
        self::assertEquals('oro.sales.mailbox.process.lead.label', $this->provider->getLabel());
    }
}
