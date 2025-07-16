<?php

namespace Oro\Bundle\CaseBundle\Tests\Unit\Provider;

use Oro\Bundle\CaseBundle\Entity\CaseMailboxProcessSettings;
use Oro\Bundle\CaseBundle\Form\Type\CaseMailboxProcessSettingsType;
use Oro\Bundle\CaseBundle\Provider\CaseMailboxProcessProvider;
use PHPUnit\Framework\TestCase;

class CaseMailboxProcessProviderTest extends TestCase
{
    private CaseMailboxProcessProvider $provider;

    #[\Override]
    protected function setUp(): void
    {
        $this->provider = new CaseMailboxProcessProvider();
    }

    public function testIsEnabled(): void
    {
        self::assertTrue($this->provider->isEnabled());
    }

    public function testGetProcessDefinitionName(): void
    {
        self::assertEquals(
            CaseMailboxProcessProvider::PROCESS_DEFINITION_NAME,
            $this->provider->getProcessDefinitionName()
        );
    }

    public function testGetSettingsEntityFQCN(): void
    {
        self::assertEquals(CaseMailboxProcessSettings::class, $this->provider->getSettingsEntityFQCN());
    }

    public function testGetSettingsFormType(): void
    {
        self::assertEquals(CaseMailboxProcessSettingsType::class, $this->provider->getSettingsFormType());
    }

    public function testGetLabel(): void
    {
        self::assertEquals('oro.case.mailbox.process.case.label', $this->provider->getLabel());
    }
}
